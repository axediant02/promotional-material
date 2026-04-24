<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import NotificationInboxPopover from '../../../components/shared/NotificationInboxPopover.vue'
import { useAuthStore } from '../../../stores/auth'
import { useThemeStore } from '../../../stores/theme'

const props = defineProps({
  searchQuery: { type: String, default: '' },
  folderLabel: { type: String, default: 'Assigned Folder' },
  user: { type: Object, default: null },
  notifications: { type: Array, default: () => [] },
  notificationsLoading: { type: Boolean, default: false },
  unreadCount: { type: Number, default: 0 },
  markReadAction: { type: Function, required: true },
  markAllReadAction: { type: Function, required: true },
})

const emit = defineEmits(['update:searchQuery', 'open-request'])

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const initials = computed(() => {
  const name = props.user?.name?.trim()

  if (!name) {
    return 'C'
  }

  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('')
})

const handleLogout = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}
</script>

<template>
  <header class="border-b border-border/70 px-6 py-5 backdrop-blur sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-8">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-brand-600 dark:text-brand-100">Promotional Materials</p>
          <h1 class="mt-1 text-2xl font-semibold tracking-tight text-ink dark:text-white">Client Portal</h1>
        </div>

        <nav class="flex flex-wrap items-center gap-3 text-sm font-medium text-muted dark:text-zinc-300">
          <span class="rounded-full bg-brand-50 px-3 py-1 text-brand-700 dark:bg-white/10 dark:text-white">{{ folderLabel }}</span>
          <a href="#asset-catalog" class="rounded-full px-3 py-1 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-white/10 dark:hover:text-white">Assets</a>
          <button
            type="button"
            class="rounded-full px-3 py-1 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-white/10 dark:hover:text-white"
            @click="emit('open-request')"
          >
            Request
          </button>
          <a href="#request-history" class="rounded-full px-3 py-1 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-white/10 dark:hover:text-white">History</a>
        </nav>
      </div>

      <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
        <label class="relative block min-w-0 lg:w-[26rem]">
          <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted dark:text-zinc-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
            </svg>
          </span>
          <input
            :value="searchQuery"
            type="search"
            placeholder="Search approved files or categories..."
            class="pm-input w-full rounded-2xl py-3 pl-11 pr-4 text-sm"
            @input="emit('update:searchQuery', $event.target.value)"
          />
        </label>

        <div class="flex items-center gap-3 self-end lg:self-auto">
          <NotificationInboxPopover
            title="Notifications"
            description="Track due dates and live status updates for your submitted requests."
            :notifications="notifications"
            :loading="notificationsLoading"
            :unread-count="unreadCount"
            empty-message="Due-date and request-status notifications will appear here."
            :mark-read-action="markReadAction"
            :mark-all-read-action="markAllReadAction"
          />
          <button
            class="flex h-[46px] w-[46px] items-center justify-center rounded-full border border-border bg-white/60 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
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

          <div class="flex items-center gap-3 rounded-full border border-border bg-white/75 px-2 py-2 shadow-sm dark:border-white/10 dark:bg-white/5">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-700 to-brand-500 text-sm font-semibold text-white">
              {{ initials }}
            </div>
            <div class="hidden pr-2 sm:block">
              <p class="text-sm font-semibold text-ink dark:text-white">{{ user?.name ?? 'Client User' }}</p>
              <p class="text-xs uppercase tracking-[0.25em] text-muted dark:text-zinc-400">Secure Access</p>
            </div>
            <button
              class="rounded-full border border-border px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:text-white dark:hover:border-white/20"
              @click="handleLogout"
            >
              Sign out
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
