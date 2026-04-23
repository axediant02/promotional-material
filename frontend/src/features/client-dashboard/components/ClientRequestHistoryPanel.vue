<script setup>
defineProps({
  requests: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const statusClasses = {
  pending: 'border-amber-200 bg-amber-50 text-amber-700',
  in_progress: 'border-blue-200 bg-blue-50 text-blue-700',
  done: 'border-emerald-200 bg-emerald-50 text-emerald-700',
}

function formatStatus(status) {
  if (!status) {
    return 'Unknown'
  }

  return status
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

function formatRequestType(type) {
  if (!type) {
    return 'Request'
  }

  return type
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

function formatDate(value) {
  if (!value) {
    return 'Awaiting admin review'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(value))
}
</script>

<template>
  <section id="request-history" class="pm-surface rounded-[1.75rem] p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-muted dark:text-zinc-400">Request History</p>
        <h3 class="mt-2 text-xl font-semibold text-ink dark:text-white">Recent requests</h3>
        <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">
          Track the requests you already submitted while production works through the queue.
        </p>
      </div>
      <div class="rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-border/70 dark:bg-white/5 dark:ring-white/10">
        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Total</p>
        <p class="mt-1 text-lg font-semibold text-ink dark:text-white">{{ requests.length }}</p>
      </div>
    </div>

    <div v-if="loading" class="mt-6 space-y-4">
      <div
        v-for="item in 3"
        :key="item"
        class="rounded-2xl border border-border bg-white/60 p-4 dark:border-white/10 dark:bg-white/5"
      >
        <div class="h-3 w-24 animate-pulse rounded bg-brand-50 dark:bg-white/10"></div>
        <div class="mt-3 h-5 w-1/2 animate-pulse rounded bg-brand-50 dark:bg-white/10"></div>
        <div class="mt-3 h-4 w-full animate-pulse rounded bg-brand-50 dark:bg-white/10"></div>
      </div>
    </div>

    <div
      v-else-if="!requests.length"
      class="mt-6 rounded-2xl border border-dashed border-border bg-white/60 px-5 py-6 text-sm text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
    >
      No requests yet. Submit your first request from the panel above.
    </div>

    <div v-else class="mt-6 space-y-4">
      <article
        v-for="request in requests"
        :key="request.request_id"
        class="rounded-2xl border border-border bg-white/60 p-4 transition hover:border-brand-300 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20"
        :class="request.status === 'done' ? 'opacity-80' : 'shadow-[0_12px_28px_rgba(75,61,116,0.05)]'"
      >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]"
                :class="statusClasses[request.status] ?? 'border-border bg-white text-muted dark:border-white/10 dark:bg-white/10 dark:text-white'"
              >
                {{ formatStatus(request.status) }}
              </span>
              <span class="text-[10px] font-semibold uppercase tracking-[0.24em] text-muted dark:text-zinc-400">
                {{ formatRequestType(request.request_type) }}
              </span>
              <span
                class="rounded-full bg-brand-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-brand-700 dark:bg-white/10 dark:text-white"
              >
                {{ request.status === 'done' ? 'Completed' : 'Open Request' }}
              </span>
            </div>
            <h4 class="mt-3 text-base font-semibold text-ink dark:text-white">{{ request.title }}</h4>
            <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ request.description }}</p>
          </div>

          <div class="sm:w-40 sm:flex-shrink-0">
            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-muted dark:text-zinc-400">{{ request.due_date ? 'Due Date' : 'Timeline' }}</p>
            <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ formatDate(request.due_date) }}</p>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
