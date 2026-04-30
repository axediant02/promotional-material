<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import AssignmentChatWidget from '../../chat/components/AssignmentChatWidget.vue'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import ProductionSidebar from '../components/ProductionSidebar.vue'
import RequestDetailModal from '../components/RequestDetailModal.vue'
import ProductionTopbar from '../components/ProductionTopbar.vue'
import RequestStickyNote from '../components/RequestStickyNote.vue'
import { provideProductionWorkspace } from '../productionWorkspace'
import { fetchRecycleBin } from '../../../services/activityLogService'
import { fetchDashboard } from '../../../services/dashboardService'
import { downloadFile, fetchFiles, restoreFile, uploadFile } from '../../../services/fileService'
import { fetchFolders } from '../../../services/folderService'
import { fetchProductionRequests, updateProductionRequestStatus } from '../../../services/requestService'
import { useAuthStore } from '../../../stores/auth'
import { useNotificationStore } from '../../../stores/notifications'

const FILE_BROWSER_STORAGE_KEY = 'production_folder_view_mode'
const SIDEBAR_COLLAPSED_STORAGE_KEY = 'production_sidebar_collapsed'
const DUE_SOON_DAYS = 3
const FOLDER_FILTERS = ['all', 'needs_action', 'has_requests', 'recently_updated', 'empty']
const FOLDER_SORTS = ['recent', 'client_name', 'due_date', 'request_volume']
const SIDEBAR_EXPANDED_WIDTH = '18.5rem'
const SIDEBAR_COLLAPSED_WIDTH = '6.5rem'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const getSavedBrowserMode = () => {
  if (typeof window === 'undefined') {
    return 'grid'
  }

  const savedMode = window.localStorage.getItem(FILE_BROWSER_STORAGE_KEY)
  return savedMode === 'list' ? 'list' : 'grid'
}

const getSavedSidebarCollapsed = () => {
  if (typeof window === 'undefined') {
    return false
  }

  return window.localStorage.getItem(SIDEBAR_COLLAPSED_STORAGE_KEY) === 'true'
}

const normalizeBrowserMode = (value) => (value === 'list' ? 'list' : 'grid')
const normalizeFilter = (value) => (FOLDER_FILTERS.includes(value) ? value : 'all')
const normalizeSort = (value) => (FOLDER_SORTS.includes(value) ? value : 'recent')

const loading = ref(true)
const error = ref('')
const activeSection = ref('files')
const activeQueueFilter = ref('all')
const searchQuery = ref('')
const folderBrowserMode = ref(getSavedBrowserMode())
const folderBrowserFilter = ref('all')
const folderBrowserSort = ref('recent')
const sidebarCollapsed = ref(getSavedSidebarCollapsed())
const updatingRequestId = ref('')
const restoringFileId = ref('')
const downloadingFileId = ref('')
const syncingFolderQuery = ref(false)
const uploadingFileId = ref('')
const selectedOverviewRequestId = ref('')

const dashboardData = ref({
  user: null,
  stats: {
    folders: 0,
    files: 0,
  },
  folders: [],
  recentFiles: [],
})
const folders = ref([])
const productionRequests = ref([])
const files = ref([])
const recycleBinFiles = ref([])

const currentUser = computed(() => dashboardData.value.user ?? authStore.user ?? {})
const currentUserId = computed(() => currentUser.value?.user_id ?? currentUser.value?.id ?? '')
const isFolderRoute = computed(() => ['production-folder-index', 'production-folder-detail'].includes(route.name))

const sectionCounts = computed(() => ({
  files: folders.value.length,
  queue: productionRequests.value.length,
  recycle: recycleBinFiles.value.length,
}))

const currentSectionMeta = computed(() => {
  if (activeSection.value === 'files') {
    return {
      eyebrow: currentUser.value?.name ? `${currentUser.value.name} / production` : 'Production workspace',
      // title: 'Assigned folders',
      // description: 'Review assigned client workspaces, inspect request pressure, and open folder contents without leaving the production shell.',
    }
  }

  if (activeSection.value === 'recycle') {
    return {
      eyebrow: `${recycleBinFiles.value.length} recoverable files`,
      title: 'Recovery queue.',
      description: 'Restore recently deleted files that still belong to your assigned client workspaces.',
    }
  }

  return {
    eyebrow: `${productionRequests.value.length} requests in scope`,
    title: 'Assigned work.',
    description: 'Track request movement across your assigned folders and keep delivery statuses aligned with production progress.',
  }
})

