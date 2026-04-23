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
    <section class="pm-surface w-full max-w-2xl rounded-[2rem] p-8 backdrop-blur lg:p-10">
      <p class="text-xs font-semibold uppercase tracking-[0.35em] text-brand-500">Client registration</p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-ink">Request access to your files</h1>
      <p class="mt-2 text-sm text-muted">Local testing mode creates your client folder when you submit your first request after signing in.</p>

      <form class="mt-8 grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
        <label class="block sm:col-span-2">
          <span class="mb-2 block text-sm font-medium text-muted">Full name</span>
          <input v-model="form.name" class="pm-input w-full rounded-2xl px-4 py-3" required />
        </label>

        <label class="block sm:col-span-2">
          <span class="mb-2 block text-sm font-medium text-muted">Email</span>
          <input v-model="form.email" type="email" class="pm-input w-full rounded-2xl px-4 py-3" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-muted">Password</span>
          <input v-model="form.password" type="password" class="pm-input w-full rounded-2xl px-4 py-3" required />
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-muted">Confirm password</span>
          <input v-model="form.password_confirmation" type="password" class="pm-input w-full rounded-2xl px-4 py-3" required />
        </label>

        <p v-if="error" class="sm:col-span-2 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ error }}</p>
        <p v-if="success" class="sm:col-span-2 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ success }}</p>

        <button class="sm:col-span-2 pm-gradient-primary rounded-2xl px-4 py-3 text-sm font-semibold transition hover:brightness-110" :disabled="loading">
          {{ loading ? 'Creating account...' : 'Create account' }}
        </button>
      </form>
    </section>
  </div>
</template>
