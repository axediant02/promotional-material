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
    return 'No due date yet'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(value))
}
</script>

<template>
  <section class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">Request History</p>
        <h3 class="mt-2 text-xl font-semibold text-slate-950">Recent requests</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">
          Track the requests you already submitted while production works through the queue.
        </p>
      </div>
      <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200/70">
        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-slate-400">Total</p>
        <p class="mt-1 text-lg font-semibold text-slate-950">{{ requests.length }}</p>
      </div>
    </div>

    <div v-if="loading" class="mt-6 space-y-4">
      <div
        v-for="item in 3"
        :key="item"
        class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4"
      >
        <div class="h-3 w-24 animate-pulse rounded bg-slate-200"></div>
        <div class="mt-3 h-5 w-1/2 animate-pulse rounded bg-slate-200"></div>
        <div class="mt-3 h-4 w-full animate-pulse rounded bg-slate-200"></div>
      </div>
    </div>

    <div
      v-else-if="!requests.length"
      class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-5 py-6 text-sm text-slate-500"
    >
      No requests yet. Submit your first request from the panel above.
    </div>

    <div v-else class="mt-6 space-y-4">
      <article
        v-for="request in requests"
        :key="request.request_id"
        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:border-slate-300"
      >
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]"
                :class="statusClasses[request.status] ?? 'border-slate-200 bg-white text-slate-600'"
              >
                {{ formatStatus(request.status) }}
              </span>
              <span class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                {{ formatRequestType(request.request_type) }}
              </span>
            </div>
            <h4 class="mt-3 text-base font-semibold text-slate-950">{{ request.title }}</h4>
            <p class="mt-2 text-sm leading-6 text-slate-500">{{ request.description }}</p>
          </div>

          <div class="sm:w-40 sm:flex-shrink-0">
            <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">Due Date</p>
            <p class="mt-2 text-sm font-semibold text-slate-900">{{ formatDate(request.due_date) }}</p>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
