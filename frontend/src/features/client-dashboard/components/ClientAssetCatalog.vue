<script setup>
import ClientAssetCard from './ClientAssetCard.vue'
import ClientAssetRow from './ClientAssetRow.vue'

defineProps({
  files: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  searchQuery: { type: String, default: '' },
  viewMode: { type: String, default: 'grid' },
})

const emit = defineEmits(['update:viewMode', 'request-change'])
</script>

<template>
  <section>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div class="max-w-2xl">
        <h3 class="text-2xl font-semibold tracking-tight text-ink dark:text-white">Asset Catalog</h3>
        <p class="mt-1 text-sm text-muted dark:text-zinc-300">
          Browse approved files, switch views, and select an asset when you need a precise revision request.
        </p>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="rounded-2xl border border-border/80 bg-white/80 px-4 py-3 shadow-sm dark:border-white/10 dark:bg-white/5">
          <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Visible Assets</p>
          <p class="mt-1 text-lg font-semibold text-ink dark:text-white">{{ files.length }}</p>
        </div>

        <div class="inline-flex w-fit rounded-2xl border border-border bg-white/80 p-1 shadow-sm dark:border-white/10 dark:bg-white/5">
          <button
            :class="[
              'rounded-[1rem] px-4 py-2 text-sm font-medium transition',
              viewMode === 'grid'
                ? 'bg-brand-600 text-white shadow-sm'
                : 'text-muted hover:text-ink dark:text-zinc-300 dark:hover:text-white'
            ]"
            @click="emit('update:viewMode', 'grid')"
          >
            Grid
          </button>
          <button
            :class="[
              'rounded-[1rem] px-4 py-2 text-sm font-medium transition',
              viewMode === 'list'
                ? 'bg-brand-600 text-white shadow-sm'
                : 'text-muted hover:text-ink dark:text-zinc-300 dark:hover:text-white'
            ]"
            @click="emit('update:viewMode', 'list')"
          >
            List
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="loading"
      class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
    >
      <div
        v-for="item in 6"
        :key="item"
        class="overflow-hidden rounded-[1.4rem] border border-border bg-white/80 p-4 shadow-sm dark:border-white/10 dark:bg-white/5"
      >
        <div class="aspect-[16/8.5] animate-pulse rounded-2xl bg-brand-50 dark:bg-white/10"></div>
        <div class="mt-4 space-y-3">
          <div class="h-4 w-1/2 animate-pulse rounded bg-brand-50 dark:bg-white/10"></div>
          <div class="h-3 w-2/3 animate-pulse rounded bg-brand-50 dark:bg-white/10"></div>
          <div class="h-9 w-full animate-pulse rounded-xl bg-brand-50 dark:bg-white/10"></div>
        </div>
      </div>
    </div>

    <div
      v-else-if="!files.length"
      class="pm-surface rounded-[1.75rem] border-dashed px-6 py-16 text-center"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-700 dark:bg-white/10 dark:text-white">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h4.38a2.5 2.5 0 0 1 1.77.73l.62.62A2.5 2.5 0 0 0 14.04 7h4.46A2.5 2.5 0 0 1 21 9.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-11Z" />
        </svg>
      </div>
      <h4 class="mt-5 text-lg font-semibold text-ink dark:text-white">No files match this view</h4>
      <p class="mt-2 text-sm text-muted dark:text-zinc-300">
        {{ searchQuery ? `No approved files match "${searchQuery}". Try a broader search.` : 'Approved files will appear here once production shares them to your assigned folder.' }}
      </p>
    </div>

    <div
      v-else-if="viewMode === 'grid'"
      class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
    >
      <ClientAssetCard
        v-for="file in files"
        :key="file.file_id"
        :file="file"
        @request-change="emit('request-change', $event)"
      />
    </div>

    <div
      v-else
      class="pm-surface overflow-hidden rounded-[1.75rem]"
    >
      <ClientAssetRow
        v-for="file in files"
        :key="file.file_id"
        :file="file"
        @request-change="emit('request-change', $event)"
      />
    </div>
  </section>
</template>
