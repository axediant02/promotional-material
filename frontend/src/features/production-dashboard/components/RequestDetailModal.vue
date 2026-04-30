<script setup>
import { onBeforeUnmount, onMounted } from 'vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  request: {
    type: Object,
    default: null,
  },
  updatingRequestId: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'update-status', 'view-folder'])

const handleVieFolder = () => {
  if(props.request?.folderId){
    emit('view-folder', props.request.folderId)
  }
}

const handleKeydown = (event) => {
  if (event.key === 'Escape' && props.open) {
    emit('close')
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeydown)
  }
})

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeydown)
  }
})
</script>

<template>
  <div
    v-if="open && request"
    class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm sm:items-center"
    @click.self="emit('close')"
  >
    <div class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-border/90 bg-[linear-gradient(180deg,rgba(253,249,255,0.98),rgba(246,237,252,0.96))] shadow-[0_28px_80px_rgba(34,18,68,0.18)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgb(45,36,69)_0%,rgb(26,22,37)_100%)] dark:shadow-[0_28px_80px_rgba(0,0,0,0.45)]">
      <div class="border-b border-border/80 px-6 py-5 dark:border-white/10">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full border border-brand-300/40 bg-brand-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 dark:border-amber-300/30 dark:bg-amber-400/10 dark:text-amber-100">
                {{ request.statusLabel }}
              </span>
              <span
                v-if="request.isHighPriority"
                class="rounded-full border border-orange-300/50 bg-orange-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-orange-900 dark:border-orange-300/30 dark:bg-orange-400/10 dark:text-orange-100"
              >
                High Priority
              </span>
              <span class="text-[10px] uppercase tracking-[0.28em] text-muted dark:text-zinc-400">{{ request.reference }}</span>
            </div>
            <h3 class="mt-3 text-2xl font-semibold text-ink dark:text-white">{{ request.title }}</h3>
            <p class="mt-2 text-sm text-muted dark:text-zinc-300">{{ request.clientName }} / {{ request.workspace }}</p>
          </div>

          <button
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border/80 bg-white/70 text-muted transition hover:border-brand-400 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
            type="button"
            @click="emit('close')"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
          </button>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-4">
          <div class="rounded-2xl border border-border bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Due</p>
            <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ request.dueLabel }}</p>
          </div>
          <div class="rounded-2xl border border-border bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Type</p>
            <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ request.requestType }}</p>
          </div>
          <div class="rounded-2xl border border-border bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Files</p>
            <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ request.fileCount }} in workspace</p>
          </div>
          <div class="rounded-2xl border border-border bg-white/70 px-4 py-3 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Status</p>
            <select
              class="pm-input mt-2 w-full rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
              :disabled="updatingRequestId === request.id"
              :value="request.status"
              @change="emit('update-status', request.id, $event.target.value)"
            >
              <option class="text-slate-900" value="pending">Pending</option>
              <option class="text-slate-900" value="in_progress">In Progress</option>
              <option class="text-slate-900" value="done">Done</option>
            </select>
          </div>
        </div>
      </div>

      <div class="grid gap-6 px-6 py-6 lg:grid-cols-[minmax(0,1.2fr)_18rem]">
        <section class="rounded-[1.6rem] border border-border bg-white/70 p-5 dark:border-white/10 dark:bg-white/[0.04]">
          <p class="text-[10px] uppercase tracking-[0.3em] text-brand-700 dark:text-brand-100">Task details</p>
          <p class="mt-4 text-sm leading-7 text-muted dark:text-zinc-300">
            {{ request.description || 'No additional description was provided for this request.' }}
          </p>
        </section>

        <aside class="space-y-4">
          <section class="rounded-[1.6rem] border border-border bg-white/70 p-4 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.28em] text-brand-700 dark:text-brand-100">Workspace summary</p>
            <div class="mt-4 space-y-3 text-sm text-muted dark:text-zinc-300">
              <p>{{ request.clientName }}</p>
              <p>{{ request.workspace }}</p>
              <p>{{ request.fileCount }} files already in scope</p>
            </div>
          </section>

          <section class="rounded-[1.6rem] border border-border bg-white/70 p-4 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.28em] text-brand-700 dark:text-brand-100">Files preview</p>
            <div class="mt-4 flex flex-wrap gap-2">
              <span
                v-for="fileName in request.fileNames"
                :key="fileName"
                class="rounded-full border border-border bg-white px-3 py-1 text-[11px] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
              >
                {{ fileName }}
              </span>
              <p v-if="!request.fileNames?.length" class="text-sm text-muted dark:text-zinc-500">No related files surfaced yet.</p>
            </div>
          </section>
        </aside>
      </div>
    </div>
  </div>
</template>
