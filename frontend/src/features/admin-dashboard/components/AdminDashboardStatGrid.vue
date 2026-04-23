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
        'border px-5 py-4 sm:px-6',
        stat.emphasis
          ? 'border-[#dfb3ab] bg-[radial-gradient(circle_at_top_left,rgba(240,59,52,0.10),transparent_58%),#fff7f5] dark:border-[#57231f] dark:bg-[radial-gradient(circle_at_top_left,rgba(240,59,52,0.14),transparent_58%),#1b1b1b]'
          : 'border-black/10 bg-white/60 dark:border-white/10 dark:bg-[#1c1c1c]',
      ]"
    >
      <p class="text-[10px] uppercase tracking-[0.38em] text-zinc-500">{{ stat.label }}</p>
      <div class="mt-3 flex items-end gap-3">
        <p :class="['text-4xl leading-none [font-family:\'Iowan_Old_Style\',\'Palatino_Linotype\',\'Book_Antiqua\',Palatino,serif]', stat.emphasis ? 'text-[#d73931] dark:text-[#f03b34]' : 'text-zinc-950 dark:text-white']">
          {{ stat.value }}
        </p>
      </div>
      <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-500">{{ stat.help }}</p>
    </article>
  </section>
</template>
