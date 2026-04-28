<script setup>
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import {
  fetchChatThread,
  fetchChatThreads,
  markChatThreadAsRead,
  sendChatMessage,
} from '../../../services/chatService'

const props = defineProps({
  open: { type: Boolean, default: false },
  currentUserId: { type: String, default: '' },
  title: { type: String, default: 'Messages' },
})

const emit = defineEmits(['close', 'unread-count-change'])

const threads = ref([])
const loading = ref(false)
const threadLoading = ref(false)
const sendError = ref('')
const messageDraft = ref('')
const sending = ref(false)
const echo = ref(null)
const userChannel = ref(null)
const userChannelName = ref('')
const activeChannel = ref(null)
const activeChannelName = ref('')
const panelRef = ref(null)
const threadScrollRef = ref(null)
const selectedThreadId = ref('')

const selectedThread = computed(() => threads.value.find((thread) => thread.thread_id === selectedThreadId.value) ?? null)
const selectedThreadArchived = computed(() => selectedThread.value?.status === 'archived')

const resolveBroadcastAuthEndpoint = () => {
  const apiUrl = import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api'
  return apiUrl.replace(/\/$/, '') + '/broadcasting/auth'
}

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
  status: thread?.status ?? 'active',
  last_message_at: thread?.last_message_at ?? null,
  last_message_preview: thread?.last_message_preview ?? 'No messages yet.',
  unread_count: Number(thread?.unread_count ?? 0),
  counterpart: thread?.counterpart ?? null,
  messages: (thread?.messages ?? []).map(normalizeMessage),
})

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

const getAvatarLabel = (name) => {
  if (!name) {
    return 'C'
  }

  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('')
}

const ensureEcho = () => {
  const token = localStorage.getItem('pm_token')

  if (!token) {
    return null
  }

  if (echo.value) {
    return echo.value
  }

  window.Pusher = Pusher

  const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http'
  const host = import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1'
  const port = Number(import.meta.env.VITE_REVERB_PORT ?? 8080)

  echo.value = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY ?? 'promotional-materials-key',
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

  return echo.value
}

const disconnectRealtime = () => {
  if (activeChannel.value && activeChannelName.value) {
    echo.value?.leave(activeChannelName.value)
    activeChannel.value = null
    activeChannelName.value = ''
  }

  if (echo.value) {
    echo.value.disconnect()
    echo.value = null
  }
}

const leaveUserChannel = () => {
  if (userChannel.value && userChannelName.value) {
    echo.value?.leave(userChannelName.value)
    userChannel.value = null
    userChannelName.value = ''
  }
}

const handleDocumentPointerDown = (event) => {
  if (!props.open) {
    return
  }

  if (panelRef.value?.contains(event.target)) {
    return
  }

  emit('close')
}

const handleDocumentKeydown = (event) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

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

    return new Date(right.last_message_at ?? 0).getTime() - new Date(left.last_message_at ?? 0).getTime()
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

const scrollMessagesToBottom = async () => {
  await nextTick()

  if (threadScrollRef.value) {
    threadScrollRef.value.scrollTop = threadScrollRef.value.scrollHeight
  }
}

const emitUnreadCount = () => {
  emit(
    'unread-count-change',
    threads.value.reduce((total, thread) => total + Number(thread.unread_count ?? 0), 0),
  )
}

const loadThreads = async () => {
  if (!props.currentUserId) {
    threads.value = []
    return
  }

  loading.value = true

  try {
    const response = await fetchChatThreads()
    const refreshedThreadIds = new Set()

    for (const thread of response.data.data.threads ?? []) {
      refreshedThreadIds.add(thread?.thread_id ?? '')
      upsertThread(thread)
    }

    threads.value = threads.value.filter((thread) => refreshedThreadIds.has(thread.thread_id))
  } catch (error) {
    console.error('Failed to load chat threads:', error)
  } finally {
    loading.value = false
  }
}

