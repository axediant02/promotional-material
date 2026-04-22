<script setup>
import { reactive, ref } from 'vue'
import { useAuthStore } from '../../../stores/auth'

const authStore = useAuthStore()
const loading = ref(false)
const error = ref('')
const success = ref('')

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await authStore.performRegister(form)
    const folderName = response.data?.data?.user?.assigned_folder?.folder_name

    success.value = folderName
      ? `Registration completed. Your folder "${folderName}" is ready. You can sign in now.`
      : 'Registration completed. Your folder will be created when you submit your first request after signing in.'

    Object.assign(form, {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    })
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to register.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center px-4 py-10">
    <section class="w-full max-w-2xl rounded-[2rem] border border-white/80 bg-white/90 p-8 shadow-[0_25px_80px_rgba(15,23,42,0.08)] lg:p-10">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-orange-600">Client registration</p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Request access to your files</h1>
      <p class="mt-2 text-sm text-slate-500">Local testing mode creates your client folder when you submit your first request after signing in.</p>

      <form class="mt-8 grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
        <label class="block sm:col-span-2">
          <span class="mb-2 block text-sm font-medium text-slate-700">Full name</span>
          <input v-model="form.name" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-orange-300" required />
        </label>

        <label class="block sm:col-span-2">
          <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
          <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-orange-300" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
          <input v-model="form.password" type="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-orange-300" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Confirm password</span>
          <input v-model="form.password_confirmation" type="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-orange-300" required />
        </label>

        <p v-if="error" class="sm:col-span-2 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ error }}</p>
        <p v-if="success" class="sm:col-span-2 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ success }}</p>

        <button class="sm:col-span-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-600" :disabled="loading">
          {{ loading ? 'Creating account...' : 'Create account' }}
        </button>
      </form>
    </section>
  </div>
</template>
