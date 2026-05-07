<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
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
const profileMenuOpen = ref(false)
const profileMenuRef = ref(null)

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
  profileMenuOpen.value = false
  await authStore.performLogout()
  router.push({ name: 'login' })
}

const toggleProfileMenu = () => {
  profileMenuOpen.value = !profileMenuOpen.value
}

const closeProfileMenu = () => {
  profileMenuOpen.value = false
}

const handleDocumentPointerDown = (event) => {
  if (!profileMenuOpen.value) {
    return
  }

  if (profileMenuRef.value?.contains(event.target)) {
    return
  }

  closeProfileMenu()
}

const handleDocumentKeydown = (event) => {
  if (event.key === 'Escape') {
    closeProfileMenu()
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', handleDocumentPointerDown)
  document.addEventListener('keydown', handleDocumentKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleDocumentPointerDown)
  document.removeEventListener('keydown', handleDocumentKeydown)
})
</script>

<template>
  <header class="sticky top-0 z-30 border-b border-border/70 bg-[rgba(251,248,255,0.92)] px-4 py-4 backdrop-blur dark:bg-[rgba(15,12,22,0.86)] sm:px-6 lg:px-10">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between xl:gap-6">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-6 xl:min-w-0 xl:flex-1">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">Promotional Materials</p>
          <h1 class="mt-1 text-[1.65rem] font-semibold tracking-tight text-ink dark:text-white sm:text-2xl">Client Portal</h1>
        </div>

        <nav class="scrollbar-none flex flex-wrap items-center gap-2 text-sm font-medium text-muted dark:text-zinc-300 xl:flex-nowrap">
          <span class="shrink-0 whitespace-nowrap rounded-full bg-brand-50 px-3 py-1 text-brand-700 dark:bg-white/10 dark:text-white">{{ folderLabel }}</span>
          <a href="#asset-catalog" class="shrink-0 whitespace-nowrap rounded-full px-2.5 py-1 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-white/10 dark:hover:text-white">Assets</a>
          <button
            type="button"
            class="shrink-0 whitespace-nowrap rounded-full px-2.5 py-1 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-white/10 dark:hover:text-white"
            @click="emit('open-request')"
          >
            Request
          </button>
          <a href="#request-history" class="shrink-0 whitespace-nowrap rounded-full px-2.5 py-1 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-white/10 dark:hover:text-white">History</a>
        </nav>
      </div>

      <div class="flex w-full flex-col gap-3 sm:gap-4 lg:flex-row lg:items-center xl:ml-auto xl:w-auto xl:flex-nowrap xl:justify-end xl:gap-2">
        <label class="relative block min-w-0 w-full lg:w-[20rem] xl:w-[15rem] 2xl:w-[22rem]">
          <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted dark:text-zinc-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
            </svg>
          </span>
          <input
            :value="searchQuery"
            type="search"
            placeholder="Search approved files or categories..."
            class="pm-input w-full rounded-2xl py-2.5 pl-11 pr-4 text-sm xl:py-2"
            @input="emit('update:searchQuery', $event.target.value)"
          />
        </label>

        <div class="flex w-full flex-wrap items-center gap-2 sm:gap-3 lg:w-auto lg:self-auto xl:flex-nowrap xl:justify-end">
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
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-border bg-white/60 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20 sm:h-[46px] sm:w-[46px]"
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

          <div class="relative shrink-0 2xl:hidden" ref="profileMenuRef">
            <button
              type="button"
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-border bg-white/60 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20 sm:h-[46px] sm:w-[46px]"
              :aria-expanded="profileMenuOpen ? 'true' : 'false'"
              aria-haspopup="menu"
              aria-label="Open profile menu"
              @click="toggleProfileMenu"
            >
              <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-700 to-brand-500 text-sm font-semibold text-white">
                {{ initials }}
              </div>
            </button>

            <div
              v-if="profileMenuOpen"
              class="absolute right-0 top-full z-40 mt-3 w-[min(18rem,calc(100vw-2rem))] overflow-hidden rounded-[1.35rem] border border-border bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(247,243,252,0.97))] p-3 shadow-[0_20px_48px_rgba(18,12,32,0.18)] backdrop-blur dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(21,18,33,0.98),rgba(28,24,42,0.96))]"
              role="menu"
              aria-label="Profile menu"
            >
              <div class="flex items-center gap-3 rounded-2xl border border-border/80 bg-white/70 px-3 py-3 dark:border-white/10 dark:bg-white/5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-700 to-brand-500 text-sm font-semibold text-white">
                  {{ initials }}
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ user?.name ?? 'Client User' }}</p>
                  <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Secure Access</p>
                </div>
              </div>

              <button
                type="button"
                class="mt-3 flex w-full items-center justify-between rounded-2xl border border-border bg-white/70 px-3 py-3 text-left text-sm font-semibold text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-200 dark:hover:border-white/20 dark:hover:text-white"
                @click="handleLogout"
              >
                <span>Sign out</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M10 17l5-5-5-5" />
                  <path d="M15 12H3" />
                  <path d="M21 3v18" />
                </svg>
              </button>
            </div>
          </div>

          <div class="hidden w-full items-center gap-3 rounded-[1.5rem] border border-border bg-white/75 px-3 py-2.5 shadow-sm dark:border-white/10 dark:bg-white/5 2xl:flex 2xl:w-auto 2xl:rounded-full">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-700 to-brand-500 text-sm font-semibold text-white">
              {{ initials }}
            </div>
            <div class="min-w-0 flex-1 pr-2 sm:flex-none">
              <p class="text-sm font-semibold text-ink dark:text-white">{{ user?.name ?? 'Client User' }}</p>
              <p class="text-xs uppercase tracking-[0.25em] text-muted dark:text-zinc-400">Secure Access</p>
            </div>
            <button
              class="shrink-0 rounded-full border border-border px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:text-white dark:hover:border-white/20"
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