const queueFilterMeta = [
  { id: 'all', label: 'All' },
  { id: 'pending', label: 'Pending' },
  { id: 'in_progress', label: 'In Progress' },
  { id: 'done', label: 'Done' },
]

const categoryToneLookup = {
  image: 'border-brand-300/20 bg-brand-50 text-brand-700 dark:bg-brand-300/10 dark:text-brand-100',
  video: 'border-brand-400/20 bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-100',
  pdf: 'border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-white/75',
}

const folderLookup = computed(() => {
  const map = new Map()

  for (const folder of folders.value) {
    map.set(folder.folder_id, folder)
  }

  for (const folder of dashboardData.value.folders ?? []) {
    const folderId = folder.folder_id ?? folder.id
    if (!map.has(folderId)) {
      map.set(folderId, folder)
    }
  }

  return map
})

const normalizeTimestamp = (value) => {
  if (!value) {
    return 0
  }

  return new Date(value).getTime()
}

const formatShortId = (value, prefix = 'REQ') => {
  if (!value) {
    return prefix
  }

  return `${prefix}-${value.slice(0, 4).toUpperCase()}`
}

const formatDateLabel = (value) => {
  if (!value) {
    return 'No date set'
  }

  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const formatRequestType = (value) => {
  if (value === 'update_asset') {
    return 'Update asset'
  }

  return 'New asset'
}

const getDueSoonState = (value) => {
  if (!value) {
    return { isDueSoon: false, isOverdue: false }
  }

  const dueAt = normalizeTimestamp(value)
  const now = Date.now()
  const diff = dueAt - now
  const threshold = DUE_SOON_DAYS * 24 * 60 * 60 * 1000

  return {
    isDueSoon: diff >= 0 && diff <= threshold,
    isOverdue: diff < 0,
  }
}

const getPriorityState = (value) => {
  const { isDueSoon, isOverdue } = getDueSoonState(value)

  return {
    isHighPriority: isDueSoon || isOverdue,
    priorityLabel: isDueSoon || isOverdue ? 'High Priority' : '',
  }
}

const getFileFolderId = (file) => file.folder?.folder_id ?? file.folder_id ?? null
const getFileFolderName = (file) =>
  file.folder?.folder_name ?? folderLookup.value.get(getFileFolderId(file))?.folder_name ?? 'Workspace'
const getFileClientName = (file) =>
  folderLookup.value.get(getFileFolderId(file))?.client?.name ?? 'Assigned client'
const getFileUploaderName = (file) => file.uploader?.name ?? currentUser.value?.name ?? 'Uploader'

const queueRows = computed(() =>
  productionRequests.value.map((request) => {
    const folder = folderLookup.value.get(request.folder_id)
    const relatedFiles = files.value.filter((file) => getFileFolderId(file) === request.folder_id)
    const { isHighPriority, priorityLabel } = getPriorityState(request.due_date)

    return {
      id: request.request_id,
      folderId: request.folder_id ?? '',
      reference: formatShortId(request.request_id),
      title: request.title ?? 'Untitled request',
      description: request.description ?? 'No request description provided.',
      sortTimestamp: normalizeTimestamp(request.updated_at ?? request.created_at ?? request.due_date ?? ''),
      clientName: folder?.client?.name ?? 'Assigned client',
      workspace: folder?.folder_name ?? 'Assigned workspace',
      requestType: formatRequestType(request.request_type),
      status: request.status ?? 'pending',
      statusTone: request.status ?? 'pending',
      statusLabel: (request.status ?? 'pending').replaceAll('_', ' '),
      dueLabel: request.due_date ? `Due ${formatDateLabel(request.due_date)}` : 'No due date set',
      isHighPriority,
      priorityLabel,
      fileCount: relatedFiles.length,
      fileNames: relatedFiles.slice(0, 3).map((file) => file.file_name),
    }
  })
)

const filteredQueueRows = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return queueRows.value.filter((row) => {
    const matchesFilter = activeQueueFilter.value === 'all' || row.status === activeQueueFilter.value

    if (!matchesFilter) {
      return false
    }

    if (!query) {
      return true
    }

    return `${row.reference} ${row.title} ${row.description} ${row.clientName} ${row.workspace}`
      .toLowerCase()
      .includes(query)
  })
})

