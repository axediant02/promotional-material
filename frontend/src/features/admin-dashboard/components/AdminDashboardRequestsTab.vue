<script setup>
import AdminDashboardRequestsSection from './AdminDashboardRequestsSection.vue'

defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="space-y-6">
    <header class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-zinc-950 dark:text-white">All requests</h2>
      <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Full request queue view for due-date review and governance triage.
      </p>
    </header>

    <AdminDashboardRequestsSection :requests="rows" />
  </section>
</template>
