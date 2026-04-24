<script setup>
import NotificationInboxPopover from '../../../components/shared/NotificationInboxPopover.vue'
import { useThemeStore } from '../../../stores/theme'

defineProps({
  searchQuery: {
    type: String,
    default: '',
  },
  currentUser: {
    type: Object,
    default: () => ({}),
  },
  title: {
    type: String,
    default: 'Work queue.',
  },
  eyebrow: {
    type: String,
    default: 'Production team',
  },
  description: {
    type: String,
    default: '',
  },
  unreadCount: {
    type: Number,
    default: 0,
  },
  notifications: {
    type: Array,
    default: () => [],
  },
  notificationsLoading: {
    type: Boolean,
    default: false,
  },
  markReadAction: {
    type: Function,
    required: true,
  },
  markAllReadAction: {
    type: Function,
    required: true,
  },
})

defineEmits(['update:searchQuery'])

const themeStore = useThemeStore()
</script>

<template>
  <header class="border-b border-border/70 px-6 py-5 dark:border-white/10 sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
      <div class="min-w-0">
        <p class="text-[11px] uppercase tracking-[0.42em] text-brand-600 dark:text-brand-100">{{ eyebrow }}</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-[-0.045em] text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          {{ title }}
        </h1>
        <p v-if="description" class="mt-3 max-w-2xl text-sm leading-6 text-muted dark:text-zinc-300">
          {{ description }}
        </p>
      </div>

      <div class="flex w-full max-w-xl items-center gap-4 lg:justify-end">
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-3 flex items-center text-muted dark:text-zinc-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="m21 21-4.35-4.35m1.6-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
            </svg>
          </span>
          <input
            :value="searchQuery"
            type="text"
            placeholder="Search queue, files, or folders..."
            class="pm-input w-full rounded-2xl py-3 pl-10 pr-4 text-sm placeholder:text-muted dark:placeholder:text-zinc-500"
            @input="$emit('update:searchQuery', $event.target.value)"
          >
        </div>

        <NotificationInboxPopover
          title="Notifications"
          description="New client assignments and workflow updates will appear here in real time."
          :notifications="notifications"
          :loading="notificationsLoading"
          :unread-count="unreadCount"
          empty-message="Assignment notifications will appear here when admin routes new client work to you."
          :mark-read-action="markReadAction"
          :mark-all-read-action="markAllReadAction"
        />
        <button
          class="flex h-12 w-12 items-center justify-center rounded-2xl border border-border/80 bg-white/60 text-muted transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20 dark:hover:text-white"
          type="button"
          @click="themeStore.toggleTheme()"
        >
          <svg v-if="themeStore.isDark" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="3.5" />
            <path d="M12 2v3" />
            <path d="M12 19v3" />
            <path d="m4.93 4.93 2.12 2.12" />
            <path d="m16.95 16.95 2.12 2.12" />
            <path d="M2 12h3" />
            <path d="M19 12h3" />
            <path d="m4.93 19.07 2.12-2.12" />
            <path d="m16.95 7.05 2.12-2.12" />
          </svg>
          <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7.1 7.1 0 0 0 9.8 9.8Z" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>
