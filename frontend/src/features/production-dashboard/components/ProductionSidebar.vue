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
  { id: 'files', label: 'Folders', icon: 'files' },
  { id: 'queue', label: 'Queue', icon: 'queue' },
  { id: 'recycle', label: 'Recycle Bin', icon: 'recycle' },
]
</script>

<template>
  <aside class="flex h-full min-h-screen flex-col bg-[linear-gradient(180deg,#58489b_0%,#4b3d74_100%)] text-white shadow-[24px_0_60px_rgba(75,61,116,0.24)] xl:sticky xl:top-0">
    <div class="border-b border-white/10 px-6 py-7">
      <p class="text-[11px] uppercase tracking-[0.42em] text-white/60">Nexus Archive</p>
      <h1 class="mt-3 text-[2.35rem] font-semibold tracking-[-0.05em] text-white ">
        Studio.
      </h1>
      <p class="mt-1 text-sm text-white/65">Delivery execution</p>
      <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.32em] text-white">
        <span class="text-[#d8c5ff]">&bull;</span>
        Production Desk
      </div>
    </div>

    <nav class="flex-1 px-4 py-5">
      <button
        v-for="section in sectionMeta"
        :key="section.id"
        :class="[
          'mb-2 flex w-full items-center justify-between rounded-2xl px-4 py-3.5 text-left text-sm transition',
          activeSection === section.id
            ? 'bg-white text-brand-700 shadow-[0_16px_34px_rgba(34,18,68,0.18)]'
            : 'text-white/72 hover:bg-white/10 hover:text-white',
        ]"
        @click="$emit('change-section', section.id)"
      >
        <span class="flex items-center gap-3">
          <svg v-if="section.icon === 'queue'" class="h-4 w-4" :class="activeSection === section.id ? 'text-brand-600' : 'text-white/65'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7h16" />
            <path d="M4 12h10" />
            <path d="M4 17h12" />
            <path d="m16 12 2 2 4-4" />
          </svg>
          <svg v-else-if="section.icon === 'files'" class="h-4 w-4" :class="activeSection === section.id ? 'text-brand-600' : 'text-white/65'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M14 3v5h5" />
            <path d="M5 8.5V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3.5" />
            <path d="M3 14h11" />
            <path d="M3 18h8" />
          </svg>
          <svg v-else class="h-4 w-4" :class="activeSection === section.id ? 'text-brand-600' : 'text-white/65'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7h16" />
            <path d="M9 7V5h6v2" />
            <path d="M7 7v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7" />
            <path d="M10 11v5" />
            <path d="M14 11v5" />
          </svg>
          <span>{{ section.label }}</span>
        </span>
        <span :class="['text-[11px] uppercase tracking-[0.22em]', activeSection === section.id ? 'text-brand-500' : 'text-white/45']">{{ sectionCounts[section.id] ?? 0 }}</span>
      </button>
    </nav>

    <div class="mt-auto border-t border-white/10 px-6 py-5">
      <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/10 text-sm font-semibold text-white">
          {{ (currentUser.name || 'P').split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() }}
        </div>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-white">{{ currentUser.name || 'Production User' }}</p>
          <p class="mt-1 text-[10px] uppercase tracking-[0.24em] text-white/55">{{ currentUser.role || 'production' }}</p>
        </div>
        <button class="text-white/55 transition hover:text-white" @click="$emit('sign-out')">
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
