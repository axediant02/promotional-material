<script setup>
import { computed, defineAsyncComponent, onMounted, ref } from 'vue'
import { useAuthStore } from '../../../stores/auth'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchFiles } from '../../../services/fileService'
import { fetchRequests } from '../../../services/requestService'
import { useNotificationStore } from '../../../stores/notifications'
import ClientAssetCatalog from '../components/ClientAssetCatalog.vue'
import ClientDashboardTopbar from '../components/ClientDashboardTopbar.vue'
import ClientDeliveryHero from '../components/ClientDeliveryHero.vue'
import ClientRequestHistoryPanel from '../components/ClientRequestHistoryPanel.vue'
import AssignmentChatPanel from '../../chat/components/AssignmentChatPanel.vue'

const ClientRequestSidebar = defineAsyncComponent(() => import('../components/ClientRequestSidebar.vue'))

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

const handleHeroAction = () => {
  if (heroContent.value.actionTarget === 'request') {
    openRequestDrawer('new_asset')
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
      :notifications="notificationStore.notifications"
      :notifications-loading="notificationStore.loading"
      :unread-count="notificationStore.unreadCount"
      :mark-read-action="notificationStore.markAsRead"
      :mark-all-read-action="notificationStore.markAllAsRead"
      @open-request="openRequestDrawer('new_asset')"
    />

    <main class="flex flex-col xl:flex-row">
      <section v-if="loading" class="min-w-0 flex-1 p-6 sm:p-8 xl:p-10">
        <DashboardOverviewSkeleton />
      </section>

      <template v-else>
        <section class="min-w-0 flex-1 p-6 sm:p-8 xl:p-10">
          <ClientDeliveryHero
            :folder="assignedFolder"
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
            @open-request="openRequestDrawer('new_asset')"
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

            <div class="mt-5 grid gap-3">
              <div class="rounded-2xl border border-border/80 bg-white/70 p-4 dark:border-white/10 dark:bg-white/5">
                <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">How it works</p>
                <p class="mt-2 text-sm font-semibold text-ink dark:text-white">Ask for a new file or request an update.</p>
                <p class="mt-1 text-sm text-muted dark:text-zinc-300">Choose an existing file if you want changes, or start a new request if you need something new.</p>
              </div>
            </div>

            <button
              class="pm-gradient-primary mt-5 w-full rounded-2xl px-4 py-3 text-sm font-semibold transition hover:brightness-110"
              @click="openRequestDrawer(selectedFile ? 'update_asset' : 'new_asset')"
            >
              {{ selectedFile ? 'Update selected asset' : 'Submit a request' }}
            </button>
            </section>

            <AssignmentChatPanel
              :current-user-id="authStore.user?.user_id ?? ''"
              title="Production chat"
              description="Chat directly with your assigned production contact while the assignment is active."
              empty-message="Chat will appear here once admin assigns your account to a production owner."
            />

            <ClientRequestHistoryPanel
              id="request-history"
              :requests="requests"
              :loading="requestsLoading"
            />
          </div>
        </aside>
      </template>
    </main>
    <ClientRequestSidebar
      :open="isRequestDrawerOpen"
      :folder="assignedFolder"
      :files="files"
      :mode="requestMode"
      :selected-file="selectedFile"
      :support-summary="supportSummary"
      @close="closeRequestDrawer"
      @update:mode="handleRequestModeChange"
      @select-file="selectedFile = $event"
      @clear-selected-file="clearSelectedFile"
      @request-created="handleRequestCreated"
    />
  </div>
</template>
