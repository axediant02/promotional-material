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

    <section class="pm-surface rounded-[1.75rem]">
      <header class="border-b border-border/70 px-5 py-4">
        <div class="flex items-center justify-between gap-4">
          <h2 class="text-lg font-semibold text-ink dark:text-white">Admin signals</h2>
          <p class="rounded-full bg-brand-50 px-3 py-1 text-[10px] uppercase tracking-[0.28em] text-brand-700 dark:bg-white/10 dark:text-white">Live</p>
        </div>
      </header>

      <article
        v-for="insight in insights"
        :key="insight.id"
        class="border-b border-border/70 px-5 py-4 last:border-b-0 dark:border-white/10"
      >
        <div class="flex items-baseline justify-between gap-4">
          <p class="text-sm uppercase tracking-[0.18em] text-muted dark:text-zinc-400">{{ insight.label }}</p>
          <p class="text-lg font-semibold text-ink dark:text-white">{{ insight.value }}</p>
        </div>
        <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ insight.detail }}</p>
      </article>
    </section>
  </div>
</template>
