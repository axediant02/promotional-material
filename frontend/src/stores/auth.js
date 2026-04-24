import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { currentUser, login, logout, register } from '../services/authService'
import { useNotificationStore } from './notifications'

export const useAuthStore = defineStore('auth', () => {
  const notificationStore = useNotificationStore()
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
      notificationStore.reset()
      isReady.value = true
      return
    }

    try {
      const response = await currentUser()
      user.value = response.data.data.user
      await notificationStore.initializeForUser(user.value)
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
    await notificationStore.initializeForUser(user.value)
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
    notificationStore.reset()
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
