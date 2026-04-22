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
import ClientStatusBanner from '../components/ClientStatusBanner.vue'
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

const heroStats = computed(() => [
  {
    label: 'Total Assets',
    value: `${files.value.length}`,
    help: files.value.length === 1 ? 'Approved file' : 'Approved files',
  },
  {
    label: 'Size',
    value: totalBytes.value > 0 ? formatBytes(totalBytes.value) : 'Secure',
    help: totalBytes.value > 0 ? 'Combined download size' : 'Protected delivery',
  },
  {
    label: 'Access',
    value: 'Private',
    help: folderLabel.value,
  },
])

const statusDetails = computed(() => [
  {
    label: 'Folder',
    value: folderLabel.value,
  },
  {
    label: 'Visible Files',
    value: `${filteredFiles.value.length}`,
  },
  {
    label: 'Access Scope',
    value: 'Approved client only',
  },
])

const supportSummary = computed(() => ({
  label: 'Need a revision?',
  description: selectedFile.value
    ? `Feedback will be attached to ${selectedFile.value.file_name}.`
    : 'Select a file from the catalog to prefill your request context, or submit a folder-level note.',
}))

const welcomeSubtitle = computed(() => {
  if (assignedFolder.value?.folder_name) {
    return `Review approved materials in ${assignedFolder.value.folder_name} and send change requests with clear context.`
  }

  return 'Review approved materials, track your assigned folder, and send change requests with clear context.'
})

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
  <div class="min-h-screen bg-[linear-gradient(180deg,#0f172a_0%,#16233f_26%,#eef3fb_26%,#f4f7fb_100%)] text-slate-900">
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
          :subtitle="welcomeSubtitle"
        />

        <ClientStatusBanner
          :details="statusDetails"
          :folder="assignedFolder"
          :selected-file="selectedFile"
        />

        <ClientAssetCatalog
          v-model:view-mode="viewMode"
          :files="filteredFiles"
          :loading="loading"
          :search-query="searchQuery"
          @request-change="selectFileForRequest"
        />
      </section>

      <aside class="w-full border-t border-slate-200/60 bg-slate-50/70 p-6 xl:w-[440px] xl:border-l xl:border-t-0 xl:p-8 2xl:w-[480px]">
        <div class="flex h-full flex-col gap-6">
          <ClientRequestSidebar
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
