<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="grid gap-4 lg:grid-cols-3">
    <article
      v-for="item in items"
      :key="item.id"
      :class="[
        'pm-surface rounded-[1.6rem] px-5 py-5',
        item.tone === 'danger'
          ? 'border-brand-200 bg-[radial-gradient(circle_at_top_left,rgba(109,80,162,0.20),transparent_62%),linear-gradient(180deg,rgba(255,255,255,0.98),rgba(248,242,252,0.96))]'
          : item.tone === 'warning'
            ? 'border-brand-200 bg-[radial-gradient(circle_at_top_left,rgba(101,76,129,0.18),transparent_60%),linear-gradient(180deg,rgba(255,255,255,0.98),rgba(244,236,251,0.96))]'
            : '',
      ]"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[10px] uppercase tracking-[0.34em] text-muted dark:text-zinc-400">{{ item.label }}</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-ink dark:text-white">{{ item.value }}</p>
        </div>
        <span
          :class="[
            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.26em]',
            item.tone === 'danger'
              ? 'bg-brand-100 text-brand-700 dark:bg-white/10 dark:text-white'
              : item.tone === 'warning'
                ? 'bg-brand-50 text-brand-600 dark:bg-white/10 dark:text-white'
                : 'bg-white/70 text-muted dark:bg-white/10 dark:text-white',
          ]"
        >
          {{ item.badge }}
        </span>
      </div>
      <p class="mt-3 text-sm leading-6 text-muted dark:text-zinc-300">{{ item.detail }}</p>
    </article>
  </section>
</template>
