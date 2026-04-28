<script setup>
import SkeletonBlock from '../../../components/shared/SkeletonBlock.vue'
import ClientAssetCard from './ClientAssetCard.vue'
import ClientAssetCatalogHeader from './ClientAssetCatalogHeader.vue'
import ClientAssetRequestTile from './ClientAssetRequestTile.vue'
import ClientAssetRow from './ClientAssetRow.vue'

defineProps({
  files: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  searchQuery: { type: String, default: '' },
  viewMode: { type: String, default: 'grid' },
  folderLabel: { type: String, default: 'Assigned Folder' },
  lastUpdatedLabel: { type: String, default: 'No files yet' },
  selectedFileId: { type: String, default: null },
})

const emit = defineEmits(['update:viewMode', 'request-change', 'clear-search', 'open-request'])
</script>

<template>
  <section id="asset-catalog">
    <ClientAssetCatalogHeader
      :count="files.length"
      :folder-label="folderLabel"
      :last-updated-label="lastUpdatedLabel"
      :view-mode="viewMode"
      @update:view-mode="emit('update:viewMode', $event)"
    />

    <div
      v-if="loading"
      class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
    >
      <div
        v-for="item in 6"
        :key="item"
        class="overflow-hidden rounded-[1.4rem] border border-white/10 bg-white/5 p-4"
      >
        <SkeletonBlock width="w-full" height="aspect-[16/11]" rounded="rounded-2xl" />
        <div class="mt-4 space-y-3">
          <SkeletonBlock width="w-1/2" height="h-4" />
          <SkeletonBlock width="w-2/3" height="h-3" />
          <SkeletonBlock width="w-full" height="h-9" rounded="rounded-xl" />
        </div>
      </div>
    </div>

    <div
      v-else-if="!files.length"
      class="rounded-[1.75rem] border border-dashed border-white/15 bg-[#10131c] px-6 py-16 text-center"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-violet-500/25 bg-violet-500/10 text-violet-200">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h4.38a2.5 2.5 0 0 1 1.77.73l.62.62A2.5 2.5 0 0 0 14.04 7h4.46A2.5 2.5 0 0 1 21 9.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-11Z" />
        </svg>
      </div>
      <h4 class="mt-5 text-lg font-semibold text-white">
        {{ searchQuery ? 'No files match this search' : 'No approved files yet' }}
      </h4>
      <p class="mt-2 text-sm text-zinc-400">
        {{ searchQuery ? `No approved files match "${searchQuery}". Try a broader search.` : 'Approved files will appear here once production shares them to your assigned folder.' }}
      </p>
      <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
        <button
          v-if="searchQuery"
          class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-zinc-300 transition hover:border-white/20 hover:text-white"
          @click="emit('clear-search')"
        >
          Clear search
        </button>
        <button
          v-else
          type="button"
          class="pm-gradient-primary rounded-full px-4 py-2 text-sm font-semibold transition hover:brightness-110"
          @click="emit('open-request')"
        >
          Submit a request
        </button>
      </div>
    </div>

    <div
      v-else-if="viewMode === 'grid'"
      class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
    >
      <ClientAssetCard
        v-for="file in files"
        :key="file.file_id"
        :file="file"
        :selected="selectedFileId === file.file_id"
        @request-change="emit('request-change', $event)"
      />
      <ClientAssetRequestTile @click="emit('open-request')" />
    </div>

    <div
      v-else
      class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/5"
    >
      <ClientAssetRow
        v-for="file in files"
        :key="file.file_id"
        :file="file"
        :selected="selectedFileId === file.file_id"
        @request-change="emit('request-change', $event)"
      />
      <div class="border-t border-white/10 p-4">
        <ClientAssetRequestTile @click="emit('open-request')" />
      </div>
    </div>
  </section>
</template>
