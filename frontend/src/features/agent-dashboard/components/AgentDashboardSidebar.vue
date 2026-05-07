<script setup>
const props = defineProps({
  currentUser: {
    type: Object,
    default: () => ({}),
  },
  activeView: {
    type: String,
    default: 'folders',
  },
  selectedFolder: {
    type: Object,
    default: null,
  },
  sectionCounts: {
    type: Object,
    default: () => ({}),
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['change-view', 'sign-out', 'toggle-collapse'])

const sections = [
  { id: 'folders', label: 'Folders', icon: 'folder' },
  { id: 'folder', label: 'Open Folder', icon: 'document' },
  { id: 'recent', label: 'Recent Files', icon: 'clock' },
]

const getInitials = (value) =>
  (value || 'Agent User')
    .split(' ')
    .filter(Boolean)
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()

const isSectionActive = (sectionId) => props.activeView === sectionId

function getSectionButtonClass(sectionId) {
  const active = isSectionActive(sectionId)

  if (props.collapsed) {
    return [
      'group relative flex w-full items-start gap-3 rounded-[1.35rem] border px-4 py-3.5 text-left transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] xl:min-h-[4.5rem] xl:justify-start xl:px-3 xl:py-3',
      active
        ? 'border-white/20 bg-white/12 text-white shadow-[0_18px_36px_rgba(34,18,68,0.24)]'
        : 'border-transparent bg-transparent text-white/72 hover:bg-white/10 hover:text-white',
    ].join(' ')
  }

  return [
    'group flex w-full items-center gap-3 rounded-[1.35rem] border px-4 py-3.5 text-left transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]',
    active
      ? 'border-white/14 bg-white/95 text-brand-700 shadow-[0_18px_36px_rgba(34,18,68,0.18)]'
      : 'border-transparent bg-transparent text-white/72 hover:border-white/12 hover:bg-white/10 hover:text-white',
  ].join(' ')
}

function getSectionIconClass(sectionId) {
  const active = isSectionActive(sectionId)

  return [
    'transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-110',
    active ? 'scale-105 text-current' : 'scale-100 text-current',
  ].join(' ')
}

function getNavTooltipClass() {
  return props.collapsed ? 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100' : 'opacity-0'
}
</script>

<template>
  <aside
    :class="[
      'pm-dashboard-sidebar relative flex h-full min-h-screen flex-col overflow-hidden border-r border-white/10 transition-[width,transform] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] xl:sticky xl:top-0',
      collapsed ? 'w-full xl:w-[6.5rem]' : 'w-full xl:w-[18.5rem]',
    ]"
  >
    <div
      class="relative border-b border-white/10 transition-[padding] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
      :class="collapsed ? 'px-3 pt-4 pb-4' : 'px-6 py-7'"
    >
      <button
        class="absolute z-20 inline-flex shrink-0 items-center justify-center rounded-full border border-white/12 bg-white/10 text-white/80 shadow-[0_12px_24px_rgba(17,11,34,0.12)] transition-all duration-500 hover:border-white/20 hover:bg-white/15 hover:text-white"
        :class="collapsed ? 'left-1/2 top-3 h-11 w-11 -translate-x-1/2 rounded-[1.15rem]' : 'right-4 top-5 h-9 w-9'"
        type="button"
        :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        @click="emit('toggle-collapse')"
      >
        <svg
          class="h-5 w-5 transition-transform duration-500"
          :class="collapsed ? 'rotate-0' : 'rotate-180'"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.9"
          aria-hidden="true"
        >
          <path d="m10 7 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      <div :class="collapsed ? 'flex flex-col items-center gap-1 pr-10 pt-10' : 'flex items-start justify-between gap-4 pr-12'">
        <div
          class="min-w-0 overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
          :class="collapsed ? 'max-h-0 max-w-0 opacity-0' : 'max-h-[14rem] max-w-[13rem] opacity-100'"
        >
          <p class="text-[11px] uppercase tracking-[0.42em] text-white/55">Promotional Materials</p>
          <h1 class="mt-3 text-[2.35rem] font-semibold tracking-[-0.05em] text-white">
            Agent.
          </h1>
          <p class="mt-1 text-sm text-white/65">Client file access</p>
          <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.32em] text-white">
            <span class="h-2 w-2 rounded-full bg-[#d8c5ff]"></span>
            Download Desk
          </div>
        </div>

      </div>
    </div>

    <nav class="flex-1" :class="collapsed ? 'px-2 pt-8 pb-5' : 'px-4 py-5'">
      <p
        class="overflow-hidden px-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-white/42 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
        :class="collapsed ? 'max-h-0 pb-0 opacity-0' : 'max-h-8 pb-3 opacity-100'"
      >
        Navigation
      </p>

      <div :class="collapsed ? 'space-y-3' : 'space-y-2'">
        <button
          v-for="section in sections"
          :key="section.id"
          type="button"
          :disabled="section.id === 'folder' && !props.selectedFolder"
          :title="collapsed ? section.label : ''"
          :aria-pressed="isSectionActive(section.id)"
          :class="[
            getSectionButtonClass(section.id),
            section.id === 'folder' && !props.selectedFolder ? 'cursor-not-allowed opacity-45' : '',
          ]"
          @click="emit('change-view', section.id)"
        >
          <span :class="collapsed ? 'flex w-full items-center justify-center' : 'flex items-center gap-3'">
            <span
              class="relative flex items-center justify-center"
              :class="collapsed ? 'h-11 w-11 shrink-0 rounded-[1.15rem] border border-white/12 bg-white/8' : 'h-11 w-11 rounded-[1.15rem] border border-transparent bg-transparent group-hover:border-white/10 group-hover:bg-white/8'"
            >
              <svg
                v-if="section.icon === 'folder'"
                class="h-5 w-5"
                :class="getSectionIconClass(section.id)"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
              >
                <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
              </svg>
              <svg
                v-else-if="section.icon === 'document'"
                class="h-5 w-5"
                :class="getSectionIconClass(section.id)"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
              >
                <path d="M14 3v5h5" />
                <path d="M5 5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z" />
                <path d="M9 14h6" />
                <path d="M9 17h4" />
              </svg>
              <svg
                v-else
                class="h-5 w-5"
                :class="getSectionIconClass(section.id)"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
              >
                <path d="M4 7h16" stroke-linecap="round" />
                <path d="M4 12h12" stroke-linecap="round" />
                <path d="M4 17h8" stroke-linecap="round" />
                <path d="M18 14v5" stroke-linecap="round" />
                <path d="m15.5 16.5 2.5 2.5 2.5-2.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>

            <span
              class="flex items-center gap-3 overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
              :class="collapsed ? 'xl:max-w-0 xl:opacity-0' : 'max-w-[12rem] opacity-100'"
            >
              <span class="flex-1 truncate text-sm font-medium">{{ section.label }}</span>
              <span
                class="inline-flex min-w-[1.9rem] items-center justify-center rounded-full bg-white/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] transition-all duration-500"
                :class="isSectionActive(section.id) ? 'text-brand-600 dark:text-brand-200' : 'text-white/48'"
              >
                {{ props.sectionCounts[section.id] ?? 0 }}
              </span>
            </span>
          </span>

          <span
            class="pointer-events-none absolute left-full top-1/2 z-20 ml-3 -translate-y-1/2 whitespace-nowrap rounded-full border border-white/15 bg-slate-950/95 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-white shadow-[0_12px_24px_rgba(17,11,34,0.22)] transition duration-200"
            :class="getNavTooltipClass()"
            :aria-hidden="collapsed ? 'false' : 'true'"
          >
            {{ section.label }}
          </span>
        </button>
      </div>
    </nav>

    <div class="mt-auto border-t border-white/10" :class="collapsed ? 'px-3 py-5' : 'px-5 py-5'">
      <div
        :class="[
          'flex items-center gap-3 rounded-[1.25rem] border border-white/10 bg-white/10 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]',
          collapsed ? 'flex-col px-2 py-3' : 'px-3 py-3.5',
        ]"
      >
        <div
          class="flex h-10 w-10 items-center justify-center rounded-full bg-white/12 text-sm font-semibold text-white transition-all duration-500"
        >
          {{ getInitials(props.currentUser?.name) }}
        </div>

        <div
          class="min-w-0 flex-1 overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
          :class="collapsed ? 'xl:max-w-0 xl:opacity-0' : 'max-w-[10rem] opacity-100'"
        >
          <p class="truncate text-sm font-semibold text-white">
            {{ props.currentUser?.name || 'Agent User' }}
          </p>
          <p class="mt-1 text-[10px] uppercase tracking-[0.22em] text-white/52">
            {{ props.currentUser?.role || 'agent' }}
          </p>
        </div>

        <button
          class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/68 transition-all duration-500 hover:border-white/20 hover:bg-white/10 hover:text-white"
          :class="collapsed ? '' : 'ml-auto'"
          type="button"
          :aria-label="collapsed ? 'Sign out' : 'Sign out of agent dashboard'"
          @click="emit('sign-out')"
        >
          <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M15 7h4v10h-4" />
            <path d="m10 17 5-5-5-5" />
            <path d="M15 12H3" />
          </svg>
        </button>
      </div>
    </div>
  </aside>
</template>