const loadSelectedThread = async (threadId) => {
  if (!threadId) {
    return
  }

  threadLoading.value = true
  sendError.value = ''

  if (activeChannel.value && activeChannelName.value) {
    echo.value?.leave(activeChannelName.value)
    activeChannel.value = null
    activeChannelName.value = ''
  }

  try {
    const response = await fetchChatThread(threadId)
    upsertThread(response.data.data.thread)
    await markChatThreadAsRead(threadId)

    const echoInstance = ensureEcho()
    if (echoInstance) {
      activeChannelName.value = `assignment-chat.${threadId}`
      activeChannel.value = echoInstance.private(activeChannelName.value)
      activeChannel.value.listen('.assignment-chat.message.created', async (payload) => {
        if (!payload?.message) {
          return
        }

        const incomingMessage = normalizeMessage(payload.message)
        const currentThread = threads.value.find((thread) => thread.thread_id === threadId)

        if (!currentThread) {
          return
        }

        const messageWasAdded = upsertMessageIntoThread(currentThread, incomingMessage)

        currentThread.last_message_at = incomingMessage.created_at
        currentThread.last_message_preview = incomingMessage.body
        currentThread.unread_count = incomingMessage.is_own_message ? 0 : currentThread.unread_count + (messageWasAdded ? 1 : 0)
        upsertThread(currentThread)

        if (!incomingMessage.is_own_message) {
          try {
            await markChatThreadAsRead(threadId)
            await loadThreads()
          } catch {
            // Keep failures quiet in the launcher.
          }
        }

        await scrollMessagesToBottom()
      })
    }

    await scrollMessagesToBottom()
  } catch (err) {
    sendError.value = err.response?.data?.message ?? 'Unable to load this conversation.'
  } finally {
    threadLoading.value = false
  }
}

const ensurePayloadThread = async (threadId) => {
  if (!threadId) {
    return null
  }

  const existingThread = threads.value.find((item) => item.thread_id === threadId) ?? null
  if (existingThread) {
    return existingThread
  }

  const response = await fetchChatThread(threadId)
  upsertThread(response.data.data.thread)
  return threads.value.find((item) => item.thread_id === threadId) ?? null
}

const handleIncomingMessagePayload = async (payload) => {
  if (!payload?.message) {
    return
  }

  const incomingMessage = normalizeMessage(payload.message)
  const threadId = payload.thread_id ?? incomingMessage.thread_id
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
    try {
      await markChatThreadAsRead(currentThread.thread_id)
    } catch {
      // Keep failures quiet in the launcher.
    }
  }

  if (isSelectedThread) {
    await scrollMessagesToBottom()
  }
}

const subscribeToUserChat = () => {
  if (userChannel.value || !props.currentUserId) {
    return
  }

  const echoInstance = ensureEcho()
  if (!echoInstance) {
    return
  }

  userChannelName.value = `assignment-chat-user.${props.currentUserId}`
  userChannel.value = echoInstance.private(userChannelName.value)
  userChannel.value.listen('.assignment-chat.message.created', (payload) => {
    void handleIncomingMessagePayload(payload).catch(() => {})
  })
}

const openThread = async (threadId) => {
  if (!threadId) {
    return
  }

  selectedThreadId.value = threadId
  await loadSelectedThread(threadId)
}

const backToInbox = () => {
  selectedThreadId.value = ''
  if (activeChannel.value && activeChannelName.value) {
    echo.value?.leave(activeChannelName.value)
    activeChannel.value = null
    activeChannelName.value = ''
  }
}

const sendMessage = async () => {
  const body = messageDraft.value.trim()

  if (!body || !selectedThread.value || selectedThreadArchived.value) {
    return
  }

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
    await scrollMessagesToBottom()
  } catch (err) {
    sendError.value = err.response?.data?.message ?? 'Unable to send the message.'
  } finally {
    sending.value = false
  }
}

watch(
  () => props.open,
  (value) => {
    if (value) {
      selectedThreadId.value = ''
      subscribeToUserChat()
      void loadThreads()
      return
    }

    selectedThreadId.value = ''
    if (activeChannel.value && activeChannelName.value) {
      echo.value?.leave(activeChannelName.value)
      activeChannel.value = null
      activeChannelName.value = ''
    }
  },
)

onMounted(() => {
  document.addEventListener('pointerdown', handleDocumentPointerDown)
  document.addEventListener('keydown', handleDocumentKeydown)

  subscribeToUserChat()
  void loadThreads()
})

