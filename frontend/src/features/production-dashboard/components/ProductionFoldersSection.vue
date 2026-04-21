<script setup>
import Badge from '../../../components/shared/Badge.vue'

defineProps({
  clientFolders: {
    type: Array,
    required: true,
  },
})

defineEmits(['view-directory'])
</script>

<template>
  <section class="mb-10">
    <div class="mb-6 flex items-center justify-between">
      <h2 class="font-bold text-slate-900">Assigned Client Folders</h2>
      <button class="flex items-center gap-1 text-xs font-semibold text-blue-600 transition hover:text-blue-800" @click="$emit('view-directory')">
        View Directory
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="m9 5 7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
      </button>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <article
        v-for="client in clientFolders"
        :key="client.id"
        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_18px_40px_rgba(15,23,42,0.05)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_45px_rgba(15,23,42,0.08)]"
      >
        <div class="mb-6 flex items-start justify-between">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white">
            {{ client.initials }}
          </div>
          <Badge :variant="client.status === 'Priority' ? 'info' : 'default'" size="sm">{{ client.status }}</Badge>
        </div>

        <div class="mb-6">
          <h3 class="font-bold text-slate-900">{{ client.name }}</h3>
          <p class="text-xs text-slate-400">{{ client.location }}</p>
        </div>

        <div class="flex justify-between border-t border-slate-50 pt-4">
          <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Active Requests</p>
            <p class="text-lg font-bold text-slate-900">{{ client.requests }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Assets</p>
            <p class="text-lg font-bold text-slate-900">{{ client.assets }}</p>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
