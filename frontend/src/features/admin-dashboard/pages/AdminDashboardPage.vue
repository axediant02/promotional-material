<script setup>
import { computed, onMounted, ref } from 'vue'
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
  removeAdminAssignment,
  saveAdminAssignment,
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
const assignmentsSaving = ref(false)
const assignmentDeletingId = ref('')

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
    const folder = folderLookup.value.get(request.folder_id)
    const reference = request.request_id?.slice(0, 8)?.toUpperCase() ?? `REQ-${index + 1000}`
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
      id: request.request_id ?? index,
      reference,
      title: request.title ?? 'Untitled request',
      clientName,
      folderName,
      requestTypeLabel,
      status: request.status ?? 'pending',
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

const usersTabRows = computed(() => adminDashboardFallbacks.users)

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

const loadAdminDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const [dashboardResponse, requestsResponse, logsResponse, assignmentsResponse] = await Promise.all([
      fetchDashboard(),
      fetchAdminRequests(),
      fetchAdminActivityLogs(),
      fetchAdminAssignments(),
    ])

    dashboardPayload.value = dashboardResponse.data.data
    requestsPayload.value = requestsResponse.data.data.requests ?? []
    activityLogs.value = logsResponse.data.data.logs ?? []
    assignmentsPayload.value = assignmentsResponse.data.data.assignments ?? []
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
    <div class="min-h-screen xl:grid xl:grid-cols-[17.75rem_minmax(0,1fr)]">
      <AdminDashboardSidebar :current-user="currentUser" :active-item="activeItem" @navigate="activeItem = $event" />

      <main class="min-w-0 bg-[linear-gradient(to_right,rgba(109,80,162,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(109,80,162,0.08)_1px,transparent_1px)] bg-[size:8px_8px] dark:bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)]">
        <AdminDashboardHeader :active-item="activeItem" />

        <div class="px-6 py-8 sm:px-8 lg:px-10">
          <p v-if="error" class="mb-6 border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-[#57231f] dark:bg-[#261716] dark:text-[#f06753]">
            {{ error }}
          </p>

          <div v-if="loading" class="flex min-h-[18rem] items-center justify-center border border-border/80 bg-surface/60 text-sm uppercase tracking-[0.3em] text-muted dark:border-white/10 dark:bg-[#1a1a1a]">
            Loading admin overview
          </div>

          <template v-else>
            <template v-if="activeItem === 'overview'">
              <AdminDashboardStatGrid :stats="stats" />

              <div class="mt-6">
                <AdminDashboardAttentionPanel :items="attentionItems" />
              </div>

              <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.65fr)_minmax(19rem,0.9fr)]">
                <AdminDashboardRequestsSection :requests="queueRows.slice(0, 6)" />
                <AdminDashboardSecondaryPanels :folders="folderCards" :insights="governanceInsights" />
              </div>
            </template>

            <AdminDashboardRequestsTab v-else-if="activeItem === 'requests'" :rows="queueRows" />
            <AdminDashboardUsersTab v-else-if="activeItem === 'users'" :users="usersTabRows" />
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