const folderWorkspaceRows = computed(() =>
  folders.value.map((folder) => {
    const folderFiles = files.value
      .filter((file) => getFileFolderId(file) === folder.folder_id)
      .sort((left, right) => normalizeTimestamp(right.updated_at) - normalizeTimestamp(left.updated_at))

    const folderRequests = productionRequests.value
      .filter((request) => request.folder_id === folder.folder_id)
      .sort((left, right) => normalizeTimestamp(left.due_date ?? left.updated_at ?? left.created_at) - normalizeTimestamp(right.due_date ?? right.updated_at ?? right.created_at))

    const activeRequestCount = folderRequests.filter((request) => request.status !== 'done').length
    const dueSoonRequests = folderRequests.filter((request) => {
      const { isDueSoon, isOverdue } = getDueSoonState(request.due_date)
      return request.status !== 'done' && (isDueSoon || isOverdue)
    })

    const latestFileAt = folderFiles[0]?.updated_at ?? ''
    const latestRequestAt = folderRequests.reduce((latest, request) => {
      const candidate = request.updated_at ?? request.created_at ?? request.due_date ?? ''
      return normalizeTimestamp(candidate) > normalizeTimestamp(latest) ? candidate : latest
    }, '')
    const latestActivityAt = normalizeTimestamp(latestFileAt) >= normalizeTimestamp(latestRequestAt) ? latestFileAt : latestRequestAt

    let statusTone = 'ready'
    let statusLabel = 'Ready'

    if (!folderFiles.length && !folderRequests.length) {
      statusTone = 'empty'
      statusLabel = 'Empty'
    } else if (dueSoonRequests.length) {
      statusTone = 'needs_action'
      statusLabel = 'Needs action'
    } else if (activeRequestCount) {
      statusTone = 'in_progress'
      statusLabel = 'In progress'
    }

    const nextDueDate = folderRequests
      .filter((request) => request.status !== 'done' && request.due_date)
      .sort((left, right) => normalizeTimestamp(left.due_date) - normalizeTimestamp(right.due_date))[0]?.due_date

    return {
      id: folder.folder_id,
      clientId: folder.client?.user_id ?? folder.client_id ?? '',
      clientName: folder.client?.name ?? 'Assigned client',
      email: folder.client?.email ?? '',
      workspace: folder.folder_name ?? 'Assigned workspace',
      requestCount: folderRequests.length,
      activeRequestCount,
      fileCount: folderFiles.length,
      latestActivityAt,
      latestActivityLabel: latestActivityAt ? `Updated ${formatDateLabel(latestActivityAt)}` : 'No activity yet',
      dueSoonCount: dueSoonRequests.length,
      dueDate: nextDueDate ?? '',
      dueDateLabel: nextDueDate ? `Next due ${formatDateLabel(nextDueDate)}` : 'No due date set',
      statusTone,
      statusLabel,
      fileNamesSearch: folderFiles.map((file) => file.file_name).join(' '),
    }
  })
)

const selectedFolder = computed(() => {
  const folderId = typeof route.params.folderId === 'string' ? route.params.folderId : ''
  return folderWorkspaceRows.value.find((folder) => folder.id === folderId) ?? null
})

const selectedFolderRequests = computed(() => {
  if (!selectedFolder.value) {
    return []
  }

  return queueRows.value.filter((request) => {
    const source = productionRequests.value.find((item) => item.request_id === request.id)
    return source?.folder_id === selectedFolder.value.id
  })
})

const selectedFolderFiles = computed(() => {
  if (!selectedFolder.value) {
    return []
  }

  return files.value
    .filter((file) => getFileFolderId(file) === selectedFolder.value.id)
    .sort((left, right) => normalizeTimestamp(right.updated_at) - normalizeTimestamp(left.updated_at))
    .map((file) => ({
      ...file,
      shortId: formatShortId(file.file_id, 'FILE'),
      uploaderName: getFileUploaderName(file),
      updatedLabel: formatDateLabel(file.updated_at),
      folderName: getFileFolderName(file),
    }))
})

