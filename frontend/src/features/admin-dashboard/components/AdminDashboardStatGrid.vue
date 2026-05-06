<script setup>
const toneStyles = {
  warning: {
    card: 'border-amber-200 bg-gradient-to-br from-white via-amber-50/70 to-amber-100/45 shadow-[0_8px_24px_rgba(217,119,6,0.12)] hover:shadow-[0_12px_32px_rgba(217,119,6,0.18)]',
    icon: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
    value: 'text-amber-700 dark:text-amber-200',
    badge: 'border-amber-300 bg-amber-100 text-amber-700 dark:border-amber-400/40 dark:bg-amber-500/20 dark:text-amber-200',
  },
  danger: {
    card: 'border-red-200 bg-gradient-to-br from-white via-red-50/70 to-red-100/45 shadow-[0_8px_24px_rgba(220,38,38,0.12)] hover:shadow-[0_12px_32px_rgba(220,38,38,0.18)]',
    icon: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-200',
    value: 'text-red-700 dark:text-red-200',
    badge: 'border-red-300 bg-red-100 text-red-700 dark:border-red-400/40 dark:bg-red-500/20 dark:text-red-200',
  },
  success: {
    card: 'border-emerald-200 bg-gradient-to-br from-white via-emerald-50/70 to-emerald-100/45 shadow-[0_8px_24px_rgba(16,185,129,0.12)] hover:shadow-[0_12px_32px_rgba(16,185,129,0.18)]',
    icon: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
    value: 'text-emerald-700 dark:text-emerald-200',
    badge: 'border-emerald-300 bg-emerald-100 text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-500/20 dark:text-emerald-200',
  },
  neutral: {
    card: 'border-slate-200 bg-white/85 shadow-[0_4px_16px_rgba(15,23,42,0.06)] hover:shadow-[0_8px_24px_rgba(15,23,42,0.1)] dark:border-white/12 dark:bg-white/[0.04] dark:shadow-[0_4px_18px_rgba(0,0,0,0.18)]',
    icon: 'bg-slate-100 text-slate-700 dark:bg-white/8 dark:text-slate-200',
    value: 'text-slate-900 dark:text-white',
    badge: 'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-white/[0.06] dark:text-slate-200',
  },
}

defineProps({
  stats: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <article
      v-for="stat in stats"
      :key="stat.label"
      :class="[
        'relative overflow-hidden rounded-2xl border px-4 py-4 transition-all duration-200 sm:px-5',
        toneStyles[stat.tone ?? 'neutral'].card,
      ]"
    >
      <div class="flex items-start justify-between gap-4">
        <div
          :class="[
            'flex h-10 w-10 items-center justify-center rounded-xl',
            toneStyles[stat.tone ?? 'neutral'].icon,
          ]"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 19h16" />
            <path d="M7 16V8" />
            <path d="M12 16V5" />
            <path d="M17 16v-4" />
          </svg>
        </div>
        <span
          v-if="stat.emphasis"
          :class="['rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]', toneStyles[stat.tone ?? 'neutral'].badge]"
        >
          Action first
        </span>
      </div>
      <p class="mt-4 text-[10px] font-medium uppercase tracking-[0.3em] text-[#8a7a9e] dark:text-zinc-400">
        {{ stat.label }}
      </p>
      <div class="mt-3 flex items-end gap-3">
        <p
          :class="[
            'text-[2.15rem] leading-none font-semibold tracking-[-0.035em]',
            toneStyles[stat.tone ?? 'neutral'].value,
          ]"
        >
          {{ stat.value }}
        </p>
      </div>
      <p class="mt-2 text-sm leading-6 text-[#7a6a8e] dark:text-zinc-400">{{ stat.help }}</p>

      <div
        v-if="stat.emphasis"
        class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-brand-200/20 dark:bg-brand-400/10"
      />
    </article>
  </section>
</template>
