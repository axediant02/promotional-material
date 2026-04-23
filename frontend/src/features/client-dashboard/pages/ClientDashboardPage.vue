<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../../../stores/auth'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchFiles } from '../../../services/fileService'
import { fetchRequests } from '../../../services/requestService'
import ClientAssetCatalog from '../components/ClientAssetCatalog.vue'
import ClientDashboardTopbar from '../components/ClientDashboardTopbar.vue'
import ClientDeliveryHero from '../components/ClientDeliveryHero.vue'
import ClientPortalFooter from '../components/ClientPortalFooter.vue'
import ClientRequestHistoryPanel from '../components/ClientRequestHistoryPanel.vue'
import ClientRequestSidebar from '../components/ClientRequestSidebar.vue'
import ClientSupportCard from '../components/ClientSupportCard.vue'

const authStore = useAuthStore()

const payload = ref({ user: null, stats: {}, folders: [], recentFiles: [] })
const files = ref([])
const requests = ref([])
const loading = ref(false)
const requestsLoading = ref(false)
const searchQuery = ref('')
const viewMode = ref('grid')
const selectedFile = ref(null)
const isRequestDrawerOpen = ref(false)

const assignedFolder = computed(() => payload.value.folders?.[0] ?? null)

const latestUpdatedAt = computed(() => {
  const timestamps = files.value
    .map((file) => file.updated_at)
    .filter(Boolean)
    .map((value) => new Date(value).getTime())
    .filter((value) => Number.isFinite(value))

  if (!timestamps.length) {
    return null
  }

  return new Date(Math.max(...timestamps))
})

const filteredFiles = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  if (!query) {
    return files.value
  }

  return files.value.filter((file) => {
    const haystack = [
      file.file_name,
      file.category,
      file.folder?.folder_name,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(query)
  })
})

const totalBytes = computed(() =>
  files.value.reduce((sum, file) => {
    const size = Number(file.size)
    return Number.isFinite(size) ? sum + size : sum
  }, 0)
)

const folderLabel = computed(() => assignedFolder.value?.folder_name ?? 'Assigned Folder')

const heroContent = computed(() => {
  if (!assignedFolder.value?.folder_id) {
    return {
      eyebrow: 'Secure review workspace',
      title: 'Request your first asset',
      accent: 'Folder created after submission',
      subtitle: 'Your folder will be created automatically when you send your first request. Use the request panel to tell production what you need.',
      actionLabel: 'Start a request',
      actionTarget: 'request',
    }
  }

  if (!files.value.length) {
    return {
      eyebrow: 'Assigned folder active',
      title: 'Your workspace is ready',
      accent: 'Waiting for approved files',
      subtitle: 'You already have a secured folder. Submit a request if you need new materials or changes while production prepares delivery.',
      actionLabel: 'Open request panel',
      actionTarget: 'request',
    }
  }

  return {
    eyebrow: 'Assigned folder active',
    title: 'Files ready for review',
    accent: 'Select an asset or download directly',
    subtitle: `Review approved materials in ${folderLabel.value} and submit focused change requests with clear context.`,
    actionLabel: 'Browse assets',
    actionTarget: '#asset-catalog',
  }
})

const catalogSummary = computed(() => ({
  visibleAssets: filteredFiles.value.length,
  folderLabel: folderLabel.value,
  lastUpdatedLabel: latestUpdatedAt.value
    ? new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    }).format(latestUpdatedAt.value)
    : 'No files yet',
}))

const heroStats = computed(() => [
  {
    label: 'Total Assets',
    value: `${files.value.length}`,
    help: files.value.length === 1 ? 'Approved file' : 'Approved files',
  },
])

const supportSummary = computed(() => ({
  label: 'Need a revision?',
  description: selectedFile.value
    ? `Feedback will be attached to ${selectedFile.value.file_name}.`
    : 'Select a file from the catalog to prefill your request context, or submit a folder-level note.',
}))

const selectFileForRequest = (file) => {
  selectedFile.value = file
  isRequestDrawerOpen.value = true
}

const clearSelectedFile = () => {
  selectedFile.value = null
}

const openRequestDrawer = () => {
  isRequestDrawerOpen.value = true
}

const closeRequestDrawer = () => {
  isRequestDrawerOpen.value = false
}

