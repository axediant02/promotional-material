<script setup>
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
</script>

<template>
  <section class="pm-surface rounded-[1.8rem] p-5">
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
        <p v-if="description" class="mt-3 text-sm text-muted dark:text-zinc-300">{{ description }}</p>
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

    <div v-if="loading" class="mt-5 space-y-3">
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
      class="mt-5 rounded-2xl border border-dashed border-border bg-white/60 px-4 py-6 text-sm text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
    >
      {{ emptyMessage }}
    </div>

    <div v-else class="mt-5 space-y-3">
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
</template>
