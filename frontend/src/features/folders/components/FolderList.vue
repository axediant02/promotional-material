<script setup>
defineProps({
  folders: { type: Array, required: true },
})
</script>

<template>
  <section class="pm-surface rounded-[1.8rem] p-6 lg:p-8">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white ">Folders</h2>
        <p class="mt-2 text-sm text-muted dark:text-zinc-300">Structured client spaces for downloads and organization.</p>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="folder in folders"
        :key="folder.id"
        class="rounded-[1.4rem] border border-border/80 bg-white/50 p-5 dark:border-white/10 dark:bg-black/10"
      >
        <p class="text-sm font-semibold text-ink dark:text-white">{{ folder.name }}</p>
        <p class="mt-2 text-[10px] uppercase tracking-[0.3em] text-muted dark:text-zinc-400">{{ folder.client?.name || 'Shared' }}</p>
      </article>
    </div>
  </section>
</template>
