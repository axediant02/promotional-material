import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { login, logout, me, register } from '../services/authService'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('pm_token'))
  const user = ref(null)
  const isReady = ref(false)

  const defaultRoute = computed(() => {
    if (!user.value) {
      return { name: 'login' }
    }

    if (user.value.role === 'production') {
      return { name: 'production-folder-index' }
    }

    if (user.value.role === 'agent') {
      return { name: 'agent-dashboard' }
    }

    if (user.value.role === 'admin') {
      return { name: 'admin-dashboard' }
    }

    return { name: 'client-dashboard' }
  })

  const bootstrap = async () => {
    if (!token.value) {
      isReady.value = true
      return
    }

    try {
      const response = await me()
      user.value = response.data.data.user
    } catch {
      clearSession()
    } finally {
      isReady.value = true
    }
  }

  const performLogin = async (payload) => {
    const response = await login(payload)
    token.value = response.data.data.token
    user.value = response.data.data.user
    localStorage.setItem('pm_token', token.value)
    isReady.value = true
  }

  const performRegister = async (payload) => register(payload)

  const performLogout = async () => {
    try {
      await logout()
    } finally {
      clearSession()
    }
  }

  const clearSession = () => {
    token.value = null
    user.value = null
    isReady.value = true
    localStorage.removeItem('pm_token')
  }

  return {
    token,
    user,
    isReady,
    defaultRoute,
    bootstrap,
    performLogin,
    performRegister,
    performLogout,
    clearSession,
  }
})
