<script setup>
import { computed, ref } from 'vue'
import { downloadFile } from '../../../services/fileService'

const props = defineProps({
  file: { type: Object, required: true },
})

const emit = defineEmits(['request-change'])

const isDownloading = ref(false)

const palette = computed(() => {
  const variants = {
    image: {
      frame: 'from-sky-100 via-blue-50 to-cyan-50',
      badge: 'bg-sky-100 text-sky-700',
      accent: 'text-sky-700',
      icon: 'IMG',
    },
    video: {
      frame: 'from-violet-100 via-fuchsia-50 to-violet-50',
      badge: 'bg-violet-100 text-violet-700',
      accent: 'text-violet-700',
      icon: 'VID',
    },
    pdf: {
      frame: 'from-rose-100 via-red-50 to-orange-50',
      badge: 'bg-rose-100 text-rose-700',
      accent: 'text-rose-700',
      icon: 'PDF',
    },
  }

  return variants[props.file.category] ?? {
    frame: 'from-slate-100 to-slate-50',
    badge: 'bg-slate-100 text-slate-700',
    accent: 'text-slate-700',
    icon: 'FILE',
  }
})

const updatedLabel = computed(() => {
  if (!props.file.updated_at) {
    return 'Recently updated'
  }

  return new Date(props.file.updated_at).toLocaleDateString()
})

const sizeLabel = computed(() => {
  const size = Number(props.file.size)
  if (!Number.isFinite(size) || size <= 0) {
    return props.file.category?.toUpperCase() ?? 'FILE'
  }

  return formatBytes(size)
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
  <article class="group overflow-hidden rounded-[1.4rem] border bg-white/85 shadow-[0_14px_32px_rgba(75,61,116,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_40px_rgba(75,61,116,0.12)] dark:bg-white/5"
    :class="[
      file.category === 'image' ? 'border-blue-200 bg-blue-50/30' : '',
      file.category === 'pdf' ? 'border-rose-200 bg-rose-50/30' : '',
      file.category === 'video' ? 'border-violet-200 bg-violet-50/30' : '',
      !['image', 'pdf', 'video'].includes(file.category) ? 'border-slate-200' : '',
    ]"
  >
    <div :class="['aspect-[16/9] p-4', palette.frame]">
      <div class="flex h-full flex-col justify-between rounded-[1rem] border border-white/80 bg-white/70 p-4 backdrop-blur-sm">
        <div class="flex items-start justify-between gap-3">
          <span :class="['rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', palette.badge]">
            {{ file.category ?? 'file' }}
          </span>
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted dark:text-zinc-400">{{ sizeLabel }}</span>
        </div>

        <div class="flex h-full items-center justify-center">
          <img
            v-if="file.category === 'image'"
            :src="`/api/files/${file.file_id}/preview`"
            :alt="file.file_name"
            class="h-full w-full rounded-2xl object-cover"
          />
          <div v-else class="flex flex-col items-center justify-center gap-2 text-center">
            <p :class="['text-4xl font-semibold tracking-[0.18em]', palette.accent]">{{ palette.icon }}</p>
            <p class="text-sm font-medium text-ink dark:text-white">
              {{ file.category === 'video' ? 'Video File' : file.category === 'pdf' ? 'PDF' : 'File' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="p-4">
      <div class="min-w-0">
        <h4 class="truncate text-sm font-semibold text-ink dark:text-white" :title="file.file_name">
          {{ file.file_name }}
        </h4>
        <p class="mt-2 text-sm text-muted dark:text-zinc-300">{{ file.folder?.folder_name ?? 'Assigned folder' }}</p>
        <p class="mt-2 text-sm text-muted dark:text-zinc-400">Uploaded {{ updatedLabel }}</p>
      </div>

      <div class="mt-4 flex translate-y-2 gap-2 opacity-0 transition duration-200 group-hover:translate-y-0 group-hover:opacity-100">
        <button
          class="pm-gradient-primary flex-1 rounded-xl px-3 py-2.5 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="isDownloading"
          @click="handleDownload"
        >
          {{ isDownloading ? 'Preparing...' : 'Download' }}
        </button>
        <button
          class="rounded-xl border border-border bg-white px-3 py-2.5 text-sm font-medium text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/20"
          @click="emit('request-change', file)"
        >
          Request Change
        </button>
      </div>
    </div>
  </article>
</template>
