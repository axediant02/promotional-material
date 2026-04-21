<script setup>
import { ref } from 'vue'
import RequestForm from './RequestForm.vue'

const props = defineProps({
  folder: { type: Object, default: null },
  selectedFile: { type: Object, default: null },
  supportSummary: { type: Object, default: () => ({ label: '', description: '' }) },
})

const emit = defineEmits(['clear-selected-file'])
const successMessage = ref('')

const handleRequestSuccess = (payload) => {
  successMessage.value = payload?.message || 'Request created.'

  if (props.selectedFile) {
    emit('clear-selected-file')
  }
}
</script>

<template>
  <section class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
    <div class="flex items-start gap-4">
      <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.4-8.6a2 2 0 1 1 2.8 2.8L11.8 16H9v-2.8l8.6-8.6Z" />
        </svg>
      </div>
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">Request Feedback</p>
        <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ supportSummary.label }}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ supportSummary.description }}</p>
      </div>
    </div>

    <div class="mt-6 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70">
      <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-slate-400">Assigned Folder</p>
      <p class="mt-2 text-sm font-semibold text-slate-900">{{ folder?.folder_name ?? 'Waiting for folder assignment' }}</p>

      <div v-if="selectedFile" class="mt-4 rounded-xl border border-blue-100 bg-blue-50/80 p-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-blue-600">Selected Asset</p>
            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ selectedFile.file_name }}</p>
          </div>
          <button
            class="rounded-full border border-blue-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-blue-700 transition hover:border-blue-300"
            @click="emit('clear-selected-file')"
          >
            Clear
          </button>
        </div>
      </div>
    </div>

    <div class="mt-6">
      <div
        v-if="successMessage"
        class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
      >
        {{ successMessage }}
      </div>

      <RequestForm
        :file="selectedFile"
        :folder="folder"
        @close="emit('clear-selected-file')"
        @success="handleRequestSuccess"
      />
    </div>

    <div class="mt-5 flex items-center justify-center gap-2">
      <span class="h-2 w-2 rounded-full bg-blue-600"></span>
      <span class="text-[10px] font-semibold uppercase tracking-[0.26em] text-blue-700">Secure delivery active</span>
    </div>
  </section>
</template>
