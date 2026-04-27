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
      class="pm-surface rounded-[1.8rem] px-5 py-5"
    >
      <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ item.label }}</p>
      <p class="mt-4 text-4xl leading-none text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
        {{ item.value }}
      </p>
      <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ item.help }}</p>
    </article>
  </div>
</template>
