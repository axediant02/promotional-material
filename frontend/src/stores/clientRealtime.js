import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { ref } from 'vue'
import { defineStore } from 'pinia'
import { normalizeClientWorkspaceEvent } from '../features/client-dashboard/utils/clientWorkspaceSync.js'

let echo = null
let activeChannel = null

const DEFAULT_REVERB_APP_KEY = 'promotional-materials-key'
const DEFAULT_REVERB_HOST = '127.0.0.1'
const DEFAULT_REVERB_PORT = 8080
const DEFAULT_REVERB_SCHEME = 'http'

const resolveBroadcastAuthEndpoint = () => {
  const apiUrl = import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api'
  return apiUrl.replace(/\/$/, '') + '/broadcasting/auth'
}

const buildUserChannelName = (userId) => `App.Models.User.${userId}`

export const useClientRealtimeStore = defineStore('clientRealtime', () => {
  const activeUserId = ref('')
  const isLiveConnected = ref(false)
  const lastWorkspaceEvent = ref(null)

  const connectForUser = (userId) => {
    const token = localStorage.getItem('pm_token')

    if (!userId || !token) {
      return
    }

    if (activeUserId.value === userId && echo) {
      return
    }

    if (echo) {
      echo.leave(buildUserChannelName(activeUserId.value))
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

    activeChannel = echo.private(buildUserChannelName(userId))

    activeChannel.subscribed(() => {
      isLiveConnected.value = true
    })

    const pusherConnection = echo.connector?.pusher?.connection

    pusherConnection?.bind('connected', () => {
      isLiveConnected.value = true
    })

    pusherConnection?.bind('disconnected', () => {
      isLiveConnected.value = false
    })

    pusherConnection?.bind('unavailable', () => {
      isLiveConnected.value = false
    })

    activeChannel.listen('.client.workspace.sync', (payload) => {
      lastWorkspaceEvent.value = normalizeClientWorkspaceEvent(payload)
    })

    activeUserId.value = userId
  }

  const initializeForUser = async (user) => {
    if (!user?.user_id || user?.role !== 'client') {
      reset()
      return null
    }

    connectForUser(user.user_id)
    return lastWorkspaceEvent.value
  }

  const disconnect = () => {
    if (echo && activeUserId.value) {
      echo.leave(buildUserChannelName(activeUserId.value))
      echo.disconnect()
      echo = null
      activeChannel = null
    }

    activeUserId.value = ''
    isLiveConnected.value = false
  }

  const reset = () => {
    disconnect()
    lastWorkspaceEvent.value = null
  }

  return {
    activeUserId,
    isLiveConnected,
    lastWorkspaceEvent,
    initializeForUser,
    disconnect,
    reset,
  }
})
