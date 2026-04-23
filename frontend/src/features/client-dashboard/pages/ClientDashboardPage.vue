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
      actionTarget: '#request-panel',
    }
  }

  if (!files.value.length) {
    return {
      eyebrow: 'Assigned folder active',
      title: 'Your workspace is ready',
      accent: 'Waiting for approved files',
      subtitle: 'You already have a secured folder. Submit a request if you need new materials or changes while production prepares delivery.',
      actionLabel: 'Open request panel',
      actionTarget: '#request-panel',
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
}

const clearSelectedFile = () => {
  selectedFile.value = null
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
        />
      </section>

      <aside class="w-full border-t border-border/80 bg-[linear-gradient(180deg,rgba(250,246,255,0.92),rgba(244,238,252,0.78))] p-6 xl:w-[440px] xl:border-l xl:border-t-0 xl:p-8 2xl:w-[480px] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.03))]">
        <div class="flex h-full flex-col gap-6">
          <ClientRequestSidebar
            id="request-panel"
            :folder="assignedFolder"
            :selected-file="selectedFile"
            :support-summary="supportSummary"
            @clear-selected-file="clearSelectedFile"
            @request-created="loadRequests"
          />
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
  </div>
</template>
