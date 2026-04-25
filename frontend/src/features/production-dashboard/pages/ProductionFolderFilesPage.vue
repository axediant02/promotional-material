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

const closeUploadPanel = () => {
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
    <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.26em] text-zinc-500">
      <button
        class="rounded-full border border-border bg-white/60 px-3 py-1.5 transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:text-white"
        @click="workspace.goToFolderIndex"
      >
        Production
      </button>
      <span>/</span>
      <button
        class="rounded-full border border-border bg-white/60 px-3 py-1.5 transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20 dark:hover:text-white"
        @click="workspace.goToFolderIndex"
      >
        Client folders
      </button>
      <span>/</span>
      <span class="rounded-full border border-[#9cdcfe]/30 bg-[#9cdcfe]/10 px-3 py-1.5 text-[#6ecff6] dark:text-[#b9e7ff]">
        {{ selectedFolder?.workspace ?? 'Folder' }}
      </span>
    </nav>

    <section class="grid gap-6 2xl:grid-cols-[minmax(0,1.4fr)_24rem]">
      <div class="overflow-hidden rounded-[2rem] border border-slate-900/70 bg-[linear-gradient(180deg,#252526_0%,#1e1e1e_100%)] shadow-[0_24px_80px_rgba(15,23,42,0.3)]">
        <div class="border-b border-white/10 px-5 py-5">
          <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-[0.36em] text-[#9cdcfe]">Folder contents</p>
                <h2 class="mt-2 truncate text-2xl font-semibold text-white">{{ selectedFolder?.workspace ?? 'Assigned folder' }}</h2>
                <p class="mt-2 text-sm text-zinc-400">
                  {{ selectedFolder?.clientName ?? 'Assigned client' }} / {{ folderFiles.length }} files visible in this workspace.
                </p>
              </div>

              <div class="inline-flex rounded-xl border border-white/10 bg-black/20 p-1 self-start">
                <button
                  :class="[
                    'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                    fileGrid ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]' : 'text-zinc-300 hover:text-white',
                  ]"
                  @click="workspace.setFolderBrowserMode('grid')"
                >
                  Grid
                </button>
                <button
                  :class="[
                    'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                    !fileGrid ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]' : 'text-zinc-300 hover:text-white',
                  ]"
                  @click="workspace.setFolderBrowserMode('list')"
                >
                  List
                </button>
              </div>
            </div>

            <section class="rounded-[1.6rem] border border-[#9cdcfe]/18 bg-[linear-gradient(135deg,rgba(156,220,254,0.14),rgba(255,255,255,0.04))] p-4 sm:p-5">
              <div class="flex flex-col gap-6">
                <div class="flex min-w-0 items-start gap-4">
                  <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border border-[#9cdcfe]/18 bg-[#9cdcfe]/10 text-[#d7f0ff]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V7m0 0l-3.5 3.5M12 7l3.5 3.5M5 17.5A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5" />
                    </svg>
                  </div>

                  <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-[0.34em] text-[#9cdcfe]">Production action</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Add client delivery files</h3>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-300">
                      Prepare uploads directly inside this folder so new assets stay tied to the selected client workspace.
                    </p>
                  </div>
                </div>

                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                  <div class="flex min-w-0 flex-col gap-2">
                    <p class="text-[10px] uppercase tracking-[0.32em] text-zinc-500">Primary action</p>
                    <button
                      class="inline-flex min-h-14 w-full items-center justify-center gap-3 rounded-xl border border-[#9cdcfe]/40 bg-[linear-gradient(135deg,rgba(156,220,254,0.24),rgba(156,220,254,0.12))] px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-white shadow-[0_18px_38px_rgba(34,79,112,0.26)] transition hover:border-[#cceeff]/65 hover:bg-[linear-gradient(135deg,rgba(156,220,254,0.32),rgba(156,220,254,0.16))] hover:shadow-[0_22px_44px_rgba(34,79,112,0.3)] xl:w-auto xl:min-w-[18rem]"
                      @click="openUploadPanel"
                    >
                      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-[#e6f6ff]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                      </span>
                      <span class="min-w-0">
                        <span class="block">Upload To Folder</span>
                        <span class="mt-1 block text-[10px] font-medium normal-case tracking-normal text-[#d7f0ff]/78">
                          Add new client assets to this workspace
                        </span>
                      </span>
                    </button>
                  </div>

                  <div class="grid grid-cols-3 gap-2 xl:min-w-[16rem]">
                    <div class="rounded-2xl border border-white/8 bg-black/15 px-3 py-3 text-center">
                      <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Files</p>
                      <p class="mt-1 text-base font-semibold text-white">{{ folderFiles.length }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/8 bg-black/15 px-3 py-3 text-center">
                      <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Requests</p>
                      <p class="mt-1 text-base font-semibold text-white">{{ selectedFolderRequestCount }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/8 bg-black/15 px-3 py-3 text-center">
                      <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Status</p>
                      <p class="mt-1 truncate text-sm font-semibold text-white">{{ selectedFolder?.statusLabel ?? 'Ready' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>

        <div class="px-5 py-5">
          <div v-if="fileGrid" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            <article
              v-for="file in folderFiles"
              :key="file.file_id"
              class="rounded-[1.5rem] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0.03))] p-5 transition hover:border-[#9cdcfe]/35 hover:bg-[linear-gradient(180deg,rgba(255,255,255,0.1),rgba(255,255,255,0.04))]"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span
                      :class="[
                        'rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]',
                        workspace.categoryToneLookup[file.category] ?? 'border-white/10 bg-white/5 text-white/75',
                      ]"
                    >
                      {{ file.category ?? 'asset' }}
                    </span>
                    <span class="text-[11px] uppercase tracking-[0.22em] text-zinc-500">{{ file.shortId }}</span>
                  </div>
                  <h3 class="mt-3 truncate text-lg font-semibold text-white">{{ file.file_name }}</h3>
                  <p class="mt-2 text-sm text-zinc-400">{{ file.uploaderName }}</p>
                </div>

                <button
                  class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white transition hover:border-[#9cdcfe]/35 hover:bg-[#9cdcfe]/10 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="workspace.downloadingFileId.value === file.file_id"
                  @click="workspace.handleDownloadFile(file)"
                >
                  {{ workspace.downloadingFileId.value === file.file_id ? 'Downloading...' : 'Download' }}
                </button>
              </div>

              <div class="mt-4 space-y-2 text-sm text-zinc-400">
                <p>{{ file.updatedLabel }}</p>
                <p>{{ file.folderName }}</p>
              </div>
            </article>
          </div>

          <div v-else class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-black/20">
            <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1fr)_8rem_9rem] gap-4 border-b border-white/10 bg-white/[0.04] px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400 md:grid">
              <span>Name</span>
              <span>Updated</span>
              <span>Type</span>
              <span class="text-right">Action</span>
            </div>

            <article
              v-for="file in folderFiles"
              :key="file.file_id"
              class="border-b border-white/8 px-5 py-4 last:border-b-0"
            >
              <div class="grid gap-3 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_8rem_9rem] md:items-center md:gap-4">
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-white">{{ file.file_name }}</p>
                  <p class="mt-1 text-xs text-zinc-500">{{ file.shortId }} · {{ file.uploaderName }}</p>
                </div>
                <p class="truncate text-sm text-zinc-300">{{ file.updatedLabel }}</p>
                <div>
                  <span
                    :class="[
                      'inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]',
                      workspace.categoryToneLookup[file.category] ?? 'border-white/10 bg-white/5 text-white/75',
                    ]"
                  >
                    {{ file.category ?? 'asset' }}
                  </span>
                </div>
                <div class="md:text-right">
                  <button
                    class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white transition hover:border-[#9cdcfe]/35 hover:bg-[#9cdcfe]/10 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="workspace.downloadingFileId.value === file.file_id"
                    @click="workspace.handleDownloadFile(file)"
                  >
                    {{ workspace.downloadingFileId.value === file.file_id ? 'Downloading...' : 'Download' }}
                  </button>
                </div>
              </div>
            </article>
          </div>

          <article
            v-if="!folderFiles.length"
            class="rounded-[1.6rem] border border-dashed border-white/15 bg-white/[0.03] px-6 py-12 text-center"
          >
            <p class="text-[10px] uppercase tracking-[0.32em] text-[#9cdcfe]">No files found</p>
            <h3 class="mt-3 text-xl font-semibold text-white">No files are currently available in this assigned folder.</h3>
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
      <div class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,#252526_0%,#1f1f1f_100%)] shadow-[0_28px_80px_rgba(0,0,0,0.45)]">
        <div class="border-b border-white/10 px-6 py-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <p class="text-[10px] uppercase tracking-[0.34em] text-[#9cdcfe]">Upload files</p>
              <h3 class="mt-2 text-2xl font-semibold text-white">{{ selectedFolder?.workspace ?? 'Assigned folder' }}</h3>
              <p class="mt-2 text-sm text-zinc-400">{{ selectedFolder?.clientName ?? 'Assigned client' }}</p>
            </div>

            <button
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-zinc-300 transition hover:border-white/20 hover:text-white"
              @click="closeUploadPanel"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
              </svg>
            </button>
          </div>

          <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
              <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Destination</p>
              <p class="mt-2 truncate text-sm font-semibold text-white">{{ selectedFolder?.workspace ?? 'Assigned folder' }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
              <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Client</p>
              <p class="mt-2 truncate text-sm font-semibold text-white">{{ selectedFolder?.clientName ?? 'Assigned client' }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
              <p class="text-[10px] uppercase tracking-[0.22em] text-zinc-500">Mode</p>
              <p class="mt-2 text-sm font-semibold text-white">UI preview only</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[minmax(0,1.15fr)_18rem]">
          <div class="space-y-6">
            <section class="rounded-[1.6rem] border border-dashed border-[#9cdcfe]/30 bg-[#9cdcfe]/[0.06] px-6 py-10 text-center">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-[#9cdcfe]/20 bg-[#9cdcfe]/10 text-[#d7f0ff]">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V7m0 0l-3.5 3.5M12 7l3.5 3.5M5 17.5A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5" />
                </svg>
              </div>
              <h4 class="mt-4 text-lg font-semibold text-white">Select client assets</h4>
              <p class="mt-2 text-sm text-zinc-400">Drag files into this area or browse from your device once upload logic is connected.</p>
            </section>

            <section class="grid gap-4 md:grid-cols-2">
              <label class="block">
                <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400">Category</span>
                <select class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white outline-none">
                  <option class="bg-[#1f1f1f]">Image</option>
                  <option class="bg-[#1f1f1f]">Video</option>
                  <option class="bg-[#1f1f1f]">PDF</option>
                </select>
              </label>

              <label class="block">
                <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400">Client folder</span>
                <input
                  class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300 outline-none"
                  :value="selectedFolder?.workspace ?? ''"
                  readonly
                  type="text"
                />
              </label>
            </section>

            <label class="block">
              <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400">Upload note</span>
              <textarea
                class="min-h-28 w-full rounded-[1.5rem] border border-white/10 bg-white/5 px-4 py-3 text-sm text-white outline-none"
                placeholder="Add a delivery note, batch label, or internal handoff context."
              ></textarea>
            </label>
          </div>

          <aside class="rounded-[1.6rem] border border-white/10 bg-black/20 p-4">
            <p class="text-[10px] uppercase tracking-[0.28em] text-[#9cdcfe]">Upload summary</p>
            <div class="mt-4 space-y-4">
              <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-400">Selected files</p>
                <p class="mt-2 text-sm text-zinc-500">No files selected yet.</p>
              </div>

              <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-400">Visibility</p>
                <p class="mt-2 text-sm text-zinc-300">Files will be prepared for this client folder only.</p>
              </div>

              <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-400">Status</p>
                <div class="mt-2 flex items-center justify-between gap-3">
                  <p class="text-sm text-zinc-300">Waiting for file selection</p>
                  <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] uppercase tracking-[0.22em] text-zinc-400">
                    Pending
                  </span>
                </div>
              </div>
            </div>
          </aside>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-white/10 px-6 py-5">
          <button
            class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-300 transition hover:border-white/20 hover:text-white"
            @click="closeUploadPanel"
          >
            Cancel
          </button>
          <button
            class="inline-flex items-center gap-2 rounded-xl border border-[#9cdcfe]/20 bg-[#9cdcfe]/10 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#d7f0ff] opacity-70"
            disabled
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V7m0 0l-3.5 3.5M12 7l3.5 3.5M5 17.5A2.5 2.5 0 0 0 7.5 20h9a2.5 2.5 0 0 0 2.5-2.5" />
            </svg>
            Upload files
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
