import { computed, ref, watch } from 'vue'
import { fetchAgentWorkspace } from '../../../services/agentWorkspaceService'
import {
  FOLDER_FILTERS,
  FOLDER_SORTS,
  formatDateLabel,
  formatShortId,
  getFileCategory,
  getFileFolderId,
  getFileId,
  getFileName,
  getFileUpdatedAt,
  getFileUploaderName,
  getFolderClientEmail,
  getFolderClientName,
  getFolderId,
  getFolderName,
  normalizeFilter,
  normalizeSort,
  normalizeTimestamp,
  normalizeViewMode,
} from '../utils/agentDashboardHelpers'

const VIEW_STORAGE_KEY = 'agent_file_browser_view_mode'

const getSavedBrowserMode = () => {
  if (typeof window === 'undefined') {
    return 'grid'
  }

  return normalizeViewMode(window.localStorage.getItem(VIEW_STORAGE_KEY))
}

export const useAgentDashboardData = () => {
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
  const files = ref([])
  const loading = ref(true)
  const error = ref('')
  const searchQuery = ref('')
  const folderBrowserMode = ref(getSavedBrowserMode())
  const folderBrowserFilter = ref('all')
  const folderBrowserSort = ref('recent')
  const selectedFolderId = ref('')
  const activeView = ref('folders')

  const folderLookup = computed(() => {
    const map = new Map()

    for (const folder of folders.value) {
      const folderId = getFolderId(folder)
      if (folderId) {
        map.set(folderId, folder)
      }
    }

    for (const folder of dashboardData.value.folders ?? []) {
      const folderId = getFolderId(folder)
      if (folderId && !map.has(folderId)) {
        map.set(folderId, folder)
      }
    }

    return map
  })

  const allFiles = computed(() => {
    const map = new Map()

    for (const file of [...files.value, ...(dashboardData.value.recentFiles ?? [])]) {
      const fileId = getFileId(file)
      if (fileId && !map.has(fileId)) {
        map.set(fileId, file)
      }
    }

    return [...map.values()]
  })

  const folderRows = computed(() => {
    const rows = [...folderLookup.value.entries()].map(([folderId, folder]) => {
      const folderFiles = allFiles.value
        .filter((file) => getFileFolderId(file) === folderId)
        .sort((left, right) => normalizeTimestamp(getFileUpdatedAt(right)) - normalizeTimestamp(getFileUpdatedAt(left)))
      const latestFile = folderFiles[0]
      const latestActivityAt = getFileUpdatedAt(latestFile) || folder.updated_at || folder.created_at || ''

      return {
        id: folderId,
        workspace: getFolderName(folder),
        clientName: getFolderClientName(folder),
        email: getFolderClientEmail(folder),
        fileCount: folderFiles.length,
        latestActivityAt,
        latestActivityLabel: latestActivityAt ? `Updated ${formatDateLabel(latestActivityAt)}` : 'No file activity yet',
        statusTone: folderFiles.length ? 'ready' : 'empty',
        statusLabel: folderFiles.length ? 'Available' : 'Empty',
        fileNamesSearch: folderFiles.map((file) => getFileName(file)).join(' '),
      }
    })

    return rows
  })

  const visibleFolderRows = computed(() => {
    const query = searchQuery.value.trim().toLowerCase()
    const recentThreshold = Date.now() - 7 * 24 * 60 * 60 * 1000

    return folderRows.value
      .filter((folder) => {
        if (query) {
          const haystack = `${folder.clientName} ${folder.workspace} ${folder.email} ${folder.fileNamesSearch}`.toLowerCase()
          if (!haystack.includes(query)) {
            return false
          }
        }

        if (folderBrowserFilter.value === 'recently_updated') {
          return folder.latestActivityAt && normalizeTimestamp(folder.latestActivityAt) >= recentThreshold
        }

        if (folderBrowserFilter.value === 'has_files') {
          return folder.fileCount > 0
        }

        if (folderBrowserFilter.value === 'empty') {
          return folder.fileCount === 0
        }

        return true
      })
      .slice()
      .sort((left, right) => {
        if (folderBrowserSort.value === 'client_name') {
          return left.clientName.localeCompare(right.clientName)
        }

        if (folderBrowserSort.value === 'file_volume') {
          return right.fileCount - left.fileCount || left.clientName.localeCompare(right.clientName)
        }

        return normalizeTimestamp(right.latestActivityAt) - normalizeTimestamp(left.latestActivityAt)
      })
  })

  const selectedFolder = computed(() =>
    folderRows.value.find((folder) => folder.id === selectedFolderId.value) ?? visibleFolderRows.value[0] ?? null
  )

  const selectedFolderFiles = computed(() => {
    if (!selectedFolder.value) {
      return []
    }

    return allFiles.value
      .filter((file) => getFileFolderId(file) === selectedFolder.value.id)
      .sort((left, right) => normalizeTimestamp(getFileUpdatedAt(right)) - normalizeTimestamp(getFileUpdatedAt(left)))
      .map((file) => ({
        ...file,
        file_id: getFileId(file),
        file_name: getFileName(file),
        category: getFileCategory(file),
        shortId: formatShortId(getFileId(file)),
        uploaderName: getFileUploaderName(file),
        updatedLabel: formatDateLabel(getFileUpdatedAt(file)),
        folderName: selectedFolder.value.workspace,
      }))
  })

  const recentFiles = computed(() =>
    allFiles.value
      .slice()
      .sort((left, right) => normalizeTimestamp(getFileUpdatedAt(right)) - normalizeTimestamp(getFileUpdatedAt(left)))
      .slice(0, 6)
      .map((file) => {
        const folder = folderLookup.value.get(getFileFolderId(file))

        return {
          id: getFileId(file),
          name: getFileName(file),
          category: getFileCategory(file),
          folderName: getFolderName(folder),
          clientName: getFolderClientName(folder),
          updatedLabel: formatDateLabel(getFileUpdatedAt(file)),
        }
      })
  )

  const workspaceSummaryStats = computed(() => [
    {
      id: 'folders',
      label: 'Client folders',
      value: folderRows.value.length || dashboardData.value.stats.folders || 0,
      detail: 'Download-access workspaces',
    },
    {
      id: 'files',
      label: 'Files visible',
      value: allFiles.value.length || dashboardData.value.stats.files || 0,
      detail: 'Materials available to agents',
    },
    {
      id: 'recent',
      label: 'Recently updated',
      value: folderRows.value.filter((folder) => folder.latestActivityAt).length,
      detail: 'Folders with file activity',
    },
    {
      id: 'mode',
      label: 'Access mode',
      value: 'Read',
      detail: 'Browse and download only',
    },
  ])

  const categoryStats = computed(() =>
    ['image', 'video', 'pdf'].map((category) => ({
      id: category,
      label: category.toUpperCase(),
      value: allFiles.value.filter((file) => getFileCategory(file) === category).length,
    }))
  )

  const currentUser = computed(() => dashboardData.value.user ?? null)

  const sectionCounts = computed(() => ({
    folders: folderRows.value.length,
    folder: selectedFolderFiles.value.length,
    recent: recentFiles.value.length,
  }))

  const activeViewMeta = computed(() => {
    if (activeView.value === 'folder') {
      return {
        eyebrow: selectedFolder.value?.clientName ?? 'Client folder',
        title: selectedFolder.value?.workspace ?? 'Folder contents',
        description: 'View and download the client files available inside this folder.',
      }
    }

    if (activeView.value === 'recent') {
      return {
        eyebrow: 'Agent library',
        title: 'Recent files.',
        description: 'Scan the latest files available across your accessible client folders.',
      }
    }

    return {
      eyebrow: 'Agent dashboard',
      title: 'Client folders.',
      description: 'Open a client folder to view its files with download-only access.',
    }
  })

  const setFolderBrowserMode = (value) => {
    folderBrowserMode.value = normalizeViewMode(value)
  }

  const setFolderBrowserFilter = (value) => {
    folderBrowserFilter.value = normalizeFilter(value)
  }

  const setFolderBrowserSort = (value) => {
    folderBrowserSort.value = normalizeSort(value)
  }

  const selectFolder = (folderId) => {
    selectedFolderId.value = folderId
    activeView.value = 'folder'
  }

  const goToFolders = () => {
    activeView.value = 'folders'
  }

  watch(folderBrowserMode, (value) => {
    if (typeof window !== 'undefined') {
      window.localStorage.setItem(VIEW_STORAGE_KEY, normalizeViewMode(value))
    }
  })

  watch(visibleFolderRows, (rows) => {
    if (!rows.length) {
      selectedFolderId.value = ''
      return
    }

    if (!rows.some((folder) => folder.id === selectedFolderId.value)) {
      selectedFolderId.value = rows[0].id
    }
  })

  const loadData = async () => {
    loading.value = true
    error.value = ''

    try {
      const response = await fetchAgentWorkspace()
      const workspace = response.data.data ?? {}

      dashboardData.value = workspace.dashboard ?? dashboardData.value
      folders.value = workspace.folders ?? []
      files.value = workspace.files ?? []
      selectedFolderId.value = visibleFolderRows.value[0]?.id ?? ''
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Unable to load the agent dashboard.'
    } finally {
      loading.value = false
    }
  }

  return {
    dashboardData,
    folders,
    files,
    loading,
    error,
    searchQuery,
    folderBrowserMode,
    folderBrowserFilter,
    folderBrowserSort,
    selectedFolderId,
    activeView,
    folderLookup,
    allFiles,
    folderRows,
    visibleFolderRows,
    selectedFolder,
    selectedFolderFiles,
    recentFiles,
    workspaceSummaryStats,
    categoryStats,
    currentUser,
    sectionCounts,
    activeViewMeta,
    loadData,
    setFolderBrowserMode,
    setFolderBrowserFilter,
    setFolderBrowserSort,
    selectFolder,
    goToFolders,
    FOLDER_FILTERS,
    FOLDER_SORTS,
  }
}
