<script setup>
defineProps({
  folders: { type: Array, required: true },
})
</script>

<template>
  <section class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h2 class="text-xl font-semibold text-slate-950">Folders</h2>
        <p class="text-sm text-slate-500">Structured client spaces for downloads and organization.</p>
      </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="folder in folders"
        :key="folder.id"
        class="rounded-[1.25rem] border border-slate-200 bg-slate-50/80 p-4"
      >
        <p class="text-sm font-semibold text-slate-950">{{ folder.name }}</p>
        <p class="mt-2 text-xs uppercase tracking-[0.3em] text-slate-500">{{ folder.client?.name || 'Shared' }}</p>
      </article>
    </div>
  </section>
</template>
