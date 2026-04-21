<script setup>
defineProps({
  summaryStats: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <section class="mb-10 grid grid-cols-1 gap-6 xl:grid-cols-4">
    <article
      v-for="stat in summaryStats"
      :key="stat.label"
      class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)]"
    >
      <div class="flex items-center gap-4">
        <div
          :class="[
            'flex h-12 w-12 items-center justify-center rounded-2xl',
            {
              'bg-blue-50 text-blue-600': stat.tone === 'blue',
              'bg-indigo-50 text-indigo-600': stat.tone === 'indigo',
              'bg-amber-50 text-amber-600': stat.tone === 'amber',
              'bg-sky-50 text-sky-600': stat.tone === 'sky',
            },
          ]"
        >
          <svg v-if="stat.icon === 'transfer'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M19 11H5m14 0a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2m14 0V9a2 2 0 0 0-2-2M5 11V9a2 2 0 0 1 2-2m0 0V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M7 7h10" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          <svg v-else-if="stat.icon === 'check'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          <svg v-else-if="stat.icon === 'alert'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 9v2m0 4h.01M5.06 19h13.88c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.33 16c-.77 1.33.19 3 1.73 3Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 3v18M6 7.5h12M6 16.5h12M7 6l10 12M17 6 7 18" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">{{ stat.label }}</p>
          <p class="text-2xl font-bold text-slate-900">{{ stat.value }}</p>
        </div>
      </div>
    </article>
  </section>
</template>
