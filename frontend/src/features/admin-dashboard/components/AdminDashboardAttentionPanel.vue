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
        'border px-5 py-5',
        item.tone === 'danger'
          ? 'border-[#dfb3ab] bg-[radial-gradient(circle_at_top_left,rgba(240,59,52,0.10),transparent_60%),#fff8f6] dark:border-[#5a201d] dark:bg-[radial-gradient(circle_at_top_left,rgba(240,59,52,0.12),transparent_60%),#1b1b1b]'
          : item.tone === 'warning'
            ? 'border-[#e5d19a] bg-[radial-gradient(circle_at_top_left,rgba(240,182,64,0.10),transparent_60%),#fffbef] dark:border-[#5a4a14] dark:bg-[radial-gradient(circle_at_top_left,rgba(240,182,64,0.12),transparent_60%),#1b1b1b]'
            : 'border-black/10 bg-white/60 dark:border-white/10 dark:bg-[#1b1b1b]',
      ]"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[10px] uppercase tracking-[0.34em] text-zinc-500">{{ item.label }}</p>
          <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-zinc-950 dark:text-white">{{ item.value }}</p>
        </div>
        <span
          :class="[
            'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.26em]',
            item.tone === 'danger'
              ? 'bg-[#fbe1de] text-[#d73931] dark:bg-[#3b1715] dark:text-[#f06753]'
              : item.tone === 'warning'
                ? 'bg-[#fff0c8] text-[#9b6a00] dark:bg-[#3f3310] dark:text-[#f0b640]'
                : 'bg-black/[0.04] text-zinc-500 dark:bg-white/[0.04] dark:text-zinc-400',
          ]"
        >
          {{ item.badge }}
        </span>
      </div>
      <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ item.detail }}</p>
    </article>
  </section>
</template>