const handleHeroAction = () => {
  if (heroContent.value.actionTarget === 'request') {
    openRequestDrawer()
    return
  }

  const element = document.querySelector(heroContent.value.actionTarget)
  element?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const handleRequestCreated = async () => {
  await loadRequests()
}

const loadRequests = async () => {
  requestsLoading.value = true

  try {
    const response = await fetchRequests()
    requests.value = response.data.data.requests || []
  } catch (error) {
    console.error('Failed to load client requests:', error)
  } finally {
    requestsLoading.value = false
  }
}

onMounted(async () => {
  loading.value = true
  requestsLoading.value = true

  try {
    const [dashboardResponse, filesResponse, requestsResponse] = await Promise.all([
      fetchDashboard(),
      fetchFiles(),
      fetchRequests(),
    ])

    payload.value = dashboardResponse.data.data
    files.value = filesResponse.data.data.files || []
    requests.value = requestsResponse.data.data.requests || []
  } catch (error) {
    console.error('Failed to load client dashboard:', error)
  } finally {
    loading.value = false
    requestsLoading.value = false
  }
})

function formatBytes(bytes) {
  if (!bytes || bytes <= 0) {
    return '0 B'
  }

  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  const value = bytes / 1024 ** exponent

  return `${value.toFixed(value >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`
}
</script>

<template>
  <div class="pm-page text-ink dark:text-white">
    <ClientDashboardTopbar
      v-model:search-query="searchQuery"
      :folder-label="folderLabel"
      :user="authStore.user"
      @open-request="openRequestDrawer"
    />

    <main class="flex flex-col xl:flex-row">
      <section class="min-w-0 flex-1 p-6 sm:p-8 xl:p-10">
        <ClientDeliveryHero
          :folder="assignedFolder"
          :stats="heroStats"
          :user="authStore.user"
          :eyebrow="heroContent.eyebrow"
          :title="heroContent.title"
          :accent="heroContent.accent"
          :subtitle="heroContent.subtitle"
          :action-label="heroContent.actionLabel"
          :action-target="heroContent.actionTarget"
          @action-click="handleHeroAction"
        />

        <ClientAssetCatalog
          id="asset-catalog"
          v-model:view-mode="viewMode"
          :files="filteredFiles"
          :loading="loading"
          :search-query="searchQuery"
          :folder-label="catalogSummary.folderLabel"
          :last-updated-label="catalogSummary.lastUpdatedLabel"
          :selected-file-id="selectedFile?.file_id ?? null"
          @request-change="selectFileForRequest"
          @clear-search="searchQuery = ''"
          @open-request="openRequestDrawer"
        />
      </section>

      <aside class="w-full border-t border-border/80 bg-[linear-gradient(180deg,rgba(250,246,255,0.92),rgba(244,238,252,0.78))] p-6 xl:w-[440px] xl:border-l xl:border-t-0 xl:p-8 2xl:w-[480px] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.03))]">
        <div class="flex h-full flex-col gap-6">
          <section id="request-panel" class="pm-surface rounded-[1.75rem] p-6">
            <div class="flex items-start gap-4">
              <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-white/10 dark:text-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.4-8.6a2 2 0 1 1 2.8 2.8L11.8 16H9v-2.8l8.6-8.6Z" />
                </svg>
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-muted dark:text-zinc-400">Request Updates</p>
                <h3 class="mt-2 text-xl font-semibold text-ink dark:text-white">{{ supportSummary.label }}</h3>
                <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ supportSummary.description }}</p>
              </div>
            </div>

            <div class="mt-5 rounded-2xl bg-white/70 p-4 ring-1 ring-border/70 dark:bg-white/5 dark:ring-white/10">
              <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Assigned Folder</p>
              <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ assignedFolder?.folder_name ?? 'Created after first request' }}</p>

              <div v-if="selectedFile" class="mt-4 rounded-xl border border-brand-200 bg-brand-50/80 p-3 dark:border-white/10 dark:bg-white/10">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-100">Ready To Update</p>
                    <p class="mt-1 truncate text-sm font-semibold text-ink dark:text-white">{{ selectedFile.file_name }}</p>
                  </div>
                  <button
                    class="rounded-full border border-brand-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-700 transition hover:border-brand-300 dark:border-white/10 dark:text-white"
                    @click="clearSelectedFile"
                  >
                    Clear
                  </button>
                </div>
              </div>
            </div>

            <div class="mt-5 grid gap-3">
              <div class="rounded-2xl border border-border/80 bg-white/70 p-4 dark:border-white/10 dark:bg-white/5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">How it works</p>
                <p class="mt-2 text-sm font-semibold text-ink dark:text-white">Open the request drawer when you are ready</p>
                <p class="mt-1 text-sm text-muted dark:text-zinc-300">Choose a file for a precise update request, or start a folder-level request if you need something new.</p>
              </div>
            </div>

            <button
              class="pm-gradient-primary mt-5 w-full rounded-2xl px-4 py-3 text-sm font-semibold transition hover:brightness-110"
              @click="openRequestDrawer"
            >
              {{ selectedFile ? 'Update selected asset' : 'Start a request' }}
            </button>
          </section>
          <ClientRequestHistoryPanel
            :requests="requests"
            :loading="requestsLoading"
          />
          <ClientSupportCard />
        </div>
      </aside>
    </main>

    <ClientPortalFooter
      :file-count="files.length"
      :folder-label="folderLabel"
      :total-size-label="totalBytes > 0 ? formatBytes(totalBytes) : null"
    />

    <ClientRequestSidebar
      :open="isRequestDrawerOpen"
      :folder="assignedFolder"
      :selected-file="selectedFile"
      :support-summary="supportSummary"
      @close="closeRequestDrawer"
      @clear-selected-file="clearSelectedFile"
      @request-created="handleRequestCreated"
    />
  </div>
</template>
