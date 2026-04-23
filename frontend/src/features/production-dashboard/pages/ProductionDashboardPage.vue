<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ProductionSidebar from '../components/ProductionSidebar.vue'
import ProductionTopbar from '../components/ProductionTopbar.vue'
import { fetchRecycleBin } from '../../../services/activityLogService'
import { fetchDashboard } from '../../../services/dashboardService'
import { downloadFile, fetchFiles, restoreFile } from '../../../services/fileService'
import { fetchFolders } from '../../../services/folderService'
import { fetchProductionRequests, updateProductionRequestStatus } from '../../../services/requestService'
import { useAuthStore } from '../../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref('')
const activeSection = ref('queue')
const activeQueueFilter = ref('all')
const searchQuery = ref('')
const updatingRequestId = ref('')
const restoringFileId = ref('')
const downloadingFileId = ref('')

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

const sectionCounts = computed(() => ({
  queue: productionRequests.value.length,
  files: files.value.length,
  recycle: recycleBinFiles.value.length,
}))

const currentSectionMeta = computed(() => {
  if (activeSection.value === 'files') {
    return {
      eyebrow: `${dashboardData.value.stats.files ?? files.value.length} visible assets`,
      title: 'Assigned files.',
      description: 'Browse the real file inventory across your assigned client folders, with downloads and the latest activity in scope.',
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
    eyebrow: currentUser.value?.name ? `${currentUser.value.name} · production` : 'Production workspace',
    title: 'Assigned work.',
    description: 'Track active request work, see which client spaces are assigned to you, and keep request statuses aligned with delivery progress.',
  }
})

const folderLookup = computed(() => {
  const map = new Map()

  for (const folder of folders.value) {
    map.set(folder.folder_id, folder)
  }

  for (const folder of dashboardData.value.folders ?? []) {
    map.set(folder.folder_id ?? folder.id, map.get(folder.folder_id ?? folder.id) ?? folder)
  }

  return map
})

const queueStats = computed(() => {
  const total = productionRequests.value.length
  const pending = productionRequests.value.filter((request) => request.status === 'pending').length
  const inProgress = productionRequests.value.filter((request) => request.status === 'in_progress').length
  const done = productionRequests.value.filter((request) => request.status === 'done').length

  return [
    { id: 'assigned_clients', label: 'Assigned Clients', value: folders.value.length, detail: 'Visible workspaces', accent: true },
    { id: 'open_requests', label: 'Open Requests', value: pending + inProgress, detail: `${total} total in queue` },
    { id: 'in_progress', label: 'In Progress', value: inProgress, detail: 'Actively moving' },
    { id: 'done', label: 'Done', value: done, detail: 'Completed requests' },
  ]
})

const queueFilterMeta = [
  { id: 'all', label: 'All' },
  { id: 'pending', label: 'Pending' },
  { id: 'in_progress', label: 'In Progress' },
  { id: 'done', label: 'Done' },
]

const statusToneLookup = {
  pending: 'pending',
  in_progress: 'in_progress',
  done: 'done',
}

const categoryToneLookup = {
  image: 'border-brand-300/20 bg-brand-50 text-brand-700 dark:bg-brand-300/10 dark:text-brand-100',
  video: 'border-brand-400/20 bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-100',
  pdf: 'border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-white/75',
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

const queueRows = computed(() =>
  productionRequests.value.map((request) => {
    const folder = folderLookup.value.get(request.folder_id)
    const relatedFiles = files.value.filter((file) => {
      const fileFolderId = file.folder?.folder_id ?? file.folder_id
      return fileFolderId === request.folder_id
    })

    return {
      id: request.request_id,
      reference: formatShortId(request.request_id),
      title: request.title ?? 'Untitled request',
      description: request.description ?? 'No request description provided.',
      clientName: folder?.client?.name ?? 'Assigned client',
      workspace: folder?.folder_name ?? 'Assigned workspace',
      requestType: formatRequestType(request.request_type),
      status: request.status ?? 'pending',
      statusTone: statusToneLookup[request.status] ?? 'pending',
      dueLabel: formatDateLabel(request.due_date),
      fileCount: relatedFiles.length,
      fileNames: relatedFiles.slice(0, 3).map((file) => file.file_name),
      hasDueDate: Boolean(request.due_date),
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

const assignedClientWorkspaces = computed(() =>
  folders.value.map((folder) => {
    const folderFiles = files.value.filter((file) => (file.folder?.folder_id ?? file.folder_id) === folder.folder_id)
    const folderRequests = productionRequests.value.filter((request) => request.folder_id === folder.folder_id)

    return {
      id: folder.folder_id,
      clientName: folder.client?.name ?? 'Assigned client',
      email: folder.client?.email ?? '',
      workspace: folder.folder_name,
      requestCount: folderRequests.length,
      fileCount: folderFiles.length,
      activeRequestCount: folderRequests.filter((request) => request.status !== 'done').length,
      newestFileLabel: folderFiles[0] ? formatDateLabel(folderFiles[0].updated_at) : 'No files yet',
    }
  })
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

const filteredFiles = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return files.value.filter((file) => {
    if (!query) {
      return true
    }

    return [
      file.file_name,
      file.folder?.folder_name,
      folderLookup.value.get(file.folder?.folder_id ?? file.folder_id)?.client?.name,
      file.category,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(query)
  })
})

const fileCategoryStats = computed(() => {
  const categories = ['image', 'video', 'pdf']

  return categories.map((category) => ({
    id: category,
    label: category.toUpperCase(),
    value: files.value.filter((file) => file.category === category).length,
  }))
})

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

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="pm-page min-h-screen text-ink dark:text-white">
    <div class="min-h-screen xl:grid xl:grid-cols-[18.5rem_minmax(0,1fr)]">
      <ProductionSidebar
        :current-user="currentUser"
        :active-section="activeSection"
        :section-counts="sectionCounts"
        @change-section="activeSection = $event"
        @sign-out="signOut"
      />

      <main class="min-w-0">
        <ProductionTopbar
          v-model:search-query="searchQuery"
          :current-user="currentUser"
          :title="currentSectionMeta.title"
          :eyebrow="currentSectionMeta.eyebrow"
          :description="currentSectionMeta.description"
        />

        <div class="px-6 py-8 sm:px-8 lg:px-10">
          <p v-if="error" class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
            {{ error }}
          </p>

          <div
            v-if="loading"
            class="pm-surface flex min-h-[18rem] items-center justify-center rounded-[2rem] text-sm uppercase tracking-[0.3em] text-muted dark:text-zinc-400"
          >
            Loading production workspace
          </div>

          <template v-else>
            <section v-if="activeSection === 'queue'" class="space-y-8">
              <section class="grid gap-4 xl:grid-cols-4">
                <article
                  v-for="stat in queueStats"
                  :key="stat.id"
                  :class="[
                    'pm-surface rounded-[1.8rem] px-5 py-5',
                    stat.accent
                      ? 'border-brand-200 bg-[radial-gradient(circle_at_top_left,rgba(109,80,162,0.22),transparent_58%),linear-gradient(180deg,rgba(255,255,255,0.98),rgba(245,239,251,0.96))] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.04))]'
                      : '',
                  ]"
                >
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
                  <p :class="['mt-4 text-4xl leading-none [font-family:\'Iowan_Old_Style\',\'Palatino_Linotype\',\'Book_Antiqua\',Palatino,serif]', stat.accent ? 'text-brand-700 dark:text-white' : 'text-ink dark:text-white']">
                    {{ stat.value }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ stat.detail }}</p>
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
                            <span
                              :class="[
                                'inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]',
                                row.statusTone === 'in_progress'
                                  ? 'border-brand-400/30 bg-brand-100 text-brand-700 dark:bg-white/10 dark:text-white'
                                  : row.statusTone === 'done'
                                    ? 'border-emerald-400/20 bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200'
                                    : 'border-brand-300/20 bg-brand-50 text-brand-700 dark:bg-white/10 dark:text-white',
                              ]"
                            >
                              {{ row.status.replaceAll('_', ' ') }}
                            </span>
                            <span class="inline-flex items-center rounded-full border border-border bg-white/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                              {{ row.requestType }}
                            </span>
                          </div>

                          <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
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
                            <span>{{ row.hasDueDate ? `due ${row.dueLabel}` : 'No due date set' }}</span>
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
                      <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                        No assigned requests match the current filter.
                      </h2>
                    </article>
                  </section>
                </div>

                <aside class="space-y-4">
                  <section class="pm-surface rounded-[1.8rem] p-5">
                    <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Assigned workspaces</p>
                    <div class="mt-5 space-y-3">
                      <article
                        v-for="workspace in assignedClientWorkspaces"
                        :key="workspace.id"
                        class="rounded-2xl border border-border bg-white/60 p-4 dark:border-white/10 dark:bg-black/10"
                      >
                        <div class="flex items-start justify-between gap-4">
                          <div class="min-w-0">
                            <h3 class="truncate text-lg font-semibold text-ink dark:text-white">{{ workspace.clientName }}</h3>
                            <p class="mt-1 text-sm text-muted dark:text-zinc-300">{{ workspace.workspace }}</p>
                          </div>
                          <span class="rounded-full border border-brand-400/20 bg-brand-50 px-2.5 py-1 text-[10px] uppercase tracking-[0.22em] text-brand-700 dark:bg-white/10 dark:text-white">
                            {{ workspace.activeRequestCount }} active
                          </span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3 text-[11px] uppercase tracking-[0.18em] text-muted dark:text-zinc-400">
                          <span>{{ workspace.requestCount }} requests</span>
                          <span>{{ workspace.fileCount }} files</span>
                          <span>{{ workspace.newestFileLabel }}</span>
                        </div>
                      </article>
                      <p v-if="!assignedClientWorkspaces.length" class="text-sm text-muted dark:text-zinc-300">
                        No client workspace is currently assigned to this production user.
                      </p>
                    </div>
                  </section>

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
                </aside>
              </section>
            </section>

            <section v-else-if="activeSection === 'files'" class="space-y-8">
              <section class="grid gap-4 xl:grid-cols-[repeat(3,minmax(0,1fr))_minmax(0,1.15fr)]">
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Visible files</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    {{ files.length }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">Current accessible file inventory</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Folders</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    {{ folders.length }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">Assigned client workspaces</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Recent files</p>
                  <p class="mt-4 text-4xl leading-none text-brand-700 dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    {{ recentActivityFiles.length }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">Latest scoped activity</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Categories</p>
                  <div class="mt-4 grid grid-cols-3 gap-3">
                    <div v-for="stat in fileCategoryStats" :key="stat.id" class="rounded-2xl border border-border bg-white/60 px-3 py-3 text-center dark:border-white/10 dark:bg-black/10">
                      <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
                      <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ stat.value }}</p>
                    </div>
                  </div>
                </article>
              </section>

              <section class="grid gap-3 xl:grid-cols-2">
                <article
                  v-for="file in filteredFiles"
                  :key="file.file_id"
                  class="pm-surface rounded-[1.8rem] px-5 py-5 transition hover:border-brand-500"
                >
                  <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                      <div class="flex flex-wrap items-center gap-2">
                        <span
                          :class="[
                            'rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]',
                            categoryToneLookup[file.category] ?? 'border-white/10 bg-white/5 text-white/75',
                          ]"
                        >
                          {{ file.category ?? 'asset' }}
                        </span>
                        <span class="text-[11px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">
                          {{ formatShortId(file.file_id, 'FILE') }}
                        </span>
                      </div>
                      <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                        {{ file.file_name }}
                      </h2>
                    </div>
                    <button
                      class="pm-gradient-primary rounded-2xl px-4 py-3 text-[12px] font-semibold uppercase tracking-[0.22em] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="downloadingFileId === file.file_id"
                      @click="handleDownloadFile(file)"
                    >
                      {{ downloadingFileId === file.file_id ? 'Downloading...' : 'Download' }}
                    </button>
                  </div>

                  <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] text-muted dark:text-zinc-400">
                    <span>{{ folderLookup.get(file.folder?.folder_id ?? file.folder_id)?.client?.name ?? 'Assigned client' }}</span>
                    <span>{{ file.folder?.folder_name ?? folderLookup.get(file.folder_id)?.folder_name ?? 'Workspace' }}</span>
                    <span>{{ file.uploader?.name ?? currentUser.name ?? 'Uploader' }}</span>
                    <span>{{ formatDateLabel(file.updated_at) }}</span>
                  </div>
                </article>

                <article
                  v-if="!filteredFiles.length"
                  class="pm-surface rounded-[1.8rem] border-dashed px-6 py-10 text-center xl:col-span-2"
                >
                  <p class="text-[10px] uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">No files found</p>
                  <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    No assigned files match the current search.
                  </h2>
                </article>
              </section>
            </section>

            <section v-else class="space-y-6">
              <section class="grid gap-4 xl:grid-cols-3">
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Deleted files</p>
                  <p class="mt-4 text-4xl leading-none text-brand-700 dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    {{ recycleBinFiles.length }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">In assigned recycle scope</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Recoverable clients</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    {{ new Set(recycleBinFiles.map((file) => file.folder?.folder_id ?? file.folder_id)).size }}
                  </p>
                  <p class="mt-3 text-sm text-muted dark:text-zinc-300">Workspaces with deleted assets</p>
                </article>
                <article class="pm-surface rounded-[1.8rem] px-5 py-5">
                  <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Live library</p>
                  <p class="mt-4 text-4xl leading-none text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
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
                      <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
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
                  <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
                    No assigned deleted files match the current search.
                  </h2>
                </article>
              </section>
            </section>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>
