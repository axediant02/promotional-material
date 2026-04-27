<script setup>
import { computed, defineAsyncComponent, onMounted, ref } from 'vue'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import AdminDashboardAttentionPanel from '../components/AdminDashboardAttentionPanel.vue'
import AdminDashboardHeader from '../components/AdminDashboardHeader.vue'
import AdminDashboardRequestsSection from '../components/AdminDashboardRequestsSection.vue'
import AdminDashboardSecondaryPanels from '../components/AdminDashboardSecondaryPanels.vue'
import AdminDashboardSidebar from '../components/AdminDashboardSidebar.vue'
import AdminDashboardStatGrid from '../components/AdminDashboardStatGrid.vue'
import { useAdminAssignments } from '../composables/useAdminAssignments'
import { useAdminDashboardData } from '../composables/useAdminDashboardData'
import { useAdminRealtimeRefresh } from '../composables/useAdminRealtimeRefresh'
import { useAdminRequestDueDates } from '../composables/useAdminRequestDueDates'
import { useAdminUsers } from '../composables/useAdminUsers'
import { adminDashboardFallbacks } from '../data/adminDashboardFallbacks'
import {
  buildAssignedClientIdSet,
  mapAssignmentsTabRows,
  mapAttentionItems,
  mapClientLookup,
  mapFolderCards,
  mapFolderLookup,
  mapProductionUserLookup,
  mapQueueRows,
  mapStats,
  mapUsersTabRows,
} from '../utils/adminDashboardMappers'
import { useAuthStore } from '../../../stores/auth'
import { useNotificationStore } from '../../../stores/notifications'

const AdminDashboardRequestsTab = defineAsyncComponent(() => import('../components/AdminDashboardRequestsTab.vue'))
const AdminDashboardUsersTab = defineAsyncComponent(() => import('../components/AdminDashboardUsersTab.vue'))
const AdminDashboardAssignmentsTab = defineAsyncComponent(() => import('../components/AdminDashboardAssignmentsTab.vue'))

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const activeItem = ref('overview')

const currentUser = computed(() => authStore.user ?? {})

const {
  loading,
  error,
  dashboardPayload,
  requestsPayload,
  activityLogs,
  assignmentsPayload,
  productionUsersPayload,
  usersPayload,
  setError,
  clearError,
  loadAssignments,
  loadAdminDashboard,
  refreshDashboardAndRequests,
} = useAdminDashboardData()

const folderLookup = computed(() => {
  return mapFolderLookup(dashboardPayload.value.folders ?? [])
})

const productionUserLookup = computed(() => {
  return mapProductionUserLookup({
    productionUsers: productionUsersPayload.value ?? [],
    activityLogs: activityLogs.value ?? [],
    assignments: assignmentsPayload.value ?? [],
  })
})

const clientLookup = computed(() => {
  return mapClientLookup({
    folders: dashboardPayload.value.folders ?? [],
    requests: requestsPayload.value ?? [],
    assignments: assignmentsPayload.value ?? [],
  })
})

const assignedClientIds = computed(() => buildAssignedClientIdSet(assignmentsPayload.value ?? []))

const queueRows = computed(() => mapQueueRows({
  requests: requestsPayload.value ?? [],
  folderLookup: folderLookup.value,
  assignedClientIds: assignedClientIds.value,
}))

const folderCards = computed(() => mapFolderCards({
  folders: dashboardPayload.value.folders ?? [],
  recentFiles: dashboardPayload.value.recentFiles ?? [],
}))

const stats = computed(() => mapStats({
  requests: requestsPayload.value ?? [],
  assignedClientIds: assignedClientIds.value,
}))

const attentionItems = computed(() => mapAttentionItems({
  requests: requestsPayload.value ?? [],
  assignedClientIds: assignedClientIds.value,
}))

const adminInsights = computed(() => {
  if (!activityLogs.value.length) {
    return adminDashboardFallbacks.adminInsights
  }

  return adminDashboardFallbacks.adminInsights.map((item, index) => {
    const log = activityLogs.value[index]

    if (!log) {
      return item
    }

    return {
      ...item,
      value: log.user?.name ?? item.value,
      detail: log.description ?? item.detail,
    }
  })
})

const usersTabRows = computed(() => mapUsersTabRows({
  users: usersPayload.value ?? [],
  currentUserId: currentUser.value?.user_id,
}))

const productionOptions = computed(() =>
  Array.from(productionUserLookup.value.values()).sort((left, right) => left.name.localeCompare(right.name))
)

const clientOptions = computed(() =>
  Array.from(clientLookup.value.values()).sort((left, right) => left.name.localeCompare(right.name))
)

