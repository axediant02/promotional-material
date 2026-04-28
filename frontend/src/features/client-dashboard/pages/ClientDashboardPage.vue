<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../../../stores/auth'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchFiles } from '../../../services/fileService'
import { fetchRequests } from '../../../services/requestService'
import { useNotificationStore } from '../../../stores/notifications'
import ClientDashboardWorkspace from '../components/ClientDashboardWorkspace.vue'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const payload = ref({ user: null, stats: {}, folders: [], recentFiles: [] })
const files = ref([])
const requests = ref([])
const loading = ref(false)
const requestsLoading = ref(false)
const searchQuery = ref('')
const viewMode = ref('grid')
const selectedFile = ref(null)
const isRequestDrawerOpen = ref(false)
const requestMode = ref('new_asset')

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

const folderLabel = computed(() => assignedFolder.value?.folder_name ?? 'Assigned Folder')

const heroContent = computed(() => {
  if (!assignedFolder.value?.folder_id) {
    return {
      eyebrow: 'Your client space',
      title: 'Request your first file',
      accent: 'We will set up your folder for you',
      subtitle: 'Send your first request to get started.',
      actionLabel: 'Start a request',
      actionTarget: 'request',
    }
  }

  if (!files.value.length) {
    return {
      eyebrow: 'Your files area',
      title: 'You are all set',
      accent: 'Files will appear here soon',
      subtitle: 'You can send a request anytime if you need a new file or want something updated.',
      actionLabel: 'Open request panel',
      actionTarget: 'request',
    }
  }

  return {
    eyebrow: 'Your files are ready',
    title: 'Review your delivered files',
    accent: 'Download files or ask for an update',
    subtitle: `You can review the files in Asset Catalog section, download what you need, or send a request if something needs to be changed.`,
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

const supportSummary = computed(() => ({
  label: requestMode.value === 'update_asset' ? 'Update an existing asset' : 'Request a new asset',
  description: requestMode.value === 'update_asset'
    ? (selectedFile.value
      ? `Your request will be linked to ${selectedFile.value.file_name}.`
      : 'Choose the file you want updated so we know exactly which one to work on.')
    : 'Tell us what you need and we will prepare it for you.',
}))

const selectFileForRequest = (file) => {
  selectedFile.value = file
  requestMode.value = 'update_asset'
  isRequestDrawerOpen.value = true
}

const clearSelectedFile = () => {
  selectedFile.value = null
}

const openRequestDrawer = (mode = 'new_asset') => {
  requestMode.value = mode
  if (mode === 'new_asset') {
    selectedFile.value = null
  }
  isRequestDrawerOpen.value = true
}

const handleRequestModeChange = (mode) => {
  requestMode.value = mode

  if (mode === 'new_asset') {
    selectedFile.value = null
  }
}

const closeRequestDrawer = () => {
  isRequestDrawerOpen.value = false
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

</script>

<template>
  <section v-if="loading" class="min-h-screen">
    <DashboardOverviewSkeleton />
  </section>

  <ClientDashboardWorkspace
    v-else
    :user="authStore.user"
    :notifications="notificationStore.notifications"
    :notifications-loading="notificationStore.loading"
    :unread-count="notificationStore.unreadCount"
    :mark-read-action="notificationStore.markAsRead"
    :mark-all-read-action="notificationStore.markAllAsRead"
    :folder-label="folderLabel"
    :folder="assignedFolder"
    :files="filteredFiles"
    :loading="loading"
    :requests="requests"
    :requests-loading="requestsLoading"
    :search-query="searchQuery"
    :view-mode="viewMode"
    :selected-file-id="selectedFile?.file_id ?? null"
    :selected-file="selectedFile"
    :hero-content="heroContent"
    :catalog-summary="catalogSummary"
    :support-summary="supportSummary"
    :assigned-folder="assignedFolder"
    :request-drawer-open="isRequestDrawerOpen"
    :request-mode="requestMode"
    :current-user-id="authStore.user?.user_id ?? ''"
    @update:searchQuery="searchQuery = $event"
    @update:view-mode="viewMode = $event"
    @open-request="openRequestDrawer('new_asset')"
    @request-change="selectFileForRequest"
    @clear-search="searchQuery = ''"
    @select-file="selectedFile = $event"
    @clear-selected-file="clearSelectedFile"
    @request-created="handleRequestCreated"
    @close-request="closeRequestDrawer"
    @update:request-mode="handleRequestModeChange"
  />
</template>
