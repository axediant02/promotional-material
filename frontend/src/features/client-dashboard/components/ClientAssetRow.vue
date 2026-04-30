<script setup>
import { computed, ref } from 'vue'
import { downloadFile } from '../../../services/fileService'

const props = defineProps({
  file: { type: Object, required: true },
  selected: { type: Boolean, default: false },
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
  <article
    class="flex flex-col gap-4 border-b border-border/70 px-5 py-5 last:border-b-0 dark:border-white/10 md:flex-row md:items-center md:justify-between"
    :class="selected ? 'bg-brand-50/80 dark:bg-white/5' : ''"
  >
    <div class="min-w-0 flex-1">
      <div class="flex flex-wrap items-center gap-3">
        <h4 class="truncate text-base font-semibold text-ink dark:text-white">{{ file.file_name }}</h4>
        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-brand-700 dark:bg-white/10 dark:text-white">
          {{ file.category ?? 'file' }}
        </span>
        <span
          v-if="selected"
          class="rounded-full border border-brand-200 bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-brand-700 dark:border-white/10 dark:bg-white/10 dark:text-white"
        >
          Selected
        </span>
      </div>
      <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-muted dark:text-zinc-300">
        <span>{{ file.folder?.folder_name ?? 'Assigned folder' }}</span>
        <span class="text-brand-300 dark:text-zinc-500">&bull;</span>
        <span>{{ sizeLabel }}</span>
        <span class="text-brand-300 dark:text-zinc-500">&bull;</span>
        <span>Updated {{ updatedLabel }}</span>
      </div>
    </div>

    <div class="flex gap-3">
      <button
        class="pm-gradient-primary rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="isDownloading"
        @click="handleDownload"
      >
        {{ isDownloading ? 'Preparing...' : 'Download' }}
      </button>
      <button
        class="rounded-xl border border-border/70 bg-white/80 px-4 py-2.5 text-sm font-semibold text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
        @click="emit('request-change', file)"
      >
        Request Change
      </button>
    </div>
  </article>
</template>
