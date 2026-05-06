<script setup>
import { computed, defineAsyncComponent, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import AdminDashboardAttentionPanel from '../components/AdminDashboardAttentionPanel.vue'
import AdminDashboardHeader from '../components/AdminDashboardHeader.vue'
import AdminDashboardRequestsSection from '../components/AdminDashboardRequestsSection.vue'
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
const AdminDashboardClientFoldersTab = defineAsyncComponent(() => import('../components/AdminDashboardClientFoldersTab.vue'))
const AdminDashboardSignalsTab = defineAsyncComponent(() => import('../components/AdminDashboardSignalsTab.vue'))

const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const route = useRoute()
const router = useRouter()

const sectionIds = new Set(['overview', 'requests', 'users', 'assignments', 'folders', 'signals'])

const normalizeSection = (section) => {
  const value = Array.isArray(section) ? section[0] : section

  return sectionIds.has(value) ? value : 'overview'
}

const activeItem = ref(normalizeSection(route.query.section))

watch(
  () => route.query.section,
  (section) => {
    const nextSection = normalizeSection(section)

    if (activeItem.value !== nextSection) {
      activeItem.value = nextSection
    }
  },
  { immediate: true }
)

const handleSectionNavigate = async (section) => {
  const nextSection = normalizeSection(section)

  activeItem.value = nextSection

  const query = { ...route.query }

  if (nextSection === 'overview') {
    delete query.section
  } else {
    query.section = nextSection
  }

  await router.replace({ query })
}

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
  assignments: assignmentsPayload.value ?? [],
  productionUserLookup: productionUserLookup.value,
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

const overviewSummary = computed(() => {
  const actionNeeded = stats.value[0]?.value ?? 0
  const blocked = stats.value[1]?.value ?? 0
  const unassigned = stats.value[2]?.value ?? 0
  const inProgress = stats.value[3]?.value ?? 0

  return `${actionNeeded} need action | ${blocked} blocked | ${unassigned} unassigned | ${inProgress} in progress`
})

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

const overviewActions = computed(() => {
  const attentionLookup = new Map(attentionItems.value.map((item) => [item.id, item]))

  return [
    {
      id: 'review-unassigned',
      label: 'Review unassigned requests',
      detail: `${stats.value[2]?.value ?? 0} requests still need a production owner.`,
      actionLabel: 'Queue focus',
      count: stats.value[2]?.value ?? 0,
      tone: 'danger',
      onClick: () => {
        activeItem.value = 'requests'
      },
    },
    {
      id: 'set-due-dates',
      label: 'Set missing due dates',
      detail: `${attentionLookup.get('due-dates')?.value ?? 0} requests are waiting on a schedule decision.`,
      actionLabel: 'Queue focus',
      count: attentionLookup.get('due-dates')?.value ?? 0,
      tone: 'warning',
      onClick: () => {
        activeItem.value = 'requests'
      },
    },
    {
      id: 'inspect-assignments',
      label: 'Inspect assignments',
      detail: `${assignmentsTabRows.value.length} live assignment records are visible in the admin workspace.`,
      actionLabel: 'Role action',
      count: assignmentsTabRows.value.length,
      tone: 'success',
      onClick: () => {
        activeItem.value = 'assignments'
      },
    },
  ]
})

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
      <AdminDashboardSidebar :current-user="currentUser" :active-item="activeItem" @navigate="handleSectionNavigate" />

      <main class="min-w-0">
        <AdminDashboardHeader
          :active-item="activeItem"
          :notifications="notificationStore.notifications"
          :notifications-loading="notificationStore.loading"
          :unread-count="notificationStore.unreadCount"
          :mark-read-action="notificationStore.markAsRead"
          :mark-all-read-action="notificationStore.markAllAsRead"
          :overview-summary="overviewSummary"
          :overview-actions="overviewActions"
        />

        <div class="px-5 py-6 sm:px-6 lg:px-8 xl:px-10">
          <p v-if="error" class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700">
            {{ error }}
          </p>

          <DashboardOverviewSkeleton v-if="loading" />

          <template v-else>
            <template v-if="activeItem === 'overview'">
              <AdminDashboardStatGrid :stats="stats" />

              <div class="mt-5">
                <AdminDashboardAttentionPanel :items="attentionItems" />
              </div>

              <div class="mt-5 space-y-6">
                <AdminDashboardRequestsSection
                  :requests="queueRows.slice(0, 5)"
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
            <AdminDashboardClientFoldersTab
              v-else-if="activeItem === 'folders'"
              :folders="folderCards"
            />
            <AdminDashboardSignalsTab
              v-else-if="activeItem === 'signals'"
              :insights="adminInsights"
            />
          </template>
        </div>
      </main>
    </div>
  </div>
</template>
