<script setup>
defineProps({
  folders: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section>
    <div class="mb-4 flex items-center justify-between gap-4">
      <h2 class="text-lg font-semibold text-ink dark:text-white">Client folders</h2>
      <p class="rounded-full bg-brand-50 px-3 py-1 text-[11px] uppercase tracking-[0.34em] text-brand-700 dark:bg-white/10 dark:text-white">{{ folders.length }} entries</p>
    </div>

    <div class="pm-surface overflow-hidden rounded-[1.75rem]">
      <article
        v-for="folder in folders"
        :key="folder.id"
        class="flex items-center justify-between gap-4 border-b border-border/70 px-5 py-4 last:border-b-0 dark:border-white/10"
      >
        <div class="min-w-0">
          <h3 class="truncate text-lg font-semibold text-ink dark:text-white">
            {{ folder.clientName }}
          </h3>
          <p class="mt-1 text-[11px] uppercase tracking-[0.28em] text-muted dark:text-zinc-400">
            /{{ folder.slug }} &bull; {{ folder.fileCount }} files
          </p>
        </div>
        <p class="shrink-0 text-sm text-muted dark:text-zinc-300">{{ folder.updatedLabel }}</p>
      </article>
    </div>
  </section>
</template>
