<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../../../components/layout/AppShell.vue'
import StatsGrid from '../../../components/ui/StatsGrid.vue'
import FolderList from '../../folders/components/FolderList.vue'
import FileTable from '../../files/components/FileTable.vue'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchActivityLogs, fetchRecycleBin } from '../../../services/activityLogService'

const payload = ref({ stats: {}, folders: [], recentFiles: [] })
const recycleBin = ref([])
const logs = ref([])

const stats = computed(() => [
  { label: 'Folders', value: payload.value.stats.folders ?? 0, help: 'Client spaces under production control.' },
  { label: 'Files', value: payload.value.stats.files ?? 0, help: 'Live uploaded photo assets.' },
  { label: 'Recycle Bin', value: recycleBin.value.length, help: 'Deleted files still recoverable.' },
])

onMounted(async () => {
  const [dashboardResponse, recycleResponse, logsResponse] = await Promise.all([
    fetchDashboard(),
    fetchRecycleBin(),
    fetchActivityLogs(),
  ])

  payload.value = dashboardResponse.data.data
  recycleBin.value = recycleResponse.data.data.files
  logs.value = logsResponse.data.data.logs
})
</script>

<template>
  <AppShell title="Production Admin" subtitle="Manage uploads, client folders, recycle-bin recovery, and the internal activity trail.">
    <div class="space-y-6">
      <StatsGrid :stats="stats" />
      <FolderList :folders="payload.folders" />
      <FileTable :files="payload.recentFiles" />

      <section class="grid gap-6 xl:grid-cols-3">
        <article class="pm-surface rounded-[1.75rem] p-6">
          <h2 class="text-xl font-semibold text-ink">Assigned client folders</h2>
          <ul class="mt-4 space-y-3 text-sm text-muted">
            <li v-for="folder in payload.folders.slice(0, 8)" :key="folder.folder_id" class="rounded-2xl bg-brand-50 px-4 py-3">
              <p class="font-semibold text-ink">{{ folder.client?.name ?? folder.folder_name }}</p>
              <p>{{ folder.folder_name }}</p>
            </li>
          </ul>
        </article>

        <article class="pm-surface rounded-[1.75rem] p-6">
          <h2 class="text-xl font-semibold text-ink">Recycle bin</h2>
          <ul class="mt-4 space-y-3 text-sm text-muted">
            <li v-for="file in recycleBin" :key="file.file_id ?? file.id" class="rounded-2xl bg-brand-50 px-4 py-3">
              <p class="font-semibold text-ink">{{ file.file_name ?? file.original_name }}</p>
              <p>{{ file.folder?.folder_name ?? file.folder?.name ?? 'Unknown folder' }}</p>
            </li>
          </ul>
        </article>

        <article class="pm-surface rounded-[1.75rem] p-6">
          <h2 class="text-xl font-semibold text-ink">Recent log entries</h2>
          <ul class="mt-4 space-y-3 text-sm text-muted">
            <li v-for="log in logs.slice(0, 8)" :key="log.log_id ?? log.id" class="rounded-2xl bg-brand-50 px-4 py-3">
              <p class="font-semibold capitalize text-ink">{{ log.action.replaceAll('_', ' ') }}</p>
              <p>{{ log.description }}</p>
            </li>
          </ul>
        </article>
      </section>
    </div>
  </AppShell>
</template>
