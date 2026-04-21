<script setup>
import { computed, onMounted, ref } from 'vue'
import AppShell from '../../../components/layout/AppShell.vue'
import StatsGrid from '../../../components/ui/StatsGrid.vue'
import FolderList from '../../folders/components/FolderList.vue'
import MediaGrid from '../components/MediaGrid.vue'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchFiles } from '../../../services/fileService'

const payload = ref({ stats: {}, folders: [], recentFiles: [] })
const files = ref([])
const loading = ref(false)

const stats = computed(() => [
  { label: 'Folders', value: payload.value.stats.folders ?? 0, help: 'Your assigned storage area.' },
  { label: 'Files', value: payload.value.stats.files ?? 0, help: 'Available photo materials.' },
  { label: 'Access', value: 'Private', help: 'Downloads stay inside your approved folder.' },
])

onMounted(async () => {
  loading.value = true
  try {
    const [dashboardResponse, filesResponse] = await Promise.all([
      fetchDashboard(),
      fetchFiles(),
    ])
    
    payload.value = dashboardResponse.data.data
    files.value = filesResponse.data.data.files || []
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AppShell title="Client Dashboard" subtitle="Review your recent materials, browse your folder, and download approved assets.">
    <div class="space-y-6">
      <StatsGrid :stats="stats" />
      <FolderList :folders="payload.folders" />
      <MediaGrid :files="files" :loading="loading" />
    </div>
  </AppShell>
</template>
