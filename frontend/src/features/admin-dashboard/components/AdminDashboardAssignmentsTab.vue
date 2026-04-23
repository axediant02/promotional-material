<script setup>
defineProps({
  assignments: {
    type: Array,
    default: () => [],
  },
})

const statusStyles = {
  active: 'bg-[#e8f4eb] text-[#2f7a45] dark:bg-[#15281d] dark:text-[#77d18f]',
  needs_review: 'bg-[#fff0c8] text-[#9b6a00] dark:bg-[#3f3310] dark:text-[#f0b640]',
}
</script>

<template>
  <section class="space-y-6">
    <header class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-zinc-950 dark:text-white">Assignments</h2>
      <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Operational view for client-to-production ownership and balancing delivery load.
      </p>
    </header>

    <section class="grid gap-4 xl:grid-cols-3">
      <article
        v-for="assignment in assignments"
        :key="assignment.id"
        class="border border-black/10 bg-white/65 px-5 py-5 dark:border-white/10 dark:bg-[#181818]"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-[10px] uppercase tracking-[0.28em] text-zinc-500">Client</p>
            <h3 class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ assignment.clientName }}</h3>
          </div>
          <span :class="['inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', statusStyles[assignment.status] ?? statusStyles.active]">
            {{ assignment.status.replaceAll('_', ' ') }}
          </span>
        </div>

        <div class="mt-5 space-y-3 text-sm">
          <div>
            <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Production owner</p>
            <p class="mt-1 text-zinc-800 dark:text-zinc-200">{{ assignment.productionName }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Workload</p>
            <p class="mt-1 text-zinc-800 dark:text-zinc-200">{{ assignment.workload }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Notes</p>
            <p class="mt-1 leading-6 text-zinc-600 dark:text-zinc-400">{{ assignment.note }}</p>
          </div>
        </div>
      </article>
    </section>
  </section>
</template>
