<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  title: { type: String, default: 'Notifications' },
  description: { type: String, default: '' },
  notifications: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  unreadCount: { type: Number, default: 0 },
  emptyMessage: { type: String, default: 'No notifications yet.' },
  markReadAction: { type: Function, required: true },
  markAllReadAction: { type: Function, required: true },
})

const isOpen = ref(false)
const containerRef = ref(null)
const triggerRef = ref(null)
const panelRef = ref(null)
const panelPosition = ref({
  top: '0px',
  right: '1rem',
  width: 'min(24rem, calc(100vw - 2rem))',
})

const badgeLabel = computed(() => (props.unreadCount > 99 ? '99+' : props.unreadCount))

const panelStyle = computed(() => ({
  top: panelPosition.value.top,
  right: panelPosition.value.right,
  width: panelPosition.value.width,
}))

const formatTimestamp = (value) => {
  if (!value) {
    return 'Just now'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(new Date(value))
}

const formatKind = (kind) => {
  if (!kind) {
    return 'Workflow'
  }

  return kind
    .split('_')
    .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
    .join(' ')
}

const updatePanelPosition = () => {
  if (typeof window === 'undefined' || !triggerRef.value) {
    return
  }

  const rect = triggerRef.value.getBoundingClientRect()
  const viewportPadding = 16
  const desiredWidth = Math.min(384, window.innerWidth - viewportPadding * 2)
  const right = Math.max(viewportPadding, window.innerWidth - rect.right)
  const top = Math.min(window.innerHeight - viewportPadding, rect.bottom + 12)

  panelPosition.value = {
    top: `${top}px`,
    right: `${right}px`,
    width: `${desiredWidth}px`,
  }
}

const toggleOpen = async () => {
  isOpen.value = !isOpen.value

  if (isOpen.value) {
    await nextTick()
    updatePanelPosition()
  }
}

const close = () => {
  isOpen.value = false
}

const handlePointerDown = (event) => {
  const clickedTrigger = containerRef.value?.contains(event.target)
  const clickedPanel = panelRef.value?.contains(event.target)

  if (!clickedTrigger && !clickedPanel) {
    close()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    close()
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', handlePointerDown)
  document.addEventListener('keydown', handleEscape)
  window.addEventListener('resize', updatePanelPosition)
  window.addEventListener('scroll', updatePanelPosition, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handlePointerDown)
  document.removeEventListener('keydown', handleEscape)
  window.removeEventListener('resize', updatePanelPosition)
  window.removeEventListener('scroll', updatePanelPosition, true)
})
</script>

<template>
  <div ref="containerRef" class="relative">
    <button
      ref="triggerRef"
      type="button"
      class="relative flex h-12 w-12 items-center justify-center rounded-2xl border border-border/80 bg-white/60 text-muted transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20 dark:hover:text-white"
      :aria-expanded="isOpen ? 'true' : 'false'"
      aria-haspopup="dialog"
      aria-label="Open notifications"
      @click="toggleOpen"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-brand-600 px-1.5 text-[10px] font-semibold leading-none text-white shadow-sm"
      >
        {{ badgeLabel }}
      </span>
    </button>

    <Teleport to="body">
      <transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
      >
        <section
          v-if="isOpen"
          ref="panelRef"
          class="fixed z-[120] overflow-hidden rounded-[1.8rem] border border-border/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(247,243,252,0.96))] p-4 shadow-[0_30px_60px_rgba(18,12,32,0.18)] backdrop-blur dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(21,18,33,0.98),rgba(28,24,42,0.96))]"
          :style="panelStyle"
          role="dialog"
          aria-label="Notifications inbox"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3">
                <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ title }}</p>
                <span
                  class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]"
                  :class="unreadCount ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-white/15 dark:bg-white/10 dark:text-white' : 'border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300'"
                >
                  {{ unreadCount }} unread
                </span>
              </div>
              <p v-if="description" class="mt-3 max-w-[17rem] text-sm leading-6 text-muted dark:text-zinc-300">{{ description }}</p>
            </div>

            <button
              type="button"
              class="rounded-full border border-border bg-white/70 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-muted transition hover:border-brand-500 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
              :disabled="!unreadCount"
              @click="markAllReadAction"
            >
              Mark all read
            </button>
          </div>

          <div v-if="loading" class="mt-4 space-y-3">
            <div
              v-for="index in 3"
              :key="index"
              class="animate-pulse rounded-2xl border border-border bg-white/60 px-4 py-4 dark:border-white/10 dark:bg-white/5"
            >
              <div class="h-3 w-28 rounded bg-slate-200 dark:bg-white/10" />
              <div class="mt-3 h-4 w-2/3 rounded bg-slate-200 dark:bg-white/10" />
              <div class="mt-2 h-3 w-full rounded bg-slate-200 dark:bg-white/10" />
            </div>
          </div>

          <div
            v-else-if="!notifications.length"
            class="mt-4 rounded-2xl border border-dashed border-border bg-white/60 px-4 py-6 text-sm text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
          >
            {{ emptyMessage }}
          </div>

          <div v-else class="mt-4 max-h-[26rem] space-y-3 overflow-y-auto pr-1">
            <article
              v-for="notification in notifications"
              :key="notification.id"
              class="rounded-2xl border px-4 py-4 transition"
              :class="notification.readAt
                ? 'border-border bg-white/60 dark:border-white/10 dark:bg-white/5'
                : 'border-brand-200 bg-brand-50/80 shadow-[0_12px_28px_rgba(75,61,116,0.05)] dark:border-white/15 dark:bg-white/10'"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-border bg-white/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-muted dark:border-white/10 dark:bg-black/10 dark:text-zinc-300">
                      {{ formatKind(notification.kind) }}
                    </span>
                    <span class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">
                      {{ formatTimestamp(notification.createdAt) }}
                    </span>
                  </div>
                  <h3 class="mt-3 text-sm font-semibold text-ink dark:text-white">{{ notification.title }}</h3>
                  <p class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ notification.body }}</p>
                </div>

                <button
                  v-if="!notification.readAt"
                  type="button"
                  class="rounded-full border border-border bg-white/80 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-muted transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-black/10 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
                  @click="markReadAction(notification.id)"
                >
                  Mark read
                </button>
              </div>
            </article>
          </div>
        </section>
      </transition>
    </Teleport>
  </div>
</template>
