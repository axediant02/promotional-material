<script setup>
import AdminDashboardFoldersPanel from './AdminDashboardFoldersPanel.vue'

defineProps({
  folders: {
    type: Array,
    default: () => [],
  },
  insights: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="space-y-6">
    <AdminDashboardFoldersPanel :folders="folders" />

    <section class="border border-black/10 bg-white/65 dark:border-white/10 dark:bg-[#181818]">
      <header class="border-b border-black/10 px-5 py-4 dark:border-white/10">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Governance signals</h2>
          <p class="text-[10px] uppercase tracking-[0.28em] text-zinc-500">Fallback</p>
        </div>
      </header>

      <article
        v-for="insight in insights"
        :key="insight.id"
        class="border-b border-black/10 px-5 py-4 last:border-b-0 dark:border-white/10"
      >
        <div class="flex items-baseline justify-between gap-4">
          <p class="text-sm uppercase tracking-[0.18em] text-zinc-500">{{ insight.label }}</p>
          <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ insight.value }}</p>
        </div>
        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ insight.detail }}</p>
      </article>
    </section>
  </div>
</template>
