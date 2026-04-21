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
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-2xl font-semibold tracking-tight text-slate-950">Asset Catalog</h3>
        <p class="mt-1 text-sm text-slate-500">
          Browse approved files, switch views, and select an asset when you need a precise revision request.
        </p>
      </div>

      <div class="inline-flex w-fit rounded-2xl border border-slate-200 bg-white p-1 shadow-sm">
        <button
          :class="[
            'rounded-[1rem] px-4 py-2 text-sm font-medium transition',
            viewMode === 'grid'
              ? 'bg-blue-700 text-white shadow-sm'
              : 'text-slate-500 hover:text-slate-900'
          ]"
          @click="emit('update:viewMode', 'grid')"
        >
          Grid
        </button>
        <button
          :class="[
            'rounded-[1rem] px-4 py-2 text-sm font-medium transition',
            viewMode === 'list'
              ? 'bg-blue-700 text-white shadow-sm'
              : 'text-slate-500 hover:text-slate-900'
          ]"
          @click="emit('update:viewMode', 'list')"
        >
          List
        </button>
      </div>
    </div>

    <div
      v-if="loading"
      class="grid gap-5 md:grid-cols-2"
    >
      <div
        v-for="item in 4"
        :key="item"
        class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm"
      >
        <div class="aspect-[16/10] animate-pulse rounded-2xl bg-slate-100"></div>
        <div class="mt-4 space-y-3">
          <div class="h-4 w-1/2 animate-pulse rounded bg-slate-100"></div>
          <div class="h-3 w-1/3 animate-pulse rounded bg-slate-100"></div>
          <div class="h-10 w-full animate-pulse rounded-xl bg-slate-100"></div>
        </div>
      </div>
    </div>

    <div
      v-else-if="!files.length"
      class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-[0_18px_45px_rgba(15,23,42,0.05)]"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-500">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h4.38a2.5 2.5 0 0 1 1.77.73l.62.62A2.5 2.5 0 0 0 14.04 7h4.46A2.5 2.5 0 0 1 21 9.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-11Z" />
        </svg>
      </div>
      <h4 class="mt-5 text-lg font-semibold text-slate-950">No files match this view</h4>
      <p class="mt-2 text-sm text-slate-500">
        {{ searchQuery ? `No approved files match "${searchQuery}". Try a broader search.` : 'Approved files will appear here once production shares them to your assigned folder.' }}
      </p>
    </div>

    <div
      v-else-if="viewMode === 'grid'"
      class="grid gap-6 lg:grid-cols-2"
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
      class="overflow-hidden rounded-[1.75rem] border border-slate-200/70 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.06)]"
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
