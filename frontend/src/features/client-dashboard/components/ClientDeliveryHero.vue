<script setup>
defineProps({
  folder: { type: Object, default: null },
  stats: { type: Array, default: () => [] },
  user: { type: Object, default: null },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <section class="pm-surface-strong mb-8 rounded-[2rem] p-7 sm:p-8">
    <div class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
      <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-muted dark:text-zinc-400">
          Assigned Folder <span class="mx-2 text-brand-200 dark:text-zinc-500">/</span> {{ folder?.folder_name ?? 'Waiting for assignment' }}
        </p>
        <h2 class="mt-3 text-4xl font-semibold tracking-tight text-ink dark:text-white sm:text-5xl">
          Client Delivery
          <span class="block bg-gradient-to-r from-brand-700 via-brand-600 to-brand-400 bg-clip-text text-transparent">
            Ready for Review
          </span>
        </h2>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-muted dark:text-zinc-300">
          {{ subtitle }}
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-3 xl:min-w-[34rem]">
        <article
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-[1.5rem] border border-border/80 bg-white/80 p-5 shadow-[0_14px_35px_rgba(75,61,116,0.08)] dark:border-white/10 dark:bg-white/5"
        >
          <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">{{ stat.label }}</p>
          <p class="mt-3 text-2xl font-semibold text-ink dark:text-white">{{ stat.value }}</p>
          <p class="mt-1 text-sm text-muted dark:text-zinc-300">{{ stat.help }}</p>
        </article>
      </div>
    </div>
  </section>
</template>
