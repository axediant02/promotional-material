<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../../../components/layout/AppShell.vue'
import StatsGrid from '../../../components/ui/StatsGrid.vue'
import FolderList from '../../folders/components/FolderList.vue'
import FileTable from '../../files/components/FileTable.vue'
import { fetchDashboard } from '../../../services/dashboardService'

const payload = ref({ stats: {}, folders: [], recentFiles: [] })

const stats = computed(() => [
  { label: 'Folders', value: payload.value.stats.folders ?? 0, help: 'Your assigned storage area.' },
  { label: 'Files', value: payload.value.stats.files ?? 0, help: 'Available photo materials.' },
  { label: 'Access', value: 'Private', help: 'Downloads stay inside your approved folder.' },
])

onMounted(async () => {
  const response = await fetchDashboard()
  payload.value = response.data.data
})
</script>

<template>
  <AppShell title="Client Dashboard" subtitle="Review your recent materials, browse your folder, and download approved assets.">
    <div class="space-y-6">
      <StatsGrid :stats="stats" />
      <FolderList :folders="payload.folders" />
      <FileTable :files="payload.recentFiles" />
    </div>
  </AppShell>
</template>
