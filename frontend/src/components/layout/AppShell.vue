<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useThemeStore } from '../../stores/theme'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const badgeLabel = computed(() => authStore.user?.display_role ?? authStore.user?.role ?? 'Guest')

const logout = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="pm-page bg-transparent text-ink dark:text-white">
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
      <header class="pm-surface mb-8 rounded-[2rem] p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-brand-500 dark:text-brand-100">Promotional Materials</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink dark:text-white">{{ title }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-muted dark:text-zinc-300">{{ subtitle }}</p>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="pm-gradient-primary rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em]">
              {{ badgeLabel }}
            </div>
            <button
              class="flex h-11 w-11 items-center justify-center rounded-full border border-border bg-white/60 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
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
            <button
              class="pm-button-secondary rounded-full px-4 py-2 text-sm font-medium"
              @click="logout"
            >
              Sign out
            </button>
          </div>
        </div>
      </header>

      <main class="flex-1">
        <slot />
      </main>
    </div>
  </div>
</template>
