/**
 * Shared composable for assignment chat functionality.
 * Consolidates logic used by AssignmentChatPanel and ClientChatDrawer.
 */
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import {
  fetchChatThread,
  fetchChatThreads,
  markChatThreadAsRead,
  sendChatMessage,
} from '../../../services/chatService'

const DEFAULT_REVERB_APP_KEY = 'promotional-materials-key'
const DEFAULT_REVERB_HOST = '127.0.0.1'
const DEFAULT_REVERB_PORT = 8080
const DEFAULT_REVERB_SCHEME = 'http'
const SCROLL_BOTTOM_THRESHOLD = 48

export function useAssignmentChat(props, options = {}) {
  const {
    onSendError = () => {},
    onLoadError = () => {},
    scrollContainerRef = null,
  } = options

  // Refs
  const loading = ref(false)
  const threadLoading = ref(false)
  const error = ref('')
  const sendError = ref('')
  const threads = ref([])
  const selectedThreadId = ref('')
  const messageDraft = ref('')
  const sending = ref(false)
  const stickToBottom = ref(false)
  const isNearBottom = ref(true)
  const scrollContainerElement = ref(null)
  const resizeObserver = ref(null)
  const scrollFrameId = ref(null)
  const echoInstance = ref(null)
  const activeChannel = ref(null)
  const activeChannelName = ref('')
  const userChannel = ref(null)
  const userChannelName = ref('')

  // Computed
  const activeThreads = computed(() => threads.value.filter((thread) => thread.status === 'active'))
  const archivedThreads = computed(() => threads.value.filter((thread) => thread.status === 'archived'))
  const selectedThread = computed(() => threads.value.find((thread) => thread.thread_id === selectedThreadId.value) ?? null)
  const showThreadList = computed(() => threads.value.length > 1)
  const selectedThreadArchived = computed(() => selectedThread.value?.status === 'archived')

  // Echo setup helpers
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

  // Scroll controller
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

  // Normalizers
  const normalizeMessage = (message) => ({
    message_id: message?.message_id ?? '',
    thread_id: message?.thread_id ?? '',
    sender_user_id: message?.sender_user_id ?? '',
    sender_name: message?.sender_name ?? 'User',
    sender_role: message?.sender_role ?? '',
    body: message?.body ?? '',
    created_at: message?.created_at ?? new Date().toISOString(),
    is_own_message: message?.sender_user_id === props.currentUserId,
  })

  const normalizeThread = (thread) => ({
    thread_id: thread?.thread_id ?? '',
    client_id: thread?.client_id ?? '',
    production_id: thread?.production_id ?? '',
    status: thread?.status ?? 'active',
    started_at: thread?.started_at ?? null,
    closed_at: thread?.closed_at ?? null,
    last_message_at: thread?.last_message_at ?? null,
    unread_count: Number(thread?.unread_count ?? 0),
    last_message_preview: thread?.last_message_preview ?? '',
    counterpart: thread?.counterpart ?? null,
    messages: (thread?.messages ?? []).map(normalizeMessage),
  })

  // Formatters
  const formatTimestamp = (value) => {
    if (!value) {
      return 'Just now'
    }

    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    }).format(new Date(value))
  }

  const formatThreadStatus = (value) => (value === 'archived' ? 'Archived' : 'Active')

  // Thread management
  const upsertThread = (incomingThread) => {
    const normalized = normalizeThread(incomingThread)
    const hasMessagePayload = Object.prototype.hasOwnProperty.call(incomingThread ?? {}, 'messages')
      && Array.isArray(incomingThread.messages)
    const nextThreads = [...threads.value]
    const existingIndex = nextThreads.findIndex((item) => item.thread_id === normalized.thread_id)

    if (existingIndex >= 0) {
      const existingThread = nextThreads[existingIndex]

      nextThreads.splice(existingIndex, 1, {
        ...existingThread,
        ...normalized,
        messages: hasMessagePayload ? normalized.messages : existingThread.messages,
      })
    } else {
      nextThreads.unshift(normalized)
    }

    threads.value = nextThreads.sort((left, right) => {
      if (left.status !== right.status) {
        return left.status === 'active' ? -1 : 1
      }

      return new Date(right.last_message_at ?? right.started_at ?? 0).getTime()
        - new Date(left.last_message_at ?? left.started_at ?? 0).getTime()
    })
  }

  const upsertMessageIntoThread = (thread, incomingMessage) => {
    const existingIndex = incomingMessage.message_id
      ? thread.messages.findIndex((message) => message.message_id === incomingMessage.message_id)
      : -1

    if (existingIndex >= 0) {
      thread.messages = thread.messages.map((message, index) =>
        index === existingIndex ? { ...message, ...incomingMessage } : message
      )

      return false
    }

    thread.messages = [...thread.messages, incomingMessage]

    return true
  }

  const selectBestThread = (preferredClientId = '') => {
    if (preferredClientId) {
      const preferredThread = threads.value.find((thread) => thread.client_id === preferredClientId && thread.status === 'active')
        ?? threads.value.find((thread) => thread.client_id === preferredClientId)

      selectedThreadId.value = preferredThread?.thread_id ?? ''
      return
    }

    const currentThreadExists = threads.value.some((thread) => thread.thread_id === selectedThreadId.value)
    if (currentThreadExists) {
      return
    }

    selectedThreadId.value =
      activeThreads.value[0]?.thread_id
      ?? threads.value[0]?.thread_id
      ?? ''
  }

  // API calls
  const loadThreads = async (options = {}) => {
    const { autoSelect = true } = options

    loading.value = true
    error.value = ''

    try {
      const response = await fetchChatThreads()
      const refreshedThreadIds = new Set()

      for (const thread of response.data.data.threads ?? []) {
        refreshedThreadIds.add(thread?.thread_id ?? '')
        upsertThread(thread)
      }

      threads.value = threads.value.filter((thread) => refreshedThreadIds.has(thread.thread_id))

      if (autoSelect) {
        selectBestThread(props.preferredClientId)
      }
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Unable to load chat threads.'
      onLoadError(err)
    } finally {
      loading.value = false
    }
  }

  const markSelectedThreadAsRead = async () => {
    if (!selectedThread.value || !selectedThread.value.unread_count) {
      return
    }

    try {
      const response = await markChatThreadAsRead(selectedThread.value.thread_id)
      upsertThread(response.data.data.thread)
    } catch {
      // Keep read failures non-blocking in the UI.
    }
  }

  const ensurePayloadThread = async (threadId) => {
    if (!threadId) {
      return null
    }

    let thread = threads.value.find((item) => item.thread_id === threadId) ?? null

    if (thread) {
      return thread
    }

    const response = await fetchChatThread(threadId)
    upsertThread(response.data.data.thread)

    if (!selectedThreadId.value) {
      selectBestThread(props.preferredClientId)
    }

    return threads.value.find((item) => item.thread_id === threadId) ?? null
  }

  const handleIncomingMessagePayload = async (payload) => {
    if (!payload?.message) {
      return
    }

    const incomingMessage = normalizeMessage(payload.message)
    const threadId = payload.thread_id ?? incomingMessage.thread_id
    const shouldAutoScroll = stickToBottom.value || isNearBottom.value
    const currentThread = await ensurePayloadThread(threadId)

    if (!currentThread) {
      return
    }

    const isSelectedThread = selectedThreadId.value === currentThread.thread_id
    const messageWasAdded = upsertMessageIntoThread(currentThread, incomingMessage)

    currentThread.last_message_at = incomingMessage.created_at
    currentThread.last_message_preview = incomingMessage.body
    currentThread.unread_count = incomingMessage.is_own_message || isSelectedThread
      ? 0
      : currentThread.unread_count + (messageWasAdded ? 1 : 0)
    upsertThread(currentThread)

    if (isSelectedThread && !incomingMessage.is_own_message) {
      await markSelectedThreadAsRead()
    }

    if (isSelectedThread && shouldAutoScroll) {
      stickToBottom.value = true
      await requestScrollToBottom()
    }
  }

  const subscribeToUserChat = () => {
    if (userChannel.value) {
      return
    }

    const echo = ensureEcho()
    if (!echo || !props.currentUserId) {
      return
    }

    userChannelName.value = `assignment-chat-user.${props.currentUserId}`
    userChannel.value = echo.private(userChannelName.value)
    userChannel.value.listen('.assignment-chat.message.created', (payload) => {
      void handleIncomingMessagePayload(payload).catch(() => {})
    })
  }

  const subscribeToThread = (threadId) => {
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
      void handleIncomingMessagePayload(payload).catch(() => {})
    })
  }

  const loadSelectedThread = async (threadId) => {
    if (!threadId) {
      return
    }

    threadLoading.value = true
    sendError.value = ''

    subscribeToThread(threadId)

    try {
      const response = await fetchChatThread(threadId)
      upsertThread(response.data.data.thread)
      await markSelectedThreadAsRead()
    } catch (err) {
      sendError.value = err.response?.data?.message ?? 'Unable to load this conversation.'
      onSendError(err)
    } finally {
      threadLoading.value = false
    }
  }

  const openThread = async (threadId) => {
    if (!threadId || selectedThreadId.value === threadId) {
      return
    }

    selectedThreadId.value = threadId
  }

  const sendMessage = async () => {
    const body = messageDraft.value.trim()

    if (!body || !selectedThread.value || selectedThreadArchived.value) {
      return
    }

    const shouldAutoScroll = stickToBottom.value || isNearBottom.value
    sending.value = true
    sendError.value = ''

    try {
      const response = await sendChatMessage(selectedThread.value.thread_id, { body })
      const message = normalizeMessage(response.data.data.message)
      const currentThread = selectedThread.value

      upsertMessageIntoThread(currentThread, message)
      currentThread.last_message_at = message.created_at
      currentThread.last_message_preview = message.body
      currentThread.unread_count = 0
      upsertThread(currentThread)

      messageDraft.value = ''

      if (shouldAutoScroll) {
        stickToBottom.value = true
        await requestScrollToBottom()
      }
    } catch (err) {
      sendError.value = err.response?.data?.message ?? 'Unable to send the message.'
      onSendError(err)
    } finally {
      sending.value = false
    }
  }

  // Lifecycle
  const initialize = () => {
    subscribeToUserChat()
    void loadThreads()
  }

  const cleanup = () => {
    detachScrollTracking()
    disconnectRealtime()
  }

  // Watches
  watch(
    () => props.preferredClientId,
    () => {
      selectBestThread(props.preferredClientId)
    }
  )

  watch(
    selectedThreadId,
    (threadId) => {
      if (threadId) {
        stickToBottom.value = true
        void requestScrollToBottom({ force: true })
        void loadSelectedThread(threadId)
        return
      }

      stickToBottom.value = false
      leaveActiveThreadChannel()
    },
    { flush: 'post' }
  )

  watch(
    () => scrollContainerRef?.value,
    (container) => {
      attachScrollTracking(container)
    },
    { immediate: true, flush: 'post' }
  )

  watch(
    () => selectedThread.value?.messages?.length ?? 0,
    (newLength, oldLength) => {
      if (newLength === oldLength || !selectedThread.value) {
        return
      }

      if (stickToBottom.value || isNearBottom.value) {
        stickToBottom.value = true
        void requestScrollToBottom({ force: true })
      }
    },
    { flush: 'post' }
  )

  // Auto-cleanup on unmount
  onBeforeUnmount(() => {
    cleanup()
  })

  return {
    // State
    threads,
    selectedThreadId,
    selectedThread,
    selectedThreadArchived,
    messageDraft,
    sending,
    loading,
    threadLoading,
    error,
    sendError,
    activeThreads,
    archivedThreads,
    showThreadList,

    // Methods
    loadThreads,
    loadSelectedThread,
    openThread,
    sendMessage,
    markSelectedThreadAsRead,
    scrollMessagesToBottom: requestScrollToBottom,
    initialize,
    cleanup,
    selectBestThread,

    // Utilities
    normalizeMessage,
    normalizeThread,
    formatTimestamp,
    formatThreadStatus,
    upsertThread,
    upsertMessageIntoThread,

    // Echo
    subscribeToUserChat,
    subscribeToThread,
    ensureEcho,
    disconnectRealtime,
  }
}
