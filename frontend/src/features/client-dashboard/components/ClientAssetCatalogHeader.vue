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
  <div class="mb-4 flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
      <div class="flex items-center gap-3">
        <h3 class="text-2xl font-semibold tracking-tight text-white">Asset Catalog</h3>
        <span class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-white">
          {{ count }}
        </span>
      </div>
      <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-400">
        <span>Folder: {{ folderLabel }}</span>
        <span class="text-zinc-600">&bull;</span>
        <span>Updated: {{ lastUpdatedLabel }}</span>
      </p>
    </div>

    <div class="inline-flex items-center rounded-[1rem] border border-white/10 bg-white/5 p-1.5">
      <button
        type="button"
        :class="[
          'inline-flex items-center gap-2 rounded-[0.85rem] px-3 py-2 text-sm font-semibold transition',
          viewMode === 'grid'
            ? 'bg-white/10 text-white shadow-[0_8px_20px_rgba(0,0,0,0.18)]'
            : 'text-zinc-400 hover:text-white',
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
          'inline-flex items-center gap-2 rounded-[0.85rem] px-3 py-2 text-sm font-semibold transition',
          viewMode === 'list'
            ? 'bg-white/10 text-white shadow-[0_8px_20px_rgba(0,0,0,0.18)]'
            : 'text-zinc-400 hover:text-white',
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
