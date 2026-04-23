<script setup>
import { ref, watch } from 'vue'
import RequestForm from './RequestForm.vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  folder: { type: Object, default: null },
  selectedFile: { type: Object, default: null },
  supportSummary: { type: Object, default: () => ({ label: '', description: '' }) },
})

const emit = defineEmits(['close', 'clear-selected-file', 'request-created'])
const successMessage = ref('')

watch(
  () => props.open,
  (value) => {
    if (value) {
      successMessage.value = ''
    }
  },
)

const handleRequestSuccess = (payload) => {
  successMessage.value = payload?.message || 'Request created.'
  emit('request-created')
}

const handleClose = () => {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-50 bg-[rgba(19,12,36,0.5)] backdrop-blur-sm"
        @click.self="handleClose"
      >
        <Transition
          enter-active-class="transform transition duration-300 ease-out"
          enter-from-class="translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transform transition duration-200 ease-in"
          leave-from-class="translate-x-0"
          leave-to-class="translate-x-full"
        >
          <aside
            v-if="open"
            class="absolute right-0 top-0 flex h-full w-full max-w-[42rem] flex-col border-l border-border/70 bg-[linear-gradient(180deg,rgba(253,251,255,0.98),rgba(245,239,252,0.98))] shadow-[0_25px_70px_rgba(25,18,48,0.22)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(24,20,36,0.98),rgba(18,14,29,0.98))]"
          >
            <div class="flex items-start justify-between gap-4 border-b border-border/70 px-6 py-5 dark:border-white/10">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-muted dark:text-zinc-400">Request Workspace</p>
                <h2 class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ supportSummary.label }}</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-muted dark:text-zinc-300">{{ supportSummary.description }}</p>
              </div>
              <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full border border-border bg-white/70 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
                @click="handleClose"
              >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M6 6l12 12" />
                  <path d="M18 6 6 18" />
                </svg>
              </button>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
              <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-border/80 bg-white/75 p-4 dark:border-white/10 dark:bg-white/5">
                  <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Step 1</p>
                  <p class="mt-2 text-sm font-semibold text-ink dark:text-white">Choose context</p>
                  <p class="mt-1 text-sm text-muted dark:text-zinc-300">Use the selected file for targeted feedback or continue with a folder-level request.</p>
                </div>
                <div class="rounded-2xl border border-border/80 bg-white/75 p-4 dark:border-white/10 dark:bg-white/5">
                  <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Step 2</p>
                  <p class="mt-2 text-sm font-semibold text-ink dark:text-white">Add clear details</p>
                  <p class="mt-1 text-sm text-muted dark:text-zinc-300">Specific copy, format, and delivery notes reduce back-and-forth later.</p>
                </div>
                <div class="rounded-2xl border border-border/80 bg-white/75 p-4 dark:border-white/10 dark:bg-white/5">
                  <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Step 3</p>
                  <p class="mt-2 text-sm font-semibold text-ink dark:text-white">Submit with confidence</p>
                  <p class="mt-1 text-sm text-muted dark:text-zinc-300">Your request goes into the queue and stays visible in recent history.</p>
                </div>
              </div>

              <div class="rounded-[1.6rem] border border-border/80 bg-white/75 p-5 shadow-[0_12px_28px_rgba(75,61,116,0.06)] dark:border-white/10 dark:bg-white/5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Assigned Folder</p>
                    <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ folder?.folder_name ?? 'Created after first request' }}</p>
                  </div>
                  <button
                    v-if="selectedFile"
                    type="button"
                    class="rounded-full border border-brand-200 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 transition hover:border-brand-300 dark:border-white/10 dark:text-white"
                    @click="emit('clear-selected-file')"
                  >
                    Clear file
                  </button>
                </div>

                <div
                  v-if="selectedFile"
                  class="mt-4 rounded-2xl border border-brand-200 bg-brand-50/80 p-4 dark:border-white/10 dark:bg-white/10"
                >
                  <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-100">Selected Asset</p>
                  <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ selectedFile.file_name }}</p>
                  <p class="mt-1 text-sm text-muted dark:text-zinc-300">This request will be submitted as an update for the selected file.</p>
                </div>
              </div>

              <div
                v-if="successMessage"
                class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50/90 px-4 py-4 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
              >
                <p class="text-[10px] font-semibold uppercase tracking-[0.28em]">Request sent</p>
                <p class="mt-2 text-base font-semibold">Your request is now in the review queue.</p>
                <p class="mt-1 leading-6">{{ successMessage }}</p>
              </div>

              <div class="rounded-[1.6rem] border border-border/80 bg-white/75 p-5 shadow-[0_12px_28px_rgba(75,61,116,0.06)] dark:border-white/10 dark:bg-white/5">
                <RequestForm
                  :file="selectedFile"
                  :folder="folder"
                  @close="emit('clear-selected-file')"
                  @success="handleRequestSuccess"
                />
              </div>
            </div>

            <div class="border-t border-border/70 px-6 py-4 dark:border-white/10">
              <div class="flex items-center justify-center gap-2">
                <span class="h-2 w-2 rounded-full bg-brand-600"></span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.26em] text-brand-700 dark:text-brand-100">Secure delivery active</span>
              </div>
            </div>
          </aside>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
