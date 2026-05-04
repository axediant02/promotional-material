<script setup>
defineProps({
  recycleBinSummaryStats: {
    type: Array,
    default: () => [],
  },
  recycleBinFiles: {
    type: Array,
    default: () => [],
  },
  restoringFileId: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['restore-file'])
</script>

<template>
  <section class="space-y-6">
    <section class="grid gap-4 xl:grid-cols-3">
      <article class="pm-surface rounded-[1.8rem] px-5 py-5">
        <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ recycleBinSummaryStats[0]?.label ?? 'Deleted files' }}</p>
        <p class="mt-4 text-4xl leading-none text-brand-700 dark:text-white ">
          {{ recycleBinSummaryStats[0]?.value ?? 0 }}
        </p>
        <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ recycleBinSummaryStats[0]?.detail ?? 'In assigned recycle scope' }}</p>
      </article>
      <article class="pm-surface rounded-[1.8rem] px-5 py-5">
        <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ recycleBinSummaryStats[1]?.label ?? 'Recoverable clients' }}</p>
        <p class="mt-4 text-4xl leading-none text-ink dark:text-white ">
          {{ recycleBinSummaryStats[1]?.value ?? 0 }}
        </p>
        <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ recycleBinSummaryStats[1]?.detail ?? 'Workspaces with deleted assets' }}</p>
      </article>
      <article class="pm-surface rounded-[1.8rem] px-5 py-5">
        <p class="text-[10px] uppercase tracking-[0.38em] text-muted dark:text-zinc-400">{{ recycleBinSummaryStats[2]?.label ?? 'Live library' }}</p>
        <p class="mt-4 text-4xl leading-none text-ink dark:text-white ">
          {{ recycleBinSummaryStats[2]?.value ?? 0 }}
        </p>
        <p class="mt-3 text-sm text-muted dark:text-zinc-300">{{ recycleBinSummaryStats[2]?.detail ?? 'Files still active outside recycle bin' }}</p>
      </article>
    </section>

    <section class="space-y-3">
      <article
        v-for="file in recycleBinFiles"
        :key="file.file_id"
        class="pm-surface rounded-[1.8rem] px-5 py-5 transition hover:border-brand-500"
      >
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
          <div class="min-w-0">
            <p class="text-[11px] uppercase tracking-[0.26em] text-muted dark:text-zinc-400">{{ file.shortId }}</p>
            <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white ">
              {{ file.file_name }}
            </h2>
            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] text-muted dark:text-zinc-400">
              <span>{{ file.clientName }}</span>
              <span>{{ file.folderName }}</span>
              <span>{{ file.uploaderName }}</span>
              <span>deleted {{ file.deletedLabel }}</span>
            </div>
          </div>

          <button
            class="pm-gradient-primary rounded-2xl px-4 py-3 text-[12px] font-semibold uppercase tracking-[0.22em] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="restoringFileId === file.file_id"
            @click="emit('restore-file', file.file_id)"
          >
            {{ restoringFileId === file.file_id ? 'Restoring...' : 'Restore file' }}
          </button>
        </div>
      </article>

      <article
        v-if="!recycleBinFiles.length"
        class="pm-surface rounded-[1.8rem] border-dashed px-6 py-10 text-center"
      >
        <p class="text-[10px] uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">Recycle bin clear</p>
        <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white ">
          No assigned deleted files match the current search.
        </h2>
      </article>
    </section>
  </section>
</template>
