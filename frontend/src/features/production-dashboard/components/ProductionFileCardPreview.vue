<script setup>
const props = defineProps({
  file: {
    type: Object,
    default: null,
  },
  previewUrl: {
    type: String,
    default: '',
  },
  compact: {
    type: Boolean,
    default: false,
  },
})

const categoryFallbackClasses = {
  image: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/25 dark:bg-sky-500/10 dark:text-sky-200',
  video: 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/25 dark:bg-violet-500/10 dark:text-violet-200',
  pdf: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200',
}

const getPreviewShellClasses = () =>
  props.compact
    ? 'h-16 w-16 rounded-xl'
    : 'aspect-[4/3] w-full rounded-[1.15rem]'

const getPreviewInnerClasses = () =>
  props.compact
    ? 'rounded-xl'
    : 'rounded-[1.15rem]'

const getFallbackClasses = () =>
  categoryFallbackClasses[props.file?.category] ??
  'border-border bg-slate-50 text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300'

const isVideo = () => props.file?.category === 'video'
const isImage = () => props.file?.category === 'image'
const isPdf = () => props.file?.category === 'pdf'
</script>

<template>
  <div
    :class="[
      'relative overflow-hidden border',
      getPreviewShellClasses(),
      previewUrl ? 'border-border/70 bg-black/5 dark:border-white/10 dark:bg-black/30' : getFallbackClasses(),
    ]"
  >
    <img
      v-if="previewUrl && isImage()"
      :src="previewUrl"
      :alt="file?.file_name ?? 'Preview image'"
      :class="['h-full w-full object-cover', getPreviewInnerClasses()]"
    />

    <video
      v-else-if="previewUrl && isVideo()"
      :src="previewUrl"
      :class="['h-full w-full object-cover', getPreviewInnerClasses()]"
      autoplay
      muted
      loop
      playsinline
      preload="metadata"
    />

    <iframe
      v-else-if="previewUrl && isPdf() && !compact"
      :src="previewUrl"
      :title="file?.file_name ?? 'Preview document'"
      class="h-full w-full border-0 bg-white"
    />

    <div
      v-else
      class="flex h-full w-full flex-col items-center justify-center gap-2 p-3 text-center"
    >
      <svg
        v-if="file?.category === 'image'"
        class="h-6 w-6 text-current"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
      >
        <path d="M4 16.5 8.5 12l3 3 4.5-4.5L20 14" />
        <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" />
        <circle cx="9" cy="9" r="1.4" />
      </svg>
      <svg
        v-else-if="file?.category === 'video'"
        class="h-6 w-6 text-current"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
      >
        <path d="M5 4h10l4 4v12H5z" />
        <path d="M10 9.5v5l4-2.5-4-2.5Z" />
      </svg>
      <svg
        v-else-if="isPdf()"
        class="h-6 w-6 text-current"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
      >
        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
        <path d="M14 3v5h5" />
        <path d="M8 12h8" />
        <path d="M8 16h5" />
      </svg>
      <svg
        v-else
        class="h-6 w-6 text-current"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true"
      >
        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
        <path d="M14 3v5h5" />
        <path d="M8 13h8" />
        <path d="M8 17h8" />
      </svg>
      <div class="space-y-0.5">
        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-current/80">
          {{ (file?.category ?? 'file').toUpperCase() }}
        </p>
        <p class="text-[10px] leading-4 text-current/70">
          {{ isPdf() ? 'PDF preview unavailable in compact view' : 'Preview unavailable' }}
        </p>
      </div>
    </div>
  </div>
</template>
