<script setup>
defineProps({
  folders: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section>
    <div class="mb-4 flex items-center justify-between gap-4">
      <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Client folders</h2>
      <p class="text-[11px] uppercase tracking-[0.34em] text-zinc-500">{{ folders.length }} entries</p>
    </div>

    <div class="border border-black/10 bg-white/65 dark:border-white/10 dark:bg-[#181818]">
      <article
        v-for="folder in folders"
        :key="folder.id"
        class="flex items-center justify-between gap-4 border-b border-black/10 px-5 py-4 last:border-b-0 dark:border-white/10"
      >
        <div class="min-w-0">
          <h3 class="truncate text-lg font-semibold text-zinc-950 dark:text-white">
            {{ folder.clientName }}
          </h3>
          <p class="mt-1 text-[11px] uppercase tracking-[0.28em] text-zinc-500">
            /{{ folder.slug }} &bull; {{ folder.fileCount }} files
          </p>
        </div>
        <p class="shrink-0 text-sm text-zinc-500">{{ folder.updatedLabel }}</p>
      </article>
    </div>
  </section>
</template>
