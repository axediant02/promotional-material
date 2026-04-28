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
  currentUserId: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: 'Messages',
  },
  description: {
    type: String,
    default: '',
  },
  emptyMessage: {
    type: String,
    default: 'Chat will appear here once an assignment is active.',
  },
  preferredClientId: {
    type: String,
    default: '',
  },
})

const DEFAULT_REVERB_APP_KEY = 'promotional-materials-key'
const DEFAULT_REVERB_HOST = '127.0.0.1'
const DEFAULT_REVERB_PORT = 8080
const DEFAULT_REVERB_SCHEME = 'http'

const loading = ref(false)
const threadLoading = ref(false)
const error = ref('')
const sendError = ref('')
const threads = ref([])
const selectedThreadId = ref('')
const messageDraft = ref('')
const sending = ref(false)
const echo = ref(null)
const activeChannel = ref(null)
const activeChannelName = ref('')
const userChannel = ref(null)
const userChannelName = ref('')
const threadScrollRef = ref(null)

const activeThreads = computed(() => threads.value.filter((thread) => thread.status === 'active'))
const archivedThreads = computed(() => threads.value.filter((thread) => thread.status === 'archived'))
const selectedThread = computed(() => threads.value.find((thread) => thread.thread_id === selectedThreadId.value) ?? null)
const showThreadList = computed(() => threads.value.length > 1)
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
  client_id: thread?.client_id ?? '',
  production_id: thread?.production_id ?? '',
  status: thread?.status ?? 'active',
  started_at: thread?.started_at ?? null,
  closed_at: thread?.closed_at ?? null,
  last_message_at: thread?.last_message_at ?? null,
  unread_count: thread?.unread_count ?? 0,
  last_message_preview: thread?.last_message_preview ?? '',
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

const formatThreadStatus = (value) => (value === 'archived' ? 'Archived' : 'Active')

