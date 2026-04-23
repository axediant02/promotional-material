<script setup>
defineProps({
  stats: { type: Array, required: true },
})
</script>

<template>
  <div class="grid gap-4 md:grid-cols-3">
    <article
      v-for="item in stats"
      :key="item.label"
    class="rounded-[1.5rem] border border-border/80 bg-surface p-5 shadow-[0_18px_45px_rgba(75,61,116,0.06)]"
  >
      <p class="text-xs font-semibold uppercase tracking-[0.3em] text-muted">{{ item.label }}</p>
      <p class="mt-3 text-4xl font-semibold tracking-tight text-ink">{{ item.value }}</p>
      <p class="mt-2 text-sm text-muted">{{ item.help }}</p>
    </article>
  </div>
</template>
