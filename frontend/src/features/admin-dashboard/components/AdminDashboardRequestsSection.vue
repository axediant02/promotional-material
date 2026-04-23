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
        <h2 class="text-xl font-semibold text-ink dark:text-white">All requests</h2>
        <p class="mt-1 text-sm text-muted dark:text-zinc-300">Fresh queue items that need due dates, assignment, or governance review.</p>
      </div>
      <p class="rounded-full bg-brand-50 px-3 py-1 text-[11px] uppercase tracking-[0.34em] text-brand-700 dark:bg-white/10 dark:text-white">{{ requests.length }} entries</p>
    </div>

    <AdminDashboardQueueTable :rows="requests" />
  </section>
</template>
