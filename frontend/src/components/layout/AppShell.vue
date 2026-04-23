<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})

const router = useRouter()
const authStore = useAuthStore()

const badgeLabel = computed(() => authStore.user?.display_role ?? authStore.user?.role ?? 'Guest')

const logout = async () => {
  await authStore.performLogout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="pm-page bg-transparent text-ink">
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
      <header class="pm-surface mb-8 rounded-[2rem] p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-brand-500">Promotional Materials</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-ink">{{ title }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-muted">{{ subtitle }}</p>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="pm-gradient-primary rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em]">
              {{ badgeLabel }}
            </div>
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
