<script setup>
defineProps({
  selectedFolder: {
    type: Object,
    default: null,
  },
  folderFiles: {
    type: Array,
    default: () => [],
  },
  folderRequests: {
    type: Array,
    default: () => [],
  },
  downloadingFileId: {
    type: String,
    default: '',
  },
  updatingRequestId: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['download-file', 'update-request-status'])

const requestStatusClasses = {
  pending: 'border-amber-300/25 bg-amber-400/10 text-amber-100',
  in_progress: 'border-sky-300/25 bg-sky-400/10 text-sky-100',
  done: 'border-emerald-300/25 bg-emerald-400/10 text-emerald-100',
}

const categoryClasses = {
  image: 'border-brand-300/20 bg-brand-50 text-brand-700 dark:bg-brand-300/10 dark:text-brand-100',
  video: 'border-brand-400/20 bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-100',
  pdf: 'border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-white/75',
}
</script>

<template>
  <aside class="pm-surface-strong rounded-[2rem] p-5 lg:p-6">
    <template v-if="selectedFolder">
      <div class="border-b border-border/70 pb-5 dark:border-white/10">
        <p class="text-[10px] uppercase tracking-[0.34em] text-brand-600 dark:text-brand-100">Selected folder</p>
        <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          {{ selectedFolder.workspace }}
        </h2>
        <p class="mt-2 text-sm text-muted dark:text-zinc-300">{{ selectedFolder.clientName }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3">
          <div class="rounded-2xl border border-border bg-white/60 px-4 py-3 dark:border-white/10 dark:bg-black/10">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Files</p>
            <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ selectedFolder.fileCount }}</p>
          </div>
          <div class="rounded-2xl border border-border bg-white/60 px-4 py-3 dark:border-white/10 dark:bg-black/10">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">Active requests</p>
            <p class="mt-2 text-2xl font-semibold text-ink dark:text-white">{{ selectedFolder.activeRequestCount }}</p>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-[11px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">
          <span>{{ selectedFolder.latestActivityLabel }}</span>
          <span>{{ selectedFolder.dueDateLabel }}</span>
          <span>{{ selectedFolder.statusLabel }}</span>
        </div>
      </div>

      <section class="mt-6">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-brand-600 dark:text-brand-100">Request summary</p>
            <h3 class="mt-2 text-lg font-semibold text-ink dark:text-white">Current production queue</h3>
          </div>
          <span class="rounded-full border border-border bg-white/70 px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
            {{ folderRequests.length }} total
          </span>
        </div>

        <div class="mt-4 space-y-3">
          <article
            v-for="request in folderRequests"
            :key="request.id"
            class="rounded-[1.4rem] border border-border bg-white/65 p-4 dark:border-white/10 dark:bg-white/5"
          >
            <div class="flex flex-col gap-4">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span :class="['rounded-full border px-2.5 py-1 text-[10px] uppercase tracking-[0.22em]', requestStatusClasses[request.statusTone]]">
                      {{ request.statusLabel }}
                    </span>
                    <span class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">{{ request.reference }}</span>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-ink dark:text-white">{{ request.title }}</p>
                </div>
              </div>

              <p class="text-sm leading-6 text-muted dark:text-zinc-300">{{ request.description }}</p>

              <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-muted dark:text-zinc-400">{{ request.dueLabel }}</p>
                <select
                  class="pm-input rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
                  :disabled="updatingRequestId === request.id"
                  :value="request.status"
                  @change="emit('update-request-status', request.id, $event.target.value)"
                >
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="done">Done</option>
                </select>
              </div>
            </div>
          </article>

          <article
            v-if="!folderRequests.length"
            class="rounded-[1.4rem] border border-dashed border-border bg-white/50 px-4 py-8 text-center dark:border-white/10 dark:bg-white/[0.03]"
          >
            <p class="text-sm text-muted dark:text-zinc-300">No requests are currently attached to this folder.</p>
          </article>
        </div>
      </section>

      <section class="mt-6">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-brand-600 dark:text-brand-100">Files</p>
            <h3 class="mt-2 text-lg font-semibold text-ink dark:text-white">Latest accessible assets</h3>
          </div>
          <span class="rounded-full border border-border bg-white/70 px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
            Download only
          </span>
        </div>

        <div class="mt-4 space-y-3">
          <article
            v-for="file in folderFiles"
            :key="file.file_id"
            class="rounded-[1.4rem] border border-border bg-white/65 p-4 dark:border-white/10 dark:bg-white/5"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span
                    :class="[
                      'rounded-full border px-2.5 py-1 text-[10px] uppercase tracking-[0.22em]',
                      categoryClasses[file.category] ?? 'border-white/10 bg-white/5 text-white/75',
                    ]"
                  >
                    {{ file.category ?? 'asset' }}
                  </span>
                  <span class="text-[10px] uppercase tracking-[0.22em] text-muted dark:text-zinc-500">{{ file.shortId }}</span>
                </div>
                <p class="mt-3 truncate text-sm font-semibold text-ink dark:text-white">{{ file.file_name }}</p>
                <p class="mt-2 text-xs text-muted dark:text-zinc-400">{{ file.uploaderName }} · {{ file.updatedLabel }}</p>
              </div>

              <button
                class="pm-gradient-primary rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="downloadingFileId === file.file_id"
                @click="emit('download-file', file)"
              >
                {{ downloadingFileId === file.file_id ? 'Downloading...' : 'Download' }}
              </button>
            </div>
          </article>

          <article
            v-if="!folderFiles.length"
            class="rounded-[1.4rem] border border-dashed border-border bg-white/50 px-4 py-8 text-center dark:border-white/10 dark:bg-white/[0.03]"
          >
            <p class="text-sm text-muted dark:text-zinc-300">No files are currently available in this assigned folder.</p>
          </article>
        </div>
      </section>
    </template>

    <article
      v-else
      class="flex min-h-[24rem] items-center justify-center rounded-[1.6rem] border border-dashed border-border bg-white/40 px-6 text-center dark:border-white/10 dark:bg-white/[0.03]"
    >
      <div>
        <p class="text-[10px] uppercase tracking-[0.3em] text-brand-600 dark:text-brand-100">No folder selected</p>
        <h3 class="mt-3 text-2xl font-semibold text-ink dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          Select an assigned folder to inspect its requests and files.
        </h3>
      </div>
    </article>
  </aside>
</template>
