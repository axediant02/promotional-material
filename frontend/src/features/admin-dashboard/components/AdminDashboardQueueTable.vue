<script setup>
defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
})

const statusStyles = {
  pending: 'bg-black/[0.04] text-zinc-700 dark:bg-white/[0.04] dark:text-zinc-300',
  in_progress: 'bg-[#fbe1de] text-[#d73931] dark:bg-[#3b1715] dark:text-[#f06753]',
  done: 'bg-[#fff0c8] text-[#9b6a00] dark:bg-[#3f3310] dark:text-[#f0b640]',
}

const formatStatus = (status) => (status ?? 'pending').replaceAll('_', ' ')
</script>

<template>
  <section class="pm-surface overflow-hidden rounded-[1.9rem]">
    <header class="hidden gap-4 border-b border-border/70 px-5 py-4 text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:border-white/10 dark:text-zinc-400 lg:grid lg:grid-cols-[minmax(0,2.1fr)_minmax(7rem,0.8fr)_minmax(7rem,0.8fr)_minmax(8rem,0.9fr)_minmax(7rem,0.8fr)_auto]">
      <span>Request</span>
      <span>Client</span>
      <span>Type</span>
      <span>Due Date</span>
      <span>State</span>
      <span class="text-right">Action</span>
    </header>

    <article
      v-for="row in rows"
      :key="row.id"
      class="border-b border-border/70 px-5 py-5 last:border-b-0 dark:border-white/10 lg:grid lg:grid-cols-[minmax(0,2.1fr)_minmax(7rem,0.8fr)_minmax(7rem,0.8fr)_minmax(8rem,0.9fr)_minmax(7rem,0.8fr)_auto] lg:gap-4"
    >
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-3">
          <p class="text-[11px] uppercase tracking-[0.24em] text-muted dark:text-zinc-400">#{{ row.reference }}</p>
          <span
            :class="[
              'inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]',
              statusStyles[row.status] ?? statusStyles.pending,
            ]"
          >
            {{ formatStatus(row.status) }}
          </span>
          <span
            v-if="row.needsAttention"
            class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-brand-700 dark:bg-white/10 dark:text-white"
          >
            Needs attention
          </span>
        </div>

        <h3 class="mt-3 truncate text-lg font-semibold text-ink dark:text-white">{{ row.title }}</h3>
        <p class="mt-1 truncate text-sm text-muted dark:text-zinc-300">{{ row.folderName }}</p>
      </div>

      <div class="mt-4 grid gap-3 text-sm lg:mt-0 lg:contents">
        <div class="lg:block">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:text-zinc-400 lg:hidden">Client</p>
          <p class="font-medium text-ink dark:text-white">{{ row.clientName }}</p>
        </div>

        <div class="lg:block">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:text-zinc-400 lg:hidden">Type</p>
          <p class="text-muted dark:text-zinc-300">{{ row.requestTypeLabel }}</p>
        </div>

        <div class="lg:block">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:text-zinc-400 lg:hidden">Due Date</p>
          <p :class="[row.isMissingDueDate ? 'text-brand-700 dark:text-white' : 'text-muted dark:text-zinc-300']">{{ row.dueLabel }}</p>
        </div>

        <div class="lg:block">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:text-zinc-400 lg:hidden">State</p>
          <p :class="[row.isUnassigned ? 'text-brand-700 dark:text-white' : 'text-muted dark:text-zinc-300']">
            {{ row.isUnassigned ? 'Unassigned' : 'Assigned' }}
          </p>
        </div>

        <div class="pt-1 lg:flex lg:justify-end lg:pt-0">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-border bg-white/70 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 transition hover:border-brand-500 hover:bg-brand-50 dark:border-white/10 dark:bg-white/10 dark:text-white dark:hover:border-white/20 dark:hover:bg-white/15"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="4" y="5" width="16" height="15" rx="1.5" />
              <path d="M8 3v4" />
              <path d="M16 3v4" />
              <path d="M4 10h16" />
            </svg>
            Set due
          </button>
        </div>
      </div>
    </article>
  </section>
</template>
