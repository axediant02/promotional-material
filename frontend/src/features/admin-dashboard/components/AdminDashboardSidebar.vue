<script setup>
const props = defineProps({
  currentUser: {
    type: Object,
    default: () => ({}),
  },
  activeItem: {
    type: String,
    default: 'overview',
  },
})

const emit = defineEmits(['navigate'])

const navigationItems = [
  { id: 'overview', label: 'Overview', icon: 'grid' },
  { id: 'requests', label: 'All Requests', icon: 'list' },
  { id: 'users', label: 'Users & Roles', icon: 'user' },
  { id: 'assignments', label: 'Assignments', icon: 'link' },
]

const initials = (props.currentUser?.name ?? 'Admin User')
  .split(' ')
  .map((part) => part[0])
  .join('')
  .slice(0, 2)
  .toUpperCase()
</script>

<template>
  <aside class="border-r border-border/80 bg-brand-50 text-ink dark:border-white/10 dark:bg-[#181818] dark:text-zinc-200">
    <div class="flex min-h-full flex-col">
      <div class="border-b border-border/80 px-8 py-6 dark:border-white/10">
        <p class="text-[11px] uppercase tracking-[0.42em] text-muted">Work &amp; Flow</p>
        <h1 class="mt-3 text-[2.4rem] font-semibold tracking-[-0.05em] text-brand-600 [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          Studio.
        </h1>
        <div class="mt-6 inline-flex items-center gap-2 border border-border/80 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.32em] text-ink dark:border-white/10 dark:text-white">
          <span class="text-brand-500">&bull;</span>
          Admin
        </div>
      </div>

      <nav class="flex-1 px-4 py-5">
        <button
          v-for="item in navigationItems"
          :key="item.id"
          type="button"
          :class="[
            'mb-2 flex w-full items-center gap-3 px-4 py-3.5 text-left text-sm transition',
            activeItem === item.id
              ? 'border-l-2 border-brand-500 bg-brand-100 text-ink dark:bg-white/[0.04] dark:text-white'
              : 'border-l-2 border-transparent text-muted hover:bg-brand-100 hover:text-ink dark:text-zinc-400 dark:hover:bg-white/[0.03] dark:hover:text-white',
          ]"
          @click="emit('navigate', item.id)"
        >
          <span class="text-zinc-500">
            <svg v-if="item.icon === 'grid'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="4" y="4" width="6" height="6" />
              <rect x="14" y="4" width="6" height="6" />
              <rect x="4" y="14" width="6" height="6" />
              <rect x="14" y="14" width="6" height="6" />
            </svg>
            <svg v-else-if="item.icon === 'list'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M9 6h11" />
              <path d="M9 12h11" />
              <path d="M9 18h11" />
              <path d="M4 6h.01" />
              <path d="M4 12h.01" />
              <path d="M4 18h.01" />
            </svg>
            <svg v-else-if="item.icon === 'user'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="8" r="4" />
              <path d="M5 20a7 7 0 0 1 14 0" />
            </svg>
            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="m10 13 4-4" />
              <path d="M7 7h5v5" />
              <path d="m14 11 3 3a2 2 0 0 1 0 3l-.5.5a2 2 0 0 1-3 0l-3-3" />
              <path d="m10 13-3-3a2 2 0 0 0-3 0l-.5.5a2 2 0 0 0 0 3l3 3" />
            </svg>
          </span>
          <span>{{ item.label }}</span>
        </button>
      </nav>

      <div class="border-t border-black/10 px-6 py-5 dark:border-white/10">
        <div class="flex items-center gap-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-full border border-border/80 bg-brand-100 text-sm font-semibold text-ink dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
            {{ initials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-lg font-medium text-ink dark:text-white">{{ currentUser?.name ?? 'Admin User' }}</p>
            <p class="text-[11px] uppercase tracking-[0.28em] text-muted">{{ currentUser?.role ?? 'admin' }}</p>
          </div>
          <span class="text-muted">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M13 5h6v6" />
              <path d="m19 5-8 8" />
              <path d="M11 19H5V13" />
            </svg>
          </span>
        </div>
      </div>
    </div>
  </aside>
</template>
