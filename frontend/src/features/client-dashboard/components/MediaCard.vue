<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../../../stores/auth'

const props = defineProps({
  file: { type: Object, required: true },
})

const emit = defineEmits(['request-change'])
const authStore = useAuthStore()
const showModal = ref(false)

const categoryIcons = {
  image: '🖼️',
  video: '🎬',
  pdf: '📄',
}

const categoryColors = {
  image: 'from-blue-500/10 to-blue-600/5 border-blue-200',
  video: 'from-purple-500/10 to-purple-600/5 border-purple-200',
  pdf: 'from-red-500/10 to-red-600/5 border-red-200',
}

const categoryBadge = {
  image: 'bg-blue-100 text-blue-700',
  video: 'bg-purple-100 text-purple-700',
  pdf: 'bg-red-100 text-red-700',
}

const getFileExtension = (filename) => {
  const parts = filename?.split('.') || []
  return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE'
}

const openRequestModal = () => {
  showModal.value = true
}
</script>

<template>
  <div :class="[
    'group relative overflow-hidden rounded-2xl border bg-gradient-to-br p-4 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1',
    categoryColors[file.category] || 'from-slate-500/10 to-slate-600/5 border-slate-200'
  ]">
    <!-- Category Badge -->
    <div class="absolute right-3 top-3">
      <span :class="[
        'rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide',
        categoryBadge[file.category] || 'bg-slate-100 text-slate-700'
      ]">
        {{ file.category }}
      </span>
    </div>

    <!-- Preview Area -->
    <div class="mb-4 flex h-32 items-center justify-center rounded-xl bg-white/60">
      <!-- Image Preview -->
      <img
        v-if="file.category === 'image'"
        :src="`/api/files/${file.file_id}/preview`"
        :alt="file.file_name"
        class="h-full w-full rounded-xl object-cover"
      />
      
      <!-- Video Preview -->
      <div v-else-if="file.category === 'video'" class="flex flex-col items-center gap-2">
        <span class="text-4xl">{{ categoryIcons.video }}</span>
        <span class="text-sm font-medium text-slate-600">Video File</span>
      </div>
      
      <!-- PDF Preview -->
      <div v-else class="flex flex-col items-center gap-2">
        <span class="text-4xl">{{ categoryIcons.pdf }}</span>
        <span class="text-xs font-semibold text-red-600">{{ getFileExtension(file.file_name) }}</span>
      </div>
    </div>

    <!-- File Info -->
    <div class="space-y-2">
      <h3 class="truncate text-sm font-semibold text-slate-900" :title="file.file_name">
        {{ file.file_name }}
      </h3>
      <p class="truncate text-xs text-slate-500">
        {{ file.folder?.folder_name || 'Unknown folder' }}
      </p>
      <p class="text-xs text-slate-400">
        Uploaded {{ file.updated_at ? new Date(file.updated_at).toLocaleDateString() : 'recently' }}
      </p>
    </div>

    <!-- Actions -->
    <div class="mt-4 flex gap-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
      <a
        :href="`/api/files/${file.file_id}/download`"
        class="flex-1 rounded-lg bg-slate-900 px-3 py-2 text-center text-xs font-medium text-white transition hover:bg-slate-800"
      >
        Download
      </a>
      <button
        @click="openRequestModal"
        class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-center text-xs font-medium text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
      >
        Request Change
      </button>
    </div>
  </div>

  <!-- Request Modal -->
  <Teleport to="body">
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click.self="showModal = false"
    >
      <div class="mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-900">Request Change</h2>
          <button
            @click="showModal = false"
            class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
          >
            ✕
          </button>
        </div>

        <!-- File Reference -->
        <div class="mb-4 rounded-xl bg-slate-50 p-3">
          <p class="text-xs text-slate-500">Requesting change for:</p>
          <p class="truncate font-medium text-slate-900">{{ file.file_name }}</p>
          <p class="text-xs text-slate-400 capitalize">{{ file.category }}</p>
        </div>

        <RequestForm
          :file="file"
          @close="showModal = false"
          @success="showModal = false"
        />
      </div>
    </div>
  </Teleport>
</template>
