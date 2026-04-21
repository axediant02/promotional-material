<script setup>
import { computed, ref } from 'vue'
import { downloadFile } from '../../../services/fileService'

const props = defineProps({
  file: { type: Object, required: true },
})

const emit = defineEmits(['request-change'])

const isDownloading = ref(false)

const sizeLabel = computed(() => {
  const size = Number(props.file.size)
  if (!Number.isFinite(size) || size <= 0) {
    return props.file.category?.toUpperCase() ?? 'FILE'
  }

  return formatBytes(size)
})

const updatedLabel = computed(() => {
  if (!props.file.updated_at) {
    return 'Recently updated'
  }

  return new Date(props.file.updated_at).toLocaleDateString()
})

const handleDownload = async () => {
  if (isDownloading.value) {
    return
  }

  isDownloading.value = true

  try {
    await downloadFile(props.file)
  } catch (error) {
    console.error('Failed to download file:', error)
  } finally {
    isDownloading.value = false
  }
}

function formatBytes(bytes) {
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  const value = bytes / 1024 ** exponent

  return `${value.toFixed(value >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`
}
</script>

<template>
  <article class="flex flex-col gap-4 border-b border-slate-200/70 px-5 py-5 last:border-b-0 md:flex-row md:items-center md:justify-between">
    <div class="min-w-0 flex-1">
      <div class="flex flex-wrap items-center gap-3">
        <h4 class="truncate text-base font-semibold text-slate-950">{{ file.file_name }}</h4>
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-600">
          {{ file.category ?? 'file' }}
        </span>
      </div>
      <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-500">
        <span>{{ file.folder?.folder_name ?? 'Assigned folder' }}</span>
        <span class="text-slate-300">•</span>
        <span>{{ sizeLabel }}</span>
        <span class="text-slate-300">•</span>
        <span>Updated {{ updatedLabel }}</span>
      </div>
    </div>

    <div class="flex gap-3">
      <button
        class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="isDownloading"
        @click="handleDownload"
      >
        {{ isDownloading ? 'Preparing…' : 'Download' }}
      </button>
      <button
        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700"
        @click="emit('request-change', file)"
      >
        Request Change
      </button>
    </div>
  </article>
</template>
