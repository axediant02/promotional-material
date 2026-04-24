<script setup>
import SkeletonBlock from '../../../components/shared/SkeletonBlock.vue'
import MediaCard from './MediaCard.vue'

defineProps({
  files: { type: Array, required: true },
  loading: { type: Boolean, default: false },
})

const categoryFilters = [
  { value: 'all', label: 'All Files' },
  { value: 'image', label: 'Images' },
  { value: 'video', label: 'Videos' },
  { value: 'pdf', label: 'PDFs' },
]
</script>

<template>
  <section class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold text-slate-950">Media Files</h2>
        <p class="text-sm text-slate-500">Browse and request changes for your files by category.</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div
        v-for="i in 8"
        :key="i"
        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
      >
        <SkeletonBlock width="w-full" height="h-32" rounded="rounded-xl" tone="bg-slate-200" class-name="mb-4" />
        <div class="space-y-2">
          <SkeletonBlock width="w-3/4" height="h-4" tone="bg-slate-200" />
          <SkeletonBlock width="w-1/2" height="h-3" tone="bg-slate-200" />
          <SkeletonBlock width="w-1/4" height="h-3" tone="bg-slate-200" />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!files || files.length === 0"
      class="flex flex-col items-center justify-center py-16 text-center"
    >
      <div class="mb-4 text-6xl">📁</div>
      <h3 class="text-lg font-medium text-slate-900">No files yet</h3>
      <p class="mt-2 max-w-sm text-sm text-slate-500">
        Your assigned folder is empty. Files uploaded by production will appear here.
      </p>
    </div>

    <!-- Files Grid -->
    <div
      v-else
      class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
      <MediaCard
        v-for="file in files"
        :key="file.file_id"
        :file="file"
      />
    </div>
  </section>
</template>
