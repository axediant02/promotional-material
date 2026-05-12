/**
 * Shared composable for assignment chat functionality.
 * Consolidates logic used by AssignmentChatPanel and ClientChatDrawer.
 */
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import {
  fetchChatThread,
  fetchChatThreads,
  markChatThreadAsRead,
  sendChatMessage,
} from '../../../services/chatService'
import { normalizeMessage, normalizeThread, formatTimestamp, formatThreadStatus } from './chatNormalization'
import { useRealtimeChat } from './realtimeChat'

export function useAssignmentChat(props, options = {}) {
  const {
    onSendError = () => {},
    onLoadError = () => {},
    scrollContainerRef = null,
  } = options

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

  const activeThreads = computed(() => threads.value.filter((thread) => thread.status === 'active'))
  const archivedThreads = computed(() => threads.value.filter((thread) => thread.status === 'archived'))
  const selectedThread = computed(() => threads.value.find((thread) => thread.thread_id === selectedThreadId.value) ?? null)
  const showThreadList = computed(() => threads.value.length > 1)
  const selectedThreadArchived = computed(() => selectedThread.value?.status === 'archived')

  const realtime = useRealtimeChat({
    scrollContainerRef,
    currentUserId: props.currentUserId,
    stickToBottom,
    isNearBottom,
    onListenerError: (err) => {
      error.value = err?.message ?? 'Unable to process a chat update.'
    },
  })

  const {
    leaveActiveThreadChannel,
    disconnectRealtime,
    requestScrollToBottom,
    subscribeToUserChat,
    subscribeToThread,
    cleanup: cleanupRealtime,
  } = realtime

  const upsertThread = (incomingThread) => {
    const normalized = normalizeThread(incomingThread, props.currentUserId)
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

    const incomingMessage = normalizeMessage(payload.message, props.currentUserId)
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

  const loadSelectedThread = async (threadId) => {
    if (!threadId) {
      return
    }

    threadLoading.value = true
    sendError.value = ''

    subscribeToThread(threadId, handleIncomingMessagePayload)

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
      const message = normalizeMessage(response.data.data.message, props.currentUserId)
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

  const initialize = () => {
    subscribeToUserChat(handleIncomingMessagePayload)
    void loadThreads()
  }

  const cleanup = () => {
    cleanupRealtime()
  }

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

  onBeforeUnmount(() => {
    cleanup()
  })

  return {
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
    loadThreads,
    loadSelectedThread,
    openThread,
    sendMessage,
    markSelectedThreadAsRead,
    scrollMessagesToBottom: requestScrollToBottom,
    initialize,
    cleanup,
    selectBestThread,
    normalizeMessage,
    normalizeThread,
    formatTimestamp,
    formatThreadStatus,
    upsertThread,
    upsertMessageIntoThread,
    subscribeToUserChat,
    subscribeToThread,
    ensureEcho: realtime.ensureEcho,
    disconnectRealtime,
    leaveActiveThreadChannel,
  }
}
