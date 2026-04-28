<script setup>
defineProps({
  stats: { type: Array, required: true },
})
</script>

<template>
  <div class="grid gap-4 xl:grid-cols-3">
    <article
      v-for="item in stats"
      :key="item.label"
      class="pm-surface rounded-lg px-5 py-5 transition-shadow hover:shadow-md"
    >
      <p class="text-[11px] uppercase tracking-[0.28em] text-muted dark:text-zinc-400">
        {{ item.label }}
      </p>
      <!-- Policy: Utility-first sans for numbers/stat values, not serif -->
      <p class="mt-4 text-4xl font-semibold leading-none tracking-tight text-ink dark:text-white">
        {{ item.value }}
      </p>
      <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ item.help }}</p>
    </article>
  </div>
</template>
