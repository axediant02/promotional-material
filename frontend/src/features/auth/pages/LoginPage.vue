<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const error = ref('')

const form = reactive({
  email: '',
  password: '',
})

const submit = async () => {
  loading.value = true
  error.value = ''

  try {
    await authStore.performLogin(form)
    router.push(authStore.defaultRoute)
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to sign in.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center px-4 py-10">
    <div class="grid w-full max-w-6xl gap-8 lg:grid-cols-[1.1fr_0.9fr]">
      <section class="pm-gradient-hero relative overflow-hidden rounded-[2rem] border border-white/15 p-8 lg:p-12">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.22),transparent_32%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.12),transparent_28%)]"></div>
        <div class="relative">
          <p class="text-xs font-semibold uppercase tracking-[0.45em] text-brand-100">Client file portal</p>
          <h1 class="mt-4 text-4xl font-semibold tracking-tight">Secure access for production, agents, and clients.</h1>
          <p class="mt-4 max-w-xl text-sm text-brand-50">
            A premium download workspace for assigned client folders, internal operations, recycle-bin recovery, and role-based file access.
          </p>
          <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
              <p class="text-3xl font-semibold">1</p>
              <p class="mt-2 text-sm text-brand-50">Client self-registration with immediate folder assignment for local testing.</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
              <p class="text-3xl font-semibold">3</p>
              <p class="mt-2 text-sm text-brand-50">Role-based workspaces for client, agent, and production teams.</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
              <p class="text-3xl font-semibold">30d</p>
              <p class="mt-2 text-sm text-brand-50">Recycle-bin retention before permanent file purge.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="pm-surface rounded-[2rem] p-8 backdrop-blur lg:p-10">
        <div class="mb-8">
          <p class="text-xs font-semibold uppercase tracking-[0.35em] text-brand-500">Sign in</p>
          <h2 class="mt-3 text-3xl font-semibold tracking-tight text-ink">Welcome back</h2>
          <p class="mt-2 text-sm text-muted">Use the seeded production account or any registered client account.</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-muted">Email</span>
            <input v-model="form.email" class="pm-input w-full rounded-2xl px-4 py-3" type="email" required />
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-muted">Password</span>
            <input v-model="form.password" class="pm-input w-full rounded-2xl px-4 py-3" type="password" required />
          </label>

          <p v-if="error" class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ error }}</p>

          <button
            class="pm-gradient-primary w-full rounded-2xl px-4 py-3 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="loading"
          >
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </form>

        <p class="mt-6 text-sm text-muted">
          Need a client account?
          <RouterLink class="font-semibold text-brand-500" to="/register">Register here</RouterLink>
        </p>
      </section>
    </div>
  </div>
</template>
