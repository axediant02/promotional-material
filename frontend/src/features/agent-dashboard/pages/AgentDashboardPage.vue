<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'
import { fetchDashboard } from '../../../services/dashboardService'
import { downloadFile, fetchFiles } from '../../../services/fileService'
import { fetchFolders } from '../../../services/folderService'
import { useAuthStore } from '../../../stores/auth'
import { useThemeStore } from '../../../stores/theme'

const VIEW_STORAGE_KEY = 'agent_file_browser_view_mode'
const FOLDER_FILTERS = ['all', 'recently_updated', 'has_files', 'empty']
const FOLDER_SORTS = ['recent', 'client_name', 'file_volume']

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
const folderBrowserMode = ref('grid')
const folderBrowserFilter = ref('all')
const folderBrowserSort = ref('recent')
const selectedFolderId = ref('')
const downloadingFileId = ref('')
const activeView = ref('folders')

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const normalizeViewMode = (value) => (value === 'list' ? 'list' : 'grid')
const normalizeFilter = (value) => (FOLDER_FILTERS.includes(value) ? value : 'all')
const normalizeSort = (value) => (FOLDER_SORTS.includes(value) ? value : 'recent')

const getSavedBrowserMode = () => {
  if (typeof window === 'undefined') {
    return 'grid'
  }

  return normalizeViewMode(window.localStorage.getItem(VIEW_STORAGE_KEY))
}

folderBrowserMode.value = getSavedBrowserMode()

