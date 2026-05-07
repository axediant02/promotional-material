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
  { id: 'folders', label: 'Client Folders', icon: 'folder' },
  { id: 'signals', label: 'Admin Activity', icon: 'pulse' },
]

const initials = (props.currentUser?.name ?? 'Admin User')
  .split(' ')
  .map((part) => part[0])
  .join('')
  .slice(0, 2)
  .toUpperCase()
</script>

<template>
  <aside class="pm-dashboard-sidebar">
    <div class="flex min-h-full flex-col">
      <div class="border-b border-white/10 px-7 py-6">
        <p class="text-[10px] font-medium uppercase tracking-[0.38em] text-white/50">Nexus Archive</p>
        <h1 class="mt-2.5 text-[2.1rem] font-semibold tracking-[-0.04em] text-white">
          Studio
        </h1>
        <p class="mt-0.5 text-sm font-medium text-white/60">Admin management</p>
        <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-[10px] font-semibold uppercase tracking-[0.28em] text-white">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
          Admin Command
        </div>
      </div>

      <nav class="flex-1 px-4 py-5">
        <button
          v-for="item in navigationItems"
          :key="item.id"
          type="button"
          :class="[
            'group mb-1.5 flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium transition-all duration-150',
            activeItem === item.id
              ? 'pm-dashboard-sidebar-item-active'
              : 'pm-dashboard-sidebar-item',
          ]"
          @click="emit('navigate', item.id)"
        >
          <span
            :class="[
              'flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-150',
              activeItem === item.id
                ? 'bg-[#4b3d74] text-white dark:bg-brand-400/30 dark:text-white'
                : 'bg-white/10 text-white/60 group-hover:bg-white/15 group-hover:text-white dark:bg-white/[0.06] dark:text-zinc-400 dark:group-hover:bg-white/[0.1] dark:group-hover:text-white',
            ]"
          >
            <svg v-if="item.icon === 'grid'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="4" y="4" width="6" height="6" />
              <rect x="14" y="4" width="6" height="6" />
              <rect x="4" y="14" width="6" height="6" />
              <rect x="14" y="14" width="6" height="6" />
            </svg>
            <svg v-else-if="item.icon === 'list'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M9 6h11" />
              <path d="M9 12h11" />
              <path d="M9 18h11" />
              <path d="M4 6h.01" />
              <path d="M4 12h.01" />
              <path d="M4 18h.01" />
            </svg>
            <svg v-else-if="item.icon === 'user'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="8" r="4" />
              <path d="M5 20a7 7 0 0 1 14 0" />
            </svg>
            <svg v-else-if="item.icon === 'pulse'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M3 12h4l2-4 4 8 2-4h6" />
            </svg>
            <svg v-else-if="item.icon === 'folder'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
            </svg>
            <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="m10 13 4-4" />
              <path d="M7 7h5v5" />
              <path d="m14 11 3 3a2 2 0 0 1 0 3l-.5.5a2 2 0 0 1-3 0l-3-3" />
              <path d="m10 13-3-3a2 2 0 0 0-3 0l-.5.5a2 2 0 0 0 0 3l3 3" />
            </svg>
          </span>
          <span>{{ item.label }}</span>
          <span
            v-if="activeItem === item.id"
            class="ml-auto h-1.5 w-1.5 rounded-full bg-[#4b3d74] dark:bg-white"
          />
        </button>
      </nav>

      <div class="border-t border-white/10 px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-sm font-semibold text-white backdrop-blur-sm dark:border-white/10 dark:bg-white/[0.06] dark:text-white">
            {{ initials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-white dark:text-white">{{ currentUser?.name ?? 'Admin User' }}</p>
            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-white/50">{{ currentUser?.role ?? 'admin' }}</p>
          </div>
          <span class="flex h-8 w-8 items-center justify-center rounded-lg text-white/40 hover:bg-white/10 hover:text-white/70 transition-colors cursor-pointer">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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
