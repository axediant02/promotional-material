<script setup>
import { useThemeStore } from '../../../stores/theme'

defineProps({
  activeItem: {
    type: String,
    default: 'overview',
  },
})

const themeStore = useThemeStore()
</script>

<template>
  <header class="border-b border-black/10 px-6 py-5 dark:border-white/10 sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <p class="text-[11px] uppercase tracking-[0.38em] text-zinc-500">Governance Overview</p>
        <h1 class="mt-2 text-4xl font-semibold tracking-[-0.045em] text-zinc-950 dark:text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          Admin desk.
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-500">
          Review new client requests, assign due dates, and keep governance decisions visible across the delivery system.
        </p>
      </div>

      <div class="flex items-center gap-3 self-start">
        <button class="inline-flex items-center gap-3 border border-black/10 px-4 py-2.5 text-sm font-medium text-zinc-950 transition hover:border-black/20 hover:bg-black/[0.03] dark:border-white/10 dark:text-white dark:hover:border-white/20 dark:hover:bg-white/[0.03]">
          <span>{{ activeItem === 'requests' ? 'Requests queue' : 'All requests' }}</span>
          <span aria-hidden="true">&rarr;</span>
        </button>
        <button
          class="flex h-11 w-11 items-center justify-center border border-black/10 text-zinc-500 transition hover:border-black/20 hover:text-zinc-950 dark:border-white/10 dark:hover:border-white/20 dark:hover:text-white"
          type="button"
          @click="themeStore.toggleTheme()"
        >
          <svg v-if="themeStore.isDark" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="3.5" />
            <path d="M12 2v3" />
            <path d="M12 19v3" />
            <path d="m4.93 4.93 2.12 2.12" />
            <path d="m16.95 16.95 2.12 2.12" />
            <path d="M2 12h3" />
            <path d="M19 12h3" />
            <path d="m4.93 19.07 2.12-2.12" />
            <path d="m16.95 7.05 2.12-2.12" />
          </svg>
          <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7.1 7.1 0 0 0 9.8 9.8Z" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>
