<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({
  email: '',
  password: '',
})
const showPassword = ref(false)

const goToLogin = () => {
  router.push({
    name: 'login',
    query: form.email ? { email: form.email } : undefined,
  })
}

const goToRegister = () => {
  router.push('/register')
}
</script>

<template>
  <aside class="pm-surface-strong px-8 py-7 dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(26,26,29,0.96),rgba(20,18,20,0.985))] dark:shadow-[0_28px_80px_rgba(0,0,0,0.34)] sm:px-9 sm:py-8">
    <div class="space-y-2.5">
      <h2 class="max-w-[12ch] text-[2.05rem] font-semibold leading-[0.94] tracking-[-0.045em] text-ink dark:text-white ">
        Welcome back to Studio
      </h2>
      <p class="max-w-[24rem] text-[10px] uppercase tracking-[0.34em] text-muted">
        Enter your credentials to access the workflow
      </p>
    </div>

    <form class="mt-8 space-y-5" @submit.prevent="goToLogin">
      <label class="block">
        <span class="mb-2.5 block text-[10px] font-semibold uppercase tracking-[0.36em] text-muted">
          Email Address
        </span>
        <div class="flex items-center gap-3 border border-border/80 bg-surface/70 px-4 py-3.5 transition focus-within:border-brand-500 dark:border-white/10 dark:bg-black/20 dark:focus-within:border-white/25">
          <span class="text-muted dark:text-zinc-600" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M4 7.5h16v9H4z" />
              <path d="m5 8 7 5 7-5" />
            </svg>
          </span>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            placeholder="name@studio.com"
            class="landing-auth-input w-full text-[0.95rem] text-ink outline-none placeholder:text-muted dark:text-zinc-100 dark:placeholder:text-zinc-600"
          />
        </div>
      </label>

      <label class="block">
        <div class="mb-2.5 flex items-center justify-between gap-4">
          <span class="text-[10px] font-semibold uppercase tracking-[0.36em] text-muted">
            Security Key
          </span>
          <button
            type="button"
            class="text-[10px] uppercase tracking-[0.2em] text-muted transition hover:text-brand-700 dark:text-zinc-600 dark:hover:text-zinc-400"
            @click="showPassword = !showPassword"
          >
            {{ showPassword ? 'Hide key' : 'Forgot?' }}
          </button>
        </div>

        <div class="flex items-center gap-3 border border-border/80 bg-surface/70 px-4 py-3.5 transition focus-within:border-brand-500 dark:border-white/10 dark:bg-black/20 dark:focus-within:border-white/25">
          <span class="text-muted dark:text-zinc-600" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="5" y="11" width="14" height="9" rx="1.5" />
              <path d="M8 11V8.5a4 4 0 1 1 8 0V11" />
            </svg>
          </span>
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            placeholder="••••••••"
            class="landing-auth-input w-full text-[0.95rem] tracking-[0.24em] text-ink outline-none placeholder:tracking-[0.24em] placeholder:text-muted dark:text-zinc-100 dark:placeholder:text-zinc-600"
          />
          <button
            type="button"
            class="text-muted transition hover:text-brand-700 dark:text-zinc-600 dark:hover:text-zinc-300"
            @click="showPassword = !showPassword"
          >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </button>
        </div>
      </label>

      <button
        class="flex w-full items-center justify-center gap-3 bg-gradient-to-r from-brand-500 to-brand-700 px-5 py-3.5 text-[0.92rem] font-semibold uppercase tracking-[0.34em] text-white transition hover:brightness-110"
        type="submit"
      >
        Login
        <span aria-hidden="true" class="text-xl leading-none">&rsaquo;</span>
      </button>
    </form>

    <div class="mt-9">
      <div class="flex items-center gap-4">
        <span class="h-px flex-1 bg-border/80 dark:bg-white/10" />
        <p class="text-[10px] uppercase tracking-[0.28em] text-muted">Portal Access</p>
        <span class="h-px flex-1 bg-border/80 dark:bg-white/10" />
      </div>

      <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <button
          type="button"
          class="pm-button-secondary flex items-center justify-center gap-2.5 px-4 py-3.5 text-[10px] font-medium uppercase tracking-[0.24em]"
          @click="goToLogin"
        >
          <span class="text-brand-500" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M12 3 5 6v5c0 4.25 2.74 8.2 7 9 4.26-.8 7-4.75 7-9V6l-7-3Z" />
              <path d="m9.5 12 1.8 1.8 3.4-4" />
            </svg>
          </span>
          Client Portal
        </button>

        <button
          type="button"
          class="pm-button-secondary flex items-center justify-center gap-2.5 px-4 py-3.5 text-[10px] font-medium uppercase tracking-[0.24em]"
          @click="goToRegister"
        >
          <span class="text-brand-500" aria-hidden="true">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="12" r="8" />
              <path d="M4 12h16" />
              <path d="M12 4a13 13 0 0 1 0 16" />
              <path d="M12 4a13 13 0 0 0 0 16" />
            </svg>
          </span>
          Register
        </button>
      </div>
    </div>

    <div class="mt-8 text-center">
      <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-muted">
        Need an account? Request access
      </p>
      <button
        type="button"
        class="mt-2 border-b border-muted pb-1 text-[10px] uppercase tracking-[0.24em] text-ink transition hover:border-brand-700 hover:text-brand-700 dark:text-zinc-300 dark:hover:border-zinc-300 dark:hover:text-white"
        @click="goToRegister"
      >
        Create an account
      </button>
    </div>
  </aside>
</template>

<style scoped>
.landing-auth-input {
  background: transparent;
  appearance: none;
  -webkit-appearance: none;
  caret-color: currentColor;
}

.landing-auth-input:-webkit-autofill,
.landing-auth-input:-webkit-autofill:hover,
.landing-auth-input:-webkit-autofill:focus,
.landing-auth-input:-webkit-autofill:active {
  -webkit-text-fill-color: inherit;
  -webkit-box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0) inset;
  box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0) inset;
  transition: background-color 9999s ease-out 0s;
}
</style>
