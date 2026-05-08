<script setup>
import LandingSectionHeader from './LandingSectionHeader.vue'

const SearchIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2" />
      <path d="m16 16 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>
  `,
}

const ClockIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
      <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  `,
}

const FolderIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M3.5 7A2.5 2.5 0 0 1 6 4.5h4.2l2.2 2.5H18a2.5 2.5 0 0 1 2.5 2.5v7A2.5 2.5 0 0 1 18 19H6A2.5 2.5 0 0 1 3.5 16.5V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </svg>
  `,
}

const ChatIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v7A2.5 2.5 0 0 1 17.5 15H9l-5 4V5.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </svg>
  `,
}

const problems = [
  {
    title: 'Search takes too long',
    description: 'Teams lose time digging through drive folders and outdated links before they find the right asset.',
    icon: SearchIcon,
  },
  {
    title: 'Status is never obvious',
    description: 'Without one request surface, nobody knows what is approved, blocked, or ready to ship.',
    icon: ClockIcon,
  },
  {
    title: 'Folders drift apart',
    description: 'When access and ownership are unclear, the file structure turns into a pile of exceptions.',
    icon: FolderIcon,
  },
  {
    title: 'Updates happen twice',
    description: 'Teams repeat the same questions because the conversation is disconnected from the work.',
    icon: ChatIcon,
  },
]
</script>

<template>
  <section class="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="mx-auto max-w-[1260px]">
      <LandingSectionHeader
        kicker="Pain points"
        title="Scattered tools slow every handoff."
        description="When files, requests, and updates live in different places, the team spends its time chasing context instead of moving work forward."
      />

      <div class="mt-10 grid gap-6 lg:grid-cols-[0.92fr_1.08fr] lg:gap-8">
        <div class="rounded-[32px] border border-[#e3d7c7] bg-[#1f2937] p-8 text-white shadow-[0_20px_50px_rgba(15,23,42,0.12)] sm:p-10">
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/60">
            What slows teams down
          </p>

          <p class="mt-4 max-w-[38rem] text-[18px] leading-8 text-white/74 sm:text-[20px]">
            Teams lose momentum when the work lives in too many places at once.
          </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div
            v-for="item in problems"
            :key="item.title"
            class="rounded-[30px] border border-slate-200 bg-white/90 p-6 shadow-[0_14px_32px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_38px_rgba(15,23,42,0.1)]"
          >
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff7ed] text-[#c2410c]">
              <component :is="item.icon" class="h-6 w-6" />
            </div>

            <h3 class="mt-5 text-[22px] font-semibold tracking-[-0.03em] text-slate-950">
              {{ item.title }}
            </h3>

            <p class="mt-3 text-[15px] leading-7 text-slate-600">
              {{ item.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
