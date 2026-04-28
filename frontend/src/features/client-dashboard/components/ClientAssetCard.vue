<script setup>
import { computed, ref } from 'vue'
import { downloadFile } from '../../../services/fileService'

const props = defineProps({
  file: { type: Object, required: true },
  selected: { type: Boolean, default: false },
})

const emit = defineEmits(['request-change'])

const isDownloading = ref(false)

const palette = computed(() => {
  const variants = {
    image: {
      border: 'border-sky-500/35',
      badge: 'border-sky-500/25 bg-sky-500/10 text-sky-200',
      accent: 'text-sky-300',
    },
    video: {
      border: 'border-violet-500/35',
      badge: 'border-violet-500/25 bg-violet-500/10 text-violet-200',
      accent: 'text-violet-300',
    },
    pdf: {
      border: 'border-rose-500/35',
      badge: 'border-rose-500/25 bg-rose-500/10 text-rose-200',
      accent: 'text-rose-300',
    },
  }

  return variants[props.file.category] ?? {
    border: 'border-white/10',
    badge: 'border-white/10 bg-white/5 text-zinc-300',
    accent: 'text-zinc-300',
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
  <article
    class="group overflow-hidden rounded-[1.35rem] border bg-[#111521] shadow-[0_18px_36px_rgba(0,0,0,0.22)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_44px_rgba(0,0,0,0.28)]"
    :class="[
      palette.border,
      selected ? 'ring-2 ring-violet-400 ring-offset-2 ring-offset-[#090b12]' : '',
    ]"
  >
    <div class="flex min-h-[19rem] flex-col">
      <div class="flex items-start justify-between gap-3 px-4 pt-4">
        <span :class="['rounded-md border px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]', palette.badge]">
          {{ file.category ?? 'file' }}
        </span>
        <span
          v-if="selected"
          class="rounded-full border border-violet-400/25 bg-violet-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-violet-100"
        >
          Selected
        </span>
      </div>

      <div class="flex flex-1 items-center justify-center px-4 py-4">
        <div :class="['flex h-20 w-20 items-center justify-center rounded-2xl border bg-white/5', palette.border]">
          <svg
            v-if="file.category === 'image'"
            :class="['h-10 w-10', palette.accent]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path d="M4 16.5 8.5 12l3 3 4.5-4.5L20 14" />
            <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" />
            <circle cx="9" cy="9" r="1.4" />
          </svg>
          <svg
            v-else-if="file.category === 'video'"
            :class="['h-10 w-10', palette.accent]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path d="M5 4h10l4 4v12H5z" />
            <path d="M10 9.5v5l4-2.5-4-2.5Z" />
          </svg>
          <svg
            v-else
            :class="['h-10 w-10', palette.accent]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
          >
            <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
            <path d="M14 3v5h5" />
            <path d="M8 13h8" />
            <path d="M8 17h8" />
          </svg>
        </div>
      </div>

      <div class="border-t border-white/10 px-4 py-4">
        <div class="min-w-0">
          <h4 class="truncate text-sm font-semibold text-white" :title="file.file_name">
            {{ file.file_name }}
          </h4>
          <div class="mt-1 flex items-center justify-between gap-3 text-xs text-zinc-400">
            <span class="truncate">{{ updatedLabel }}</span>
            <span class="shrink-0">{{ sizeLabel }}</span>
          </div>
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
            class="rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm font-semibold text-zinc-300 transition hover:border-white/20 hover:text-white"
            @click="emit('request-change', file)"
          >
            Request
          </button>
        </div>
      </div>
    </div>
  </article>
</template>
