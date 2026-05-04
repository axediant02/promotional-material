<script setup>
defineProps({
  fileCategoryStats: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="pm-surface rounded-[1.8rem] p-5">
    <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">Category coverage</p>
    <div class="mt-4 grid grid-cols-3 gap-3">
      <div
        v-for="stat in fileCategoryStats"
        :key="stat.id"
        class="rounded-2xl border border-border bg-white/60 px-3 py-3 text-center dark:border-white/10 dark:bg-black/10"
      >
        <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
        <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ stat.value }}</p>
      </div>
    </div>
  </section>
</template>
