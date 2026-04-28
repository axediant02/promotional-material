<script setup>
import { RouterLink } from 'vue-router'
import LandingHeroSection from '../components/LandingHeroSection.vue'
import LandingPageFooter from '../components/LandingPageFooter.vue'
import LandingShowcaseSection from '../components/LandingShowcaseSection.vue'
import { useThemeStore } from '../../../stores/theme'

const themeStore = useThemeStore()
</script>

<template>
  <div class="pm-page text-ink transition-colors dark:text-zinc-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(109,80,162,0.12),transparent_24%),linear-gradient(to_right,rgba(24,24,27,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(24,24,27,0.05)_1px,transparent_1px)] bg-[size:auto,8px_8px,8px_8px] dark:bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.03),transparent_26%),linear-gradient(to_right,rgba(255,255,255,0.018)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.018)_1px,transparent_1px)]">
      <header class="border-b border-border/80 dark:border-white/10">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 sm:px-8 lg:px-10">
          <div class="flex items-center gap-3">
            <RouterLink
              to="/"
              class="text-4xl font-semibold tracking-[-0.05em] text-ink dark:text-white "
            >
              Studio<span class="text-brand-500">.</span>
            </RouterLink>
            <span class="pm-chip px-2 py-1 text-[10px] uppercase tracking-[0.28em]">V1</span>
          </div>

          <nav class="flex items-center gap-6 text-lg text-muted dark:text-zinc-400">
            <RouterLink class="transition hover:text-ink dark:hover:text-white" to="/login">Sign in</RouterLink>
            <RouterLink class="font-semibold text-ink transition hover:text-brand-500 dark:text-white" to="/register">
              Register <span aria-hidden="true">&rarr;</span>
            </RouterLink>
            <button
              class="flex h-11 w-11 items-center justify-center rounded-full border border-border/80 bg-surface/70 text-muted transition hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:hover:border-white/20 dark:hover:text-white"
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
          </nav>
        </div>
      </header>

      <main class="mx-auto max-w-7xl px-6 sm:px-8 lg:pl-10 lg:pr-10">
        <LandingHeroSection />
        <LandingShowcaseSection />
      </main>

      <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-10">
        <LandingPageFooter />
      </div>
    </div>
  </div>
</template>
