<script setup>
import { computed, onMounted, ref } from 'vue'
import DashboardOverviewSkeleton from '../../../components/shared/DashboardOverviewSkeleton.vue'
import AdminDashboardAssignmentsTab from '../components/AdminDashboardAssignmentsTab.vue'
import AdminDashboardAttentionPanel from '../components/AdminDashboardAttentionPanel.vue'
import AdminDashboardHeader from '../components/AdminDashboardHeader.vue'
import AdminDashboardRequestsSection from '../components/AdminDashboardRequestsSection.vue'
import AdminDashboardRequestsTab from '../components/AdminDashboardRequestsTab.vue'
import AdminDashboardSecondaryPanels from '../components/AdminDashboardSecondaryPanels.vue'
import AdminDashboardSidebar from '../components/AdminDashboardSidebar.vue'
import AdminDashboardStatGrid from '../components/AdminDashboardStatGrid.vue'
import AdminDashboardUsersTab from '../components/AdminDashboardUsersTab.vue'
import { adminDashboardFallbacks } from '../data/adminDashboardFallbacks'
import {
  fetchAdminActivityLogs,
  fetchAdminAssignments,
  fetchAdminRequests,
  fetchAdminUsers,
  removeAdminAssignment,
  saveAdminAssignment,
  updateAdminRequestDueDate,
  updateAdminUserRole,
} from '../../../services/adminService'
import { fetchDashboard } from '../../../services/dashboardService'
import { useAuthStore } from '../../../stores/auth'

const authStore = useAuthStore()

const loading = ref(true)
const error = ref('')
const activeItem = ref('overview')
const dashboardPayload = ref({
  stats: {
    folders: 0,
    files: 0,
  },
  folders: [],
  recentFiles: [],
})
const requestsPayload = ref([])
const activityLogs = ref([])
const assignmentsPayload = ref([])
const productionUsersPayload = ref([])
const usersPayload = ref([])
const assignmentsSaving = ref(false)
const assignmentDeletingId = ref('')
const editingRequestId = ref('')
const dueDateDrafts = ref({})
const requestDueDateSavingId = ref('')
const requestDueDateErrors = ref({})
const requestDueDateFeedback = ref({})
const userRoleSavingId = ref('')

const currentUser = computed(() => authStore.user ?? {})

const formatIdLabel = (value, prefix) => {
  if (!value) {
    return prefix
  }

  return `${prefix} ${value.slice(0, 8).toUpperCase()}`
}

const folderLookup = computed(() => {
  const map = new Map()

  for (const folder of dashboardPayload.value.folders ?? []) {
    map.set(folder.folder_id ?? folder.id, folder)
  }

  return map
})

const productionUserLookup = computed(() => {
  const map = new Map()

  for (const user of productionUsersPayload.value ?? []) {
    if (user?.user_id) {
      map.set(user.user_id, {
        id: user.user_id,
        name: user.name ?? formatIdLabel(user.user_id, 'Production'),
        email: user.email ?? '',
      })
    }
  }

  for (const log of activityLogs.value ?? []) {
    const user = log.user

    if (user?.role === 'production' && user.user_id) {
      map.set(user.user_id, {
        id: user.user_id,
        name: user.name ?? formatIdLabel(user.user_id, 'Production'),
        email: user.email ?? '',
      })
    }
  }

  for (const assignment of assignmentsPayload.value ?? []) {
    if (assignment.production_id && !map.has(assignment.production_id)) {
      map.set(assignment.production_id, {
        id: assignment.production_id,
        name: formatIdLabel(assignment.production_id, 'Production'),
        email: '',
      })
    }
  }

  return map
})

