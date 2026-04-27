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
    eyebrow: 'Admin Overview',
    title: 'Admin desk.',
    description: 'Review new client requests, assign due dates, and keep admin decisions visible across the delivery system.',
    action: 'All requests',
  },
  requests: {
    eyebrow: 'Requests Queue',
    title: 'Request management.',
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
  <header class="border-b border-[#e0d8ec]/70 bg-white/60 px-6 py-5 backdrop-blur-sm dark:border-white/10 dark:bg-[#1a1625]/60 sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
      <div>
        <p class="text-[10px] font-medium uppercase tracking-[0.34em] text-brand-600 dark:text-brand-300">
          {{ activeCopy.eyebrow }}
        </p>
        <h1 class="mt-2 text-[2.1rem] font-semibold leading-tight tracking-[-0.03em] text-[#1a1525] dark:text-white">
          {{ activeCopy.title }}
        </h1>
        <p class="mt-2.5 max-w-2xl text-sm leading-relaxed text-[#7a6a8e] dark:text-zinc-400">
          {{ activeCopy.description }}
        </p>
      </div>

      <div class="flex flex-col gap-3 self-start sm:flex-row sm:items-center">
        <label class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-[#9a8aac] dark:text-zinc-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-3.5-3.5" />
            </svg>
          </span>
          <input
            class="w-full rounded-xl border border-[#d4d0dc] bg-white py-2.5 pl-10 pr-4 text-sm text-[#1a1525] shadow-[0_2px_6px_rgba(75,61,116,0.06)] outline-none transition duration-150 placeholder:text-[#9a8aac] focus:border-brand-400 focus:shadow-[0_0_0_3px_rgba(109,80,162,0.12)] dark:border-white/15 dark:bg-white/[0.06] dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-brand-400 sm:w-64"
            placeholder="Search command center..."
          />
        </label>
        <button class="inline-flex items-center gap-2 rounded-xl border border-[#d4d0dc] bg-white px-4 py-2.5 text-sm font-medium text-[#4b3d74] shadow-[0_2px_6px_rgba(75,61,116,0.08)] transition duration-150 hover:border-brand-300 hover:bg-brand-50 dark:border-white/15 dark:bg-white/[0.06] dark:text-white dark:hover:border-white/25 dark:hover:bg-white/[0.1]">
          <span>{{ activeCopy.action }}</span>
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M5 12h14" />
            <path d="m12 5 7 7-7 7" />
          </svg>
        </button>
        <NotificationInboxPopover
          title="Notifications"
          description="Live workflow updates for incoming client requests and admin changes."
          :notifications="notifications"
          :loading="notificationsLoading"
          :unread-count="unreadCount"
          empty-message="New request and workflow notifications will appear here."
          :mark-read-action="markReadAction"
          :mark-all-read-action="markAllReadAction"
        />
        <button
          class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#d4d0dc] bg-white text-[#9a8aac] shadow-[0_2px_6px_rgba(75,61,116,0.06)] transition duration-150 hover:border-brand-300 hover:text-brand-600 dark:border-white/15 dark:bg-white/[0.06] dark:text-zinc-400 dark:hover:border-white/25 dark:hover:text-white"
          type="button"
          @click="themeStore.toggleTheme()"
        >
          <svg v-if="themeStore.isDark" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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
          <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7.1 7.1 0 0 0 9.8 9.8Z" />
          </svg>
        </button>
        <button
          class="inline-flex items-center gap-2 rounded-xl border border-[#d4d0dc] bg-white px-4 py-2.5 text-sm font-medium text-[#9a8aac] shadow-[0_2px_6px_rgba(75,61,116,0.06)] transition duration-150 hover:border-red-300 hover:text-red-600 dark:border-white/15 dark:bg-white/[0.06] dark:text-zinc-400 dark:hover:border-red-400/50 dark:hover:text-red-400"
          type="button"
          @click="handleLogout"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          <span>Logout</span>
        </button>
      </div>
    </div>
  </header>
</template>
