<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const mobileOpen = ref(false)

const navItems = [
  { label: 'Features', href: '#features' },
  { label: 'Teams', href: '#teams' },
  { label: 'Workflow', href: '#workflow-summary' },
]

function closeMenu() {
  mobileOpen.value = false
}
</script>

<template>
  <nav class="sticky top-0 z-50 border-b border-border/80 bg-[#f5f1ea]/85 backdrop-blur-xl">
    <div class="mx-auto flex min-h-[78px] max-w-[1260px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
      <a href="#top" class="flex items-center gap-3">
        <div
          class="grid h-11 w-11 place-items-center rounded-[12px] bg-[linear-gradient(145deg,#1f2937,#c2410c)] text-white shadow-[0_10px_20px_rgba(15,23,42,0.12)]"
          aria-hidden="true"
        >
          <svg
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
          >
            <path
              d="M7 3.75h7.25L19 8.5v11.75H7V3.75Z"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linejoin="round"
            />
            <path
              d="M14 4v5h5"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linejoin="round"
            />
            <path
              d="M10 12h6M10 15h6M10 18h4"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"
            />
          </svg>
        </div>

        <div class="leading-tight">
          <span class="block text-[15px] font-semibold uppercase tracking-[0.22em] text-slate-500">
            Promotional
          </span>
          <span class="block text-[18px] font-semibold text-slate-950 sm:text-[19px]">
            Materials Portal
          </span>
        </div>
      </a>

      <div class="hidden items-center gap-7 text-[14px] font-medium text-slate-600 md:flex">
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          class="transition-colors hover:text-slate-950"
        >
          {{ item.label }}
        </a>
        <RouterLink
          to="/login"
          class="transition-colors hover:text-slate-950"
        >
          Sign in
        </RouterLink>
        <RouterLink
          to="/register"
          class="inline-flex min-w-[128px] items-center justify-center rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(15,23,42,0.14)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#c2410c]"
        >
          Get started
        </RouterLink>
      </div>

      <button
        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-700 transition hover:border-slate-400 hover:bg-white md:hidden"
        aria-label="Toggle Navigation"
        @click="mobileOpen = !mobileOpen"
      >
        <svg
          v-if="!mobileOpen"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="h-6 w-6"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5"
          />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="h-6 w-6"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6 18 18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>

    <div v-if="mobileOpen" class="border-t border-border/80 bg-[#f5f1ea]/95 md:hidden">
      <div class="mx-auto flex max-w-[1260px] flex-col gap-3 px-4 py-4 sm:px-6">
        <a
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          class="rounded-full border border-border/80 bg-white/70 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-brand-500 hover:text-slate-950"
          @click="closeMenu"
        >
          {{ item.label }}
        </a>
        <RouterLink
          to="/login"
          class="rounded-full border border-border/80 bg-white/70 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-brand-500 hover:text-slate-950"
          @click="closeMenu"
        >
          Sign in
        </RouterLink>
        <RouterLink
          to="/register"
          class="inline-flex items-center justify-center rounded-full bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#c2410c]"
          @click="closeMenu"
        >
          Get started
        </RouterLink>
      </div>
    </div>
  </nav>
</template>
