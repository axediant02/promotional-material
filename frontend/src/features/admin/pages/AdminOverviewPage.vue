<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../../../components/layout/AppShell.vue'
import StatsGrid from '../../../components/ui/StatsGrid.vue'
import FolderList from '../../folders/components/FolderList.vue'
import FileTable from '../../files/components/FileTable.vue'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchPendingClients } from '../../../services/approvalService'
import { fetchActivityLogs, fetchRecycleBin } from '../../../services/activityLogService'

const payload = ref({ stats: {}, folders: [], recentFiles: [] })
const pendingClients = ref([])
const recycleBin = ref([])
const logs = ref([])

const stats = computed(() => [
  { label: 'Folders', value: payload.value.stats.folders ?? 0, help: 'Client spaces under production control.' },
  { label: 'Files', value: payload.value.stats.files ?? 0, help: 'Live uploaded photo assets.' },
  { label: 'Pending', value: payload.value.stats.pendingClients ?? 0, help: 'Clients awaiting approval.' },
])

onMounted(async () => {
  const [dashboardResponse, pendingResponse, recycleResponse, logsResponse] = await Promise.all([
    fetchDashboard(),
    fetchPendingClients(),
    fetchRecycleBin(),
    fetchActivityLogs(),
  ])

  payload.value = dashboardResponse.data.data
  pendingClients.value = pendingResponse.data.data.clients
  recycleBin.value = recycleResponse.data.data.files
  logs.value = logsResponse.data.data.logs
})
</script>

<template>
  <AppShell title="Production Admin" subtitle="Manage uploads, client approvals, recycle-bin recovery, and the internal activity trail.">
    <div class="space-y-6">
      <StatsGrid :stats="stats" />
      <FolderList :folders="payload.folders" />
      <FileTable :files="payload.recentFiles" />

      <section class="grid gap-6 xl:grid-cols-3">
        <article class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
          <h2 class="text-xl font-semibold text-slate-950">Pending clients</h2>
          <ul class="mt-4 space-y-3 text-sm text-slate-600">
            <li v-for="client in pendingClients" :key="client.id" class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900">{{ client.name }}</p>
              <p>{{ client.email }}</p>
            </li>
          </ul>
        </article>

        <article class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
          <h2 class="text-xl font-semibold text-slate-950">Recycle bin</h2>
          <ul class="mt-4 space-y-3 text-sm text-slate-600">
            <li v-for="file in recycleBin" :key="file.id" class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900">{{ file.original_name }}</p>
              <p>{{ file.folder?.name || 'Unknown folder' }}</p>
            </li>
          </ul>
        </article>

        <article class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
          <h2 class="text-xl font-semibold text-slate-950">Recent log entries</h2>
          <ul class="mt-4 space-y-3 text-sm text-slate-600">
            <li v-for="log in logs.slice(0, 8)" :key="log.id" class="rounded-2xl bg-slate-50 px-4 py-3">
              <p class="font-semibold capitalize text-slate-900">{{ log.action.replaceAll('_', ' ') }}</p>
              <p>{{ log.description }}</p>
            </li>
          </ul>
        </article>
      </section>
    </div>
  </AppShell>
</template>
