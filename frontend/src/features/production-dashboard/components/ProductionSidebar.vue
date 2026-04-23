<script setup>
defineProps({
  currentUser: {
    type: Object,
    default: () => ({}),
  },
  activeSection: {
    type: String,
    default: 'queue',
  },
  sectionCounts: {
    type: Object,
    default: () => ({}),
  },
})

defineEmits(['change-section', 'sign-out'])

const sectionMeta = [
  { id: 'queue', label: 'Queue', icon: 'queue' },
  { id: 'files', label: 'Files', icon: 'files' },
  { id: 'recycle', label: 'Recycle Bin', icon: 'recycle' },
]
</script>

<template>
  <aside class="flex h-full min-h-screen flex-col border-r border-white/8 bg-[#161419] text-white xl:sticky xl:top-0">
    <div class="border-b border-white/8 px-5 py-6">
      <p class="text-[11px] uppercase tracking-[0.42em] text-brand-200/70">Work &amp; Flow</p>
      <h1 class="mt-3 text-[2.35rem] font-semibold tracking-[-0.05em] text-brand-400 [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
        Studio.
      </h1>
      <div class="mt-6 inline-flex items-center gap-2 border border-white/10 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.32em] text-white/90">
        <span class="text-brand-400">&bull;</span>
        Production
      </div>
    </div>

    <nav class="flex-1 px-3 py-4">
      <button
        v-for="section in sectionMeta"
        :key="section.id"
        :class="[
          'mb-2 flex w-full items-center justify-between rounded-md border px-4 py-3 text-left text-sm transition',
          activeSection === section.id
            ? 'border-brand-400 bg-brand-500/10 text-white shadow-[inset_0_0_0_1px_rgba(216,207,240,0.18)]'
            : 'border-transparent text-white/58 hover:border-white/10 hover:bg-white/[0.03] hover:text-white',
        ]"
        @click="$emit('change-section', section.id)"
      >
        <span class="flex items-center gap-3">
          <svg v-if="section.icon === 'queue'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7h16" />
            <path d="M4 12h10" />
            <path d="M4 17h12" />
            <path d="m16 12 2 2 4-4" />
          </svg>
          <svg v-else-if="section.icon === 'files'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M14 3v5h5" />
            <path d="M5 8.5V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3.5" />
            <path d="M3 14h11" />
            <path d="M3 18h8" />
          </svg>
          <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7h16" />
            <path d="M9 7V5h6v2" />
            <path d="M7 7v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7" />
            <path d="M10 11v5" />
            <path d="M14 11v5" />
          </svg>
          <span>{{ section.label }}</span>
        </span>
        <span class="text-[11px] uppercase tracking-[0.22em] text-white/45">{{ sectionCounts[section.id] ?? 0 }}</span>
      </button>
    </nav>

    <div class="mt-auto border-t border-white/8 px-4 py-5">
      <div class="flex items-center gap-3 rounded-xl bg-white/[0.03] px-4 py-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-brand-500/12 text-sm font-semibold text-brand-100">
          {{ (currentUser.name || 'P').split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-white">{{ currentUser.name || 'Production User' }}</p>
          <p class="mt-1 text-[10px] uppercase tracking-[0.24em] text-brand-200/60">{{ currentUser.role || 'production' }}</p>
        </div>
        <button class="text-white/45 transition hover:text-brand-200" @click="$emit('sign-out')">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M15 7h4v10h-4" />
            <path d="m10 17 5-5-5-5" />
            <path d="M15 12H3" />
          </svg>
        </button>
      </div>
    </div>
  </aside>
</template>
