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
  overviewSummary: {
    type: String,
    default: '',
  },
  overviewActions: {
    type: Array,
    default: () => [],
  },
})

const themeStore = useThemeStore()
const authStore = useAuthStore()
const router = useRouter()

const copyByTab = {
  overview: {
    eyebrow: 'Admin command center',
    title: 'Command center for the live queue.',
    description: 'Surface what needs action first, then jump straight to requests or assignments.',
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

const actionToneStyles = {
  danger: {
    card: 'border-red-200 bg-red-50/80 dark:border-red-400/30 dark:bg-red-500/10',
    badge: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-100',
    title: 'group-hover:text-red-800 dark:group-hover:text-red-100',
  },
  warning: {
    card: 'border-amber-200 bg-amber-50/80 dark:border-amber-400/30 dark:bg-amber-500/10',
    badge: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-100',
    title: 'group-hover:text-amber-800 dark:group-hover:text-amber-100',
  },
  success: {
    card: 'border-emerald-200 bg-emerald-50/80 dark:border-emerald-400/30 dark:bg-emerald-500/10',
    badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
    title: 'group-hover:text-emerald-800 dark:group-hover:text-emerald-100',
  },
}

const handleLogout = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}
</script>

<template>
  <header class="border-b border-[#e0d8ec]/70 bg-white/60 px-6 py-5 backdrop-blur-sm dark:border-white/10 dark:bg-[#1a1625]/60 sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
      <div class="min-w-0 xl:max-w-[58rem]">
        <p class="text-[10px] font-medium uppercase tracking-[0.34em] text-brand-600 dark:text-brand-300">
          {{ activeCopy.eyebrow }}
        </p>
        <h1 class="mt-2 text-[2rem] font-semibold leading-tight tracking-[-0.035em] text-[#1a1525] dark:text-white sm:text-[2.15rem]">
          {{ activeCopy.title }}
        </h1>
        <p class="mt-2.5 max-w-2xl text-sm leading-relaxed text-[#7a6a8e] dark:text-zinc-400">
          {{ activeCopy.description }}
        </p>
        <p
          v-if="activeItem === 'overview' && overviewSummary"
          class="mt-4 inline-flex max-w-full flex-wrap items-center gap-2 rounded-full border border-[#d4d0dc] bg-white px-4 py-2 text-sm font-medium text-[#1a1525] shadow-[0_2px_6px_rgba(75,61,116,0.06)] dark:border-white/10 dark:bg-white/[0.05] dark:text-zinc-100"
        >
          <span class="text-[10px] font-semibold uppercase tracking-[0.24em] text-brand-600 dark:text-brand-200">Live queue</span>
          <span class="text-[#9a8aac] dark:text-zinc-500">|</span>
          <span>{{ overviewSummary }}</span>
        </p>
        <div v-if="activeItem === 'overview' && overviewActions.length" class="mt-4 grid gap-3 lg:grid-cols-3">
          <button
            v-for="action in overviewActions"
            :key="action.id"
            type="button"
            :class="[
              'group rounded-2xl border px-4 py-4 text-left shadow-[0_2px_6px_rgba(75,61,116,0.06)] transition duration-180 hover:-translate-y-0.5 hover:shadow-[0_8px_24px_rgba(75,61,116,0.12)]',
              actionToneStyles[action.tone ?? 'success'].card,
            ]"
            @click="action.onClick"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-[#8a7a9e] dark:text-zinc-400">
                  {{ action.actionLabel }}
                </p>
                <h2 :class="['mt-2 text-sm font-semibold leading-5 text-[#1a1525] transition dark:text-white', actionToneStyles[action.tone ?? 'success'].title]">
                  {{ action.label }}
                </h2>
              </div>
              <span
                :class="[
                  'inline-flex min-h-[2rem] shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-semibold',
                  actionToneStyles[action.tone ?? 'success'].badge,
                ]"
              >
                {{ action.count }}
              </span>
            </div>
            <p class="mt-3 text-sm leading-6 text-[#7a6a8e] dark:text-zinc-300">
              {{ action.detail }}
            </p>
          </button>
        </div>
      </div>

      <div class="flex flex-col gap-3 self-start sm:flex-row sm:items-center xl:pt-1.5">
        <label class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-[#9a8aac] dark:text-zinc-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-3.5-3.5" />
            </svg>
          </span>
          <input
            class="w-full rounded-xl border border-[#d4d0dc] bg-white py-2.5 pl-10 pr-4 text-sm text-[#1a1525] shadow-[0_2px_6px_rgba(75,61,116,0.06)] outline-none transition duration-150 placeholder:text-[#9a8aac] focus:border-brand-400 focus:shadow-[0_0_0_3px_rgba(109,80,162,0.12)] dark:border-white/15 dark:bg-white/[0.06] dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-brand-400 sm:w-64"
            placeholder="Search requests, clients, or assignments..."
          />
        </label>
        <NotificationInboxPopover
          title="Notifications"
          description="Live workflow updates for request intake, assignments, and due-date changes."
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