const workspaceSummaryStats = computed(() => {
  const openRequests = productionRequests.value.filter((request) => request.status !== 'done').length
  const dueSoon = productionRequests.value.filter((request) => {
    const { isDueSoon, isOverdue } = getDueSoonState(request.due_date)
    return request.status !== 'done' && (isDueSoon || isOverdue)
  }).length

  return [
    {
      id: 'assigned_folders',
      label: 'Assigned folders',
      value: folderWorkspaceRows.value.length,
      detail: 'Visible client workspaces',
      accent: 'from-brand-500/16 via-brand-500/8 to-transparent',
      chipTone: 'border-brand-300/40 bg-brand-50 text-brand-700 dark:border-white/10 dark:bg-white/10 dark:text-brand-100',
      numberTone: 'text-brand-700 dark:text-brand-100',
    },
    {
      id: 'open_requests',
      label: 'Open requests',
      value: openRequests,
      detail: `${productionRequests.value.length} total tracked`,
      accent: 'from-amber-500/16 via-amber-500/8 to-transparent',
      chipTone: 'border-amber-300/40 bg-amber-50 text-amber-900 dark:border-white/10 dark:bg-white/10 dark:text-amber-100',
      numberTone: 'text-amber-900 dark:text-amber-100',
    },
    {
      id: 'files_in_scope',
      label: 'Files in scope',
      value: files.value.length,
      detail: 'Accessible production assets',
      accent: 'from-slate-400/14 via-white/10 to-transparent',
      chipTone: 'border-border/80 bg-white/80 text-muted dark:border-white/10 dark:bg-white/10 dark:text-zinc-300',
      numberTone: 'text-ink dark:text-white',
    },
    {
      id: 'due_soon',
      label: 'High Priority',
      value: dueSoon,
      detail: 'Active requests nearing deadline',
      accent: 'from-orange-400/16 via-orange-400/8 to-transparent',
      chipTone: 'border-orange-300/40 bg-orange-50 text-orange-900 dark:border-white/10 dark:bg-white/10 dark:text-orange-100',
      numberTone: 'text-orange-900 dark:text-orange-100',
    },
  ]
})

const visibleFolderRows = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  const now = Date.now()
  const recentThreshold = now - 7 * 24 * 60 * 60 * 1000

  return folderWorkspaceRows.value
    .filter((folder) => {
      if (query) {
        const haystack = `${folder.clientName} ${folder.workspace} ${folder.email} ${folder.fileNamesSearch}`.toLowerCase()
        if (!haystack.includes(query)) {
          return false
        }
      }

      if (folderBrowserFilter.value === 'needs_action') {
        return folder.statusTone === 'needs_action'
      }

      if (folderBrowserFilter.value === 'has_requests') {
        return folder.requestCount > 0
      }

      if (folderBrowserFilter.value === 'recently_updated') {
        return folder.latestActivityAt && normalizeTimestamp(folder.latestActivityAt) >= recentThreshold
      }

      if (folderBrowserFilter.value === 'empty') {
        return folder.statusTone === 'empty'
      }

      return true
    })
    .slice()
    .sort((left, right) => {
      if (folderBrowserSort.value === 'client_name') {
        return left.clientName.localeCompare(right.clientName)
      }

      if (folderBrowserSort.value === 'due_date') {
        const leftDue = left.dueDate ? normalizeTimestamp(left.dueDate) : Number.MAX_SAFE_INTEGER
        const rightDue = right.dueDate ? normalizeTimestamp(right.dueDate) : Number.MAX_SAFE_INTEGER
        return leftDue - rightDue
      }

      if (folderBrowserSort.value === 'request_volume') {
        return right.requestCount - left.requestCount || right.activeRequestCount - left.activeRequestCount
      }

      return normalizeTimestamp(right.latestActivityAt) - normalizeTimestamp(left.latestActivityAt)
    })
})

const fileCategoryStats = computed(() =>
  ['image', 'video', 'pdf'].map((category) => ({
    id: category,
    label: category.toUpperCase(),
    value: files.value.filter((file) => file.category === category).length,
  }))
)

const recentActivityFiles = computed(() =>
  (dashboardData.value.recentFiles ?? []).map((file) => ({
    id: file.file_id,
    name: file.file_name,
    folderName: file.folder?.folder_name ?? folderLookup.value.get(file.folder_id)?.folder_name ?? 'Workspace',
    updatedLabel: formatDateLabel(file.updated_at),
    category: file.category ?? 'asset',
  }))
)

const latestAssignedRequests = computed(() =>
  queueRows.value
    .slice()
    .sort((left, right) => right.sortTimestamp - left.sortTimestamp)
)

