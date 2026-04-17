<script setup>
defineProps({
  files: { type: Array, required: true },
})
</script>

<template>
  <section class="rounded-[1.75rem] border border-slate-200/70 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
    <div class="mb-4">
      <h2 class="text-xl font-semibold text-slate-950">Recent files</h2>
      <p class="text-sm text-slate-500">Latest uploaded materials available in your access scope.</p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead>
          <tr class="text-xs uppercase tracking-[0.25em] text-slate-500">
            <th class="py-3 pr-4">Name</th>
            <th class="py-3 pr-4">Folder</th>
            <th class="py-3 pr-4">Type</th>
            <th class="py-3">Size</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="file in files" :key="file.id">
            <td class="py-3 pr-4 font-medium text-slate-900">{{ file.original_name }}</td>
            <td class="py-3 pr-4 text-slate-600">{{ file.folder?.name || 'Unknown folder' }}</td>
            <td class="py-3 pr-4 text-slate-600">{{ file.mime_type || 'image/*' }}</td>
            <td class="py-3 text-slate-600">{{ file.size }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