const assignmentsTabRows = computed(() => mapAssignmentsTabRows({
  assignments: assignmentsPayload.value ?? [],
  clientLookup: clientLookup.value,
  productionUserLookup: productionUserLookup.value,
  requests: requestsPayload.value ?? [],
}))

const {
  assignmentsSaving,
  assignmentDeletingId,
  handleAssignmentSave,
  handleAssignmentRemove,
} = useAdminAssignments({
  clearError,
  setError,
  loadAssignments,
})

const {
  editingRequestId,
  dueDateDrafts,
  requestDueDateSavingId,
  requestDueDateErrors,
  requestDueDateFeedback,
  beginRequestDueDateEdit,
  cancelRequestDueDateEdit,
  updateRequestDueDateDraft,
  saveRequestDueDate,
} = useAdminRequestDueDates({
  requestsPayload,
})

const {
  userRoleSavingId,
  creatingAgent,
  handleUserRoleUpdate,
  handleAgentCreate,
} = useAdminUsers({
  usersPayload,
  currentUser,
  authStore,
  clearError,
  setError,
})

useAdminRealtimeRefresh({
  notificationStore,
  authStore,
  refreshAction: refreshDashboardAndRequests,
  setError,
})

onMounted(() => {
  loadAdminDashboard()
})
</script>

<template>
  <div class="pm-page text-ink transition-colors dark:text-zinc-100">
    <div class="min-h-screen xl:grid xl:grid-cols-[18.5rem_minmax(0,1fr)]">
      <AdminDashboardSidebar :current-user="currentUser" :active-item="activeItem" @navigate="activeItem = $event" />

      <main class="min-w-0">
        <AdminDashboardHeader
          :active-item="activeItem"
          :notifications="notificationStore.notifications"
          :notifications-loading="notificationStore.loading"
          :unread-count="notificationStore.unreadCount"
          :mark-read-action="notificationStore.markAsRead"
          :mark-all-read-action="notificationStore.markAllAsRead"
        />

        <div class="px-6 py-8 sm:px-8 lg:px-10">
          <p v-if="error" class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700">
            {{ error }}
          </p>

          <DashboardOverviewSkeleton v-if="loading" />

          <template v-else>
            <template v-if="activeItem === 'overview'">
              <AdminDashboardStatGrid :stats="stats" />

              <div class="mt-6">
                <AdminDashboardAttentionPanel :items="attentionItems" />
              </div>

              <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.7fr)_minmax(20rem,0.92fr)]">
                <AdminDashboardRequestsSection
                  :requests="queueRows.slice(0, 6)"
                  :editing-request-id="editingRequestId"
                  :due-date-drafts="dueDateDrafts"
                  :saving-request-id="requestDueDateSavingId"
                  :request-errors="requestDueDateErrors"
                  :request-feedback="requestDueDateFeedback"
                  :start-edit-action="beginRequestDueDateEdit"
                  :cancel-edit-action="cancelRequestDueDateEdit"
                  :update-draft-action="updateRequestDueDateDraft"
                  :save-due-date-action="saveRequestDueDate"
                />
                <AdminDashboardSecondaryPanels :folders="folderCards" :insights="adminInsights" />
              </div>
            </template>

            <AdminDashboardRequestsTab
              v-else-if="activeItem === 'requests'"
              :rows="queueRows"
              :editing-request-id="editingRequestId"
              :due-date-drafts="dueDateDrafts"
              :saving-request-id="requestDueDateSavingId"
              :request-errors="requestDueDateErrors"
              :request-feedback="requestDueDateFeedback"
              :start-edit-action="beginRequestDueDateEdit"
              :cancel-edit-action="cancelRequestDueDateEdit"
              :update-draft-action="updateRequestDueDateDraft"
              :save-due-date-action="saveRequestDueDate"
            />
            <AdminDashboardUsersTab
              v-else-if="activeItem === 'users'"
              :users="usersTabRows"
              :saving-user-id="userRoleSavingId"
              :creating-agent="creatingAgent"
              :update-role-action="handleUserRoleUpdate"
              :create-agent-action="handleAgentCreate"
            />
            <AdminDashboardAssignmentsTab
              v-else-if="activeItem === 'assignments'"
              :assignments="assignmentsTabRows"
              :client-options="clientOptions"
              :production-options="productionOptions"
              :saving="assignmentsSaving"
              :deleting-id="assignmentDeletingId"
              :save-assignment-action="handleAssignmentSave"
              :remove-assignment-action="handleAssignmentRemove"
            />
          </template>
        </div>
      </main>
    </div>
  </div>
</template>
