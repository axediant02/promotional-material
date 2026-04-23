<script setup>
import { useRoute } from 'vue-router'
import ProductionFolderBrowser from '../components/ProductionFolderBrowser.vue'
import { useProductionWorkspace } from '../productionWorkspace'

const route = useRoute()
const workspace = useProductionWorkspace()
</script>

<template>
  <section class="space-y-5">
    <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.26em] text-zinc-500">
      <span class="rounded-full border border-border bg-white/60 px-3 py-1.5 dark:border-white/10 dark:bg-white/5">Production</span>
      <span>/</span>
      <span class="rounded-full border border-[#9cdcfe]/30 bg-[#9cdcfe]/10 px-3 py-1.5 text-[#6ecff6] dark:text-[#b9e7ff]">Client folders</span>
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
