import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import {
  fetchNotifications,
  markAllNotificationsAsRead,
  markNotificationAsRead,
} from '../services/notificationService'

let echo = null
let activeChannel = null

const MAX_NOTIFICATIONS = 20
const DEFAULT_REVERB_APP_KEY = 'promotional-materials-key'
const DEFAULT_REVERB_HOST = '127.0.0.1'
const DEFAULT_REVERB_PORT = 8080
const DEFAULT_REVERB_SCHEME = 'http'

const resolveBroadcastAuthEndpoint = () => {
  const apiUrl = import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api'
  return apiUrl.replace(/\/api\/?$/, '') + '/broadcasting/auth'
}

const normalizeNotification = (notification) => {
  const payload = notification?.data ? { ...notification.data, ...notification } : notification ?? {}

  return {
    id: payload.id ?? '',
    kind: payload.kind ?? 'workflow',
    title: payload.title ?? 'Notification',
    body: payload.body ?? '',
    target: payload.target ?? null,
    requestId: payload.request_id ?? payload.requestId ?? null,
    readAt: payload.read_at ?? payload.readAt ?? null,
    createdAt: payload.created_at ?? payload.createdAt ?? new Date().toISOString(),
  }
}

const buildRealtimeNotification = (notification) => ({
  ...normalizeNotification(notification),
  receivedAt: new Date().toISOString(),
})

const upsertNotification = (notifications, incoming) => {
  const normalized = normalizeNotification(incoming)
  const next = [normalized, ...notifications.filter((item) => item.id !== normalized.id)]
  return next.slice(0, MAX_NOTIFICATIONS)
}

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([])
  const loading = ref(false)
  const activeUserId = ref('')
  const lastRealtimeNotification = ref(null)
  const isLiveConnected = ref(false)
  const syncInFlight = ref(null)

  const unreadCount = computed(() => notifications.value.filter((notification) => !notification.readAt).length)

  const syncNotifications = async () => {
    if (syncInFlight.value) {
      return syncInFlight.value
    }

    syncInFlight.value = load().finally(() => {
      syncInFlight.value = null
    })

    return syncInFlight.value
  }

  const connectForUser = (userId) => {
    const token = localStorage.getItem('pm_token')

    if (!userId || !token) {
      return
    }

    if (activeUserId.value === userId && echo) {
      return
    }

    if (echo) {
      echo.leave(`users.${activeUserId.value}.notifications`)
      echo.disconnect()
      echo = null
      activeChannel = null
    }

    window.Pusher = Pusher

    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? DEFAULT_REVERB_SCHEME
    const host = import.meta.env.VITE_REVERB_HOST ?? DEFAULT_REVERB_HOST
    const port = Number(import.meta.env.VITE_REVERB_PORT ?? DEFAULT_REVERB_PORT)

    echo = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY ?? DEFAULT_REVERB_APP_KEY,
      wsHost: host,
      wsPort: port,
      wssPort: port,
      forceTLS: scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: resolveBroadcastAuthEndpoint(),
      auth: {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      },
    })

    activeChannel = echo.private(`users.${userId}.notifications`)

    activeChannel.subscribed(() => {
      isLiveConnected.value = true
      void syncNotifications()
    })

    const pusherConnection = echo.connector?.pusher?.connection

    pusherConnection?.bind('connected', () => {
      isLiveConnected.value = true
      void syncNotifications()
    })

    pusherConnection?.bind('disconnected', () => {
      isLiveConnected.value = false
    })

    pusherConnection?.bind('unavailable', () => {
      isLiveConnected.value = false
    })

    activeChannel.listen('.workflow.notification', (payload) => {
      const normalizedNotification = buildRealtimeNotification(payload)
      lastRealtimeNotification.value = normalizedNotification
      void syncNotifications()
    })

    activeChannel.notification((notification) => {
      const normalizedNotification = buildRealtimeNotification(notification)
      notifications.value = upsertNotification(notifications.value, notification)
      lastRealtimeNotification.value = normalizedNotification
    })

    activeUserId.value = userId
  }

  const disconnect = () => {
    if (echo && activeUserId.value) {
      echo.leave(`users.${activeUserId.value}.notifications`)
      echo.disconnect()
      echo = null
      activeChannel = null
    }

    activeUserId.value = ''
    isLiveConnected.value = false
  }

  const load = async () => {
    loading.value = true

    try {
      const response = await fetchNotifications()
      notifications.value = (response.data.data.notifications ?? []).map(normalizeNotification)
    } finally {
      loading.value = false
    }
  }

  const initializeForUser = async (user) => {
    if (!user?.user_id) {
      reset()
      return
    }

    await load()
    connectForUser(user.user_id)
  }

  const markAsRead = async (notificationId) => {
    const response = await markNotificationAsRead(notificationId)
    const updatedNotification = normalizeNotification(response.data.data.notification)

    notifications.value = notifications.value.map((notification) =>
      notification.id === notificationId ? updatedNotification : notification
    )
  }

  const markAllAsRead = async () => {
    await markAllNotificationsAsRead()

    notifications.value = notifications.value.map((notification) => ({
      ...notification,
      readAt: notification.readAt ?? new Date().toISOString(),
    }))
  }

  const reset = () => {
    disconnect()
    notifications.value = []
    loading.value = false
    lastRealtimeNotification.value = null
  }

  return {
    notifications,
    loading,
    unreadCount,
    lastRealtimeNotification,
    isLiveConnected,
    initializeForUser,
    markAsRead,
    markAllAsRead,
    syncNotifications,
    reset,
  }
})