onBeforeUnmount(() => {
  disconnectRealtime()
  leaveUserChannel()
  document.removeEventListener('pointerdown', handleDocumentPointerDown)
  document.removeEventListener('keydown', handleDocumentKeydown)
})

watch(
  threads,
  () => {
    emitUnreadCount()
  },
  { deep: true, immediate: true },
)
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-180 ease-out"
      enter-from-class="translate-y-2 opacity-0 scale-[0.985]"
      enter-to-class="translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-120 ease-in"
      leave-from-class="translate-y-0 opacity-100 scale-100"
      leave-to-class="translate-y-2 opacity-0 scale-[0.985]"
    >
      <aside
        v-if="open"
        ref="panelRef"
        class="fixed bottom-6 right-6 z-50 flex h-[33rem] w-[22.5rem] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-[1.25rem] border border-[#253043] bg-[#171a25] shadow-[0_22px_60px_rgba(0,0,0,0.38)] max-h-[calc(100vh-2rem)]"
      >
        <template v-if="selectedThread">
          <div class="flex items-center justify-between gap-3 border-b border-[#253043] px-4 py-4">
            <div class="flex min-w-0 items-center gap-3">
              <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-zinc-300 transition hover:bg-white/10 hover:text-white"
                @click="backToInbox"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                  <path d="m15 18-6-6 6-6" />
                </svg>
              </button>
              <div class="flex min-w-0 items-center gap-3">
                <div class="relative flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-pink-500 text-sm font-semibold text-white">
                  {{ getAvatarLabel(selectedThread.counterpart?.name) }}
                  <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-[#171a25] bg-emerald-400" />
                </div>
                <div class="min-w-0">
                  <h2 class="truncate text-[1rem] font-semibold leading-none text-white">
                    {{ selectedThread.counterpart?.name ?? 'Conversation' }}
                  </h2>
                  <p class="mt-1 truncate text-[10px] text-zinc-500">
                    {{ selectedThread.counterpart?.role === 'production' ? 'Lead Designer' : selectedThread.counterpart?.role ?? 'Conversation' }}
                  </p>
                </div>
              </div>
            </div>

            <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-full text-zinc-400 transition hover:bg-white/5 hover:text-white"
              @click="emit('close')"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M6 6l12 12" />
                <path d="M18 6 6 18" />
              </svg>
            </button>
          </div>

          <div
            ref="threadScrollRef"
            class="flex-1 overflow-y-auto px-4 py-4"
          >
            <div v-if="threadLoading" class="space-y-3">
              <div
                v-for="index in 3"
                :key="index"
                class="animate-pulse rounded-[1.2rem] border border-[#253043] bg-white/5 px-4 py-4"
              >
                <div class="h-3 w-20 rounded bg-white/10" />
                <div class="mt-3 h-3 w-full rounded bg-white/10" />
              </div>
            </div>

            <div
              v-else-if="!selectedThread.messages.length"
              class="rounded-[1.2rem] border border-dashed border-[#253043] bg-white/[0.02] px-4 py-6 text-sm text-zinc-400"
            >
              No messages yet. Start the conversation when you are ready.
            </div>

            <div v-else class="space-y-4">
              <article
                v-for="message in selectedThread.messages"
                :key="message.message_id"
                :class="[
                  'max-w-[90%] rounded-[1.1rem] px-4 py-3',
                  message.is_own_message
                    ? 'ml-auto bg-violet-600 text-white shadow-[0_12px_28px_rgba(124,58,237,0.22)]'
                    : 'bg-white/5 text-white',
                ]"
              >
                <div class="flex items-center justify-between gap-4">
                  <p :class="['text-xs font-semibold', message.is_own_message ? 'text-violet-50' : 'text-zinc-300']">
                    {{ message.sender_name }}
                  </p>
                  <p :class="['text-[10px] uppercase tracking-[0.18em]', message.is_own_message ? 'text-violet-100/80' : 'text-zinc-500']">
                    {{ formatTimestamp(message.created_at) }}
                  </p>
                </div>
                <p :class="['mt-2 text-sm leading-6', message.is_own_message ? 'text-white' : 'text-zinc-200']">
                  {{ message.body }}
                </p>
              </article>
            </div>
          </div>

          <div class="border-t border-[#253043] px-4 py-4">
            <p
              v-if="sendError"
              class="mb-3 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-200"
            >
              {{ sendError }}
            </p>

            <div v-if="selectedThreadArchived" class="rounded-[1.2rem] border border-dashed border-[#253043] bg-white/[0.02] px-4 py-4 text-sm text-zinc-400">
              Conversation archived. You can review the history, but new messages are disabled.
            </div>

            <div v-else class="flex items-center gap-2 rounded-[0.8rem] border border-[#253043] bg-[#131722] px-3 py-2">
              <input
                v-model="messageDraft"
                type="text"
                class="min-w-0 flex-1 bg-transparent text-sm text-white outline-none placeholder:text-zinc-500"
                placeholder="Reply to Sarah..."
                @keydown.enter.prevent="sendMessage"
              />
              <button
                class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-white transition hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="sending || !messageDraft.trim()"
                @click="sendMessage"
              >
                <svg v-if="sending" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M12 2v4" />
                  <path d="M12 18v4" />
                  <path d="M4.93 4.93l2.83 2.83" />
                  <path d="M16.24 16.24l2.83 2.83" />
                  <path d="M2 12h4" />
                  <path d="M18 12h4" />
                  <path d="M4.93 19.07l2.83-2.83" />
                  <path d="M16.24 7.76l2.83-2.83" />
                </svg>
                <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M22 2 11 13" />
                  <path d="M22 2 15 22 11 13 2 9 22 2Z" />
                </svg>
              </button>
            </div>
          </div>
        </template>

        <template v-else>
          <div class="flex items-start justify-between gap-4 border-b border-[#253043] px-4 py-4">
            <div class="min-w-0">
              <h2 class="text-[1rem] font-semibold leading-none text-white">{{ title }}</h2>
              <p class="mt-1 text-[10px] uppercase tracking-[0.22em] text-zinc-500">
                {{ threads.length }} conversation{{ threads.length === 1 ? '' : 's' }}
              </p>
            </div>

            <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-full text-zinc-400 transition hover:bg-white/5 hover:text-white"
              @click="emit('close')"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M6 6l12 12" />
                <path d="M18 6 6 18" />
              </svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto">
            <div v-if="loading" class="space-y-0">
              <div
                v-for="index in 3"
                :key="index"
                class="border-b border-[#253043] px-4 py-4"
              >
                <div class="h-11 rounded-full bg-white/5" />
              </div>
            </div>

            <div
              v-else-if="!threads.length"
              class="px-4 py-10 text-sm text-zinc-400"
            >
              No conversations yet.
            </div>

            <button
              v-for="(thread, index) in threads"
              :key="thread.thread_id"
              type="button"
              class="flex w-full items-start gap-3 border-b border-[#253043] px-4 py-4 text-left transition hover:bg-white/[0.03]"
              :class="thread.unread_count > 0 ? 'bg-white/[0.02]' : ''"
              @click="openThread(thread.thread_id)"
            >
              <div
                class="relative flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                :class="[
                  index === 0 ? 'bg-rose-500' : index === 1 ? 'bg-violet-500' : index === 2 ? 'bg-sky-500' : 'bg-slate-500',
                ]"
              >
                {{ getAvatarLabel(thread.counterpart?.name) }}
                <span
                  class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-[#171a25]"
                  :class="thread.status === 'active' ? 'bg-emerald-400' : 'bg-slate-500'"
                />
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <h3 class="truncate text-sm font-semibold text-zinc-100">
                      {{ thread.counterpart?.name ?? 'Conversation' }}
                    </h3>
                    <p class="mt-1 line-clamp-2 text-sm leading-5 text-zinc-400">
                      {{ thread.last_message_preview }}
                    </p>
                  </div>
                  <div class="flex flex-col items-end gap-2">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                      {{ formatTimestamp(thread.last_message_at) }}
                    </span>
                    <span
                      v-if="thread.unread_count > 0"
                      class="inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-violet-500 px-1.5 text-[10px] font-bold leading-none text-white shadow-[0_6px_16px_rgba(124,58,237,0.35)]"
                    >
                      {{ thread.unread_count > 9 ? '9+' : thread.unread_count }}
                    </span>
                  </div>
                </div>
              </div>
            </button>
          </div>
        </template>
      </aside>
    </Transition>
  </Teleport>
</template>
