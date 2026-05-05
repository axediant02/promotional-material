<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useAuthStore } from '../../../stores/auth'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import { useNotificationStore } from '../../../stores/notifications'
import ClientDashboardWorkspace from '../components/ClientDashboardWorkspace.vue'
import { useClientDashboardData } from '../composables/useClientDashboardData'
import { useClientRealtimeRefresh } from '../composables/useClientRealtimeRefresh'
import { useClientRealtimeStore } from '../../../stores/clientRealtime'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const clientRealtimeStore = useClientRealtimeStore()
const dashboardState = useClientDashboardData()

const requestsLoading = ref(false)
const searchQuery = ref('')
const viewMode = ref('grid')
const selectedFile = ref(null)
const isRequestDrawerOpen = ref(false)
const requestMode = ref('new_asset')

const loading = dashboardState.loading
const error = dashboardState.error
const files = dashboardState.files
const requests = dashboardState.requests
const assignedFolder = dashboardState.assignedFolder

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

const handleRequestCreated = (request) => {
  if (!request?.request_id) {
    return
  }

  requests.value = [
    request,
    ...requests.value.filter((item) => item.request_id !== request.request_id),
  ]
}

useClientRealtimeRefresh({
  realtimeStore: clientRealtimeStore,
  getAssignedFolderId: () => dashboardState.assignedFolderId.value,
  refreshAction: dashboardState.loadData,
  syncFileAction: async (fileId) => {
    const file = await dashboardState.syncFileWorkspace(fileId)

    if (!file || file.folder_id !== dashboardState.assignedFolderId.value) {
      selectedFile.value = null
      return file
    }

    if (selectedFile.value?.file_id === file.file_id) {
      selectedFile.value = file
    }

    return file
  },
  removeFileAction: (fileId) => {
    dashboardState.removeFileState(fileId)

    if (selectedFile.value?.file_id === fileId) {
      selectedFile.value = null
    }
  },
  setError: (message) => {
    dashboardState.error.value = message
  },
})

onMounted(async () => {
  requestsLoading.value = true

  try {
    await clientRealtimeStore.initializeForUser(authStore.user)
    await dashboardState.loadData()
  } catch (error) {
    console.error('Failed to load client dashboard:', error)
  } finally {
    requestsLoading.value = false
  }
})

onBeforeUnmount(() => {
  clientRealtimeStore.reset()
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
