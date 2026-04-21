<script setup>
defineProps({
  folder: { type: Object, default: null },
  stats: { type: Array, default: () => [] },
  user: { type: Object, default: null },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <section class="mb-10 rounded-[2rem] bg-[radial-gradient(circle_at_top,#eff6ff,transparent_55%),linear-gradient(135deg,#ffffff_0%,#f8fbff_55%,#f2f5fb_100%)] p-7 shadow-[0_22px_60px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 sm:p-8">
    <div class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
      <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">
          Assigned Folder <span class="mx-2 text-slate-300">/</span> {{ folder?.folder_name ?? 'Waiting for assignment' }}
        </p>
        <h2 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">
          Approved Asset Delivery
          <span class="block bg-gradient-to-r from-blue-800 via-blue-600 to-cyan-500 bg-clip-text text-transparent">
            Ready For Client Review
          </span>
        </h2>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
          {{ subtitle }}
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-3 xl:min-w-[34rem]">
        <article
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-[1.5rem] border border-slate-200/70 bg-white/85 p-5 shadow-[0_14px_35px_rgba(15,23,42,0.06)]"
        >
          <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">{{ stat.label }}</p>
          <p class="mt-3 text-2xl font-semibold text-slate-950">{{ stat.value }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ stat.help }}</p>
        </article>
      </div>
    </div>
  </section>
</template>
