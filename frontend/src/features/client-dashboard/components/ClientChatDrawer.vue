<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useAssignmentChat } from '../../chat/composables/useAssignmentChat'

const props = defineProps({
  open: { type: Boolean, default: false },
  currentUserId: { type: String, default: '' },
  title: { type: String, default: 'Messages' },
})

const emit = defineEmits(['close', 'unread-count-change'])

const panelRef = ref(null)
const threadScrollRef = ref(null)

const chat = useAssignmentChat(props, {
  scrollContainerRef: threadScrollRef,
})

const {
  threads,
  selectedThreadId,
  selectedThread,
  selectedThreadArchived,
  messageDraft,
  sending,
  loading,
  threadLoading,
  sendError,
  openThread,
  sendMessage,
  loadThreads,
} = chat

// Initialize without auto-selecting a thread - show the list instead
const initializeDrawer = () => {
  chat.subscribeToUserChat()
  void loadThreads({ autoSelect: false })
}

const totalUnreadCount = computed(() =>
  threads.value.reduce((total, thread) => total + Number(thread.unread_count ?? 0), 0)
)

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

const getAvatarColor = (index) => {
  const colors = ['bg-rose-500', 'bg-violet-500', 'bg-sky-500', 'bg-slate-500']
  return colors[index % colors.length]
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

const backToInbox = () => {
  selectedThreadId.value = ''
  chat.subscribeToUserChat()
}

onMounted(() => {
  document.addEventListener('pointerdown', handleDocumentPointerDown)
  document.addEventListener('keydown', handleDocumentKeydown)

  subscribeToUserChatIfOpen()
})

function subscribeToUserChatIfOpen() {
  if (props.open) {
    initializeDrawer()
  }
}

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleDocumentPointerDown)
  document.removeEventListener('keydown', handleDocumentKeydown)
})

watch(
  () => props.open,
  (value) => {
    if (value) {
      selectedThreadId.value = ''
      initializeDrawer()
      return
    }

    selectedThreadId.value = ''
  }
)

watch(totalUnreadCount, (count) => {
  emit('unread-count-change', count)
}, { immediate: true })
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
          <!-- Thread View -->
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
                    {{ chat.formatTimestamp(message.created_at) }}
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
                placeholder="Reply..."
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
          <!-- Thread List View -->
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

          <div class="flex-1 overflow-y-auto pb-2">
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

            <div v-else>
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
                  :class="getAvatarColor(index)"
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
                        {{ chat.formatTimestamp(thread.last_message_at) }}
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
          </div>
        </template>
      </aside>
    </Transition>
  </Teleport>
</template>
