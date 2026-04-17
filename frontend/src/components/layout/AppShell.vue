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
  <div class="min-h-screen bg-transparent text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
      <header class="mb-8 rounded-[2rem] border border-white/70 bg-white/75 p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] backdrop-blur">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-orange-600">Promotional Materials</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ title }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">{{ subtitle }}</p>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white">
              {{ badgeLabel }}
            </div>
            <button
              class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-orange-300 hover:text-orange-700"
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