const clientLookup = computed(() => {
  const map = new Map()

  for (const folder of dashboardPayload.value.folders ?? []) {
    const client = folder.client

    if (client?.user_id) {
      map.set(client.user_id, {
        id: client.user_id,
        name: client.name ?? folder.folder_name ?? formatIdLabel(client.user_id, 'Client'),
        email: client.email ?? '',
        folderName: folder.folder_name ?? 'Assigned Folder',
      })
    } else if (folder.client_id) {
      map.set(folder.client_id, {
        id: folder.client_id,
        name: folder.folder_name ?? formatIdLabel(folder.client_id, 'Client'),
        email: '',
        folderName: folder.folder_name ?? 'Assigned Folder',
      })
    }
  }

  for (const request of requestsPayload.value ?? []) {
    if (request.client_id && !map.has(request.client_id)) {
      map.set(request.client_id, {
        id: request.client_id,
        name: formatIdLabel(request.client_id, 'Client'),
        email: '',
        folderName: request.folder_id ? formatIdLabel(request.folder_id, 'Folder') : 'Assigned Folder',
      })
    }
  }

  for (const assignment of assignmentsPayload.value ?? []) {
    if (assignment.client_id && !map.has(assignment.client_id)) {
      map.set(assignment.client_id, {
        id: assignment.client_id,
        name: formatIdLabel(assignment.client_id, 'Client'),
        email: '',
        folderName: 'Assigned Folder',
      })
    }
  }

  return map
})

