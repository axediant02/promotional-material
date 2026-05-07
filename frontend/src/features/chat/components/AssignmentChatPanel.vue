<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAssignmentChat } from '../composables/useAssignmentChat'

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
  error,
  sendError,
  activeThreads,
  archivedThreads,
  showThreadList,
  openThread,
  sendMessage,
  initialize,
} = chat

onMounted(() => {
  initialize()
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
                  <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">{{ chat.formatTimestamp(thread.last_message_at || thread.started_at) }}</p>
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
                {{ chat.formatThreadStatus(selectedThread.status) }}
              </span>
            </div>
            <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ selectedThread.counterpart?.email ?? selectedThread.counterpart?.role ?? '' }}</p>
          </div>
          <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">
            {{ chat.formatTimestamp(selectedThread.last_message_at || selectedThread.started_at) }}
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
                {{ chat.formatTimestamp(message.created_at) }}
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
