<script setup>
defineProps({
  recentActivityFiles: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="pm-surface rounded-[1.8rem] p-5">
    <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Recent file activity</p>
    <div class="mt-5 space-y-3">
      <article
        v-for="file in recentActivityFiles"
        :key="file.id"
        class="flex items-start justify-between gap-3 border-b border-border/70 pb-3 last:border-b-0 last:pb-0 dark:border-white/10"
      >
        <div class="min-w-0">
          <p class="truncate text-sm font-medium text-ink dark:text-white">{{ file.name }}</p>
          <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ file.folderName }}</p>
        </div>
        <div class="text-right">
          <p class="text-[10px] uppercase tracking-[0.22em] text-brand-600 dark:text-brand-100">{{ file.category }}</p>
          <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ file.updatedLabel }}</p>
        </div>
      </article>
      <p v-if="!recentActivityFiles.length" class="text-sm text-muted dark:text-zinc-300">
        No recent file activity is available for your current scope.
      </p>
    </div>
  </section>
</template>
