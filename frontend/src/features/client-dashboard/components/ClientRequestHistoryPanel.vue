<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import SkeletonBlock from '../../../components/shared/SkeletonBlock.vue'

const props = defineProps({
  requests: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const isHistoryOpen = ref(false)

const latestRequest = computed(() => props.requests[0] ?? null)

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

const syncBodyScroll = (isOpen) => {
  if (typeof document === 'undefined') {
    return
  }

  document.body.style.overflow = isOpen ? 'hidden' : ''
}

watch(isHistoryOpen, (value) => {
  syncBodyScroll(value)
})

onBeforeUnmount(() => {
  syncBodyScroll(false)
})
</script>

<template>
  <section id="request-history" class="pm-surface rounded-[1.75rem] p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-muted dark:text-zinc-400">Latest Request</p>
        <h3 class="mt-2 text-xl font-semibold text-ink dark:text-white">Track your most recent update</h3>
        <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">
          Keep the sidebar light while still staying close to your newest request.
        </p>
      </div>
      <div class="rounded-2xl bg-white/70 px-4 py-3 ring-1 ring-border/70 dark:bg-white/5 dark:ring-white/10">
        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Total</p>
        <p class="mt-1 text-lg font-semibold text-ink dark:text-white">{{ requests.length }}</p>
      </div>
    </div>

    <div v-if="loading" class="mt-6">
      <div class="rounded-2xl border border-border bg-white/60 p-4 dark:border-white/10 dark:bg-white/5">
        <SkeletonBlock width="w-24" height="h-3" />
        <SkeletonBlock width="w-1/2" height="h-5" rounded="rounded-md" class-name="mt-3" />
        <SkeletonBlock width="w-full" height="h-4" class-name="mt-3" />
      </div>
    </div>

    <div
      v-else-if="!latestRequest"
      class="mt-6 rounded-2xl border border-dashed border-border bg-white/60 px-5 py-6 text-sm text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
    >
      No requests yet. Submit your first request from the panel above.
    </div>

    <article
      v-else
      class="mt-6 rounded-2xl border border-border bg-white/60 p-4 shadow-[0_12px_28px_rgba(75,61,116,0.05)] dark:border-white/10 dark:bg-white/5"
    >
      <div class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center gap-2">
          <span
            class="rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]"
            :class="statusClasses[latestRequest.status] ?? 'border-border bg-white text-muted dark:border-white/10 dark:bg-white/10 dark:text-white'"
          >
            {{ formatStatus(latestRequest.status) }}
          </span>
          <span class="text-[10px] font-semibold uppercase tracking-[0.24em] text-muted dark:text-zinc-400">
            {{ formatRequestType(latestRequest.request_type) }}
          </span>
        </div>

        <div>
          <h4 class="text-base font-semibold text-ink dark:text-white">{{ latestRequest.title }}</h4>
          <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ latestRequest.description }}</p>
        </div>

        <div class="rounded-xl bg-white/70 px-4 py-3 ring-1 ring-border/70 dark:bg-white/5 dark:ring-white/10">
          <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-muted dark:text-zinc-400">
            {{ latestRequest.due_date ? 'Due Date' : 'Timeline' }}
          </p>
          <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ formatDate(latestRequest.due_date) }}</p>
        </div>

        <button
          type="button"
          class="rounded-xl border border-border px-4 py-3 text-sm font-semibold text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:text-white dark:hover:border-white/20"
          @click="isHistoryOpen = true"
        >
          View all history
        </button>
      </div>
    </article>

    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isHistoryOpen"
          class="fixed inset-0 z-50 overflow-hidden bg-[rgba(19,12,36,0.5)] backdrop-blur-sm"
          @click.self="isHistoryOpen = false"
        >
          <div class="flex h-full items-center justify-center p-4 sm:p-6">
            <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-border/70 bg-[linear-gradient(180deg,rgba(253,251,255,0.98),rgba(245,239,252,0.98))] shadow-[0_25px_70px_rgba(25,18,48,0.22)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(24,20,36,0.98),rgba(18,14,29,0.98))]">
              <div class="flex items-start justify-between gap-4 border-b border-border/70 px-6 py-5 dark:border-white/10">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-muted dark:text-zinc-400">Request History</p>
                  <h3 class="mt-2 text-2xl font-semibold text-ink dark:text-white">All submitted requests</h3>
                  <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">
                    Review statuses, due dates, and previous submissions in one place.
                  </p>
                </div>
                <button
                  type="button"
                  class="flex h-11 w-11 items-center justify-center rounded-full border border-border bg-white/70 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
                  @click="isHistoryOpen = false"
                >
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 6l12 12" />
                    <path d="M18 6 6 18" />
                  </svg>
                </button>
              </div>

              <div class="flex-1 overflow-y-auto px-6 py-6">
                <div class="space-y-4">
                  <article
                    v-for="request in requests"
                    :key="request.request_id"
                    class="rounded-2xl border border-border bg-white/60 p-4 transition dark:border-white/10 dark:bg-white/5"
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
                        </div>
                        <h4 class="mt-3 text-base font-semibold text-ink dark:text-white">{{ request.title }}</h4>
                        <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ request.description }}</p>
                      </div>

                      <div class="sm:w-44 sm:flex-shrink-0">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-muted dark:text-zinc-400">
                          {{ request.due_date ? 'Due Date' : 'Timeline' }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ formatDate(request.due_date) }}</p>
                      </div>
                    </div>
                  </article>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>