const formatDateLabel = (value) => {
  if (!value) {
    return 'No activity yet'
  }

  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const formatShortId = (value, prefix = 'FILE') => {
  if (!value) {
    return prefix
  }

  return `${prefix}-${String(value).slice(0, 4).toUpperCase()}`
}

const normalizeTimestamp = (value) => {
  if (!value) {
    return 0
  }

  const timestamp = new Date(value).getTime()
  return Number.isFinite(timestamp) ? timestamp : 0
}

const getFolderId = (folder) => folder?.folder_id ?? folder?.id ?? ''
const getFolderName = (folder) => folder?.folder_name ?? folder?.name ?? 'Client folder'
const getFolderClientName = (folder) => folder?.client?.name ?? folder?.client_name ?? 'Client workspace'
const getFolderClientEmail = (folder) => folder?.client?.email ?? folder?.client_email ?? ''
const getFileId = (file) => file?.file_id ?? file?.id ?? ''
const getFileFolderId = (file) => file?.folder?.folder_id ?? file?.folder_id ?? file?.folder?.id ?? ''
const getFileName = (file) => file?.file_name ?? file?.original_name ?? 'Untitled file'
const getFileCategory = (file) => file?.category ?? file?.mime_type ?? 'asset'
const getFileUpdatedAt = (file) => file?.updated_at ?? file?.created_at ?? ''
const getFileUploaderName = (file) => file?.uploader?.name ?? file?.uploaded_by?.name ?? 'Production'

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

const currentUser = computed(() => dashboardData.value.user ?? authStore.user ?? {})

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

const filterOptions = [
  { id: 'all', label: 'All' },
  { id: 'recently_updated', label: 'Recently updated' },
  { id: 'has_files', label: 'Has files' },
  { id: 'empty', label: 'Empty' },
]

const sortOptions = [
  { id: 'recent', label: 'Recent activity' },
  { id: 'client_name', label: 'Client name' },
  { id: 'file_volume', label: 'File volume' },
]

const statusClasses = {
  ready: 'border-emerald-300/25 bg-emerald-400/10 text-emerald-700 dark:text-emerald-100',
  empty: 'border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300',
}

const categoryClasses = {
  image: 'border-brand-300/20 bg-brand-50 text-brand-700 dark:bg-brand-300/10 dark:text-brand-100',
  video: 'border-sky-300/20 bg-sky-50 text-sky-700 dark:border-sky-300/20 dark:bg-sky-300/10 dark:text-sky-100',
  pdf: 'border-amber-300/20 bg-amber-50 text-amber-700 dark:border-amber-300/20 dark:bg-amber-300/10 dark:text-amber-100',
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

const selectFolder = (folderId) => {
  selectedFolderId.value = folderId
  activeView.value = 'folder'
}

const goToFolders = () => {
  activeView.value = 'folders'
}

const goToSelectedFolder = () => {
  if (selectedFolder.value) {
    activeView.value = 'folder'
  }
}

const goToRecentFiles = () => {
  activeView.value = 'recent'
}

const setFolderBrowserMode = (value) => {
  folderBrowserMode.value = normalizeViewMode(value)
}

const setFolderBrowserFilter = (value) => {
  folderBrowserFilter.value = normalizeFilter(value)
}

const setFolderBrowserSort = (value) => {
  folderBrowserSort.value = normalizeSort(value)
}

const handleDownload = async (file) => {
  const fileId = getFileId(file)

  if (!fileId || downloadingFileId.value) {
    return
  }

  downloadingFileId.value = fileId
  error.value = ''

  try {
    await downloadFile({ ...file, file_id: fileId, file_name: getFileName(file) })
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to download this file.'
  } finally {
    downloadingFileId.value = ''
  }
}

const loadData = async () => {
  loading.value = true
  error.value = ''

  const [dashboardResponse, folderResponse, filesResponse] = await Promise.allSettled([
    fetchDashboard(),
    fetchFolders(),
    fetchFiles(),
  ])

  if (dashboardResponse.status === 'fulfilled') {
    dashboardData.value = dashboardResponse.value.data.data
  }

  if (folderResponse.status === 'fulfilled') {
    folders.value = folderResponse.value.data.data.folders ?? []
  } else {
    folders.value = dashboardData.value.folders ?? []
  }

  if (filesResponse.status === 'fulfilled') {
    files.value = filesResponse.value.data.data.files ?? []
  } else {
    files.value = dashboardData.value.recentFiles ?? []
  }

  if (dashboardResponse.status === 'rejected' && folderResponse.status === 'rejected' && filesResponse.status === 'rejected') {
    error.value = dashboardResponse.reason?.response?.data?.message ?? 'Unable to load the agent dashboard.'
  }

  selectedFolderId.value = visibleFolderRows.value[0]?.id ?? ''
  loading.value = false
}

onMounted(() => {
  loadData()
})

const signOut = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="pm-page min-h-screen text-ink dark:text-white">
    <div class="min-h-screen xl:grid xl:grid-cols-[18.5rem_minmax(0,1fr)]">
      <aside class="pm-dashboard-sidebar flex h-full min-h-screen flex-col xl:sticky xl:top-0">
        <div class="border-b border-white/10 px-6 py-7">
          <p class="text-[11px] uppercase tracking-[0.42em] text-white/60">Promotional Materials</p>
          <h1 class="mt-3 text-[2.35rem] font-semibold tracking-[-0.05em] text-white ">
            Agent.
          </h1>
          <p class="mt-1 text-sm text-white/65">Client file access</p>
          <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.32em] text-white">
            <span class="text-[#d8c5ff]">&bull;</span>
            Download Desk
          </div>
        </div>

        <nav class="flex-1 px-4 py-5">
          <button
            :class="[
              'mb-2 flex w-full items-center justify-between rounded-2xl px-4 py-3.5 text-left text-sm transition',
              activeView === 'folders'
                ? 'pm-dashboard-sidebar-item-active'
                : 'pm-dashboard-sidebar-item',
            ]"
            @click="goToFolders"
          >
            <span class="flex items-center gap-3">
              <svg class="h-4 w-4" :class="activeView === 'folders' ? 'text-brand-600' : 'text-white/65'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
              </svg>
              <span>Folders</span>
            </span>
            <span :class="['text-[11px] uppercase tracking-[0.22em]', activeView === 'folders' ? 'text-brand-500' : 'text-white/45']">{{ sectionCounts.folders }}</span>
          </button>

          <button
            :class="[
              'mb-2 flex w-full items-center justify-between rounded-2xl px-4 py-3.5 text-left text-sm transition disabled:cursor-not-allowed disabled:opacity-45',
              activeView === 'folder'
                ? 'pm-dashboard-sidebar-item-active'
                : 'pm-dashboard-sidebar-item',
            ]"
            :disabled="!selectedFolder"
            @click="goToSelectedFolder"
          >
            <span class="flex min-w-0 items-center gap-3">
              <svg class="h-4 w-4 shrink-0" :class="activeView === 'folder' ? 'text-brand-600' : 'text-white/65'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14 3v5h5" />
                <path d="M5 5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z" />
                <path d="M9 14h6" />
                <path d="M9 17h4" />
              </svg>
              <span class="truncate">{{ selectedFolder?.workspace ?? 'Open folder' }}</span>
            </span>
            <span :class="['text-[11px] uppercase tracking-[0.22em]', activeView === 'folder' ? 'text-brand-500' : 'text-white/45']">{{ sectionCounts.folder }}</span>
          </button>

          <button
            :class="[
              'mb-2 flex w-full items-center justify-between rounded-2xl px-4 py-3.5 text-left text-sm transition',
              activeView === 'recent'
                ? 'pm-dashboard-sidebar-item-active'
                : 'pm-dashboard-sidebar-item',
            ]"
            @click="goToRecentFiles"
          >
            <span class="flex items-center gap-3">
              <svg class="h-4 w-4" :class="activeView === 'recent' ? 'text-brand-600' : 'text-white/65'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 7h16" />
                <path d="M4 12h12" />
                <path d="M4 17h8" />
                <path d="M18 14v5" />
                <path d="m15.5 16.5 2.5 2.5 2.5-2.5" />
              </svg>
              <span>Recent Files</span>
            </span>
            <span :class="['text-[11px] uppercase tracking-[0.22em]', activeView === 'recent' ? 'text-brand-500' : 'text-white/45']">{{ sectionCounts.recent }}</span>
          </button>
        </nav>

        <div class="mt-auto border-t border-white/10 px-6 py-5">
          <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/10 text-sm font-semibold text-white">
              {{ (currentUser.name || 'A').split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-white">{{ currentUser.name || 'Agent User' }}</p>
              <p class="mt-1 text-[10px] uppercase tracking-[0.24em] text-white/55">{{ currentUser.role || 'agent' }}</p>
            </div>
            <button class="text-white/55 transition hover:text-white" @click="signOut">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M15 7h4v10h-4" />
                <path d="m10 17 5-5-5-5" />
                <path d="M15 12H3" />
              </svg>
            </button>
          </div>
        </div>
      </aside>

      <main class="min-w-0">
        <header class="border-b border-border/70 px-6 py-5 dark:border-white/10 sm:px-8 lg:px-10">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
              <p class="text-[11px] uppercase tracking-[0.42em] text-brand-600 dark:text-brand-100">{{ activeViewMeta.eyebrow }}</p>
              <h1 class="mt-3 text-4xl font-semibold tracking-[-0.045em] text-ink dark:text-white ">
                {{ activeViewMeta.title }}
              </h1>
              <p class="mt-3 max-w-2xl text-sm leading-6 text-muted dark:text-zinc-300">
                {{ activeViewMeta.description }}
              </p>
            </div>

            <div class="flex w-full max-w-xl items-center gap-4 lg:justify-end">
              <label class="relative flex-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-muted dark:text-zinc-400">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="m21 21-4.35-4.35m1.6-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                  </svg>
                </span>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search folders or files..."
                  class="pm-input w-full rounded-2xl py-3 pl-10 pr-4 text-sm placeholder:text-muted dark:placeholder:text-zinc-500"
                />
              </label>

              <button
                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-border/80 bg-white/60 text-muted transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
                type="button"
                @click="themeStore.toggleTheme()"
              >
                <svg v-if="themeStore.isDark" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <circle cx="12" cy="12" r="3.5" />
                  <path d="M12 2v3" />
                  <path d="M12 19v3" />
                  <path d="m4.93 4.93 2.12 2.12" />
                  <path d="m16.95 16.95 2.12 2.12" />
                  <path d="M2 12h3" />
                  <path d="M19 12h3" />
                  <path d="m4.93 19.07 2.12-2.12" />
                  <path d="m16.95 7.05 2.12-2.12" />
                </svg>
                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M21 12.8A9 9 0 1 1 11.2 3a7.1 7.1 0 0 0 9.8 9.8Z" />
                </svg>
              </button>
            </div>
          </div>
        </header>

        <div class="px-6 py-8 sm:px-8 lg:px-10">
          <p v-if="error" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
            {{ error }}
          </p>

          <DashboardOverviewSkeleton v-if="loading" />

          <template v-else>
            <section v-if="activeView === 'folders'" class="space-y-8">
              <section class="grid gap-4 xl:grid-cols-4">
                <article
                  v-for="stat in workspaceSummaryStats"
                  :key="stat.id"
                  class="pm-surface rounded-[1.7rem] px-5 py-5"
                >
                  <p class="text-[10px] uppercase tracking-[0.32em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white ">
                    {{ stat.value }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ stat.detail }}</p>
                </article>
              </section>

              <section class="pm-dashboard-inverse-surface overflow-hidden rounded-[2rem]">
                <div class="border-b border-white/10 px-5 py-5">
                  <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                      <DashboardSectionHeader
                        eyebrow="Agent library"
                        title="Client folders"
                        description="Click a folder to enter it and view the client's available files."
                        :badge="`${visibleFolderRows.length} folders`"
                        tone="inverse"
                        compact
                      />

                      <div class="inline-flex rounded-xl border border-white/10 bg-black/20 p-1 self-start">
                        <button
                          :class="[
                            'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                            folderBrowserMode === 'grid' ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]' : 'text-zinc-300 hover:text-white',
                          ]"
                          @click="setFolderBrowserMode('grid')"
                        >
                          Grid
                        </button>
                        <button
                          :class="[
                            'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                            folderBrowserMode === 'list' ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]' : 'text-zinc-300 hover:text-white',
                          ]"
                          @click="setFolderBrowserMode('list')"
                        >
                          List
                        </button>
                      </div>
                    </div>

                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                      <div class="flex flex-wrap gap-2">
                        <button
                          v-for="filter in filterOptions"
                          :key="filter.id"
                          :class="[
                            'rounded-full border px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] transition',
                            folderBrowserFilter === filter.id
                              ? 'border-[#9cdcfe]/40 bg-[#9cdcfe]/15 text-[#d7f0ff]'
                              : 'border-white/10 bg-white/5 text-zinc-300 hover:border-white/20 hover:text-white',
                          ]"
                          @click="setFolderBrowserFilter(filter.id)"
                        >
                          {{ filter.label }}
                        </button>
                      </div>

                      <label class="flex items-center gap-3 text-[11px] uppercase tracking-[0.22em] text-zinc-400">
                        <span>Sort</span>
                        <select
                          class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white outline-none"
                          :value="folderBrowserSort"
                          @change="setFolderBrowserSort($event.target.value)"
                        >
                          <option v-for="option in sortOptions" :key="option.id" class="bg-[#1f1f1f]" :value="option.id">
                            {{ option.label }}
                          </option>
                        </select>
                      </label>
                    </div>
                  </div>
                </div>

                <div class="px-5 py-5">
                  <div v-if="folderBrowserMode === 'grid'" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    <button
                      v-for="folder in visibleFolderRows"
                      :key="folder.id"
                      class="group relative overflow-hidden rounded-[1.55rem] border border-white/8 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.02))] p-4 text-left transition duration-200 hover:-translate-y-0.5 hover:border-white/20 hover:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.03))]"
                      @click="selectFolder(folder.id)"
                    >
                      <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-br from-[#9cdcfe]/16 via-[#9cdcfe]/6 to-transparent opacity-90" />
                      <div class="relative flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                          <div class="flex h-14 w-16 shrink-0 items-center justify-center rounded-[1rem] bg-[linear-gradient(180deg,#f9d978_0%,#f3c54c_62%,#ddac2f_100%)] shadow-[inset_0_1px_0_rgba(255,255,255,0.35),0_12px_24px_rgba(0,0,0,0.18)]">
                            <svg class="h-9 w-9 text-[#a56a00]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                              <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
                            </svg>
                          </div>
                          <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.28em] text-zinc-500">Client workspace</p>
                            <p class="mt-1 truncate text-sm font-medium text-zinc-300">{{ folder.clientName }}</p>
                          </div>
                        </div>
                        <span :class="['shrink-0 rounded-full border px-2.5 py-1 text-[10px] uppercase tracking-[0.22em]', statusClasses[folder.statusTone]]">
                          {{ folder.statusLabel }}
                        </span>
                      </div>

                      <div class="relative mt-5">
                        <p class="line-clamp-2 text-lg font-semibold leading-6 text-white">{{ folder.workspace }}</p>
                        <p class="mt-2 max-w-[22rem] text-sm leading-6 text-zinc-400">{{ folder.latestActivityLabel }}</p>
                        <div class="mt-5 grid grid-cols-2 gap-2">
                          <div class="rounded-2xl border border-white/8 bg-black/20 px-3 py-3">
                            <p class="text-[10px] uppercase tracking-[0.24em] text-zinc-500">Files</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ folder.fileCount }}</p>
                          </div>
                          <div class="rounded-2xl border border-white/8 bg-black/20 px-3 py-3">
                            <p class="text-[10px] uppercase tracking-[0.24em] text-zinc-500">Access</p>
                            <p class="mt-2 text-lg font-semibold text-white">Read</p>
                          </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3 border-t border-white/8 pt-4">
                          <span class="truncate rounded-full border border-white/8 bg-white/[0.04] px-2.5 py-1 text-[10px] uppercase tracking-[0.22em] text-zinc-500">
                            {{ folder.email || 'No client email' }}
                          </span>
                          <span class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9cdcfe]">Enter folder</span>
                        </div>
                      </div>
                    </button>
                  </div>

                  <div v-else class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-black/20">
                    <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1.15fr)_7rem_8rem] gap-4 border-b border-white/10 bg-white/[0.04] px-5 py-4 text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400 md:grid">
                      <span>Folder</span>
                      <span>Client</span>
                      <span>Files</span>
                      <span>Action</span>
                    </div>
                    <button
                      v-for="folder in visibleFolderRows"
                      :key="folder.id"
                      class="grid w-full gap-4 border-b border-white/8 px-5 py-5 text-left transition duration-200 last:border-b-0 hover:bg-white/[0.04] md:grid-cols-[minmax(0,2fr)_minmax(0,1.15fr)_7rem_8rem] md:items-center md:gap-4"
                      @click="selectFolder(folder.id)"
                    >
                      <div class="flex min-w-0 items-start gap-4">
                        <div class="mt-0.5 flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] bg-[linear-gradient(180deg,#f9d978_0%,#f3c54c_62%,#ddac2f_100%)] shadow-[inset_0_1px_0_rgba(255,255,255,0.3),0_10px_18px_rgba(0,0,0,0.16)]">
                          <svg class="h-7 w-7 text-[#a56a00]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
                          </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                          <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-sm font-semibold text-white">{{ folder.workspace }}</p>
                            <span :class="['inline-flex shrink-0 rounded-full border px-2.5 py-1 text-[10px] uppercase tracking-[0.22em]', statusClasses[folder.statusTone]]">
                              {{ folder.statusLabel }}
                            </span>
                          </div>
                          <p class="mt-1 text-xs text-zinc-400">{{ folder.latestActivityLabel }}</p>
                          <p class="mt-2 text-[11px] uppercase tracking-[0.22em] text-zinc-500 md:hidden">{{ folder.clientName }}</p>
                        </div>
                      </div>
                      <div class="min-w-0">
                        <p class="truncate text-sm text-zinc-200">{{ folder.clientName }}</p>
                        <p class="mt-1 truncate text-xs text-zinc-500">{{ folder.email || 'No client email' }}</p>
                      </div>
                      <div class="flex items-center justify-between gap-3 md:block">
                        <span class="text-[10px] uppercase tracking-[0.22em] text-zinc-500 md:hidden">Files</span>
                        <p class="text-sm font-semibold text-white">{{ folder.fileCount }}</p>
                      </div>
                      <div class="flex items-center justify-between gap-3 md:block md:text-right">
                        <span class="text-[10px] uppercase tracking-[0.22em] text-zinc-500 md:hidden">Action</span>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9cdcfe]">Enter</p>
                      </div>
                    </button>
                  </div>

                  <article
                    v-if="!visibleFolderRows.length"
                    class="rounded-[1.6rem] border border-dashed border-white/15 bg-white/[0.03] px-6 py-12 text-center"
                  >
                    <p class="text-[10px] uppercase tracking-[0.32em] text-[#9cdcfe]">No folders found</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">No client folders match the current search.</h3>
                  </article>
                </div>
              </section>
            </section>

            <section v-else-if="activeView === 'folder'" class="space-y-5">
              <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.26em] text-zinc-500">
                <button class="rounded-full border border-border bg-white/60 px-3 py-1.5 transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:text-white" @click="goToFolders">
                  Folders
                </button>
                <span>/</span>
                <span class="rounded-full border border-[#9cdcfe]/30 bg-[#9cdcfe]/10 px-3 py-1.5 text-[#6ecff6] dark:text-[#b9e7ff]">
                  {{ selectedFolder?.workspace ?? 'Folder' }}
                </span>
              </nav>

              <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.4fr)_24rem]">
                <section class="pm-dashboard-inverse-surface overflow-hidden rounded-[2rem]">
                  <div class="border-b border-white/10 px-5 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                      <DashboardSectionHeader
                        eyebrow="Folder contents"
                        :title="selectedFolder?.workspace ?? 'Client folder'"
                        :description="`${selectedFolder?.clientName ?? 'Client workspace'} / ${selectedFolderFiles.length} downloadable files.`"
                        badge="Download only"
                        tone="inverse"
                        compact
                      />
                      <div class="inline-flex rounded-xl border border-white/10 bg-black/20 p-1 self-start">
                        <button
                          :class="[
                            'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                            folderBrowserMode === 'grid' ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]' : 'text-zinc-300 hover:text-white',
                          ]"
                          @click="setFolderBrowserMode('grid')"
                        >
                          Grid
                        </button>
                        <button
                          :class="[
                            'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                            folderBrowserMode === 'list' ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]' : 'text-zinc-300 hover:text-white',
                          ]"
                          @click="setFolderBrowserMode('list')"
                        >
                          List
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="px-5 py-5">
                    <div v-if="folderBrowserMode === 'grid'" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                      <article
                        v-for="file in selectedFolderFiles"
                        :key="file.file_id"
                        class="rounded-[1.5rem] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0.03))] p-5 transition hover:border-[#9cdcfe]/35"
                      >
                        <div class="flex items-start justify-between gap-4">
                          <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                              <span :class="['rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', categoryClasses[file.category] ?? 'border-white/10 bg-white/5 text-white/75']">
                                {{ file.category }}
                              </span>
                              <span class="text-[11px] uppercase tracking-[0.22em] text-zinc-500">{{ file.shortId }}</span>
                            </div>
                            <h3 class="mt-3 truncate text-lg font-semibold text-white">{{ file.file_name }}</h3>
                            <p class="mt-2 text-sm text-zinc-400">{{ file.uploaderName }}</p>
                          </div>

                          <button
                            class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white transition hover:border-[#9cdcfe]/35 hover:bg-[#9cdcfe]/10 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="downloadingFileId === file.file_id"
                            @click="handleDownload(file)"
                          >
                            {{ downloadingFileId === file.file_id ? 'Downloading...' : 'Download' }}
                          </button>
                        </div>
                        <div class="mt-4 space-y-2 text-sm text-zinc-400">
                          <p>{{ file.updatedLabel }}</p>
                          <p>{{ file.folderName }}</p>
                        </div>
                      </article>
                    </div>

                    <div v-else class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-black/20">
                      <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1fr)_8rem_9rem] gap-4 border-b border-white/10 bg-white/[0.04] px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400 md:grid">
                        <span>Name</span>
                        <span>Updated</span>
                        <span>Type</span>
                        <span class="text-right">Action</span>
                      </div>
                      <article v-for="file in selectedFolderFiles" :key="file.file_id" class="border-b border-white/8 px-5 py-5 last:border-b-0">
                        <div class="grid gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_8rem_9rem] md:items-center md:gap-4">
                          <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                              <p class="truncate text-sm font-semibold text-white">{{ file.file_name }}</p>
                              <span :class="['inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] md:hidden', categoryClasses[file.category] ?? 'border-white/10 bg-white/5 text-white/75']">
                                {{ file.category }}
                              </span>
                            </div>
                            <p class="mt-1 text-xs text-zinc-500">{{ file.shortId }} / {{ file.uploaderName }}</p>
                          </div>
                          <div class="flex items-center justify-between gap-3 md:block">
                            <span class="text-[10px] uppercase tracking-[0.22em] text-zinc-500 md:hidden">Updated</span>
                            <p class="truncate text-sm text-zinc-300">{{ file.updatedLabel }}</p>
                          </div>
                          <div class="flex items-center justify-between gap-3 md:block">
                            <span class="text-[10px] uppercase tracking-[0.22em] text-zinc-500 md:hidden">Type</span>
                            <span :class="['inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', categoryClasses[file.category] ?? 'border-white/10 bg-white/5 text-white/75']">
                              {{ file.category }}
                            </span>
                          </div>
                          <div class="flex items-center justify-between gap-3 md:block md:text-right">
                            <span class="text-[10px] uppercase tracking-[0.22em] text-zinc-500 md:hidden">Action</span>
                            <button
                              class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white transition hover:border-[#9cdcfe]/35 hover:bg-[#9cdcfe]/10 disabled:cursor-not-allowed disabled:opacity-60"
                              :disabled="downloadingFileId === file.file_id"
                              @click="handleDownload(file)"
                            >
                              {{ downloadingFileId === file.file_id ? 'Downloading...' : 'Download' }}
                            </button>
                          </div>
                        </div>
                      </article>
                    </div>

                    <article
                      v-if="!selectedFolderFiles.length"
                      class="rounded-[1.6rem] border border-dashed border-white/15 bg-white/[0.03] px-6 py-12 text-center"
                    >
                      <p class="text-[10px] uppercase tracking-[0.32em] text-[#9cdcfe]">No files found</p>
                      <h3 class="mt-3 text-xl font-semibold text-white">No files are currently available in this client folder.</h3>
                    </article>
                  </div>
                </section>

                <aside class="space-y-4">
                  <section class="pm-surface-strong rounded-[2rem] p-5">
                    <p class="text-[10px] uppercase tracking-[0.34em] text-brand-600 dark:text-brand-100">Selected folder</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-ink dark:text-white ">
                      {{ selectedFolder?.workspace ?? 'No folder selected' }}
                    </h2>
                    <p class="mt-2 text-sm text-muted dark:text-zinc-300">{{ selectedFolder?.clientName ?? 'Choose a folder to inspect.' }}</p>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                      <div class="rounded-2xl border border-border bg-white/60 px-4 py-3 dark:border-white/10 dark:bg-black/10">
                        <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Files</p>
                        <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ selectedFolderFiles.length }}</p>
                      </div>
                      <div class="rounded-2xl border border-border bg-white/60 px-4 py-3 dark:border-white/10 dark:bg-black/10">
                        <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Access</p>
                        <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">Read</p>
                      </div>
                    </div>
                    <div class="mt-5 space-y-2 text-sm text-muted dark:text-zinc-300">
                      <p>{{ selectedFolder?.latestActivityLabel ?? 'No file activity yet' }}</p>
                      <p>{{ selectedFolder?.email || 'No client email recorded' }}</p>
                    </div>
                  </section>
                  <section class="pm-surface rounded-[1.8rem] p-5">
                    <p class="text-[10px] uppercase tracking-[0.34em] text-muted dark:text-zinc-400">Category coverage</p>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                      <div v-for="stat in categoryStats" :key="stat.id" class="rounded-2xl border border-border bg-white/60 px-3 py-3 text-center dark:border-white/10 dark:bg-black/10">
                        <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
                        <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ stat.value }}</p>
                      </div>
                    </div>
                  </section>
                </aside>
              </section>
            </section>

            <section v-else class="space-y-5">
              <section class="pm-dashboard-inverse-surface overflow-hidden rounded-[2rem]">
                <div class="border-b border-white/10 px-5 py-5">
                  <DashboardSectionHeader
                    eyebrow="Recent file activity"
                    title="Latest accessible files"
                    description="Open the parent folder from the folder list when you need the full client file set."
                    :badge="`${recentFiles.length} items`"
                    tone="inverse"
                    compact
                  />
                </div>
                <div class="px-5 py-5">
                  <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    <article
                      v-for="file in recentFiles"
                      :key="file.id"
                      class="rounded-[1.5rem] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0.03))] p-5"
                    >
                      <p class="text-[10px] uppercase tracking-[0.22em] text-[#9cdcfe]">{{ file.category }}</p>
                      <h3 class="mt-3 truncate text-lg font-semibold text-white">{{ file.name }}</h3>
                      <p class="mt-2 text-sm text-zinc-400">{{ file.clientName }} / {{ file.folderName }}</p>
                      <p class="mt-4 text-xs text-zinc-500">{{ file.updatedLabel }}</p>
                    </article>
                  </div>
                  <article v-if="!recentFiles.length" class="rounded-[1.6rem] border border-dashed border-white/15 bg-white/[0.03] px-6 py-12 text-center">
                    <p class="text-[10px] uppercase tracking-[0.32em] text-[#9cdcfe]">No recent files</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">No recent file activity is available in your current scope.</h3>
                  </article>
                </div>
              </section>
            </section>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>
