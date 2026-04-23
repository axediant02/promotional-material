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
        'pm-surface rounded-[1.8rem] px-5 py-5 sm:px-6',
        stat.emphasis
          ? 'border-brand-200 bg-[radial-gradient(circle_at_top_left,rgba(109,80,162,0.22),transparent_58%),linear-gradient(180deg,rgba(255,255,255,0.98),rgba(245,239,251,0.96))]'
          : '',
      ]"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-white/10 dark:text-white">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 19h16" />
            <path d="M7 16V8" />
            <path d="M12 16V5" />
            <path d="M17 16v-4" />
          </svg>
        </div>
        <span
          v-if="stat.emphasis"
          class="rounded-full bg-brand-100 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-brand-700 dark:bg-white/10 dark:text-white"
        >
          Priority
        </span>
      </div>
      <p class="mt-5 text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
      <div class="mt-3 flex items-end gap-3">
        <p :class="['text-4xl leading-none [font-family:\'Iowan_Old_Style\',\'Palatino_Linotype\',\'Book_Antiqua\',Palatino,serif]', stat.emphasis ? 'text-brand-700 dark:text-white' : 'text-ink dark:text-white']">
          {{ stat.value }}
        </p>
      </div>
      <p class="mt-2 text-sm text-muted dark:text-zinc-300">{{ stat.help }}</p>
    </article>
  </section>
</template>