const ensureEcho = () => {
  const token = localStorage.getItem('pm_token')

  if (!token) {
    return null
  }

  if (echo.value) {
    return echo.value
  }

  window.Pusher = Pusher

  const scheme = import.meta.env.VITE_REVERB_SCHEME ?? DEFAULT_REVERB_SCHEME
  const host = import.meta.env.VITE_REVERB_HOST ?? DEFAULT_REVERB_HOST
  const port = Number(import.meta.env.VITE_REVERB_PORT ?? DEFAULT_REVERB_PORT)

  echo.value = new Echo({
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

  return echo.value
}

const leaveActiveThreadChannel = () => {
  if (activeChannel.value && activeChannelName.value) {
    echo.value?.leave(activeChannelName.value)
    activeChannel.value = null
    activeChannelName.value = ''
  }
}

const disconnectRealtime = () => {
  leaveActiveThreadChannel()

  if (userChannel.value && userChannelName.value) {
    echo.value?.leave(userChannelName.value)
    userChannel.value = null
    userChannelName.value = ''
  }

  if (echo.value) {
    echo.value.disconnect()
    echo.value = null
  }
}

const scrollMessagesToBottom = async () => {
  await nextTick()

  if (threadScrollRef.value) {
    threadScrollRef.value.scrollTop = threadScrollRef.value.scrollHeight
  }
}

const upsertThread = (incomingThread) => {
  const normalized = normalizeThread(incomingThread)
  const hasMessagePayload = Array.isArray(incomingThread?.messages)
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

const selectBestThread = () => {
  if (props.preferredClientId) {
    const preferredThread = threads.value.find((thread) => thread.client_id === props.preferredClientId && thread.status === 'active')
      ?? threads.value.find((thread) => thread.client_id === props.preferredClientId)

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

const loadThreads = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetchChatThreads()
    threads.value = (response.data.data.threads ?? []).map(normalizeThread)
    selectBestThread()
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Unable to load chat threads.'
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
    selectBestThread()
  }

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
    await markSelectedThreadAsRead()
  }

  if (isSelectedThread) {
    await scrollMessagesToBottom()
  }
}

const subscribeToUserChat = () => {
  if (userChannel.value) {
    return
  }

  const echoInstance = ensureEcho()
  if (!echoInstance || !props.currentUserId) {
    return
  }

  userChannelName.value = `assignment-chat-user.${props.currentUserId}`
  userChannel.value = echoInstance.private(userChannelName.value)
  userChannel.value.listen('.assignment-chat.message.created', (payload) => {
    void handleIncomingMessagePayload(payload).catch(() => {})
  })
}

const subscribeToThread = (threadId) => {
  leaveActiveThreadChannel()

  if (!threadId) {
    return
  }

  const echoInstance = ensureEcho()
  if (!echoInstance) {
    return
  }

  activeChannelName.value = `assignment-chat.${threadId}`
  activeChannel.value = echoInstance.private(activeChannelName.value)
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
    await scrollMessagesToBottom()
  } catch (err) {
    sendError.value = err.response?.data?.message ?? 'Unable to load this conversation.'
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
  () => props.preferredClientId,
  () => {
    selectBestThread()
  }
)

watch(selectedThreadId, (threadId) => {
  if (threadId) {
    void loadSelectedThread(threadId)
    return
  }

  leaveActiveThreadChannel()
})

watch(
  () => selectedThread.value?.messages?.length,
  async () => {
    await scrollMessagesToBottom()
  }
)

onMounted(async () => {
  subscribeToUserChat()
  await loadThreads()
})

onBeforeUnmount(() => {
  disconnectRealtime()
})
</script>

<template>
  <section class="pm-surface rounded-[1.75rem] p-5">
    <div class="flex items-start justify-between gap-4 border-b border-border/70 pb-5 dark:border-white/10">
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-brand-600 dark:text-brand-100">Chat</p>
        <h3 class="mt-2 text-xl font-semibold text-ink dark:text-white">{{ title }}</h3>
        <p v-if="description" class="mt-2 text-sm leading-6 text-muted dark:text-zinc-300">{{ description }}</p>
      </div>
      <span class="rounded-full border border-border bg-white/70 px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
        {{ activeThreads.length }} active
      </span>
    </div>

    <div v-if="loading" class="mt-5 space-y-3">
      <div
        v-for="index in 3"
        :key="index"
        class="animate-pulse rounded-[1.4rem] border border-border bg-white/60 px-4 py-4 dark:border-white/10 dark:bg-white/5"
      >
        <div class="h-3 w-28 rounded bg-slate-200 dark:bg-white/10" />
        <div class="mt-3 h-4 w-2/3 rounded bg-slate-200 dark:bg-white/10" />
        <div class="mt-2 h-3 w-full rounded bg-slate-200 dark:bg-white/10" />
      </div>
    </div>

    <div
      v-else-if="error"
      class="mt-5 rounded-[1.4rem] border border-brand-200 bg-brand-50 px-4 py-4 text-sm text-brand-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
    >
      {{ error }}
    </div>

    <div
      v-else-if="!threads.length"
      class="mt-5 rounded-[1.4rem] border border-dashed border-border bg-white/50 px-4 py-8 text-sm text-muted dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-300"
    >
      {{ emptyMessage }}
    </div>

    <div v-else class="mt-5 space-y-5">
      <div v-if="showThreadList" class="space-y-4">
        <div v-if="activeThreads.length">
          <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-muted dark:text-zinc-400">Active threads</p>
          <div class="mt-3 space-y-2">
            <button
              v-for="thread in activeThreads"
              :key="thread.thread_id"
              :class="[
                'w-full rounded-[1.3rem] border px-4 py-3 text-left transition',
                selectedThreadId === thread.thread_id
                  ? 'border-brand-300 bg-brand-50 shadow-[0_14px_30px_rgba(75,61,116,0.08)] dark:border-white/15 dark:bg-white/10'
                  : 'border-border bg-white/60 hover:border-brand-300 hover:bg-brand-50/60 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/15 dark:hover:bg-white/10',
              ]"
              @click="openThread(thread.thread_id)"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ thread.counterpart?.name ?? 'Conversation' }}</p>
                  <p class="mt-1 line-clamp-2 text-xs text-muted dark:text-zinc-400">{{ thread.last_message_preview || 'No messages yet.' }}</p>
                </div>
                <div class="text-right">
                  <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">{{ formatTimestamp(thread.last_message_at || thread.started_at) }}</p>
                  <span
                    v-if="thread.unread_count"
                    class="mt-2 inline-flex rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-semibold text-white"
                  >
                    {{ thread.unread_count }}
                  </span>
                </div>
              </div>
            </button>
          </div>
        </div>

        <div v-if="archivedThreads.length">
          <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-muted dark:text-zinc-400">Archived history</p>
          <div class="mt-3 space-y-2">
            <button
              v-for="thread in archivedThreads"
              :key="thread.thread_id"
              :class="[
                'w-full rounded-[1.3rem] border px-4 py-3 text-left transition',
                selectedThreadId === thread.thread_id
                  ? 'border-border bg-white/70 dark:border-white/15 dark:bg-white/10'
                  : 'border-border bg-white/50 hover:border-brand-200 hover:bg-white/70 dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-white/15 dark:hover:bg-white/10',
              ]"
              @click="openThread(thread.thread_id)"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ thread.counterpart?.name ?? 'Conversation' }}</p>
                  <p class="mt-1 line-clamp-2 text-xs text-muted dark:text-zinc-400">{{ thread.last_message_preview || 'Archived without messages.' }}</p>
                </div>
                <span class="rounded-full border border-border bg-white/70 px-2 py-0.5 text-[10px] uppercase tracking-[0.2em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-400">
                  Archived
                </span>
              </div>
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="selectedThread"
        class="rounded-[1.4rem] border border-border bg-white/55 dark:border-white/10 dark:bg-white/[0.03]"
      >
        <div class="flex items-start justify-between gap-4 border-b border-border/70 px-4 py-4 dark:border-white/10">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <p class="truncate text-base font-semibold text-ink dark:text-white">{{ selectedThread.counterpart?.name ?? 'Conversation' }}</p>
              <span
                :class="[
                  'rounded-full px-2.5 py-1 text-[10px] uppercase tracking-[0.2em]',
                  selectedThread.status === 'archived'
                    ? 'border border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300'
                    : 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-100',
                ]"
              >
                {{ formatThreadStatus(selectedThread.status) }}
              </span>
            </div>
            <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ selectedThread.counterpart?.email ?? selectedThread.counterpart?.role ?? '' }}</p>
          </div>
          <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">
            {{ formatTimestamp(selectedThread.last_message_at || selectedThread.started_at) }}
          </p>
        </div>

        <div
          ref="threadScrollRef"
          class="max-h-[20rem] space-y-3 overflow-y-auto px-4 py-4"
        >
          <div v-if="threadLoading" class="space-y-3">
            <div
              v-for="index in 3"
              :key="index"
              class="animate-pulse rounded-[1.2rem] border border-border bg-white/60 px-4 py-4 dark:border-white/10 dark:bg-white/5"
            >
              <div class="h-3 w-20 rounded bg-slate-200 dark:bg-white/10" />
              <div class="mt-3 h-3 w-full rounded bg-slate-200 dark:bg-white/10" />
            </div>
          </div>

          <div
            v-else-if="!selectedThread.messages.length"
            class="rounded-[1.2rem] border border-dashed border-border bg-white/50 px-4 py-6 text-sm text-muted dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-300"
          >
            No messages yet. Start the conversation when you are ready.
          </div>

          <article
            v-for="message in selectedThread.messages"
            v-else
            :key="message.message_id"
            :class="[
              'max-w-[92%] rounded-[1.25rem] px-4 py-3',
              message.is_own_message
                ? 'ml-auto bg-brand-600 text-white shadow-[0_12px_28px_rgba(75,61,116,0.22)]'
                : 'border border-border bg-white/80 text-ink dark:border-white/10 dark:bg-white/5 dark:text-white',
            ]"
          >
            <div class="flex items-center justify-between gap-4">
              <p :class="['text-xs font-semibold', message.is_own_message ? 'text-brand-50' : 'text-muted dark:text-zinc-300']">
                {{ message.sender_name }}
              </p>
              <p :class="['text-[10px] uppercase tracking-[0.2em]', message.is_own_message ? 'text-brand-100/80' : 'text-muted dark:text-zinc-500']">
                {{ formatTimestamp(message.created_at) }}
              </p>
            </div>
            <p :class="['mt-2 text-sm leading-6', message.is_own_message ? 'text-white' : 'text-muted dark:text-zinc-200']">
              {{ message.body }}
            </p>
          </article>
        </div>

        <div class="border-t border-border/70 px-4 py-4 dark:border-white/10">
          <p
            v-if="sendError"
            class="mb-3 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-sm text-brand-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
          >
            {{ sendError }}
          </p>

          <div v-if="selectedThreadArchived" class="rounded-[1.2rem] border border-dashed border-border bg-white/50 px-4 py-4 text-sm text-muted dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-300">
            Conversation archived. You can review the history, but new messages are disabled.
          </div>

          <div v-else class="space-y-3">
            <textarea
              v-model="messageDraft"
              class="pm-input min-h-24 w-full rounded-[1.3rem] px-4 py-3 text-sm"
              placeholder="Write a message..."
            ></textarea>
            <div class="flex justify-end">
              <button
                class="pm-gradient-primary rounded-xl px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.22em] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="sending || !messageDraft.trim()"
                @click="sendMessage"
              >
                {{ sending ? 'Sending...' : 'Send message' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
