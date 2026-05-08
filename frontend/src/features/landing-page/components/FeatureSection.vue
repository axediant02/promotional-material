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
</script>

<template>
  <section id="features" class="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="mx-auto max-w-[1260px]">
      <LandingSectionHeader
        kicker="Capabilities"
        title="Powerful features, presented with less noise."
        description="The product should feel organized at a glance. These features are surfaced in a cleaner hierarchy so the eye can move from intent to action without distraction."
      />

      <div class="mt-10 grid gap-4 md:grid-cols-2">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="rounded-[30px] border border-slate-200 bg-white/90 p-6 shadow-[0_16px_34px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_20px_42px_rgba(15,23,42,0.1)]"
        >
          <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 flex-none items-center justify-center rounded-[18px] bg-[#fff7ed] text-[#c2410c] shadow-[0_0_0_1px_rgba(194,65,12,0.08)]">
              <component :is="feature.icon" class="h-7 w-7" />
            </div>

            <div class="flex-1">
              <h3 class="text-[24px] font-semibold tracking-[-0.03em] text-slate-950">
                {{ feature.title }}
              </h3>
              <p class="mt-3 max-w-[34rem] text-[15px] leading-7 text-slate-600">
                {{ feature.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
