<script setup>
defineProps({
  projectNotes: {
    type: Array,
    required: true,
  },
})

defineEmits(['view-all'])
</script>

<template>
  <section class="mb-10">
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-2 font-bold text-slate-900">
        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M7 8h10M7 12h5m-7 8 4-4h10a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
        Project Notes & Client Feedback
      </div>
      <button class="text-xs font-semibold text-blue-600 transition hover:text-blue-800" @click="$emit('view-all')">View All Notes</button>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <article
        v-for="note in projectNotes"
        :key="note.id"
        :class="[
          'relative rounded-3xl p-6 shadow-[0_14px_30px_rgba(15,23,42,0.04)]',
          {
            'bg-amber-50': note.tone === 'amber',
            'bg-sky-50': note.tone === 'sky',
            'bg-emerald-50': note.tone === 'emerald',
          },
        ]"
      >
        <div class="mb-4 flex items-center justify-between">
          <span
            :class="[
              'text-[10px] font-bold uppercase tracking-[0.22em]',
              {
                'text-amber-700': note.tone === 'amber',
                'text-sky-700': note.tone === 'sky',
                'text-emerald-700': note.tone === 'emerald',
              },
            ]"
          >
            {{ note.tag }}
          </span>
          <svg v-if="note.icon === 'alert'" class="h-4 w-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 9v2m0 4h.01M5.06 19h13.88c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.33 16c-.77 1.33.19 3 1.73 3Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          <svg v-else-if="note.icon === 'info'" class="h-4 w-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 8h.01M11 12h1v4h1m-1 5a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          <svg v-else-if="note.icon === 'pin'" class="h-4 w-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="m9 3 6 6m-7 7 5-5m-2-8 8 8-3 1-4 4-1 3-2-2 3-1 4-4 1-3-8-8Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          <svg v-else class="h-4 w-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 9v2m0 4h.01M5.06 19h13.88c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.33 16c-.77 1.33.19 3 1.73 3Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
        </div>
        <h3 class="mb-3 text-base font-semibold text-slate-900">{{ note.title }}</h3>
        <p class="mb-8 text-sm leading-6 text-slate-700">"{{ note.content }}"</p>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Client</p>
          <p class="text-sm font-semibold text-slate-900">{{ note.client }}</p>
        </div>
      </article>
    </div>
  </section>
</template>
