<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import NotificationInboxPopover from '../../../components/shared/NotificationInboxPopover.vue'
import { useAuthStore } from '../../../stores/auth'
import { useThemeStore } from '../../../stores/theme'

const props = defineProps({
  activeItem: {
    type: String,
    default: 'overview',
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

const themeStore = useThemeStore()
const authStore = useAuthStore()
const router = useRouter()

const copyByTab = {
  overview: {
    eyebrow: 'Governance Overview',
    title: 'Admin desk.',
    description: 'Review new client requests, assign due dates, and keep governance decisions visible across the delivery system.',
    action: 'All requests',
  },
  requests: {
    eyebrow: 'Requests Queue',
    title: 'Request governance.',
    description: 'Scan the full request queue, prioritize pending work, and identify missing due dates or assignment gaps.',
    action: 'Queue filters',
  },
  users: {
    eyebrow: 'User Administration',
    title: 'Users & roles.',
    description: 'Inspect role coverage, account state, and the current access model across admin, production, agent, and client users.',
    action: 'Role updates',
  },
  assignments: {
    eyebrow: 'Assignment Oversight',
    title: 'Assignment desk.',
    description: 'Track which production owners are carrying each client relationship and surface load-balancing issues early.',
    action: 'Assignments',
  },
}

const activeCopy = computed(() => copyByTab[props.activeItem] ?? copyByTab.overview)

const handleLogout = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}
</script>

<template>
  <header class="border-b border-border/70 px-6 py-5 dark:border-white/10 sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
      <div>
        <p class="text-[11px] uppercase tracking-[0.38em] text-brand-600 dark:text-brand-100">{{ activeCopy.eyebrow }}</p>
        <h1 class="mt-2 text-4xl font-semibold tracking-[-0.045em] text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          {{ activeCopy.title }}
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-muted dark:text-zinc-300">
          {{ activeCopy.description }}
        </p>
      </div>

      <div class="flex flex-col gap-3 self-start sm:flex-row sm:items-center">
        <label class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted dark:text-zinc-400">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-3.5-3.5" />
            </svg>
          </span>
          <input class="pm-input w-full rounded-2xl py-3 pl-11 pr-4 text-sm sm:w-72" placeholder="Search command center..." />
        </label>
        <button class="pm-button-secondary inline-flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium dark:text-white">
          <span>{{ activeCopy.action }}</span>
          <span aria-hidden="true">&rarr;</span>
        </button>
        <NotificationInboxPopover
          title="Notifications"
          description="Live workflow updates for incoming client requests and governance changes."
          :notifications="notifications"
          :loading="notificationsLoading"
          :unread-count="unreadCount"
          empty-message="New request and workflow notifications will appear here."
          :mark-read-action="markReadAction"
          :mark-all-read-action="markAllReadAction"
        />
        <button
          class="pm-button-secondary inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-medium dark:text-white"
          type="button"
          @click="handleLogout"
        >
          <span>Logout</span>
        </button>
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
