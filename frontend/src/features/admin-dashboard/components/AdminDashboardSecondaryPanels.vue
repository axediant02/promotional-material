<script setup>
import AdminDashboardFoldersPanel from './AdminDashboardFoldersPanel.vue'

defineProps({
  folders: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <AdminDashboardFoldersPanel :folders="folders" />
</template>