const selectedOverviewRequest = computed(() =>
  latestAssignedRequests.value.find((request) => request.id === selectedOverviewRequestId.value) ?? null
)

const filteredRecycleBinFiles = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return recycleBinFiles.value.filter((file) => {
    if (!query) {
      return true
    }

    return [
      file.file_name,
      file.folder?.folder_name,
      file.uploader?.name,
      folderLookup.value.get(file.folder?.folder_id ?? file.folder_id)?.client?.name,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(query)
  })
})

const buildFolderQuery = () => {
  const query = {}

  if (folderBrowserMode.value) {
    query.view = folderBrowserMode.value
  }

  if (folderBrowserFilter.value !== 'all') {
    query.filter = folderBrowserFilter.value
  }

  if (folderBrowserSort.value !== 'recent') {
    query.sort = folderBrowserSort.value
  }

  if (searchQuery.value.trim()) {
    query.q = searchQuery.value.trim()
  }

  return query
}

const syncFolderStateFromRoute = () => {
  if (!isFolderRoute.value) {
    return
  }

  syncingFolderQuery.value = true
  folderBrowserMode.value = normalizeBrowserMode(route.query.view ?? getSavedBrowserMode())
  folderBrowserFilter.value = normalizeFilter(route.query.filter)
  folderBrowserSort.value = normalizeSort(route.query.sort)
  searchQuery.value = typeof route.query.q === 'string' ? route.query.q : ''
  syncingFolderQuery.value = false

  if (!route.query.view) {
    router.replace({
      name: route.name,
      params: route.params,
      query: buildFolderQuery(),
    })
  }
}

watch(
  () => [route.name, route.query.view, route.query.filter, route.query.sort, route.query.q],
  () => {
    if (isFolderRoute.value) {
      activeSection.value = 'files'
      syncFolderStateFromRoute()
    }
  },
  { immediate: true }
)

watch(folderBrowserMode, (value) => {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(FILE_BROWSER_STORAGE_KEY, value)
})

watch(sidebarCollapsed, (value) => {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(SIDEBAR_COLLAPSED_STORAGE_KEY, String(value))
})

watch(searchQuery, (value) => {
  if (syncingFolderQuery.value || activeSection.value !== 'files' || !isFolderRoute.value) {
    return
  }

  const nextQuery = { ...buildFolderQuery(), q: value.trim() || undefined }
  if (!nextQuery.q) {
    delete nextQuery.q
  }

  router.replace({ name: route.name, params: route.params, query: nextQuery })
})

const setFolderBrowserMode = (value) => {
  folderBrowserMode.value = normalizeBrowserMode(value)

  if (isFolderRoute.value) {
    router.replace({ name: route.name, params: route.params, query: buildFolderQuery() })
  }
}

const setFolderBrowserFilter = (value) => {
  folderBrowserFilter.value = normalizeFilter(value)

  if (isFolderRoute.value) {
    router.replace({ name: route.name, params: route.params, query: buildFolderQuery() })
  }
}

const setFolderBrowserSort = (value) => {
  folderBrowserSort.value = normalizeSort(value)

  if (isFolderRoute.value) {
    router.replace({ name: route.name, params: route.params, query: buildFolderQuery() })
  }
}

const openFolder = (folderId) =>
  router.push({
    name: 'production-folder-detail',
    params: { folderId },
    query: buildFolderQuery(),
  })

const goToFolderIndex = () =>
  router.push({
    name: 'production-folder-index',
    query: buildFolderQuery(),
  })

const restoreRecycleFile = async (fileId) => {
  restoringFileId.value = fileId
  error.value = ''

  try {
    const response = await restoreFile(fileId)
    const restoredFile = response.data.data.file

    recycleBinFiles.value = recycleBinFiles.value.filter((file) => (file.file_id ?? file.id) !== fileId)
    files.value = [restoredFile, ...files.value]
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to restore the file.'
  } finally {
    restoringFileId.value = ''
  }
}

const handleDownloadFile = async (file) => {
  downloadingFileId.value = file.file_id
  error.value = ''

  try {
    await downloadFile(file)
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to download the file.'
  } finally {
    downloadingFileId.value = ''
  }
}

const updateRequestStatus = async (requestId, status) => {
  if (!status) {
    return
  }

  updatingRequestId.value = requestId
  error.value = ''

  try {
    const response = await updateProductionRequestStatus(requestId, { status })
    const updatedRequest = response.data.data.request

    productionRequests.value = productionRequests.value.map((request) =>
      request.request_id === requestId ? updatedRequest : request
    )
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to update request status.'
  } finally {
    updatingRequestId.value = ''
  }
}

