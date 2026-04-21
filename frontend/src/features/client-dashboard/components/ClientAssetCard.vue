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
  <article class="group overflow-hidden rounded-[1.75rem] border border-slate-200/70 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.07)] transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-[0_28px_70px_rgba(37,99,235,0.14)]">
    <div :class="['aspect-[16/10] bg-gradient-to-br p-6', palette.frame]">
      <div class="flex h-full flex-col justify-between rounded-[1.4rem] border border-white/70 bg-white/65 p-5 backdrop-blur">
        <div class="flex items-start justify-between gap-3">
          <span :class="['rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em]', palette.badge]">
            {{ file.category ?? 'file' }}
          </span>
          <span class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">{{ sizeLabel }}</span>
        </div>

        <div>
          <p :class="['text-4xl font-semibold tracking-[0.35em]', palette.accent]">{{ palette.icon }}</p>
          <p class="mt-3 text-sm text-slate-500">{{ file.folder?.folder_name ?? 'Assigned folder' }}</p>
        </div>
      </div>
    </div>

    <div class="p-5">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h4 class="truncate text-base font-semibold text-slate-950" :title="file.file_name">
            {{ file.file_name }}
          </h4>
          <p class="mt-1 text-sm text-slate-500">Updated {{ updatedLabel }}</p>
        </div>
      </div>

      <div class="mt-5 flex gap-3">
        <button
          class="flex-1 rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="isDownloading"
          @click="handleDownload"
        >
          {{ isDownloading ? 'Preparing…' : 'Download' }}
        </button>
        <button
          class="flex-1 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700"
          @click="emit('request-change', file)"
        >
          Request Change
        </button>
      </div>
    </div>
  </article>
</template>
