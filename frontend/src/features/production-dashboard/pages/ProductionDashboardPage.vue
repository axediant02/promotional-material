<script setup>
import { ref, onMounted } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import AssignmentChatWidget from '../../chat/components/AssignmentChatWidget.vue'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import ProductionCategoryCoveragePanel from '../components/ProductionCategoryCoveragePanel.vue'
import ProductionQueueSection from '../components/ProductionQueueSection.vue'
import ProductionRecentActivityPanel from '../components/ProductionRecentActivityPanel.vue'
import ProductionRecycleSection from '../components/ProductionRecycleSection.vue'
import ProductionSidebar from '../components/ProductionSidebar.vue'
import ProductionTopbar from '../components/ProductionTopbar.vue'
import ProductionWorkspaceStats from '../components/ProductionWorkspaceStats.vue'
import RequestDetailModal from '../components/RequestDetailModal.vue'
import RequestStickyNote from '../components/RequestStickyNote.vue'
import { categoryToneLookup, queueFilterMeta } from '../constants/productionDashboardUi'
import { provideProductionWorkspace } from '../productionWorkspace'
import { useProductionDashboardData } from '../composables/useProductionDashboardData'
import { useProductionDerivedData } from '../composables/useProductionDerivedData'
import { useProductionFilters } from '../composables/useProductionFilters'
import { useProductionFileActions } from '../composables/useProductionFileActions'
import { useProductionRequestActions } from '../composables/useProductionRequestActions'
import { useProductionRouting } from '../composables/useProductionRouting'
import { useProductionSidebarState } from '../composables/useProductionSidebarState'
import { useAuthStore } from '../../../stores/auth'
import { useNotificationStore } from '../../../stores/notifications'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const activeSection = ref('files')
const selectedOverviewRequestId = ref('')

const SIDEBAR_EXPANDED_WIDTH = '18.5rem'
const SIDEBAR_COLLAPSED_WIDTH = '6.5rem'

const dashboardState = useProductionDashboardData()
const filtersState = useProductionFilters()
const sidebarState = useProductionSidebarState()
const routingState = useProductionRouting({
  route,
  router,
  activeSection,
  searchQuery: filtersState.searchQuery,
  folderBrowserMode: filtersState.folderBrowserMode,
  folderBrowserFilter: filtersState.folderBrowserFilter,
  folderBrowserSort: filtersState.folderBrowserSort,
})
const derivedState = useProductionDerivedData({
  authUser: authStore.user,
  dashboardData: dashboardState.dashboardData,
  folders: dashboardState.folders,
  files: dashboardState.files,
  productionRequests: dashboardState.productionRequests,
  recycleBinFiles: dashboardState.recycleBinFiles,
  searchQuery: filtersState.searchQuery,
  activeSection,
  folderBrowserMode: filtersState.folderBrowserMode,
  folderBrowserFilter: filtersState.folderBrowserFilter,
  folderBrowserSort: filtersState.folderBrowserSort,
  selectedFolderId: routingState.selectedFolderId,
  selectedOverviewRequestId,
})
const requestActions = useProductionRequestActions({
  productionRequests: dashboardState.productionRequests,
  error: dashboardState.error,
})
const fileActions = useProductionFileActions({
  files: dashboardState.files,
  recycleBinFiles: dashboardState.recycleBinFiles,
  error: dashboardState.error,
  currentUser: derivedState.currentUser,
})

const loading = dashboardState.loading
const error = dashboardState.error
const loadData = dashboardState.loadData
const currentUser = derivedState.currentUser
const currentUserId = derivedState.currentUserId
const sectionCounts = derivedState.sectionCounts
const currentSectionMeta = derivedState.currentSectionMeta
const activeQueueFilter = derivedState.activeQueueFilter
const setActiveQueueFilter = derivedState.setActiveQueueFilter
const workspaceSummaryStats = derivedState.workspaceSummaryStats
const filteredQueueRows = derivedState.filteredQueueRows
const recentActivityFiles = derivedState.recentActivityFiles
const fileCategoryStats = derivedState.fileCategoryStats
const recycleBinSummaryStats = derivedState.recycleBinSummaryStats
const filteredRecycleBinFiles = derivedState.filteredRecycleBinFiles
const latestAssignedRequests = derivedState.latestAssignedRequests
const selectedOverviewRequest = derivedState.selectedOverviewRequest
const selectedFolder = derivedState.selectedFolder
const selectedFolderFiles = derivedState.selectedFolderFiles
const selectedFolderRequests = derivedState.selectedFolderRequests
const visibleFolderRows = derivedState.visibleFolderRows
const folderBrowserMode = filtersState.folderBrowserMode
const folderBrowserFilter = filtersState.folderBrowserFilter
const folderBrowserSort = filtersState.folderBrowserSort
const searchQuery = filtersState.searchQuery
const setFolderBrowserMode = filtersState.setFolderBrowserMode
const setFolderBrowserFilter = filtersState.setFolderBrowserFilter
const setFolderBrowserSort = filtersState.setFolderBrowserSort
const sidebarCollapsed = sidebarState.sidebarCollapsed
const toggleSidebar = sidebarState.toggleSidebar
const downloadingFileId = fileActions.downloadingFileId
const uploadingFileId = fileActions.uploadingFileId
const updatingFileId = fileActions.updatingFileId
const restoringFileId = fileActions.restoringFileId
const updatingRequestId = requestActions.updatingRequestId
const handleDownloadFile = fileActions.handleDownloadFile
const handleUploadFile = fileActions.handleUploadFile
const handleReplaceFile = fileActions.handleReplaceFile
const restoreRecycleFile = fileActions.restoreRecycleFile
const updateRequestStatus = requestActions.updateRequestStatus
const openFolder = routingState.openFolder
const goToFolderIndex = routingState.goToFolderIndex

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
  updatingFileId,
  updatingRequestId,
  categoryToneLookup,
  setFolderBrowserMode,
  setFolderBrowserFilter,
  setFolderBrowserSort,
  openFolder,
  goToFolderIndex,
  handleDownloadFile,
  handleUploadFile,
  handleReplaceFile,
  updateRequestStatus,
})

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

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="pm-page min-h-screen text-ink dark:text-white">
    <div
      class="min-h-screen transition-[grid-template-columns] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] xl:grid"
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
          <p
            v-if="error"
            class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
          >
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
              <ProductionWorkspaceStats :stats="workspaceSummaryStats" />

              <ProductionQueueSection
                :queue-filter-meta="queueFilterMeta"
                :active-queue-filter="activeQueueFilter"
                :queue-rows="filteredQueueRows"
                :updating-request-id="updatingRequestId"
                @update:active-queue-filter="setActiveQueueFilter"
                @update-request-status="updateRequestStatus"
              >
                <template #sidebar>
                  <ProductionRecentActivityPanel :recent-activity-files="recentActivityFiles" />
                  <ProductionCategoryCoveragePanel :file-category-stats="fileCategoryStats" />
                </template>
              </ProductionQueueSection>
            </section>

            <ProductionRecycleSection
              v-else
              :recycle-bin-summary-stats="recycleBinSummaryStats"
              :recycle-bin-files="filteredRecycleBinFiles"
              :restoring-file-id="restoringFileId"
              @restore-file="restoreRecycleFile"
            />
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
