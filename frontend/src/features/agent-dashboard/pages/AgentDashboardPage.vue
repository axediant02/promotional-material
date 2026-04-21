<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../../../components/layout/AppShell.vue'
import StatsGrid from '../../../components/ui/StatsGrid.vue'
import FolderList from '../../folders/components/FolderList.vue'
import FileTable from '../../files/components/FileTable.vue'
import { fetchDashboard } from '../../../services/dashboardService'

const payload = ref({ stats: {}, folders: [], recentFiles: [] })

const stats = computed(() => [
  { label: 'Folders', value: payload.value.stats.folders ?? 0, help: 'Cross-client browse access.' },
  { label: 'Files', value: payload.value.stats.files ?? 0, help: 'Searchable assets across folders.' },
  { label: 'Mode', value: 'Agent', help: 'Operational access without production admin tools.' },
])

onMounted(async () => {
  const response = await fetchDashboard()
  payload.value = response.data.data
})
</script>

<template>
  <AppShell title="Agent Dashboard" subtitle="Monitor recently updated client folders and pull files across the full client library.">
    <div class="space-y-6">
      <StatsGrid :stats="stats" />
      <FolderList :folders="payload.folders" />
      <FileTable :files="payload.recentFiles" />
    </div>
  </AppShell>
</template>
