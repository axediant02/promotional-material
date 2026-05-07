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
  <div class="flex min-h-screen bg-gray-50">
    <!-- Left: Brand / Hero Side -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-[#6d50a2] via-[#654c81] to-[#4b3d74]">
      <!-- Decorative Elements -->
      <div class="absolute top-[-15%] left-[-10%] w-[500px] h-[500px] rounded-full bg-white/10 blur-3xl mix-blend-overlay"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] rounded-full bg-[#5f509b]/40 blur-3xl mix-blend-overlay"></div>
      
      <div class="relative z-10 flex flex-col justify-center p-16 text-white w-full h-full">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.3em] text-white/80 mb-6 drop-shadow-sm">Client File Portal</p>
          <h1 class="text-5xl font-extrabold tracking-tight leading-[1.1] mb-6 drop-shadow-sm">
            Secure access for production, agents, and clients.
          </h1>
          <p class="text-lg text-white/90 max-w-md leading-relaxed drop-shadow-sm">
            A premium download workspace for assigned client folders, internal operations, recycle-bin recovery, and role-based file access.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-6 max-w-lg mt-20">
          <div class="bg-white/10 border border-white/20 rounded-2xl p-6 backdrop-blur-md shadow-xl transition-transform hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 font-bold text-xl">1</span>
            </div>
            <p class="text-sm font-medium text-white/90 leading-snug">Client self-registration with immediate folder assignment.</p>
          </div>
          <div class="bg-white/10 border border-white/20 rounded-2xl p-6 backdrop-blur-md shadow-xl transition-transform hover:-translate-y-1">
            <div class="flex items-center gap-3 mb-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 font-bold text-xl">3</span>
            </div>
            <p class="text-sm font-medium text-white/90 leading-snug">Role-based workspaces for client, agent, and production teams.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Form Side -->
    <div class="flex w-full lg:w-1/2 items-center justify-center p-6 sm:p-12 lg:p-16 bg-white shadow-[0_0_40px_rgba(0,0,0,0.05)] z-10">
      <div class="w-full max-w-[420px]">
        <!-- Mobile Logo -->
        <div class="lg:hidden mb-10 text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-[#6d50a2] to-[#4b3d74] text-white mb-4 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
          <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#4b3d74]">Client File Portal</p>
        </div>

        <div class="mb-10 lg:text-left text-center">
          <h2 class="text-3xl font-bold tracking-tight text-gray-900 mb-3">Welcome back</h2>
          <p class="text-gray-500 text-base">Sign in to your account to continue.</p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
          <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-gray-700">Email address</label>
            <input 
              id="email" 
              v-model="form.email" 
              type="email" 
              required 
              class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#6d50a2]/30 focus:border-[#6d50a2] outline-none transition-all"
              placeholder="name@example.com"
            />
          </div>

          <div class="space-y-1.5">
            <div class="flex justify-between items-center">
              <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
              <a href="#" class="text-sm font-medium text-[#6d50a2] hover:text-[#4b3d74] transition-colors">Forgot password?</a>
            </div>
            <input 
              id="password" 
              v-model="form.password" 
              type="password" 
              required 
              class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#6d50a2]/30 focus:border-[#6d50a2] outline-none transition-all"
              placeholder="••••••••"
            />
          </div>

          <!-- Error message -->
          <div v-if="error" class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start shadow-sm">
            <svg class="h-5 w-5 text-red-500 mr-3 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
               <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <p class="text-sm text-red-700 font-medium leading-relaxed">{{ error }}</p>
          </div>

          <button
            class="w-full py-3.5 px-4 bg-[#6d50a2] hover:bg-[#5f509b] text-white rounded-xl text-base font-semibold shadow-[0_4px_14px_0_rgba(109,80,162,0.39)] hover:shadow-[0_6px_20px_rgba(109,80,162,0.23)] hover:-translate-y-0.5 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6d50a2] disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none flex justify-center items-center"
            :disabled="loading"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </form>

        <div class="mt-8 pt-8 border-t border-gray-100 text-center">
          <p class="text-sm text-gray-500">
            Need a client account?
            <RouterLink class="font-semibold text-[#6d50a2] hover:text-[#4b3d74] transition-colors ml-1" to="/register">Register here</RouterLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
