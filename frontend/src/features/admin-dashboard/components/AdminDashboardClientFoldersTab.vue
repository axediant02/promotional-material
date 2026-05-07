<script setup>
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'
import AdminDashboardFoldersPanel from './AdminDashboardFoldersPanel.vue'

defineProps({
  folders: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="space-y-6">
    <DashboardSectionHeader
      eyebrow="Folder directory"
      title="Client folders"
      description="Review assigned client folders and file volume in one place."
      :badge="`${folders.length} entries`"
      compact
    />

    <AdminDashboardFoldersPanel :folders="folders" />
  </section>
</template>
