<script setup>
import LandingSectionHeader from './LandingSectionHeader.vue'

const ChatIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v7A2.5 2.5 0 0 1 17.5 15H9l-5 4V5.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </svg>
  `,
}

const BellIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M10 21h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
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

const ShieldIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 3 5 6v6c0 5 3.5 8 7 9 3.5-1 7-4 7-9V6l-7-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </svg>
  `,
}

const ChartIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M4 17 9 12l4 4 7-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M14 8h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  `,
}

const FileIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7l-5-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M14 2v5h5" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M9 13h6M9 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>
  `,
}

const features = [
  {
    title: 'Threaded communication',
    description: 'Keep conversations tied to work instead of losing them in separate chat tools.',
    icon: ChatIcon,
  },
  {
    title: 'Smart notifications',
    description: 'Get alerted when requests, approvals, and due dates actually need attention.',
    icon: BellIcon,
  },
  {
    title: 'Deadline management',
    description: 'Track timing cleanly so production and admin teams stay aligned.',
    icon: ClockIcon,
  },
  {
    title: 'Role-based security',
    description: 'Granular access control keeps each role on the right surface.',
    icon: ShieldIcon,
  },
  {
    title: 'Activity dashboard',
    description: 'See what is happening now without digging through reports.',
    icon: ChartIcon,
  },
  {
    title: 'Version control',
    description: 'Track file updates and delivery state with less manual checking.',
    icon: FileIcon,
  },
]

const highlights = [
  'Assignment chat for active threads',
  'Realtime notifications for key roles',
  'Request submission tied to live workflow',
  'Secure access with a clearer file path',
]
</script>

<template>
  <section class="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="mx-auto max-w-[1260px]">
      <LandingSectionHeader
        kicker="Product depth"
        title="Everything you need, without turning into a wall of options."
        description="The landing page should communicate breadth without looking busy. The core platform capabilities are summarized here with a calmer visual rhythm."
      />

      <div class="mt-10 grid gap-6 lg:grid-cols-[0.9fr_1.1fr] lg:gap-8">
        <div class="rounded-[32px] border border-slate-200 bg-[#111827] p-8 text-white shadow-[0_20px_50px_rgba(15,23,42,0.12)] sm:p-10">
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-white/55">
            What stays in view
          </p>

          <p class="mt-5 max-w-[36rem] text-[18px] leading-8 text-white/75">
            The core platform summary stays compact so the page can highlight the features that matter most.
          </p>

          <div class="mt-8 space-y-3">
            <div
              v-for="item in highlights"
              :key="item"
              class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-white/80"
            >
              <span class="flex h-2.5 w-2.5 rounded-full bg-[#f59e0b]" />
              <span>{{ item }}</span>
            </div>
          </div>
        </div>

        <div class="rounded-[32px] border border-slate-200 bg-white/90 p-7 shadow-[0_16px_34px_rgba(15,23,42,0.06)] sm:p-8">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-[32rem]">
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
                Feature stack
              </p>
              <h3 class="mt-2 text-[28px] font-semibold tracking-[-0.04em] text-slate-950">
                The pieces that keep the workflow moving.
              </h3>
            </div>

            <span class="w-fit rounded-full border border-[#f2c38b] bg-[#fff7ed] px-3 py-1 text-xs font-semibold text-[#b45309]">
              Six core capabilities
            </span>
          </div>

          <div class="mt-7 grid gap-4 sm:grid-cols-2">
            <div
              v-for="feature in features"
              :key="feature.title"
              class="rounded-[26px] border border-slate-200 bg-[#fffdf9] p-5 shadow-[0_10px_24px_rgba(15,23,42,0.04)]"
            >
              <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 flex-none items-center justify-center rounded-[14px] bg-[#fff7ed] text-[#c2410c]">
                  <component :is="feature.icon" class="h-5 w-5" />
                </div>

                <div>
                  <h4 class="text-[18px] font-semibold tracking-[-0.03em] text-slate-950">
                    {{ feature.title }}
                  </h4>
                  <p class="mt-2 text-[14px] leading-6 text-slate-600">
                    {{ feature.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
