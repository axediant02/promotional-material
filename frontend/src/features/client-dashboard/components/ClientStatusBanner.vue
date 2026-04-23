<script setup>
defineProps({
  details: { type: Array, default: () => [] },
  folder: { type: Object, default: null },
  selectedFile: { type: Object, default: null },
})
</script>

<template>
  <section class="pm-surface mb-8 rounded-[1.75rem] p-6">
    <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
      <div class="flex min-w-0 items-start gap-4">
        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-white/10 dark:text-white">
          <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">Portal Status</p>
          <h3 class="mt-2 text-xl font-semibold text-ink dark:text-white">Assigned materials are ready for review</h3>
          <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">
            {{ selectedFile ? `Focused request target: ${selectedFile.file_name}` : 'Use the catalog to review approved assets and select a file when you need a precise revision request.' }}
          </p>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-3 xl:min-w-[32rem]">
        <div
          v-for="detail in details"
          :key="detail.label"
          class="rounded-2xl bg-white/70 px-4 py-4 ring-1 ring-border/70 dark:bg-white/5 dark:ring-white/10"
        >
          <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">{{ detail.label }}</p>
          <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ detail.value }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
