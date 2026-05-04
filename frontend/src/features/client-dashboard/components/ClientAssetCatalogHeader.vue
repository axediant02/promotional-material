<script setup>
defineProps({
  count: { type: Number, default: 0 },
  folderLabel: { type: String, default: 'Assigned Folder' },
  lastUpdatedLabel: { type: String, default: 'No files yet' },
  viewMode: { type: String, default: 'grid' },
})

const emit = defineEmits(['update:viewMode'])
</script>

<template>
  <div class="mb-4 flex flex-col gap-4 border-b border-border/70 pb-4 dark:border-white/10 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
      <div class="flex items-center gap-3">
        <h3 class="text-xl font-semibold tracking-tight text-ink dark:text-white sm:text-2xl">Asset Catalog</h3>
        <span class="rounded-full border border-brand-200 bg-brand-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white">
          {{ count }}
        </span>
      </div>
      <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted dark:text-zinc-300">
        <span class="whitespace-nowrap">Folder: {{ folderLabel }}</span>
        <span class="hidden text-brand-200/90 dark:text-zinc-600 sm:inline">&bull;</span>
        <span class="whitespace-nowrap">Updated: {{ lastUpdatedLabel }}</span>
      </p>
    </div>

    <div class="grid w-full grid-cols-2 gap-2 rounded-[1rem] border border-border/70 bg-white/80 p-1.5 shadow-[0_8px_24px_rgba(75,61,116,0.08)] backdrop-blur dark:border-white/10 dark:bg-white/5 sm:inline-flex sm:w-auto sm:grid-cols-none">
      <button
        type="button"
        :class="[
          'inline-flex items-center justify-center gap-2 rounded-[0.85rem] px-3 py-2 text-sm font-semibold transition',
          viewMode === 'grid'
            ? 'bg-brand-50 text-brand-700 shadow-[0_8px_20px_rgba(75,61,116,0.1)] dark:bg-white/10 dark:text-white dark:shadow-[0_8px_20px_rgba(0,0,0,0.18)]'
            : 'text-muted hover:bg-brand-50/70 hover:text-brand-700 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white',
        ]"
        @click="emit('update:viewMode', 'grid')"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="4" y="4" width="6" height="6" rx="1" />
          <rect x="14" y="4" width="6" height="6" rx="1" />
          <rect x="4" y="14" width="6" height="6" rx="1" />
          <rect x="14" y="14" width="6" height="6" rx="1" />
        </svg>
        Grid
      </button>
      <button
        type="button"
        :class="[
          'inline-flex items-center justify-center gap-2 rounded-[0.85rem] px-3 py-2 text-sm font-semibold transition',
          viewMode === 'list'
            ? 'bg-brand-50 text-brand-700 shadow-[0_8px_20px_rgba(75,61,116,0.1)] dark:bg-white/10 dark:text-white dark:shadow-[0_8px_20px_rgba(0,0,0,0.18)]'
            : 'text-muted hover:bg-brand-50/70 hover:text-brand-700 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white',
        ]"
        @click="emit('update:viewMode', 'list')"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M8 6h12" />
          <path d="M8 12h12" />
          <path d="M8 18h12" />
          <path d="M4 6h.01" />
          <path d="M4 12h.01" />
          <path d="M4 18h.01" />
        </svg>
        List
      </button>
    </div>
  </div>
</template>
