<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ProductionFoldersSection from '../components/ProductionFoldersSection.vue'
import ProductionNotesSection from '../components/ProductionNotesSection.vue'
import ProductionSidebar from '../components/ProductionSidebar.vue'
import ProductionStatGrid from '../components/ProductionStatGrid.vue'
import ProductionStatusSection from '../components/ProductionStatusSection.vue'
import ProductionTopbar from '../components/ProductionTopbar.vue'
import { fetchPendingClients } from '../../../services/approvalService'
import { fetchDashboard } from '../../../services/dashboardService'
import { useAuthStore } from '../../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref('')
const dashboardData = ref({
  stats: {
    folders: 0,
    files: 0,
    pendingClients: 0,
  },
  folders: [],
  recentFiles: [],
})
const pendingClients = ref([])
const activeView = ref('live')
const searchQuery = ref('')

const currentUser = computed(() => authStore.user ?? {})

const filteredFolders = computed(() => {
  const folders = dashboardData.value.folders ?? []
  const query = searchQuery.value.trim().toLowerCase()

  if (!query) {
    return folders
  }

  return folders.filter((folder) => {
    const folderName = (folder.folder_name ?? folder.name ?? '').toLowerCase()
    const clientName = (folder.client?.name ?? '').toLowerCase()
    return folderName.includes(query) || clientName.includes(query)
  })
})

const summaryStats = computed(() => [
  {
    label: 'Active Transfers',
    value: dashboardData.value.recentFiles?.length ?? 0,
    tone: 'blue',
    icon: 'transfer',
  },
  {
    label: 'Completed Assets',
    value: dashboardData.value.stats.files ?? 0,
    tone: 'indigo',
    icon: 'check',
  },
  {
    label: 'Expiring Soon',
    value: pendingClients.value.length,
    tone: 'amber',
    icon: 'alert',
  },
  {
    label: 'Connected Clients',
    value: dashboardData.value.stats.folders ?? 0,
    tone: 'sky',
    icon: 'spark',
  },
])

const projectNotes = computed(() => {
  const pendingNotes = pendingClients.value.slice(0, 3).map((client, index) => ({
    id: client.user_id ?? client.id ?? client.email ?? index,
    tag: index === 0 ? 'Urgent Review' : index === 1 ? 'Update' : 'Missing Data',
    title: client.name,
    content: `Review ${client.email} and finalize folder access so this client can enter the delivery portal without delay.`,
    client: client.name,
    tone: index === 0 ? 'amber' : index === 1 ? 'sky' : 'emerald',
    icon: index === 0 ? 'alert' : index === 1 ? 'info' : 'triangle',
  }))

  if (pendingNotes.length) {
    return pendingNotes
  }

  return (dashboardData.value.recentFiles ?? []).slice(0, 3).map((file, index) => ({
    id: file.file_id ?? file.id ?? index,
    tag: index === 0 ? 'Recent Upload' : index === 1 ? 'Client Update' : 'Archive Ready',
    title: file.file_name ?? file.original_name ?? 'Asset ready',
    content: `Recent production activity in ${file.folder?.folder_name ?? file.folder?.name ?? 'the assigned folder'} is ready for review and next-step coordination.`,
    client: file.folder?.client?.name ?? file.folder?.folder_name ?? 'Assigned Client',
    tone: index === 0 ? 'amber' : index === 1 ? 'sky' : 'emerald',
    icon: index === 0 ? 'pin' : index === 1 ? 'info' : 'triangle',
  }))
})

const clientFolders = computed(() =>
  filteredFolders.value.slice(0, 3).map((folder, index) => {
    const relatedFiles = (dashboardData.value.recentFiles ?? []).filter((file) => {
      const fileFolderId = file.folder?.folder_id ?? file.folder_id
      return fileFolderId === (folder.folder_id ?? folder.id)
    })

    return {
      id: folder.folder_id ?? folder.id ?? index,
      name: folder.client?.name ?? folder.folder_name ?? folder.name ?? 'Client Folder',
      location: folder.folder_name ?? folder.name ?? 'Assigned Workspace',
      requests: relatedFiles.length || (index + 1) * 4,
      assets: `${Math.max(1, index + 1) * 0.8} TB`,
      status: index === 2 ? 'Priority' : 'Active',
      initials: (folder.client?.name ?? folder.folder_name ?? 'CF')
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase(),
    }
  })
)

const systemStatus = computed(() => [
  { label: 'Uptime', value: '99.998%' },
  { label: 'Avg. Latency', value: '14ms' },
  { label: 'Active Nodes', value: '1,402' },
])

const statsDescription = computed(() => `Managing ${dashboardData.value.stats.folders ?? 0} client folders across the current delivery portal.`)

const loadData = async () => {
  loading.value = true
  error.value = ''

  try {
    const [dashboardResponse, pendingClientsResponse] = await Promise.all([
      fetchDashboard(),
      fetchPendingClients(),
    ])

    dashboardData.value = dashboardResponse.data.data
    pendingClients.value = pendingClientsResponse.data.data?.clients ?? []
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to load the production dashboard.'
  } finally {
    loading.value = false
  }
}

const goToLegacyWorkspace = () => {
  router.push({ name: 'admin-overview' })
}

const signOut = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.08),_transparent_26%),linear-gradient(180deg,#f8fbff_0%,#eef3f8_100%)] text-slate-800">
    <div class="min-h-screen xl:grid xl:grid-cols-[18rem_minmax(0,1fr)]">
      <ProductionSidebar :current-user="currentUser" @legacy-workspace="goToLegacyWorkspace" @sign-out="signOut" />

      <main class="min-w-0 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <ProductionTopbar v-model:search-query="searchQuery" :current-user="currentUser" />

        <section class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Production Pulse</h1>
            <p class="mt-2 text-sm text-slate-500">{{ statsDescription }}</p>
          </div>

          <div class="flex w-full rounded-xl border border-slate-200 bg-white p-1 shadow-sm sm:w-auto">
            <button
              :class="[
                'flex-1 rounded-lg px-4 py-2 text-xs font-semibold transition sm:flex-none',
                activeView === 'live' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:text-slate-800',
              ]"
              @click="activeView = 'live'"
            >
              Live View
            </button>
            <button
              :class="[
                'flex-1 rounded-lg px-4 py-2 text-xs font-semibold transition sm:flex-none',
                activeView === 'history' ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:text-slate-800',
              ]"
              @click="activeView = 'history'"
            >
              History
            </button>
          </div>
        </section>

        <p v-if="error" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ error }}
        </p>

        <ProductionStatGrid :summary-stats="summaryStats" />
        <ProductionNotesSection :project-notes="projectNotes" @view-all="goToLegacyWorkspace" />
        <ProductionFoldersSection :client-folders="clientFolders" @view-directory="goToLegacyWorkspace" />
        <ProductionStatusSection :system-status="systemStatus" />

        <footer class="mt-12 flex flex-col gap-3 border-t border-slate-200 pt-6 text-[10px] font-medium text-slate-400 sm:flex-row sm:items-center sm:justify-between">
          <div>Production operations workspace for secure file delivery and client oversight.</div>
          <div class="flex gap-6">
            <a href="#" class="transition hover:text-slate-600">Privacy</a>
            <a href="#" class="transition hover:text-slate-600">Compliance</a>
            <a href="#" class="transition hover:text-slate-600">Support</a>
          </div>
        </footer>
      </main>
    </div>
  </div>
</template>
