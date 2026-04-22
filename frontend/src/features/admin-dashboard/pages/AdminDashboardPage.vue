<script setup>
import { computed, onMounted, ref } from 'vue'
import AdminDashboardAttentionPanel from '../components/AdminDashboardAttentionPanel.vue'
import AdminDashboardHeader from '../components/AdminDashboardHeader.vue'
import AdminDashboardRequestsSection from '../components/AdminDashboardRequestsSection.vue'
import AdminDashboardSecondaryPanels from '../components/AdminDashboardSecondaryPanels.vue'
import AdminDashboardSidebar from '../components/AdminDashboardSidebar.vue'
import AdminDashboardStatGrid from '../components/AdminDashboardStatGrid.vue'
import { adminDashboardFallbacks } from '../data/adminDashboardFallbacks'
import { fetchAdminRequests } from '../../../services/adminService'
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

const currentUser = computed(() => authStore.user ?? {})

const folderLookup = computed(() => {
  const map = new Map()

  for (const folder of dashboardPayload.value.folders ?? []) {
    map.set(folder.folder_id ?? folder.id, folder)
  }

  return map
})

const queueRows = computed(() =>
  requestsPayload.value.slice(0, 6).map((request, index) => {
    const folder = folderLookup.value.get(request.folder_id)
    const reference = request.request_id?.slice(0, 8)?.toUpperCase() ?? `REQ-${index + 1000}`
    const clientName = folder?.client?.name ?? folder?.folder_name ?? `Client ${index + 1}`
    const folderName = folder?.folder_name ?? folder?.name ?? request.folder_id ?? 'Unassigned folder'
    const requestTypeLabel = request.request_type === 'new_asset' ? 'New asset' : 'Update asset'
    const isUnassigned = !folder
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
  const unassignedRequests = openRequests.filter((request) => !folderLookup.value.get(request.folder_id))
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
  const unassignedRequests = openRequests.filter((request) => !folderLookup.value.get(request.folder_id))
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

const governanceInsights = computed(() => adminDashboardFallbacks.governanceInsights)

const loadAdminDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const [dashboardResponse, requestsResponse] = await Promise.all([
      fetchDashboard(),
      fetchAdminRequests(),
    ])

    dashboardPayload.value = dashboardResponse.data.data
    requestsPayload.value = requestsResponse.data.data.requests ?? []
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
  <div class="min-h-screen bg-[#f6f1e7] text-zinc-900 transition-colors dark:bg-[#131313] dark:text-zinc-100">
    <div class="min-h-screen xl:grid xl:grid-cols-[17.75rem_minmax(0,1fr)]">
      <AdminDashboardSidebar :current-user="currentUser" :active-item="activeItem" @navigate="activeItem = $event" />

      <main class="min-w-0 bg-[linear-gradient(to_right,rgba(24,24,27,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(24,24,27,0.05)_1px,transparent_1px)] bg-[size:8px_8px] dark:bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)]">
        <AdminDashboardHeader :active-item="activeItem" />

        <div class="px-6 py-8 sm:px-8 lg:px-10">
          <p v-if="error" class="mb-6 border border-[#dfb3ab] bg-[#fff3f1] px-4 py-3 text-sm text-[#d73931] dark:border-[#57231f] dark:bg-[#261716] dark:text-[#f06753]">
            {{ error }}
          </p>

          <div v-if="loading" class="flex min-h-[18rem] items-center justify-center border border-black/10 bg-white/60 text-sm uppercase tracking-[0.3em] text-zinc-500 dark:border-white/10 dark:bg-[#1a1a1a]">
            Loading admin overview
          </div>

          <template v-else>
            <AdminDashboardStatGrid :stats="stats" />

            <div class="mt-6">
              <AdminDashboardAttentionPanel :items="attentionItems" />
            </div>

            <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.65fr)_minmax(19rem,0.9fr)]">
              <AdminDashboardRequestsSection :requests="queueRows" />
              <AdminDashboardSecondaryPanels :folders="folderCards" :insights="governanceInsights" />
            </div>
          </template>
        </div>
      </main>
    </div>
  </div>
</template>
