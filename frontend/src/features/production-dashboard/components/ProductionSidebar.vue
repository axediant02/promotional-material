<script setup>
const props = defineProps({
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
  collapsed: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['change-section', 'sign-out', 'toggle-collapse'])

const sectionMeta = [
  { id: 'files', label: 'Folders', icon: 'files' },
  { id: 'queue', label: 'Queue', icon: 'queue' },
  { id: 'recycle', label: 'Recycle Bin', icon: 'recycle' },
]

const getInitials = (name) =>
  (name || 'Production User')
    .split(' ')
    .filter(Boolean)
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()

const isSectionActive = (sectionId) => props.activeSection === sectionId

const SIDEBAR_MORPH_EASE = 'ease-[cubic-bezier(0.22,1,0.36,1)]'

function getSectionButtonClass(sectionId) {
  const active = isSectionActive(sectionId)

  if (props.collapsed) {
    return [
      `group relative flex aspect-square w-full items-center justify-center rounded-[1.55rem] border transition-all duration-500 ${SIDEBAR_MORPH_EASE}`,
      active
        ? 'border-white/20 bg-white/12 text-white shadow-[0_18px_36px_rgba(34,18,68,0.24)]'
        : 'border-transparent bg-transparent text-white/72 hover:bg-white/10 hover:text-white',
    ].join(' ')
  }

  return [
    `group flex w-full items-center gap-3 rounded-[1.35rem] border px-4 py-3.5 text-left transition-all duration-500 ${SIDEBAR_MORPH_EASE}`,
    active
      ? 'border-white/14 bg-white/95 text-brand-700 shadow-[0_18px_36px_rgba(34,18,68,0.18)]'
      : 'border-transparent bg-transparent text-white/72 hover:border-white/12 hover:bg-white/10 hover:text-white',
  ].join(' ')
}

function getSectionIconShellClass(sectionId) {
  const active = isSectionActive(sectionId)

  if (props.collapsed) {
    return [
      `flex h-12 w-12 items-center justify-center transition-all duration-500 ${SIDEBAR_MORPH_EASE}`,
      active
        ? 'rounded-[1.35rem] bg-white/14 shadow-[0_14px_28px_rgba(34,18,68,0.18)]'
        : 'rounded-[1.15rem] bg-transparent group-hover:bg-white/10',
    ].join(' ')
  }

  return [
    `flex h-11 w-11 items-center justify-center border transition-all duration-500 ${SIDEBAR_MORPH_EASE}`,
    active
      ? 'rounded-[1rem] border-brand-200 bg-brand-50/90 shadow-[inset_0_1px_0_rgba(255,255,255,0.65)]'
      : 'rounded-[1.15rem] border-transparent bg-transparent group-hover:border-white/10 group-hover:bg-white/8',
  ].join(' ')
}

function getSectionIconClass(sectionId) {
  const active = isSectionActive(sectionId)

  if (props.collapsed) {
    return [
      `transition-all duration-500 ${SIDEBAR_MORPH_EASE} group-hover:scale-110`,
      active ? 'scale-105 -rotate-3 text-white' : 'scale-100 rotate-0 text-current',
    ].join(' ')
  }

  return [
    `transition-all duration-500 ${SIDEBAR_MORPH_EASE} group-hover:scale-110`,
    active ? 'scale-105 text-brand-600' : 'scale-100 text-current',
  ].join(' ')
}

function getFooterInitials() {
  return getInitials(props.currentUser?.name)
}
</script>

<template>
  <aside
    :class="[
      `relative flex h-full min-h-screen flex-col overflow-hidden transition-[width,transform] duration-500 ${SIDEBAR_MORPH_EASE} xl:sticky xl:top-0`,
      collapsed ? 'xl:w-[6.5rem]' : 'xl:w-[18.5rem]',
      'pm-dashboard-sidebar border-r border-white/10',
    ]"
  >
    <div
      class="border-b border-[#ddd4f0] transition-[padding] duration-500 dark:border-white/10"
      :class="collapsed ? 'px-3 py-6' : 'px-5 py-6 xl:px-6'"
    >
      <div :class="collapsed ? 'flex flex-col items-center gap-4' : 'flex items-start justify-between gap-4'">
        <div
          class="min-w-0 overflow-hidden transition-all duration-500"
          :class="[SIDEBAR_MORPH_EASE, collapsed ? 'max-h-0 max-w-0 -translate-x-3 opacity-0' : 'max-h-40 max-w-[13rem] translate-x-0 opacity-100']"
        >
          <p class="text-[10px] font-semibold uppercase tracking-[0.34em] text-white/58">
            Production
          </p>
          <h1 class="mt-3 text-[1.95rem] font-semibold tracking-[-0.05em] text-white">
            Workspace
          </h1>
          <p class="mt-3 max-w-[13rem] text-sm leading-6 text-white/65">
            Review folders, active requests, and recovery tools from one focused rail.
          </p>
        </div>

        <button
          class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/12 bg-white/10 text-white/80 shadow-[0_12px_24px_rgba(17,11,34,0.12)] transition-all duration-500 hover:border-white/20 hover:bg-white/15 hover:text-white"
          :class="SIDEBAR_MORPH_EASE"
          type="button"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          @click="emit('toggle-collapse')"
        >
          <svg
            class="h-4 w-4 transition-transform duration-500"
            :class="[SIDEBAR_MORPH_EASE, collapsed ? 'rotate-0' : 'rotate-180']"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.9"
            aria-hidden="true"
          >
            <path d="m10 7 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
    </div>

    <nav class="flex-1" :class="collapsed ? 'px-3 py-5' : 'px-4 py-5 xl:px-5'">
      <p
        class="overflow-hidden px-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-white/42 transition-all duration-500"
        :class="[SIDEBAR_MORPH_EASE, collapsed ? 'max-h-0 pb-0 opacity-0' : 'max-h-8 pb-3 opacity-100']"
      >
        Navigation
      </p>

      <div :class="collapsed ? 'space-y-3' : 'space-y-2'">
        <button
          v-for="section in sectionMeta"
          :key="section.id"
          type="button"
          :title="collapsed ? section.label : ''"
          :aria-pressed="isSectionActive(section.id)"
          :class="getSectionButtonClass(section.id)"
          @click="emit('change-section', section.id)"
        >
          <span class="flex items-center gap-3" :class="collapsed ? 'w-full justify-center gap-0' : ''">
            <span :class="getSectionIconShellClass(section.id)">
              <svg
                v-if="section.icon === 'files'"
                class="h-5 w-5"
                :class="getSectionIconClass(section.id)"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
              >
                <path d="M14 4v4h4" />
                <path d="M6 5.5A1.5 1.5 0 0 1 7.5 4h6.4L18 8.1V18.5A1.5 1.5 0 0 1 16.5 20h-9A1.5 1.5 0 0 1 6 18.5z" stroke-linejoin="round" />
                <path d="M9 12h6" stroke-linecap="round" />
                <path d="M9 15.5h4" stroke-linecap="round" />
              </svg>

              <svg
                v-else-if="section.icon === 'queue'"
                class="h-5 w-5"
                :class="getSectionIconClass(section.id)"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
              >
                <path d="M5 7h10" stroke-linecap="round" />
                <path d="M5 12h10" stroke-linecap="round" />
                <path d="M5 17h8" stroke-linecap="round" />
                <path d="m15 12 2 2 4-5" stroke-linecap="round" stroke-linejoin="round" />
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
                <path d="M4.5 7h15" stroke-linecap="round" />
                <path d="M9 7V5h6v2" stroke-linecap="round" />
                <path d="M7 7v11a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7" stroke-linejoin="round" />
                <path d="M10 11v5" stroke-linecap="round" />
                <path d="M14 11v5" stroke-linecap="round" />
              </svg>
            </span>

            <span
              class="flex items-center gap-3 overflow-hidden transition-all duration-500"
              :class="[SIDEBAR_MORPH_EASE, collapsed ? 'max-w-0 opacity-0' : 'max-w-[12rem] opacity-100']"
            >
              <span class="flex-1 truncate text-sm font-medium">{{ section.label }}</span>
              <span
                class="inline-flex min-w-[1.9rem] items-center justify-center rounded-full bg-white/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] transition-all duration-500"
                :class="[
                  SIDEBAR_MORPH_EASE,
                  isSectionActive(section.id) ? 'text-brand-600 dark:text-brand-200' : 'text-white/48',
                  collapsed ? 'translate-x-3 opacity-0' : 'translate-x-0 opacity-100',
                ]"
              >
                {{ props.sectionCounts[section.id] ?? 0 }}
              </span>
            </span>
          </span>

          <span
            class="pointer-events-none absolute left-full top-1/2 z-20 ml-3 -translate-y-1/2 whitespace-nowrap rounded-full border border-white/15 bg-slate-950/95 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-white shadow-[0_12px_24px_rgba(17,11,34,0.22)] transition duration-200"
            :class="collapsed ? 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100' : 'opacity-0'"
            :aria-hidden="collapsed ? 'false' : 'true'"
          >
            {{ section.label }}
          </span>
        </button>
      </div>
    </nav>

    <div class="mt-auto border-t border-white/10" :class="collapsed ? 'px-3 py-5' : 'px-5 py-5 xl:px-6'">
      <div
        :class="[
          `flex items-center gap-3 rounded-[1.25rem] border border-white/10 bg-white/10 transition-all duration-500 ${SIDEBAR_MORPH_EASE}`,
          collapsed ? 'flex-col px-2 py-3' : 'px-3 py-3.5',
        ]"
      >
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/12 text-sm font-semibold text-white transition-all duration-500" :class="SIDEBAR_MORPH_EASE">
          {{ getFooterInitials() }}
        </div>

        <div
          class="min-w-0 flex-1 overflow-hidden transition-all duration-500"
          :class="[SIDEBAR_MORPH_EASE, collapsed ? 'max-w-0 opacity-0' : 'max-w-[10rem] opacity-100']"
        >
          <p class="truncate text-sm font-semibold text-white">
            {{ props.currentUser.name || 'Production User' }}
          </p>
          <p class="mt-1 text-[10px] uppercase tracking-[0.22em] text-white/52">
            {{ props.currentUser.role || 'production' }}
          </p>
        </div>

        <button
          class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/68 transition-all duration-500 hover:border-white/20 hover:bg-white/10 hover:text-white"
          :class="[SIDEBAR_MORPH_EASE, collapsed ? '' : 'ml-auto']"
          type="button"
          :aria-label="collapsed ? 'Sign out' : 'Sign out of production dashboard'"
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
