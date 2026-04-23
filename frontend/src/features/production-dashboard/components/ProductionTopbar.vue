<script setup>
import { useThemeStore } from '../../../stores/theme'

defineProps({
  searchQuery: {
    type: String,
    default: '',
  },
  currentUser: {
    type: Object,
    default: () => ({}),
  },
  title: {
    type: String,
    default: 'Work queue.',
  },
  eyebrow: {
    type: String,
    default: 'Production team',
  },
  description: {
    type: String,
    default: '',
  },
})

defineEmits(['update:searchQuery'])

const themeStore = useThemeStore()
</script>

<template>
  <header class="border-b border-white/8 px-6 py-6 sm:px-8 lg:px-10">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
      <div class="min-w-0">
        <p class="text-[11px] uppercase tracking-[0.42em] text-brand-200/60">{{ eyebrow }}</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-[-0.045em] text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          {{ title }}
        </h1>
        <p v-if="description" class="mt-3 max-w-2xl text-sm leading-6 text-white/52">
          {{ description }}
        </p>
      </div>

      <div class="flex w-full max-w-xl items-center gap-4 lg:justify-end">
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-3 flex items-center text-white/30">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="m21 21-4.35-4.35m1.6-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
            </svg>
          </span>
          <input
            :value="searchQuery"
            type="text"
            placeholder="Search queue, files, or folders..."
            class="w-full border border-white/10 bg-white/[0.03] py-3 pl-10 pr-4 text-sm text-white outline-none transition placeholder:text-white/28 focus:border-brand-400 focus:bg-brand-500/8"
            @input="$emit('update:searchQuery', $event.target.value)"
          >
        </div>

        <button
          class="flex h-11 w-11 items-center justify-center border border-white/10 text-white/52 transition hover:border-brand-400 hover:text-brand-100"
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
