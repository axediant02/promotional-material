<script setup>
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

defineProps({
  insights: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="space-y-6">
    <DashboardSectionHeader
      eyebrow="Operations pulse"
      title="Admin activity"
      description="Track recent admin updates and workload signals at a glance."
      :badge="`${insights.length} metrics`"
      compact
    />

    <section class="pm-surface overflow-hidden rounded-[1.2rem] border border-border/80 shadow-sm dark:border-white/10">
      <article
        v-for="insight in insights"
        :key="insight.id"
        class="border-b border-border/80 px-5 py-5 last:border-b-0 dark:border-white/10"
      >
        <div class="flex items-baseline justify-between gap-4">
          <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted dark:text-zinc-400">{{ insight.label }}</p>
          <p class="text-2xl font-semibold text-ink dark:text-white">{{ insight.value }}</p>
        </div>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-muted dark:text-zinc-300">{{ insight.detail }}</p>
      </article>
    </section>
  </section>
</template>
