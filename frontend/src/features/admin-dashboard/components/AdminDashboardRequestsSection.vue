<script setup>
import AdminDashboardQueueTable from './AdminDashboardQueueTable.vue'

defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section>
    <div class="mb-4 flex items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">New client requests</h2>
        <p class="mt-1 text-sm text-zinc-500">Fresh queue items that need due dates, assignment, or governance review.</p>
      </div>
      <p class="text-[11px] uppercase tracking-[0.34em] text-zinc-500">{{ requests.length }} entries</p>
    </div>

    <AdminDashboardQueueTable :rows="requests" />
  </section>
</template>
