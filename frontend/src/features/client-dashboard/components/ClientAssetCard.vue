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
      border: 'border-sky-200 dark:border-sky-500/35',
      badge: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/25 dark:bg-sky-500/10 dark:text-sky-200',
      accent: 'text-sky-600 dark:text-sky-300',
      iconFrame: 'border-sky-200 bg-sky-50 text-sky-600 dark:border-sky-500/35 dark:bg-sky-500/10 dark:text-sky-200',
    },
    video: {
      border: 'border-violet-200 dark:border-violet-500/35',
      badge: 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/25 dark:bg-violet-500/10 dark:text-violet-200',
      accent: 'text-violet-600 dark:text-violet-300',
      iconFrame: 'border-violet-200 bg-violet-50 text-violet-600 dark:border-violet-500/35 dark:bg-violet-500/10 dark:text-violet-200',
    },
    pdf: {
      border: 'border-rose-200 dark:border-rose-500/35',
      badge: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200',
      accent: 'text-rose-600 dark:text-rose-300',
      iconFrame: 'border-rose-200 bg-rose-50 text-rose-600 dark:border-rose-500/35 dark:bg-rose-500/10 dark:text-rose-200',
    },
  }

  return variants[props.file.category] ?? {
    border: 'border-border/70 dark:border-white/10',
    badge: 'border-border/70 bg-white text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300',
    accent: 'text-muted dark:text-zinc-300',
    iconFrame: 'border-border/70 bg-white text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300',
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
    class="group overflow-hidden rounded-[1.35rem] border bg-[linear-gradient(180deg,rgba(255,255,255,0.95),rgba(247,240,253,0.95))] shadow-[0_18px_36px_rgba(75,61,116,0.08)] transition duration-300 hover:shadow-[0_24px_44px_rgba(75,61,116,0.14)] sm:hover:-translate-y-1 dark:bg-[linear-gradient(180deg,rgba(17,21,33,0.98),rgba(17,21,33,0.98))] dark:shadow-[0_18px_36px_rgba(0,0,0,0.22)] dark:hover:shadow-[0_24px_44px_rgba(0,0,0,0.28)]"
    :class="[
      palette.border,
      selected ? 'ring-2 ring-violet-400 ring-offset-2 ring-offset-[#fbf8ff] dark:ring-offset-[#090b12]' : '',
    ]"
  >
    <div class="flex min-h-[17rem] flex-col sm:min-h-[19rem]">
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
        <div :class="['flex h-20 w-20 items-center justify-center rounded-2xl border', palette.iconFrame]">
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

      <div class="border-t border-border/70 px-4 py-4 dark:border-white/10">
        <div class="min-w-0">
          <h4 class="truncate text-sm font-semibold text-ink dark:text-white" :title="file.file_name">
            {{ file.file_name }}
          </h4>
          <div class="mt-1 flex items-center justify-between gap-3 text-xs text-muted dark:text-zinc-400">
            <span class="truncate">{{ updatedLabel }}</span>
            <span class="shrink-0">{{ sizeLabel }}</span>
          </div>
        </div>

        <div class="mt-4 flex flex-col gap-2 opacity-100 transition duration-200 sm:flex-row sm:translate-y-2 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100">
          <button
            class="pm-gradient-primary w-full rounded-xl px-3 py-2.5 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 sm:flex-1"
            :disabled="isDownloading"
            @click="handleDownload"
          >
            {{ isDownloading ? 'Preparing...' : 'Download' }}
          </button>
          <button
            class="w-full rounded-xl border border-border/70 bg-white/80 px-3 py-2.5 text-sm font-semibold text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white sm:flex-1"
            @click="emit('request-change', file)"
          >
            Request
          </button>
        </div>
      </div>
    </div>
  </article>
</template>
