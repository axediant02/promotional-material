<script setup>
const props = defineProps({
  files: { type: Array, required: true },
  downloadingFileId: { type: String, default: '' },
  downloadAction: { type: Function, default: null },
})

const getFileId = (file) => file.file_id ?? file.id
const getFileName = (file) => file.file_name ?? file.original_name ?? 'Untitled file'
const getFolderName = (file) => file.folder?.folder_name ?? file.folder?.name ?? 'Unknown folder'
const getFileType = (file) => file.category ?? file.mime_type ?? 'file'
const getFileSize = (file) => {
  const size = Number(file.size)

  if (!Number.isFinite(size) || size <= 0) {
    return getFileType(file).toUpperCase()
  }

  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const exponent = Math.min(Math.floor(Math.log(size) / Math.log(1024)), units.length - 1)
  const value = size / 1024 ** exponent

  return `${value.toFixed(value >= 100 || exponent === 0 ? 0 : 1)} ${units[exponent]}`
}

const handleDownload = (file) => {
  if (!props.downloadAction) {
    return
  }

  props.downloadAction(file)
}
</script>

<template>
  <section class="pm-surface rounded-[1.8rem] p-6 lg:p-8">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold tracking-[-0.03em] text-ink dark:text-white ">Recent files</h2>
      <p class="mt-2 text-sm text-muted dark:text-zinc-300">Latest uploaded materials available in your access scope.</p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead>
          <tr class="border-b border-border/80 text-[10px] uppercase tracking-[0.3em] text-muted dark:border-white/10 dark:text-zinc-400">
            <th class="pb-4 font-semibold">Name</th>
            <th class="pb-4 font-semibold">Folder</th>
            <th class="pb-4 font-semibold">Type</th>
            <th class="pb-4 font-semibold">Size</th>
            <th v-if="downloadAction" class="pb-4 text-right font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border/60 dark:divide-white/10">
          <tr v-for="file in files" :key="getFileId(file)">
            <td class="py-4 pr-4 font-medium text-ink dark:text-white">{{ getFileName(file) }}</td>
            <td class="py-4 pr-4 text-muted dark:text-zinc-300">{{ getFolderName(file) }}</td>
            <td class="py-4 pr-4 text-muted dark:text-zinc-300">{{ getFileType(file) }}</td>
            <td class="py-4 text-muted dark:text-zinc-300">{{ getFileSize(file) }}</td>
            <td v-if="downloadAction" class="py-4 text-right">
              <button
                type="button"
                class="pm-button-secondary rounded-full px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.2em] transition disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="downloadingFileId === getFileId(file)"
                @click="handleDownload(file)"
              >
                {{ downloadingFileId === getFileId(file) ? 'Preparing...' : 'Download' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
