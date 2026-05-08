<template>
  <section class="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="mx-auto max-w-[1260px]">
      <div class="flex flex-col gap-4 text-left lg:max-w-[760px]">
        <span class="inline-flex w-fit items-center rounded-full border border-[#e2c18f] bg-[#fff7ed] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#b45309]">
          Why it works
        </span>

        <h2 class="text-[clamp(2.6rem,6vw,4.8rem)] font-semibold leading-[0.98] tracking-[-0.055em] text-slate-950">
          Powerful features, presented with less noise.
        </h2>

        <p class="max-w-[48rem] text-[18px] leading-8 text-slate-600 sm:text-[20px]">
          The product should feel organized at a glance. These features are surfaced in a cleaner hierarchy so the eye can move from intent to action without distraction.
        </p>
      </div>

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
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                {{ feature.kicker }}
              </p>
              <h3 class="mt-2 text-[24px] font-semibold tracking-[-0.03em] text-slate-950">
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

<script setup>
const BoltIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M13 2L4 14h6l-1 8 9-12h-6l1-8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  `,
}

const TargetIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2" />
      <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2" />
      <circle cx="12" cy="12" r="1.5" fill="currentColor" />
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

const SparklesIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 3l1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M19 15l.8 1.8L22 18l-2.2 1.2L19 21l-1.2-1.8L16 18l1.8-1.2L19 15Z" fill="currentColor" />
    </svg>
  `,
}

const features = [
  {
    kicker: 'Speed',
    title: 'Lightning fast file access',
    description:
      'Find what you need in seconds instead of hunting across folders, threads, and duplicate links.',
    icon: BoltIcon,
  },
  {
    kicker: 'Clarity',
    title: 'Progress is obvious',
    description:
      'See who owns each request, what is pending, and which items are ready without asking for a status update.',
    icon: TargetIcon,
  },
  {
    kicker: 'Control',
    title: 'Role-based access stays tight',
    description:
      'The right people see the right files, with a layout that reflects the portal’s permission model.',
    icon: ShieldIcon,
  },
  {
    kicker: 'Flow',
    title: 'Follow-ups feel automatic',
    description:
      'Reduce repetitive admin work with a workflow that keeps requests, notifications, and delivery aligned.',
    icon: SparklesIcon,
  },
]
</script>
