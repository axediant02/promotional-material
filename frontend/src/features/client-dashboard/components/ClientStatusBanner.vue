<script setup>
defineProps({
  details: { type: Array, default: () => [] },
  folder: { type: Object, default: null },
  selectedFile: { type: Object, default: null },
})
</script>

<template>
  <section class="mb-10 rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
    <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
      <div class="flex min-w-0 items-start gap-4">
        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
          <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-blue-600">Portal Status</p>
          <h3 class="mt-2 text-xl font-semibold text-slate-950">Assigned materials are ready for review</h3>
          <p class="mt-2 text-sm leading-6 text-slate-500">
            {{ selectedFile ? `Focused request target: ${selectedFile.file_name}` : 'Use the catalog to review approved assets and select a file when you need a precise revision request.' }}
          </p>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-3 xl:min-w-[32rem]">
        <div
          v-for="detail in details"
          :key="detail.label"
          class="rounded-2xl bg-slate-50 px-4 py-4 ring-1 ring-slate-200/70"
        >
          <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-slate-400">{{ detail.label }}</p>
          <p class="mt-2 text-sm font-semibold text-slate-900">{{ detail.value }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
