import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { nextTick, ref, watch } from 'vue'

const DEFAULT_REVERB_APP_KEY = 'promotional-materials-key'
const DEFAULT_REVERB_HOST = '127.0.0.1'
const DEFAULT_REVERB_PORT = 8080
const DEFAULT_REVERB_SCHEME = 'http'
const SCROLL_BOTTOM_THRESHOLD = 48

const resolveCurrentUserId = (value) => {
  if (typeof value === 'function') {
    return value() ?? ''
  }

  if (value && typeof value === 'object' && 'value' in value) {
    return value.value ?? ''
  }

  return value ?? ''
}

const safelyRunHandler = (handler, payload) => Promise.resolve()
  .then(() => handler(payload))

export function useRealtimeChat({
  scrollContainerRef = null,
  currentUserId = '',
  stickToBottom,
  isNearBottom,
}) {
  const scrollContainerElement = ref(null)
  const resizeObserver = ref(null)
  const scrollFrameId = ref(null)
  const echoInstance = ref(null)
  const activeChannel = ref(null)
  const activeChannelName = ref('')
  const userChannel = ref(null)
  const userChannelName = ref('')

  const resolveBroadcastAuthEndpoint = () => {
    const apiUrl = import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api'
    return apiUrl.replace(/\/$/, '') + '/broadcasting/auth'
  }

  const ensureEcho = () => {
    const token = localStorage.getItem('pm_token')

    if (!token) {
      return null
    }

    if (echoInstance.value) {
      return echoInstance.value
    }

    window.Pusher = Pusher

    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? DEFAULT_REVERB_SCHEME
    const host = import.meta.env.VITE_REVERB_HOST ?? DEFAULT_REVERB_HOST
    const port = Number(import.meta.env.VITE_REVERB_PORT ?? DEFAULT_REVERB_PORT)

    echoInstance.value = new Echo({
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

    return echoInstance.value
  }

  const leaveActiveThreadChannel = () => {
    if (activeChannel.value && activeChannelName.value) {
      echoInstance.value?.leave(activeChannelName.value)
      activeChannel.value = null
      activeChannelName.value = ''
    }
  }

  const disconnectRealtime = () => {
    leaveActiveThreadChannel()

    if (userChannel.value && userChannelName.value) {
      echoInstance.value?.leave(userChannelName.value)
      userChannel.value = null
      userChannelName.value = ''
    }

    if (echoInstance.value) {
      echoInstance.value.disconnect()
      echoInstance.value = null
    }
  }

  const getScrollContainer = () => scrollContainerRef?.value ?? null

  const cancelScrollFrame = () => {
    if (scrollFrameId.value === null || typeof window === 'undefined') {
      return
    }

    window.cancelAnimationFrame(scrollFrameId.value)
    scrollFrameId.value = null
  }

  const measureScrollProximity = (container = getScrollContainer()) => {
    if (!container) {
      isNearBottom.value = true
      return true
    }

    const distanceFromBottom = container.scrollHeight - container.clientHeight - container.scrollTop
    const nearBottom = distanceFromBottom <= SCROLL_BOTTOM_THRESHOLD

    isNearBottom.value = nearBottom
    return nearBottom
  }

  const scrollContainerToBottom = (container = getScrollContainer()) => {
    if (!container) {
      return false
    }

    container.scrollTop = container.scrollHeight
    measureScrollProximity(container)
    return true
  }

  const requestScrollToBottom = async ({ force = false } = {}) => {
    const container = getScrollContainer()
    const shouldScroll = force || stickToBottom.value || (container ? measureScrollProximity(container) : false)

    if (!shouldScroll) {
      return
    }

    if (force) {
      stickToBottom.value = true
    }

    await nextTick()

    const applyScroll = () => {
      const latestContainer = getScrollContainer()
      if (!latestContainer || (!force && !stickToBottom.value)) {
        return
      }

      scrollContainerToBottom(latestContainer)
    }

    if (typeof window !== 'undefined' && typeof window.requestAnimationFrame === 'function') {
      cancelScrollFrame()
      scrollFrameId.value = window.requestAnimationFrame(() => {
        scrollFrameId.value = null
        applyScroll()
      })
      return
    }

    applyScroll()
  }

  const handleScroll = () => {
    const nearBottom = measureScrollProximity()
    stickToBottom.value = nearBottom
  }

  const detachScrollTracking = () => {
    cancelScrollFrame()

    if (scrollContainerElement.value) {
      scrollContainerElement.value.removeEventListener('scroll', handleScroll)
      scrollContainerElement.value = null
    }

    if (resizeObserver.value) {
      resizeObserver.value.disconnect()
      resizeObserver.value = null
    }
  }

  const attachScrollTracking = (container) => {
    if (scrollContainerElement.value === container) {
      return
    }

    detachScrollTracking()

    if (!container) {
      return
    }

    scrollContainerElement.value = container
    container.addEventListener('scroll', handleScroll, { passive: true })
    measureScrollProximity(container)

    if (typeof ResizeObserver !== 'undefined') {
      resizeObserver.value = new ResizeObserver(() => {
        if (stickToBottom.value) {
          void requestScrollToBottom({ force: true })
        }
      })

      resizeObserver.value.observe(container)
    }

    if (stickToBottom.value) {
      void requestScrollToBottom({ force: true })
    }
  }

  const subscribeToUserChat = (onMessage = () => {}) => {
    if (userChannel.value) {
      return
    }

    const echo = ensureEcho()
    const resolvedCurrentUserId = resolveCurrentUserId(currentUserId)

    if (!echo || !resolvedCurrentUserId) {
      return
    }

    userChannelName.value = `assignment-chat-user.${resolvedCurrentUserId}`
    userChannel.value = echo.private(userChannelName.value)
    userChannel.value.listen('.assignment-chat.message.created', (payload) => {
      void safelyRunHandler(onMessage, payload).catch(() => {})
    })
  }

  const subscribeToThread = (threadId, onMessage = () => {}) => {
    leaveActiveThreadChannel()

    if (!threadId) {
      return
    }

    const echo = ensureEcho()
    if (!echo) {
      return
    }

    activeChannelName.value = `assignment-chat.${threadId}`
    activeChannel.value = echo.private(activeChannelName.value)
    activeChannel.value.listen('.assignment-chat.message.created', (payload) => {
      void safelyRunHandler(onMessage, payload).catch(() => {})
    })
  }

  const cleanup = () => {
    detachScrollTracking()
    disconnectRealtime()
  }

  watch(
    () => scrollContainerRef?.value,
    (container) => {
      attachScrollTracking(container)
    },
    { immediate: true, flush: 'post' }
  )

  return {
    ensureEcho,
    leaveActiveThreadChannel,
    disconnectRealtime,
    getScrollContainer,
    cancelScrollFrame,
    measureScrollProximity,
    scrollContainerToBottom,
    requestScrollToBottom,
    handleScroll,
    detachScrollTracking,
    attachScrollTracking,
    subscribeToUserChat,
    subscribeToThread,
    cleanup,
  }
}
