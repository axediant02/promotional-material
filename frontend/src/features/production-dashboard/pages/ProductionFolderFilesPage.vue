<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProductionFolderDetailPanel from '../components/ProductionFolderDetailPanel.vue'
import { useProductionWorkspace } from '../productionWorkspace'

const route = useRoute()
const router = useRouter()
const workspace = useProductionWorkspace()

const fileGrid = computed(() => workspace.folderBrowserMode.value === 'grid')
const selectedFolder = computed(() => workspace.selectedFolder.value)
const folderFiles = computed(() => workspace.selectedFolderFiles.value)
const selectedFolderRequestCount = computed(() => workspace.selectedFolderRequests.value.length)
const showUploadPanel = ref(false)
const selectedFiles = ref([])
const uploadError = ref('')
const fileInputRef = ref(null)

const getFileCardPalette = (category) => {
  const variants = {
    image: {
      border: 'border-sky-200 dark:border-sky-500/35',
      badge: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/25 dark:bg-sky-500/10 dark:text-sky-200',
      accent: 'text-sky-600 dark:text-sky-300',
      frame: 'border-sky-200/80 bg-sky-50/80 dark:border-sky-500/35 dark:bg-white/5',
    },
    video: {
      border: 'border-violet-200 dark:border-violet-500/35',
      badge: 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/25 dark:bg-violet-500/10 dark:text-violet-200',
      accent: 'text-violet-600 dark:text-violet-300',
      frame: 'border-violet-200/80 bg-violet-50/80 dark:border-violet-500/35 dark:bg-white/5',
    },
    pdf: {
      border: 'border-rose-200 dark:border-rose-500/35',
      badge: 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/25 dark:bg-rose-500/10 dark:text-rose-200',
      accent: 'text-rose-600 dark:text-rose-300',
      frame: 'border-rose-200/80 bg-rose-50/80 dark:border-rose-500/35 dark:bg-white/5',
    },
  }

  return variants[category] ?? {
    border: 'border-border dark:border-white/10',
    badge: 'border-border bg-slate-50 text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300',
    accent: 'text-slate-500 dark:text-zinc-300',
    frame: 'border-border bg-slate-50 dark:border-white/10 dark:bg-white/5',
  }
}

const formatBytes = (bytes) => {
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const value = Number(bytes)

  if (!Number.isFinite(value) || value <= 0) {
    return ''
  }

  const exponent = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1)
  const normalized = value / 1024 ** exponent

  return `${normalized.toFixed(normalized >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`
}

const getFileSizeLabel = (file) => formatBytes(file.size) || file.category?.toUpperCase() || 'FILE'

const ensureValidFolder = () => {
  if (workspace.loading.value) {
    return
  }

  if (!selectedFolder.value) {
    router.replace({ name: 'production-folder-index', query: route.query })
  }
}

const openUploadPanel = () => {
  showUploadPanel.value = true
}

const isDragging = ref(false)

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files ?? [])
  if (files.length) {
    selectedFiles.value = [...selectedFiles.value, ...files]
    uploadError.value = ''
  }
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const handleFileDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files ?? [])
  if (files.length) {
    selectedFiles.value = [...selectedFiles.value, ...files]
    uploadError.value = ''
  }
}

const removeSelectedFile = (index) => {
  selectedFiles.value = selectedFiles.value.filter((_, i) => i !== index)
}

const submitUpload = async () => {
  if (!selectedFolder.value?.id || !selectedFiles.value.length) {
    return
  }

  uploadError.value = ''

  for (const file of selectedFiles.value) {
    try {
      await workspace.handleUploadFile(file, selectedFolder.value.id)
    } catch {
      // error is surfaced by the dashboard-level error state
    }
  }

  selectedFiles.value = []
  showUploadPanel.value = false
}

const closeUploadPanel = () => {
  selectedFiles.value = []
  uploadError.value = ''
  showUploadPanel.value = false
}

watch(selectedFolder, ensureValidFolder)
watch(() => workspace.loading.value, ensureValidFolder)

onMounted(() => {
  ensureValidFolder()
})
</script>

