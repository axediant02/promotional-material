<script setup>
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

const toneStyles = {
  danger: {
    card: 'border-red-200 bg-gradient-to-br from-white via-red-50/80 to-red-100/45 dark:border-red-400/30 dark:from-white/[0.03] dark:via-red-500/10 dark:to-red-400/10',
    value: 'text-red-700 dark:text-red-200',
    badge: 'border-red-300 bg-red-100 text-red-700 dark:border-red-400/40 dark:bg-red-500/20 dark:text-red-200',
  },
  warning: {
    card: 'border-amber-200 bg-gradient-to-br from-white via-amber-50/80 to-amber-100/45 dark:border-amber-400/30 dark:from-white/[0.03] dark:via-amber-500/10 dark:to-amber-400/10',
    value: 'text-amber-700 dark:text-amber-200',
    badge: 'border-amber-300 bg-amber-100 text-amber-700 dark:border-amber-400/40 dark:bg-amber-500/20 dark:text-amber-200',
  },
  neutral: {
    card: 'border-slate-200 bg-white/88 dark:border-white/12 dark:bg-white/[0.04]',
    value: 'text-slate-900 dark:text-white',
    badge: 'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-white/[0.06] dark:text-slate-200',
  },
}

defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="mx-auto flex w-full max-w-[82rem] flex-col space-y-4">
    <DashboardSectionHeader
      eyebrow="Attention summary"
      title="What needs action."
      description="Queue items that need assignment, due dates, or review before they can move forward."
      :badge="`${items.length} signals`"
      compact
    />

    <div class="grid gap-3 lg:auto-rows-fr lg:grid-cols-3">
      <article
      v-for="item in items"
      :key="item.id"
      :class="[
        'flex h-full min-h-[11.5rem] flex-col rounded-2xl border px-4 py-4 shadow-[0_4px_16px_rgba(75,61,116,0.06)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(75,61,116,0.1)]',
        toneStyles[item.tone ?? 'neutral'].card,
        ]"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="text-[10px] font-medium uppercase tracking-[0.3em] text-[#8a7a9e] dark:text-zinc-400">{{ item.label }}</p>
            <p
              :class="[
                'mt-2 text-[2rem] font-semibold leading-none tracking-[-0.035em]',
                toneStyles[item.tone ?? 'neutral'].value,
              ]"
            >
              {{ item.value }}
            </p>
          </div>
          <span
            :class="[
              'inline-flex min-h-[2rem] shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]',
              toneStyles[item.tone ?? 'neutral'].badge,
            ]"
          >
            {{ item.badge }}
          </span>
        </div>
        <p class="mt-3 text-sm leading-6 text-[#7a6a8e] dark:text-zinc-300">
          {{ item.detail }}
        </p>
      </article>
    </div>
  </section>
</template>
