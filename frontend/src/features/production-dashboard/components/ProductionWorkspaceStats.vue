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
      :key="stat.id"
      class="pm-surface relative overflow-hidden rounded-[1.8rem] border border-border/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(249,244,252,0.96))] px-5 py-5 transition hover:-translate-y-[1px] hover:shadow-[var(--shadow-md)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.04))]"
    >
      <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r opacity-90" :class="stat.accent" aria-hidden="true" />
      <div class="absolute right-5 top-5 h-10 w-10 rounded-full border border-white/60 bg-white/70 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] dark:border-white/10 dark:bg-white/10" aria-hidden="true" />

      <div class="relative">
        <div class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]" :class="stat.chipTone">
          Live
        </div>
        <p class="mt-4 text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
        <p :class="['mt-3 text-4xl leading-none font-semibold tracking-[-0.04em]', stat.numberTone]">
          {{ stat.value }}
        </p>
        <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ stat.detail }}</p>
      </div>
    </article>
  </section>
</template>