const signOut = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}

const handleSectionChange = (section) => {
  activeSection.value = section

  if (section === 'files') {
    goToFolderIndex()
  }
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const openLatestRequest = () => {
  activeSection.value = 'queue'
  router.push({ name: 'production-dashboard' })
}

const openOverviewRequest = (request) => {
  selectedOverviewRequestId.value = request.id
}

const closeOverviewRequest = () => {
  selectedOverviewRequestId.value = ''
}

const viewOverviewRequestFolder = (folderId) => {
  if (!folderId) {
    return
  }

  closeOverviewRequest()
  activeSection.value = 'files'
  openFolder(folderId)
}

const handleUploadFile = async (file, folderId) => {
  uploadingFileId.value = folderId
  error.value = ''

  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('folder_id', folderId)

    const response = await uploadFile(formData)
    const newFile = response.data.data.file

    const enrichedFile = {
      ...newFile,
      shortId: formatShortId(newFile.file_id, 'FILE'),
      uploaderName: currentUser.value?.name ?? 'You',
      updatedLabel: formatDateLabel(newFile.updated_at),
      folderName: newFile.folder?.folder_name ?? 'Workspace',
    }

    files.value = [enrichedFile, ...files.value]
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to upload the file.'
  } finally {
    uploadingFileId.value = ''
  }
}

const loadData = async () => {
  loading.value = true
  error.value = ''

  try {
    const [dashboardResponse, folderResponse, requestsResponse, filesResponse, recycleResponse] = await Promise.all([
      fetchDashboard(),
      fetchFolders(),
      fetchProductionRequests(),
      fetchFiles(),
      fetchRecycleBin(),
    ])

    dashboardData.value = dashboardResponse.data.data
    folders.value = folderResponse.data.data.folders ?? []
    productionRequests.value = requestsResponse.data.data.requests ?? []
    files.value = filesResponse.data.data.files ?? []
    recycleBinFiles.value = recycleResponse.data.data.files ?? []
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to load the production dashboard.'
  } finally {
    loading.value = false
  }
}