<template>
  <section class="space-y-5">
    <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.26em] text-muted dark:text-zinc-400">
      <button
        class="rounded-full bg-white/60 px-3 py-1.5 transition hover:bg-brand-50 hover:text-brand-700 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:text-white"
        @click="workspace.goToFolderIndex"
      >
        Production
      </button>
      <span>/</span>
      <button
        class="rounded-full bg-brand-50 px-3 py-1.5 text-brand-700 transition hover:bg-brand-100 dark:bg-white/10 dark:text-white dark:hover:bg-white/15"
        @click="workspace.goToFolderIndex"
      >
        Client folders
      </button>
      <span>/</span>
      <span class="rounded-full bg-brand-100 px-3 py-1.5 text-brand-700 dark:bg-white/15 dark:text-white">
        {{ selectedFolder?.workspace ?? 'Folder' }}
      </span>
    </nav>

    <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.4fr)_24rem]">
      <div class="overflow-hidden rounded-[2rem] border border-border/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.96),rgba(248,242,252,0.94))] shadow-[var(--shadow-md)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(20,24,36,0.98),rgba(12,15,24,0.98))]">
        <div class="border-b border-border/80 px-5 py-5 dark:border-white/10">
          <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-[0.36em] text-brand-700 dark:text-brand-100">Folder contents</p>
                <h2 class="mt-2 truncate text-2xl font-semibold text-ink dark:text-white">{{ selectedFolder?.workspace ?? 'Assigned folder' }}</h2>
                <p class="mt-2 text-sm text-muted dark:text-zinc-400">
                  {{ selectedFolder?.clientName ?? 'Assigned client' }} / {{ folderFiles.length }} files visible in this workspace.
                </p>
              </div>

              <div class="inline-flex self-start rounded-xl border border-border bg-white/80 p-1 shadow-[var(--shadow-xs)] dark:border-white/10 dark:bg-black/20">
                <button
                  :class="[
                    'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                    fileGrid
                      ? 'bg-brand-700 text-white shadow-[var(--shadow-xs)] dark:bg-white dark:text-slate-900'
                      : 'text-muted hover:text-brand-700 dark:text-zinc-300 dark:hover:text-white',
                  ]"
                  @click="workspace.setFolderBrowserMode('grid')"
                >
                  Grid
                </button>
                <button
                  :class="[
                    'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                    !fileGrid
                      ? 'bg-brand-700 text-white shadow-[var(--shadow-xs)] dark:bg-white dark:text-slate-900'
                      : 'text-muted hover:text-brand-700 dark:text-zinc-300 dark:hover:text-white',
                  ]"
                  @click="workspace.setFolderBrowserMode('list')"
                >
                  List
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="px-5 py-5">
          <div v-if="fileGrid && folderFiles.length" class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
            <article
              v-for="file in folderFiles"
              :key="file.file_id"
              :class="[
                'group overflow-hidden rounded-[1.35rem] border bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(249,244,252,0.94))] shadow-[var(--shadow-sm)] transition duration-300 hover:-translate-y-1 hover:shadow-[var(--shadow-md)] dark:bg-[#111521]',
                getFileCardPalette(file.category).border,
              ]"
            >
              <div class="flex min-h-[19rem] flex-col">
                <div class="flex items-start justify-between gap-3 px-4 pt-4">
                  <span :class="['rounded-md border px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.24em]', getFileCardPalette(file.category).badge]">
                    {{ file.category ?? 'file' }}
                  </span>
                  <span class="text-[11px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">{{ file.shortId }}</span>
                </div>

                <div class="flex flex-1 items-center justify-center px-4 py-4">
                  <div :class="['flex h-20 w-20 items-center justify-center rounded-2xl border', getFileCardPalette(file.category).frame]">
                    <svg
                      v-if="file.category === 'image'"
                      :class="['h-10 w-10', getFileCardPalette(file.category).accent]"
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
                      v-else-if="file.category === 'video'"
                      :class="['h-10 w-10', getFileCardPalette(file.category).accent]"
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
                      v-else
                      :class="['h-10 w-10', getFileCardPalette(file.category).accent]"
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
                  </div>
                </div>

                <div class="border-t border-border/70 px-4 py-4 dark:border-white/10">
                  <div class="min-w-0">
                    <h4 class="truncate text-sm font-semibold text-ink dark:text-white" :title="file.file_name">
                      {{ file.file_name }}
                    </h4>
                    <div class="mt-1 flex items-center justify-between gap-3 text-xs text-muted dark:text-zinc-400">
                      <span class="truncate">{{ file.updatedLabel }}</span>
                      <span class="shrink-0">{{ getFileSizeLabel(file) }}</span>
                    </div>
                  </div>

                  <div class="mt-4 flex translate-y-2 gap-2 opacity-0 transition duration-200 group-hover:translate-y-0 group-hover:opacity-100">
                    <button
                      class="pm-gradient-primary flex-1 rounded-xl px-3 py-2.5 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="workspace.downloadingFileId.value === file.file_id"
                      @click="workspace.handleDownloadFile(file)"
                    >
                      {{ workspace.downloadingFileId.value === file.file_id ? 'Preparing...' : 'Download' }}
                    </button>
                    <button
                      class="rounded-xl border border-border bg-white/80 px-3 py-2.5 text-sm font-semibold text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
                      @click="openUploadPanel"
                    >
                      Upload
                    </button>
                  </div>
                </div>
              </div>
            </article>

            <button
              type="button"
              class="flex min-h-[20rem] w-full flex-col items-center justify-center rounded-[1.2rem] border border-dashed border-border-strong bg-[linear-gradient(180deg,rgba(255,255,255,0.92),rgba(247,241,252,0.92))] px-5 py-5 text-center transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-brand-50/70 dark:border-white/18 dark:bg-[#10131c] dark:hover:border-white/30 dark:hover:bg-[#131827]"
              @click="openUploadPanel"
            >
              <span class="flex h-10 w-10 items-center justify-center rounded-full border border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/35 dark:bg-violet-500/10 dark:text-violet-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                  <path d="M12 5v14" />
                  <path d="M5 12h14" />
                </svg>
              </span>
              <h4 class="mt-4 text-base font-semibold text-ink dark:text-white">Add new files</h4>
              <p class="mt-1 text-sm text-muted dark:text-zinc-400">Upload assets directly to this client folder</p>
            </button>
          </div>

          <div v-else-if="!fileGrid && folderFiles.length" class="overflow-hidden rounded-[1.75rem] border border-border/80 bg-white/85 dark:border-white/10 dark:bg-white/5">
            <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1fr)_8rem_9rem] gap-4 border-b border-border/70 bg-slate-50/90 px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-400 md:grid">
              <span>Name</span>
              <span>Updated</span>
              <span>Type</span>
              <span class="text-right">Action</span>
            </div>

            <article
              v-for="file in folderFiles"
              :key="file.file_id"
              class="border-b border-border/70 px-5 py-4 last:border-b-0 dark:border-white/8"
            >
              <div class="grid gap-3 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_8rem_9rem] md:items-center md:gap-4">
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ file.file_name }}</p>
                  <p class="mt-1 text-xs text-muted dark:text-zinc-500">{{ file.shortId }} / {{ getFileSizeLabel(file) }}</p>
                </div>
                <p class="truncate text-sm text-muted dark:text-zinc-300">{{ file.updatedLabel }}</p>
                <div>
                  <span
                    :class="[
                      'inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]',
                      getFileCardPalette(file.category).badge,
                    ]"
                  >
                    {{ file.category ?? 'asset' }}
                  </span>
                </div>
                <div class="md:text-right">
                  <div class="flex justify-end gap-3">
                    <button
                      class="pm-gradient-primary rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="workspace.downloadingFileId.value === file.file_id"
                      @click="workspace.handleDownloadFile(file)"
                    >
                      {{ workspace.downloadingFileId.value === file.file_id ? 'Preparing...' : 'Download' }}
                    </button>
                    <button
                      class="rounded-xl border border-border bg-white/80 px-4 py-2.5 text-sm font-semibold text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
                      @click="openUploadPanel"
                    >
                      Update
                    </button>
                  </div>
                </div>
              </div>
            </article>

            <div class="border-t border-border/70 p-4 dark:border-white/10">
              <button
                type="button"
                class="flex min-h-[14rem] w-full flex-col items-center justify-center rounded-[1.2rem] border border-dashed border-border-strong bg-[linear-gradient(180deg,rgba(255,255,255,0.92),rgba(247,241,252,0.92))] px-5 py-5 text-center transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-brand-50/70 dark:border-white/18 dark:bg-[#10131c] dark:hover:border-white/30 dark:hover:bg-[#131827]"
                @click="openUploadPanel"
              >
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/35 dark:bg-violet-500/10 dark:text-violet-200">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                  </svg>
                </span>
                <h4 class="mt-4 text-base font-semibold text-ink dark:text-white">Add new files</h4>
                <p class="mt-1 text-sm text-muted dark:text-zinc-400">Upload assets directly to this client folder</p>
              </button>
            </div>
          </div>

          <article
            v-else
            class="rounded-[1.75rem] border border-dashed border-border-strong bg-[linear-gradient(180deg,rgba(255,255,255,0.94),rgba(247,241,252,0.94))] px-6 py-16 text-center dark:border-white/15 dark:bg-[#10131c]"
          >
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/25 dark:bg-violet-500/10 dark:text-violet-200">
              <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h4.38a2.5 2.5 0 0 1 1.77.73l.62.62A2.5 2.5 0 0 0 14.04 7h4.46A2.5 2.5 0 0 1 21 9.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-11Z" />
              </svg>
            </div>
            <h3 class="mt-5 text-xl font-semibold text-ink dark:text-white">No files are currently available in this assigned folder.</h3>
            <p class="mt-2 text-sm text-muted dark:text-zinc-400">Upload the first batch of assets so this workspace can start delivery.</p>
            <div class="mt-6 flex justify-center">
              <button
                type="button"
                class="flex min-h-[14rem] w-full max-w-md flex-col items-center justify-center rounded-[1.2rem] border border-dashed border-border-strong bg-white/85 px-5 py-5 text-center transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-brand-50/70 dark:border-white/18 dark:bg-[#131827] dark:hover:border-white/30 dark:hover:bg-[#161c2d]"
                @click="openUploadPanel"
              >
                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/35 dark:bg-violet-500/10 dark:text-violet-200">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                  </svg>
                </span>
                <h4 class="mt-4 text-base font-semibold text-ink dark:text-white">Add new files</h4>
                <p class="mt-1 text-sm text-muted dark:text-zinc-400">Upload assets directly to this client folder</p>
              </button>
            </div>
          </article>
        </div>
      </div>

      <ProductionFolderDetailPanel
        :selected-folder="selectedFolder"
        :folder-files="folderFiles"
        :folder-requests="workspace.selectedFolderRequests.value"
        :downloading-file-id="workspace.downloadingFileId.value"
        :updating-request-id="workspace.updatingRequestId.value"
        :current-user-id="workspace.currentUser.value?.user_id ?? ''"
        :show-files-section="false"
        @download-file="workspace.handleDownloadFile"
        @update-request-status="workspace.updateRequestStatus"
      />
    </section>

    <div
      v-if="showUploadPanel"
      class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm sm:items-center"
      @click.self="closeUploadPanel"
    >
      <div class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-border/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.98),rgba(246,237,252,0.96))] shadow-[0_28px_80px_rgba(34,18,68,0.18)] dark:border-white/10 dark:bg-[linear-gradient(180deg,rgb(45,36,69)_0%,rgb(26,22,37)_100%)] dark:shadow-[0_28px_80px_rgba(0,0,0,0.45)]">
        <div class="border-b border-border/80 px-6 py-5 dark:border-white/10">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-[10px] uppercase tracking-[0.34em] text-brand-700 dark:text-brand-100">Upload files</p>
              <h3 class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ selectedFolder?.workspace ?? 'Assigned folder' }}</h3>
              <p class="mt-2 text-sm text-muted dark:text-zinc-400">{{ selectedFolder?.clientName ?? 'Assigned client' }}</p>
            </div>

            <button
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-white/70 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
              @click="closeUploadPanel"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
              </svg>
            </button>
          </div>

          <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-border bg-white/80 px-4 py-3 dark:border-white/10 dark:bg-black/20">
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Destination</p>
              <p class="mt-2 truncate text-sm font-semibold text-ink dark:text-white">{{ selectedFolder?.workspace ?? 'Assigned folder' }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-white/80 px-4 py-3 dark:border-white/10 dark:bg-black/20">
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Client</p>
              <p class="mt-2 truncate text-sm font-semibold text-ink dark:text-white">{{ selectedFolder?.clientName ?? 'Assigned client' }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-white/80 px-4 py-3 dark:border-white/10 dark:bg-black/20">
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">Selected</p>
              <p class="mt-2 text-sm font-semibold text-ink dark:text-white">{{ selectedFiles.length }} file{{ selectedFiles.length !== 1 ? 's' : '' }}</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[minmax(0,1.15fr)_18rem]">
          <div class="space-y-6">
            <section
              :class="[
                'rounded-[1.6rem] border border-dashed px-6 py-10 text-center transition cursor-pointer',
                isDragging
                  ? 'border-brand-300 bg-brand-50/80 dark:border-brand-200 dark:bg-brand-200/15'
                  : 'border-brand-200 bg-brand-50/70 hover:border-brand-300 dark:border-brand-200/40 dark:bg-brand-200/10 dark:hover:border-brand-200/60',
              ]"
              @click="fileInputRef?.click()"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleFileDrop"
            >
              <input
                ref="fileInputRef"
                type="file"
                multiple
                accept="image/jpeg,image/png,image/webp,image/heic,image/heif,video/mp4,video/quicktime,application/pdf"
                class="hidden"
                @change="handleFileSelect"
              />
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-brand-200 bg-white/80 text-brand-700 dark:border-brand-100/20 dark:bg-brand-100/10 dark:text-brand-100">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V7m0 0l-3.5 3.5M12 7l3.5 3.5M5 17.5A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5" />
                </svg>
              </div>
              <h4 class="mt-4 text-lg font-semibold text-ink dark:text-white">Select client assets</h4>
              <p class="mt-2 text-sm text-muted dark:text-zinc-400">
                {{ isDragging ? 'Drop files to add them' : 'Drag files here or click to browse' }}
              </p>
            </section>

            <p v-if="uploadError" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
              {{ uploadError }}
            </p>
          </div>

          <aside class="rounded-[1.6rem] border border-border bg-white/80 p-4 dark:border-white/10 dark:bg-black/20">
            <p class="text-[10px] uppercase tracking-[0.28em] text-brand-700 dark:text-[#9cdcfe]">Upload summary</p>
            <div class="mt-4 space-y-4">
              <div class="rounded-2xl border border-border bg-slate-50/80 px-4 py-4 dark:border-white/10 dark:bg-white/[0.03]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Selected files</p>
                <ul v-if="selectedFiles.length" class="mt-2 space-y-1">
                  <li
                    v-for="(file, idx) in selectedFiles"
                    :key="idx"
                    class="flex items-start justify-between gap-2 text-sm text-muted dark:text-zinc-300"
                  >
                    <span class="truncate">{{ file.name }}</span>
                    <button
                      class="shrink-0 text-muted hover:text-red-500 transition dark:text-zinc-500 dark:hover:text-red-400"
                      @click="removeSelectedFile(idx)"
                    >
                      <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                      </svg>
                    </button>
                  </li>
                </ul>
                <p v-else class="mt-2 text-sm text-muted dark:text-zinc-500">No files selected yet.</p>
              </div>

              <div class="rounded-2xl border border-border bg-slate-50/80 px-4 py-4 dark:border-white/10 dark:bg-white/[0.03]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Visibility</p>
                <p class="mt-2 text-sm text-muted dark:text-zinc-300">Files will be prepared for this client folder only.</p>
              </div>

              <div class="rounded-2xl border border-border bg-slate-50/80 px-4 py-4 dark:border-white/10 dark:bg-white/[0.03]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Status</p>
                <div class="mt-2 flex items-center justify-between gap-3">
                  <p class="text-sm text-muted dark:text-zinc-300">
                    {{ selectedFiles.length ? 'Ready to upload' : 'Waiting for file selection' }}
                  </p>
                  <span
                    :class="[
                      'rounded-full border px-3 py-1 text-[10px] uppercase tracking-[0.22em]',
                      selectedFiles.length
                        ? 'border-brand-300 bg-brand-50 text-brand-700 dark:border-brand-400/30 dark:bg-brand-400/10 dark:text-brand-300'
                        : 'border-border bg-white text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-400',
                    ]"
                  >
                    {{ selectedFiles.length ? 'Ready' : 'Pending' }}
                  </span>
                </div>
              </div>
            </div>
          </aside>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-border/80 px-6 py-5 dark:border-white/10">
          <button
            class="rounded-xl border border-border bg-white/80 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white"
            @click="closeUploadPanel"
          >
            Cancel
          </button>
          <button
            class="inline-flex items-center gap-2 rounded-xl border border-brand-200/40 bg-[linear-gradient(135deg,rgba(214,191,239,0.34),rgba(95,80,155,0.24))] px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-white transition hover:border-brand-200/60 hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!selectedFiles.length || workspace.uploadingFileId.value === selectedFolder?.id"
            @click="submitUpload"
          >
            <svg v-if="workspace.uploadingFileId.value === selectedFolder?.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V7m0 0l-3.5 3.5M12 7l3.5 3.5M5 17.5A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5" />
            </svg>
            <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V7m0 0l-3.5 3.5M12 7l3.5 3.5M5 17.5A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5" />
            </svg>
            {{ workspace.uploadingFileId.value === selectedFolder?.id ? 'Uploading...' : 'Upload files' }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

