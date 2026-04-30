<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['open-queue', 'open-request'])

const viewportWidth = ref(typeof window === 'undefined' ? 1440 : window.innerWidth)

const updateViewportWidth = () => {
  if (typeof window === 'undefined') {
    return
  }

  viewportWidth.value = window.innerWidth
}

const visibleCardCount = computed(() => {
  if (viewportWidth.value >= 1536) {
    return 4
  }

  if (viewportWidth.value >= 1280) {
    return 3
  }

  if (viewportWidth.value >= 768) {
    return 2
  }

  return 1
})

const visibleRequests = computed(() => props.requests.slice(0, visibleCardCount.value))
const hiddenRequestCount = computed(() => Math.max(props.requests.length - visibleRequests.value.length, 0))

onMounted(() => {
  updateViewportWidth()

  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateViewportWidth)
  }
})

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateViewportWidth)
  }
})
</script>

<template>
  <section v-if="visibleRequests.length" class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div class="min-w-0">
        <h2 class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white">
          Recent New Requests
        </h2>
        <p class="mt-2 max-w-2xl text-sm text-muted dark:text-zinc-300">
          Your newest assigned requests stay pinned here for quick scanning and handoff.
        </p>
      </div>

      <button
        class="mt-[1.65rem] inline-flex items-center justify-center gap-2 self-start rounded-2xl border border-amber-300/70 bg-white/70 px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-950 transition hover:border-amber-400 hover:bg-white hover:shadow-[0_10px_18px_rgba(180,83,9,0.12)] dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20 sm:mt-[2.1rem]"
        type="button"
        @click="emit('open-queue')"
      >
        View All Requests
        <span
          v-if="hiddenRequestCount"
          class="rounded-full border border-amber-300/70 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-amber-900 dark:border-white/10 dark:bg-white/10 dark:text-white"
        >
          +{{ hiddenRequestCount }}
        </span>
      </button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
      <button
        v-for="request in visibleRequests"
        :key="request.id"
        class="relative overflow-hidden rounded-[1.8rem] border border-amber-200/80 bg-[linear-gradient(180deg,rgba(255,250,229,0.98),rgba(254,240,184,0.92))] px-5 py-5 text-left shadow-[0_18px_38px_rgba(180,83,9,0.12)] transition hover:-translate-y-[1px] hover:shadow-[0_22px_42px_rgba(180,83,9,0.16)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.04))] dark:shadow-[0_24px_70px_rgba(0,0,0,0.28)]"
        type="button"
        @click="emit('open-request', request)"
      >
        <div class="absolute left-1/2 top-0 h-8 w-24 -translate-x-1/2 -translate-y-1/2 rounded-full border border-amber-50/70 bg-amber-50/80 shadow-[0_8px_20px_rgba(180,83,9,0.12)]" aria-hidden="true" />
        <div class="absolute -right-10 -top-10 h-32 w-32 rotate-12 rounded-[3rem] bg-amber-300/35 blur-2xl dark:bg-brand-500/20" aria-hidden="true" />

        <div class="relative">
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full border border-amber-300/70 bg-white/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-amber-900 dark:border-white/10 dark:bg-white/10 dark:text-white">
              {{ request.statusLabel }}
            </span>
            <span
              v-if="request.isHighPriority"
              class="rounded-full border border-orange-300/50 bg-orange-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-orange-900 dark:border-orange-300/30 dark:bg-orange-400/10 dark:text-orange-100"
            >
              High Priority
            </span>
            <span class="text-[10px] uppercase tracking-[0.28em] text-amber-900/70 dark:text-zinc-300">
              {{ request.reference }}
            </span>
          </div>

          <h3 class="mt-4 line-clamp-2 text-xl font-semibold tracking-[-0.03em] text-amber-950 dark:text-white">
            {{ request.title }}
          </h3>
          <p class="mt-3 text-sm text-amber-950/70 dark:text-zinc-300">
            {{ request.clientName }} / {{ request.workspace }}
          </p>
          <p class="mt-1 text-sm text-amber-950/70 dark:text-zinc-300">
            {{ request.dueLabel }}
          </p>
          <p v-if="request.isHighPriority" class="mt-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-orange-900/80 dark:text-orange-100">
            Due date is close or overdue
          </p>

          <p v-if="request.description" class="mt-4 line-clamp-3 text-sm leading-6 text-amber-950/75 dark:text-zinc-300">
            {{ request.description }}
          </p>

          <div class="mt-4 inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-900/80 dark:text-brand-100">
            View task
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path d="M5 12h12" stroke-linecap="round" stroke-linejoin="round" />
              <path d="m13 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
        </div>
      </button>
    </div>
  </section>
</template>
