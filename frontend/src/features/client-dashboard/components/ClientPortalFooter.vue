<script setup>
const props = defineProps({
  fileCount: { type: Number, default: 0 },
  folderLabel: { type: String, default: 'Assigned Folder' },
  totalSizeLabel: { type: String, default: null },
})
</script>

<template>
  <footer class="border-t border-border/80 bg-white/55 px-6 py-5 sm:px-8 lg:px-10 dark:border-white/10 dark:bg-white/5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div class="flex items-start gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-white/10 dark:text-white">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 0 0 2-2V9.4a1 1 0 0 0-.3-.7l-5.4-5.4A1 1 0 0 0 12.6 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-ink dark:text-white">Approved files stay scoped to {{ folderLabel }}</p>
          <p class="mt-1 text-sm text-muted dark:text-zinc-300">
            {{ fileCount }} {{ fileCount === 1 ? 'file is' : 'files are' }} available{{ totalSizeLabel ? ` with ${totalSizeLabel} of protected assets` : '' }}.
          </p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <div class="rounded-xl border border-border bg-white/80 px-4 py-3 text-sm text-muted shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
          Individual file downloads are available from the catalog.
        </div>
      </div>
    </div>
  </footer>
</template>