provideProductionWorkspace({
  loading,
  currentUser,
  visibleFolderRows,
  folderBrowserMode,
  folderBrowserFilter,
  folderBrowserSort,
  selectedFolder,
  selectedFolderFiles,
  selectedFolderRequests,
  downloadingFileId,
  uploadingFileId,
  updatingRequestId,
  categoryToneLookup,
  setFolderBrowserMode,
  setFolderBrowserFilter,
  setFolderBrowserSort,
  openFolder,
  goToFolderIndex,
  handleDownloadFile,
  handleUploadFile,
  updateRequestStatus,
})

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="pm-page min-h-screen text-ink dark:text-white">
    <div
      class="min-h-screen xl:grid"
      :style="{ '--production-sidebar-width': sidebarCollapsed ? SIDEBAR_COLLAPSED_WIDTH : SIDEBAR_EXPANDED_WIDTH }"
      :class="'xl:grid-cols-[var(--production-sidebar-width)_minmax(0,1fr)]'"
    >
      <ProductionSidebar
        :current-user="currentUser"
        :active-section="activeSection"
        :section-counts="sectionCounts"
        :collapsed="sidebarCollapsed"
        @change-section="handleSectionChange"
        @toggle-collapse="toggleSidebar"
        @sign-out="signOut"
      />

      <main class="relative min-w-0">
        <ProductionTopbar
          v-model:search-query="searchQuery"
          :current-user="currentUser"
          :title="currentSectionMeta.title"
          :eyebrow="currentSectionMeta.eyebrow"
          :description="currentSectionMeta.description"
          :notifications="notificationStore.notifications"
          :notifications-loading="notificationStore.loading"
          :unread-count="notificationStore.unreadCount"
          :mark-read-action="notificationStore.markAsRead"
          :mark-all-read-action="notificationStore.markAllAsRead"
        />

        <div class="px-6 py-8 sm:px-8 lg:px-10">
          <p v-if="error" class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
            {{ error }}
          </p>

          <DashboardOverviewSkeleton v-if="loading" />

          <template v-else>
            <section v-if="activeSection === 'files'" class="space-y-8">
              <RequestStickyNote
                :requests="latestAssignedRequests"
                @open-queue="openLatestRequest"
                @open-request="openOverviewRequest"
              />

              <RouterView />
            </section>

            <section v-else-if="activeSection === 'queue'" class="space-y-8">
              <section class="grid gap-4 xl:grid-cols-4">
                <article
                  v-for="stat in workspaceSummaryStats"
                  :key="stat.id"
                  class="pm-surface relative overflow-hidden rounded-[1.8rem] border border-border/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(249,244,252,0.96))] px-5 py-5 transition hover:-translate-y-[1px] hover:shadow-[var(--shadow-md)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.04))]"
                >
                  <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r opacity-90" :class="stat.accent" aria-hidden="true" />
                  <div class="absolute right-5 top-5 h-10 w-10 rounded-full border border-white/60 bg-white/70 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] dark:border-white/10 dark:bg-white/10" aria-hidden="true" />

                  <div class="relative">
                    <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]" :class="stat.chipTone">
                      Live
                    </div>
                    <p class="mt-4 text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
                    <p :class="['mt-3 text-4xl leading-none font-semibold tracking-[-0.04em]', stat.numberTone]">
                      {{ stat.value }}
                    </p>
                    <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ stat.detail }}</p>
                  </div>
                </article>
              </section>

              <section class="grid gap-8 xl:grid-cols-[minmax(0,1.45fr)_22rem]">
                <div class="space-y-6">
                  <section class="flex flex-wrap gap-3">
                    <button
                      v-for="filter in queueFilterMeta"
                      :key="filter.id"
                      :class="[
                        'rounded-full border px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] transition',
                        activeQueueFilter === filter.id
                          ? 'border-brand-500 bg-brand-100 text-brand-700 dark:border-white/20 dark:bg-white/10 dark:text-white'
                          : 'border-border bg-white/70 text-muted hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white',
                      ]"
                      @click="activeQueueFilter = filter.id"
                    >
                      {{ filter.label }}
                    </button>
                  </section>

                  <section class="space-y-3">
                    <article
                      v-for="row in filteredQueueRows"
                      :key="row.id"
                      class="pm-surface rounded-[1.8rem] px-5 py-5 transition hover:border-brand-500 sm:px-6"
                    >
                      <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0">
                          <div class="flex flex-wrap items-center gap-3">
                            <span class="text-[11px] uppercase tracking-[0.26em] text-muted dark:text-zinc-400">{{ row.reference }}</span>
                            <span class="inline-flex items-center rounded-full border border-brand-300/20 bg-brand-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 dark:bg-white/10 dark:text-white">
                              {{ row.statusLabel }}
                            </span>
                            <span class="inline-flex items-center rounded-full border border-border bg-white/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                              {{ row.requestType }}
                            </span>
                          </div>

                          <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-ink dark:text-white ">
                            {{ row.title }}
                          </h2>
                          <p class="mt-3 max-w-3xl text-sm leading-7 text-muted dark:text-zinc-300">
                            {{ row.description }}
                          </p>

                          <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] text-muted dark:text-zinc-400">
                            <span>{{ row.clientName }}</span>
                            <span>/</span>
                            <span>{{ row.workspace }}</span>
                            <span>{{ row.fileCount }} files in workspace</span>
                            <span>{{ row.dueLabel }}</span>
                          </div>

                          <div v-if="row.fileNames.length" class="mt-4 flex flex-wrap gap-2">
                            <span
                              v-for="fileName in row.fileNames"
                              :key="fileName"
                              class="rounded-full border border-border bg-white/70 px-3 py-1 text-[11px] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
                            >
                              {{ fileName }}
                            </span>
                          </div>
                        </div>

                        <div class="w-full xl:w-[180px]">
                          <select
                            class="pm-input w-full rounded-2xl px-4 py-3 text-[12px] font-semibold uppercase tracking-[0.22em]"
                            :disabled="updatingRequestId === row.id"
                            :value="row.status"
                            @change="updateRequestStatus(row.id, $event.target.value)"
                          >
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                          </select>
                        </div>
                      </div>
                    </article>

                    <article
                      v-if="!filteredQueueRows.length"
                      class="pm-surface rounded-[1.8rem] border-dashed px-6 py-10 text-center"
                    >
                      <p class="text-[10px] uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">Queue clear</p>
                      <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white ">
                        No assigned requests match the current filter.
                      </h2>
                    </article>
                  </section>
                </div>

                <aside class="space-y-4">
                  <section class="pm-surface rounded-[1.8rem] p-5">
                    <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Recent file activity</p>
                    <div class="mt-5 space-y-3">
                      <article
                        v-for="file in recentActivityFiles"
                        :key="file.id"
                        class="flex items-start justify-between gap-3 border-b border-border/70 pb-3 last:border-b-0 last:pb-0 dark:border-white/10"
                      >
                        <div class="min-w-0">
                          <p class="truncate text-sm font-medium text-ink dark:text-white">{{ file.name }}</p>
                          <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ file.folderName }}</p>
                        </div>
                        <div class="text-right">
                          <p class="text-[10px] uppercase tracking-[0.22em] text-brand-600 dark:text-brand-100">{{ file.category }}</p>
                          <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ file.updatedLabel }}</p>
                        </div>
                      </article>
                      <p v-if="!recentActivityFiles.length" class="text-sm text-muted dark:text-zinc-300">
                        No recent file activity is available for your current scope.
                      </p>
                    </div>
                  </section>

                  <section class="pm-surface rounded-[1.8rem] p-5">
                    <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Category coverage</p>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                      <div v-for="stat in fileCategoryStats" :key="stat.id" class="rounded-2xl border border-border bg-white/60 px-3 py-3 text-center dark:border-white/10 dark:bg-black/10">
                        <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
                        <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ stat.value }}</p>
                      </div>
                    </div>
                  </section>
                </aside>
              </section>
            </section>

            <section v-else class="space-y-6">
              <section class="grid gap-4 xl:grid-cols-3">
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Deleted files</p>
                  <p class="mt-4 text-4xl leading-none text-brand-700 dark:text-white ">
                    {{ recycleBinFiles.length }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">In assigned recycle scope</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Recoverable clients</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white ">
                    {{ new Set(recycleBinFiles.map((file) => file.folder?.folder_id ?? file.folder_id)).size }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">Workspaces with deleted assets</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Live library</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white ">
                    {{ files.length }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">Files still active outside recycle bin</p>
                </article>
              </section>

              <section class="space-y-3">
                <article
                  v-for="file in filteredRecycleBinFiles"
                  :key="file.file_id"
                  class="pm-surface rounded-[1.8rem] px-5 py-5 transition hover:border-brand-500"
                >
                  <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0">
                      <p class="text-[11px] uppercase tracking-[0.26em] text-muted dark:text-zinc-400">{{ formatShortId(file.file_id, 'FILE') }}</p>
                      <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white ">
                        {{ file.file_name }}
                      </h2>
                      <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] text-muted dark:text-zinc-400">
                        <span>{{ folderLookup.get(file.folder?.folder_id ?? file.folder_id)?.client?.name ?? 'Assigned client' }}</span>
                        <span>{{ file.folder?.folder_name ?? 'Unknown folder' }}</span>
                        <span>{{ file.uploader?.name ?? 'Unknown uploader' }}</span>
                        <span>deleted {{ formatDateLabel(file.deleted_at ?? file.updated_at) }}</span>
                      </div>
                    </div>

                    <button
                      class="pm-gradient-primary rounded-2xl px-4 py-3 text-[12px] font-semibold uppercase tracking-[0.22em] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="restoringFileId === file.file_id"
                      @click="restoreRecycleFile(file.file_id)"
                    >
                      {{ restoringFileId === file.file_id ? 'Restoring...' : 'Restore file' }}
                    </button>
                  </div>
                </article>

                <article
                  v-if="!filteredRecycleBinFiles.length"
                  class="pm-surface rounded-[1.8rem] border-dashed px-6 py-10 text-center"
                >
                  <p class="text-[10px] uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">Recycle bin clear</p>
                  <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white ">
                    No assigned deleted files match the current search.
                  </h2>
                </article>
              </section>
            </section>
          </template>
        </div>
      </main>
    </div>

    <AssignmentChatWidget
      :current-user-id="currentUserId"
      title="Messages"
    />

    <RequestDetailModal
      :open="Boolean(selectedOverviewRequest)"
      :request="selectedOverviewRequest"
      :updating-request-id="updatingRequestId"
      @close="closeOverviewRequest"
      @update-status="updateRequestStatus"
      @view-folder="viewOverviewRequestFolder"
    />
  </div>
</template>