const queueRows = computed(() =>
  requestsPayload.value.slice(0, 12).map((request, index) => {
    const requestId = request.request_id ?? ''
    const folder = folderLookup.value.get(request.folder_id)
    const reference = requestId ? requestId.slice(0, 8).toUpperCase() : `REQ-${index + 1000}`
    const clientName = folder?.client?.name ?? folder?.folder_name ?? `Client ${index + 1}`
    const folderName = folder?.folder_name ?? folder?.name ?? request.folder_id ?? 'Unassigned folder'
    const requestTypeLabel = request.request_type === 'new_asset' ? 'New asset' : 'Update asset'
    const isUnassigned = !assignmentsPayload.value.some((assignment) => assignment.client_id === request.client_id)
    const isMissingDueDate = !request.due_date
    const needsAttention = isUnassigned || isMissingDueDate || request.status === 'pending'
    const dueLabel = request.due_date
      ? new Date(request.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
      : 'Awaiting due date'

    return {
      id: requestId,
      reference,
      title: request.title ?? 'Untitled request',
      clientName,
      folderName,
      requestTypeLabel,
      status: request.status ?? 'pending',
      dueDate: request.due_date ?? '',
      dueLabel,
      isUnassigned,
      isMissingDueDate,
      needsAttention,
    }
  })
)

const folderCards = computed(() =>
  (dashboardPayload.value.folders ?? []).slice(0, 4).map((folder) => {
    const folderId = folder.folder_id ?? folder.id
    const relatedFiles = (dashboardPayload.value.recentFiles ?? []).filter((file) => {
      const candidateFolderId = file.folder?.folder_id ?? file.folder_id
      return candidateFolderId === folderId
    })

    return {
      id: folderId,
      clientName: folder.client?.name ?? folder.folder_name ?? 'Client Folder',
      slug: (folder.folder_name ?? folder.name ?? 'folder')
        .toLowerCase()
        .replaceAll(/\s+/g, '-')
        .slice(0, 18),
      fileCount: relatedFiles.length || 0,
      updatedLabel: folder.updated_at
        ? new Date(folder.updated_at).toLocaleDateString('en-US')
        : 'Active now',
    }
  })
)

const stats = computed(() => {
  const openRequests = requestsPayload.value.filter((request) => request.status !== 'done')
  const awaitingDueDate = openRequests.filter((request) => !request.due_date)
  const unassignedRequests = openRequests.filter((request) => !assignmentsPayload.value.some((assignment) => assignment.client_id === request.client_id))
  const requestingClients = new Set(openRequests.map((request) => request.client_id).filter(Boolean))

  return [
    {
      label: 'Open Requests',
      value: openRequests.length,
      help: 'Pending governance review',
      emphasis: true,
    },
    {
      label: 'Awaiting Due Date',
      value: awaitingDueDate.length,
      help: 'Need admin review',
    },
    {
      label: 'Unassigned Requests',
      value: unassignedRequests.length,
      help: 'Need assignment context',
    },
    {
      label: 'Requesting Clients',
      value: requestingClients.size,
      help: 'Currently in queue',
    },
  ]
})

const attentionItems = computed(() => {
  const openRequests = requestsPayload.value.filter((request) => request.status !== 'done')
  const awaitingDueDate = openRequests.filter((request) => !request.due_date)
  const unassignedRequests = openRequests.filter((request) => !assignmentsPayload.value.some((assignment) => assignment.client_id === request.client_id))
  const pendingReview = openRequests.filter((request) => request.status === 'pending')

  return [
    {
      id: 'due-dates',
      label: 'Missing due dates',
      value: awaitingDueDate.length,
      badge: 'Review',
      tone: awaitingDueDate.length ? 'warning' : 'default',
      detail: 'Requests still waiting for schedule decisions before production can plan delivery.',
    },
    {
      id: 'assignments',
      label: 'Unassigned requests',
      value: unassignedRequests.length,
      badge: 'Action',
      tone: unassignedRequests.length ? 'danger' : 'default',
      detail: 'Requests missing enough context to confirm assignment or routing.',
    },
    {
      id: 'pending-review',
      label: 'Pending admin review',
      value: pendingReview.length,
      badge: 'Queue',
      tone: pendingReview.length ? 'warning' : 'default',
      detail: 'Queue items still waiting on an admin decision, due date, or governance follow-through.',
    },
  ]
})

const governanceInsights = computed(() => {
  if (!activityLogs.value.length) {
    return adminDashboardFallbacks.governanceInsights
  }

  return adminDashboardFallbacks.governanceInsights.map((item, index) => {
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

const usersTabRows = computed(() =>
  (usersPayload.value ?? []).map((user) => ({
    id: user.user_id,
    name: user.name ?? formatIdLabel(user.user_id, 'User'),
    email: user.email ?? '',
    role: user.role ?? 'client',
    status: user.status ?? '',
    note: user.user_id === currentUser.value?.user_id
      ? 'Signed-in admin account. Self role changes stay disabled.'
      : 'Live backend-driven account record for admin governance.',
    isCurrentUser: user.user_id === currentUser.value?.user_id,
  }))
)

const productionOptions = computed(() =>
  Array.from(productionUserLookup.value.values()).sort((left, right) => left.name.localeCompare(right.name))
)

const clientOptions = computed(() =>
  Array.from(clientLookup.value.values()).sort((left, right) => left.name.localeCompare(right.name))
)

const assignmentsTabRows = computed(() =>
  (assignmentsPayload.value ?? []).map((assignment) => {
    const client = clientLookup.value.get(assignment.client_id)
    const production = productionUserLookup.value.get(assignment.production_id)
    const relatedRequests = requestsPayload.value.filter((request) => request.client_id === assignment.client_id)
    const openRequests = relatedRequests.filter((request) => request.status !== 'done').length

    return {
      id: assignment.id,
      clientId: assignment.client_id,
      productionId: assignment.production_id,
      clientName: client?.name ?? formatIdLabel(assignment.client_id, 'Client'),
      clientEmail: client?.email ?? '',
      folderName: client?.folderName ?? 'Assigned Folder',
      productionName: production?.name ?? formatIdLabel(assignment.production_id, 'Production'),
      productionEmail: production?.email ?? '',
      status: assignment.status ?? 'pending',
      workload: openRequests ? `${openRequests} active ${openRequests === 1 ? 'request' : 'requests'}` : 'No open requests',
      note: relatedRequests.length
        ? `Governance currently tracks ${relatedRequests.length} total ${relatedRequests.length === 1 ? 'request' : 'requests'} for this client.`
        : 'Assignment is active, but no request records are currently visible in the admin queue.',
    }
  })
)

const loadAssignments = async () => {
  const response = await fetchAdminAssignments()
  assignmentsPayload.value = response.data.data.assignments ?? []
  productionUsersPayload.value = response.data.data.production_users ?? []
}

const handleAssignmentSave = async (payload) => {
  assignmentsSaving.value = true
  error.value = ''

  try {
    await saveAdminAssignment(payload)
    await loadAssignments()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to save the client assignment.'
    throw err
  } finally {
    assignmentsSaving.value = false
  }
}

const handleAssignmentRemove = async (assignmentId) => {
  assignmentDeletingId.value = assignmentId
  error.value = ''

  try {
    await removeAdminAssignment(assignmentId)
    await loadAssignments()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to remove the client assignment.'
    throw err
  } finally {
    assignmentDeletingId.value = ''
  }
}

const handleUserRoleUpdate = async (userId, role) => {
  userRoleSavingId.value = userId
  error.value = ''

  try {
    const response = await updateAdminUserRole(userId, { role })
    const updatedUser = response.data.data.user

    usersPayload.value = usersPayload.value.map((user) =>
      user.user_id === userId ? { ...user, ...updatedUser } : user
    )

    if (currentUser.value?.user_id === userId) {
      authStore.user = {
        ...authStore.user,
        ...updatedUser,
      }
    }
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to update the user role.'
    throw err
  } finally {
    userRoleSavingId.value = ''
  }
}

const beginRequestDueDateEdit = (row) => {
  if (!row?.id || requestDueDateSavingId.value) {
    return
  }

  editingRequestId.value = row.id
  dueDateDrafts.value = {
    ...dueDateDrafts.value,
    [row.id]: row.dueDate ? String(row.dueDate).slice(0, 10) : '',
  }
  requestDueDateErrors.value = {
    ...requestDueDateErrors.value,
    [row.id]: '',
  }
  requestDueDateFeedback.value = {
    ...requestDueDateFeedback.value,
    [row.id]: '',
  }
}

const cancelRequestDueDateEdit = (requestId) => {
  if (!requestId || requestDueDateSavingId.value === requestId) {
    return
  }

  if (editingRequestId.value === requestId) {
    editingRequestId.value = ''
  }

  dueDateDrafts.value = {
    ...dueDateDrafts.value,
    [requestId]: '',
  }
  requestDueDateErrors.value = {
    ...requestDueDateErrors.value,
    [requestId]: '',
  }
}

const updateRequestDueDateDraft = (requestId, dueDate) => {
  dueDateDrafts.value = {
    ...dueDateDrafts.value,
    [requestId]: dueDate,
  }
  requestDueDateErrors.value = {
    ...requestDueDateErrors.value,
    [requestId]: '',
  }
  requestDueDateFeedback.value = {
    ...requestDueDateFeedback.value,
    [requestId]: '',
  }
}

const saveRequestDueDate = async (requestId) => {
  const dueDate = dueDateDrafts.value[requestId]?.trim?.() ?? dueDateDrafts.value[requestId] ?? ''

  if (!dueDate) {
    requestDueDateErrors.value = {
      ...requestDueDateErrors.value,
      [requestId]: 'Select a due date before saving.',
    }
    return
  }

  requestDueDateSavingId.value = requestId
  requestDueDateErrors.value = {
    ...requestDueDateErrors.value,
    [requestId]: '',
  }

  try {
    const response = await updateAdminRequestDueDate(requestId, { due_date: dueDate })
    const updatedRequest = response.data.data.request

    requestsPayload.value = requestsPayload.value.map((request) =>
      request.request_id === requestId ? { ...request, ...updatedRequest } : request
    )

    editingRequestId.value = ''
    requestDueDateFeedback.value = {
      ...requestDueDateFeedback.value,
      [requestId]: 'Due date saved.',
    }
  } catch (err) {
    requestDueDateErrors.value = {
      ...requestDueDateErrors.value,
      [requestId]:
        err.response?.data?.errors?.due_date?.[0]
        ?? err.response?.data?.message
        ?? 'Unable to update the due date.',
    }
  } finally {
    requestDueDateSavingId.value = ''
  }
}

const loadAdminDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const [dashboardResponse, requestsResponse, logsResponse, assignmentsResponse, usersResponse] = await Promise.all([
      fetchDashboard(),
      fetchAdminRequests(),
      fetchAdminActivityLogs(),
      fetchAdminAssignments(),
      fetchAdminUsers(),
    ])

    dashboardPayload.value = dashboardResponse.data.data
    requestsPayload.value = requestsResponse.data.data.requests ?? []
    activityLogs.value = logsResponse.data.data.logs ?? []
    assignmentsPayload.value = assignmentsResponse.data.data.assignments ?? []
    productionUsersPayload.value = assignmentsResponse.data.data.production_users ?? []
    usersPayload.value = usersResponse.data.data.users ?? []
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to load the admin dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAdminDashboard()
})
</script>

<template>
  <div class="pm-page text-ink transition-colors dark:text-zinc-100">
    <div class="min-h-screen xl:grid xl:grid-cols-[18.5rem_minmax(0,1fr)]">
      <AdminDashboardSidebar :current-user="currentUser" :active-item="activeItem" @navigate="activeItem = $event" />

      <main class="min-w-0">
        <AdminDashboardHeader :active-item="activeItem" />

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
                <AdminDashboardSecondaryPanels :folders="folderCards" :insights="governanceInsights" />
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
              :update-role-action="handleUserRoleUpdate"
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
