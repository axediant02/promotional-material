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

const getSectionButtonClass = (sectionId) => {
  const active = isSectionActive(sectionId)

  if (props.collapsed) {
    return [
      'group relative mx-auto flex h-12 w-12 items-center justify-center rounded-[18px] border text-left transition-all duration-200 ease-out hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(22,16,38,0.12)]',
      active
        ? 'border-white/20 bg-white/15 text-white shadow-[0_16px_30px_rgba(17,11,34,0.18)] backdrop-blur-md'
        : 'border-transparent bg-white/8 text-white/70 hover:border-white/15 hover:bg-white/12 hover:text-white',
    ].join(' ')
  }

  return [
    'group flex w-full items-center gap-3 rounded-2xl border px-4 py-3.5 text-left text-sm transition-all duration-200 ease-out hover:-translate-y-[1px] hover:shadow-[0_10px_24px_rgba(22,16,38,0.12)]',
    active
      ? 'border-white/15 bg-white/95 text-brand-700 shadow-[0_16px_34px_rgba(34,18,68,0.18)]'
      : 'border-transparent bg-white/8 text-white/74 hover:border-white/12 hover:bg-white/12 hover:text-white',
  ].join(' ')
}

const getSectionIconClass = (sectionId) => {
  if (props.collapsed) {
    return isSectionActive(sectionId) ? 'text-brand-600' : 'text-white/70'
  }

  return isSectionActive(sectionId) ? 'text-brand-600' : 'text-white/65'
}

const getFooterInitials = () => getInitials(props.currentUser?.name)
</script>

<template>
  <aside
    :class="[
      'pm-dashboard-sidebar flex h-full min-h-screen flex-col overflow-hidden transition-[width] duration-300 xl:sticky xl:top-0 xl:border-r xl:border-white/10',
      collapsed ? 'xl:w-[6.5rem]' : 'xl:w-[18.5rem]',
    ]"
  >
    <div
      class="border-b border-white/10 px-4 py-7"
      :class="collapsed ? 'px-2 py-5 xl:px-2' : 'xl:px-6'"
    >
      <div :class="collapsed ? 'flex flex-col items-center gap-3' : 'flex items-start justify-between gap-3'">
        <div :class="collapsed ? 'flex flex-col items-center gap-2' : 'min-w-0'">
          <div :class="collapsed ? 'hidden' : 'min-w-0'">
            <p class="text-[11px] uppercase tracking-[0.42em] text-white/60">
              Promotional Materials
            </p>
            <h1 class="mt-3 text-[2.15rem] font-semibold tracking-[-0.05em] text-white">
              Production Hub
            </h1>
          </div>
        </div>

        <button
          class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white/80 shadow-[0_12px_24px_rgba(17,11,34,0.12)] backdrop-blur-sm transition hover:-translate-y-[1px] hover:border-white/20 hover:bg-white/15 hover:text-white"
          type="button"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          @click="emit('toggle-collapse')"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 12h16" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 17h16" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>

      <p v-if="!collapsed" class="mt-4 max-w-[14rem] text-sm leading-6 text-white/65">
        Manage assigned client folders, move requests forward, and keep delivery files organized.
      </p>
    </div>

    <nav class="flex-1 px-3 py-5" :class="collapsed ? 'xl:px-2' : 'xl:px-4'">
      <p
        class="px-3 pb-3 text-[10px] uppercase tracking-[0.32em] text-white/45"
        :class="collapsed ? 'hidden' : ''"
      >
        Workspace
      </p>

      <div :class="collapsed ? 'space-y-2' : ''">
        <button
          v-for="section in sectionMeta"
          :key="section.id"
          type="button"
          :title="collapsed ? section.label : ''"
          :aria-pressed="isSectionActive(section.id)"
          :class="getSectionButtonClass(section.id)"
          @click="emit('change-section', section.id)"
        >
          <span
            class="relative flex items-center gap-3"
            :class="collapsed ? 'justify-center gap-0' : ''"
          >
            <svg
              v-if="section.icon === 'queue'"
              class="h-4 w-4 transition-transform duration-200 group-hover:scale-105"
              :class="getSectionIconClass(section.id)"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              aria-hidden="true"
            >
              <path d="M4 7h16" />
              <path d="M4 12h10" />
              <path d="M4 17h12" />
              <path d="m16 12 2 2 4-4" />
            </svg>
            <svg
              v-else-if="section.icon === 'files'"
              class="h-4 w-4 transition-transform duration-200 group-hover:scale-105"
              :class="getSectionIconClass(section.id)"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              aria-hidden="true"
            >
              <path d="M14 3v5h5" />
              <path d="M5 8.5V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3.5" />
              <path d="M3 14h11" />
              <path d="M3 18h8" />
            </svg>
            <svg
              v-else
              class="h-4 w-4 transition-transform duration-200 group-hover:scale-105"
              :class="getSectionIconClass(section.id)"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              aria-hidden="true"
            >
              <path d="M4 7h16" />
              <path d="M9 7V5h6v2" />
              <path d="M7 7v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7" />
              <path d="M10 11v5" />
              <path d="M14 11v5" />
            </svg>
            <span :class="collapsed ? 'hidden' : ''">{{ section.label }}</span>

            <span
              v-if="collapsed && isSectionActive(section.id)"
              class="absolute -bottom-2 left-1/2 h-1.5 w-5 -translate-x-1/2 rounded-full bg-white/85"
            />
          </span>

          <span
            v-if="collapsed"
            class="pointer-events-none absolute left-full top-1/2 z-20 ml-3 -translate-y-1/2 whitespace-nowrap rounded-full border border-white/15 bg-slate-950/95 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-white opacity-0 shadow-lg backdrop-blur-md transition duration-200 group-hover:opacity-100 group-focus-visible:opacity-100"
          >
            {{ section.label }}
          </span>

          <span
            v-if="!collapsed"
            :class="[
              'ml-auto text-[11px] uppercase tracking-[0.22em] transition',
              isSectionActive(section.id) ? 'text-brand-500' : 'text-white/45',
            ]"
          >
            {{ props.sectionCounts[section.id] ?? 0 }}
          </span>
        </button>
      </div>
    </nav>

    <div class="mt-auto border-t border-white/10 px-4 py-5" :class="collapsed ? 'px-2 xl:px-2' : 'xl:px-6'">
      <div
        :class="[
          'flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 shadow-[0_14px_26px_rgba(22,16,38,0.18)] backdrop-blur-sm',
          collapsed ? 'mx-auto flex w-full max-w-[4.25rem] flex-col items-center gap-2 px-2 py-3' : '',
        ]"
      >
        <div
          class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/10 text-sm font-semibold text-white"
        >
          {{ getFooterInitials() }}
        </div>

        <div class="min-w-0 flex-1" :class="collapsed ? 'hidden' : ''">
          <p class="truncate text-sm font-semibold text-white">
            {{ props.currentUser.name || 'Production User' }}
          </p>
          <p class="mt-1 text-[10px] uppercase tracking-[0.24em] text-white/55">
            {{ props.currentUser.role || 'production' }}
          </p>
        </div>

        <button
          class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 text-white/65 transition hover:-translate-y-[1px] hover:border-white/20 hover:bg-white/10 hover:text-white"
          :class="collapsed ? '' : 'ml-auto'"
          type="button"
          :aria-label="collapsed ? 'Sign out' : 'Sign out of production dashboard'"
          @click="emit('sign-out')"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M15 7h4v10h-4" />
            <path d="m10 17 5-5-5-5" />
            <path d="M15 12H3" />
          </svg>
        </button>
      </div>
    </div>
  </aside>
</template>
