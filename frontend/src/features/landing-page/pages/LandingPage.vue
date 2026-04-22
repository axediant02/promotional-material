<script setup>
import { RouterLink } from 'vue-router'
import LandingHeroSection from '../components/LandingHeroSection.vue'
import LandingPageFooter from '../components/LandingPageFooter.vue'
import LandingShowcaseSection from '../components/LandingShowcaseSection.vue'
</script>

<template>
  <div class="min-h-screen bg-[#111111] text-zinc-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.03),transparent_26%),linear-gradient(to_right,rgba(255,255,255,0.018)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.018)_1px,transparent_1px)] bg-[size:auto,8px_8px,8px_8px]">
      <header class="border-b border-white/10">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 sm:px-8 lg:px-10">
          <div class="flex items-center gap-3">
            <RouterLink
              to="/"
              class="text-4xl font-semibold tracking-[-0.05em] text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]"
            >
              Studio<span class="text-[#f03b34]">.</span>
            </RouterLink>
            <span class="border border-white/10 px-2 py-1 text-[10px] uppercase tracking-[0.28em] text-zinc-500">V1</span>
          </div>

          <nav class="flex items-center gap-6 text-lg text-zinc-400">
            <RouterLink class="transition hover:text-white" to="/login">Sign in</RouterLink>
            <RouterLink class="font-semibold text-white transition hover:text-[#f03b34]" to="/register">
              Register <span aria-hidden="true">→</span>
            </RouterLink>
            <button class="flex h-11 w-11 items-center justify-center border border-white/10 text-zinc-500 transition hover:border-white/20 hover:text-white">
              <span aria-hidden="true">☼</span>
            </button>
          </nav>
        </div>
      </header>

      <main class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-10">
        <LandingHeroSection />
        <LandingShowcaseSection />
      </main>

      <div class="mx-auto max-w-6xl px-6 sm:px-8 lg:px-10">
        <LandingPageFooter />
      </div>
    </div>
  </div>
</template>
