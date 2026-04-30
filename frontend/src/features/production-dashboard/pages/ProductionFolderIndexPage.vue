<script setup>
import { useRoute } from 'vue-router'
import ProductionFolderBrowser from '../components/ProductionFolderBrowser.vue'
import { useProductionWorkspace } from '../productionWorkspace'

const route = useRoute()
const workspace = useProductionWorkspace()
</script>

<template>
  <section class="space-y-5">
    <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.26em] text-muted dark:text-zinc-400">
      <span class="rounded-full bg-white/60 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.26em] dark:bg-white/5 dark:text-zinc-300">Production</span>
      <span>/</span>
      <span class="rounded-full bg-brand-50 px-3 py-1.5 text-brand-700 dark:bg-white/10 dark:text-white">Client folders</span>
    </nav>

    <ProductionFolderBrowser
      :folders="workspace.visibleFolderRows.value"
      :selected-folder-id="route.params.folderId ?? ''"
      :browser-mode="workspace.folderBrowserMode.value"
      :active-filter="workspace.folderBrowserFilter.value"
      :active-sort="workspace.folderBrowserSort.value"
      @select-folder="workspace.openFolder"
      @update:browser-mode="workspace.setFolderBrowserMode"
      @update:active-filter="workspace.setFolderBrowserFilter"
      @update:active-sort="workspace.setFolderBrowserSort"
    />
  </section>
</template>
