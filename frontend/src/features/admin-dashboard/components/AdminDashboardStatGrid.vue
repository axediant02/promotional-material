<script setup>
defineProps({
  stats: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="grid gap-4 xl:grid-cols-4">
    <article
      v-for="stat in stats"
      :key="stat.label"
      :class="[
        'relative overflow-hidden rounded-2xl border px-5 py-5 transition-all duration-200 sm:px-6',
        stat.emphasis
          ? 'border-brand-300 bg-gradient-to-br from-white via-brand-50/60 to-brand-100/40 shadow-[0_8px_32px_rgba(109,80,162,0.12)] hover:shadow-[0_12px_40px_rgba(109,80,162,0.18)] hover:-translate-y-0.5'
          : 'border-[#e0d8ec] bg-white/80 shadow-[0_4px_16px_rgba(75,61,116,0.06)] hover:shadow-[0_8px_28px_rgba(75,61,116,0.1)] hover:-translate-y-0.5 dark:border-white/12 dark:bg-white/[0.04] dark:shadow-[0_4px_20px_rgba(0,0,0,0.2)]',
      ]"
    >
      <div class="flex items-start justify-between gap-4">
        <div
          :class="[
            'flex h-11 w-11 items-center justify-center rounded-xl',
            stat.emphasis
              ? 'bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300'
              : 'bg-[#f3eef7] text-brand-500 dark:bg-white/8 dark:text-brand-300',
          ]"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 19h16" />
            <path d="M7 16V8" />
            <path d="M12 16V5" />
            <path d="M17 16v-4" />
          </svg>
        </div>
        <span
          v-if="stat.emphasis"
          class="rounded-full border border-brand-300 bg-brand-100 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-brand-600 dark:border-brand-400/40 dark:bg-brand-500/20 dark:text-brand-200"
        >
          Priority
        </span>
      </div>
      <p class="mt-5 text-[10px] font-medium uppercase tracking-[0.32em] text-[#8a7a9e] dark:text-zinc-400">
        {{ stat.label }}
      </p>
      <div class="mt-3 flex items-end gap-3">
        <p
          :class="[
            'text-4xl leading-none font-semibold tracking-[-0.03em]',
            stat.emphasis
              ? 'text-brand-700 dark:text-brand-200'
              : 'text-[#1a1525] dark:text-white',
          ]"
          style="font-family: system-ui, -apple-system, sans-serif;"
        >
          {{ stat.value }}
        </p>
      </div>
      <p class="mt-2 text-sm text-[#9a8aac] dark:text-zinc-400">{{ stat.help }}</p>

      <div
        v-if="stat.emphasis"
        class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-brand-200/20 dark:bg-brand-400/10"
      />
    </article>
  </section>
</template>
