<script setup>
import { RouterLink } from 'vue-router'

const activity = [
  {
    title: 'Client request received',
    description: 'New materials request routed to production with the right folder context.',
    status: 'New',
  },
  {
    title: 'Asset upload complete',
    description: 'Approved files are ready for client access and team review.',
    status: 'Ready',
  },
]

const stats = [
  {
    value: '10x',
    label: 'Faster file access',
  },
  {
    value: '85%',
    label: 'Less follow-up noise',
  },
  {
    value: '100%',
    label: 'Request visibility',
  },
]

const CheckIcon = {
  template: `
    <svg class="h-[18px] w-[18px] flex-none text-[#c2410c]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
      <path d="M8 12.2l2.5 2.5L16.5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  `,
}
</script>
<template>
  <section class="relative overflow-hidden px-4 pb-16 pt-14 sm:px-6 lg:px-8 lg:pb-24 lg:pt-20">
    <div class="mx-auto grid max-w-[1260px] items-center gap-14 lg:grid-cols-[1.06fr_0.94fr] lg:gap-16">
      <div class="relative z-10 max-w-[720px]">
        <span class="inline-flex items-center rounded-full border border-[#e2c18f] bg-[#fff7ed] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#b45309]">
          Secure delivery workspace
        </span>

        <h1 class="mt-6 text-[clamp(3rem,7vw,5.7rem)] font-semibold leading-[0.96] tracking-[-0.055em] text-slate-950">
          One place for requests,
          <span class="block text-[#c2410c]">
            files, and handoffs.
          </span>
        </h1>

        <p class="mt-6 max-w-[640px] text-[18px] leading-[1.75] text-slate-600 sm:text-[20px]">
          Stop piecing together email threads, drive links, and status updates. The portal keeps promotional work moving with a clean request flow, role-aware access, and fast file delivery.
        </p>

        <div class="mt-9 flex flex-wrap items-center gap-3">
          <RouterLink
            to="/login"
            class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-slate-950 px-6 text-sm font-semibold text-white shadow-[0_16px_30px_rgba(15,23,42,0.16)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#c2410c]"
          >
            Get started
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </RouterLink>

          <a
            href="#demo"
            class="inline-flex h-14 items-center justify-center rounded-full border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-800 transition duration-200 hover:-translate-y-0.5 hover:border-slate-400 hover:bg-[#fffdf8]"
          >
            Watch demo
          </a>
        </div>

        <ul class="mt-8 flex flex-wrap items-center gap-3 text-sm text-slate-600">
          <li class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 py-2 shadow-[0_4px_14px_rgba(15,23,42,0.04)]">
            <CheckIcon />
            <span>No credit card required</span>
          </li>
          <li class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 py-2 shadow-[0_4px_14px_rgba(15,23,42,0.04)]">
            <CheckIcon />
            <span>14 day free trial</span>
          </li>
          <li class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 py-2 shadow-[0_4px_14px_rgba(15,23,42,0.04)]">
            <CheckIcon />
            <span>Setup in minutes</span>
          </li>
        </ul>
      </div>

      <div class="relative">
        <div class="absolute inset-0 rounded-[36px] bg-[radial-gradient(circle_at_top,_rgba(194,65,12,0.16),_transparent_55%),radial-gradient(circle_at_20%_20%,_rgba(37,99,235,0.12),_transparent_35%)] blur-3xl" />

        <div class="relative overflow-hidden rounded-[34px] border border-white/70 bg-white/90 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.12)] backdrop-blur-sm sm:p-6">
          <div class="flex items-center justify-between rounded-[24px] border border-slate-200 bg-[#fffaf2] px-4 py-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                Live workspace
              </p>
              <p class="mt-1 text-lg font-semibold text-slate-950">
                Requests in motion
              </p>
            </div>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
              12 active today
            </span>
          </div>

          <div class="mt-5 grid gap-4 sm:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-[28px] border border-slate-200 bg-[#111827] p-5 text-white shadow-[0_18px_36px_rgba(15,23,42,0.16)]">
              <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/55">
                This week
              </p>
              <div class="mt-5 flex items-end justify-between">
                <div>
                  <p class="text-4xl font-semibold tracking-[-0.05em]">84</p>
                  <p class="mt-2 text-sm text-white/70">Files delivered</p>
                </div>
                <div class="rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-medium text-white/85">
                  +18 percent
                </div>
              </div>

              <div class="mt-6 space-y-3">
                <div class="rounded-2xl bg-white/10 px-4 py-3">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-white/70">Production queue</span>
                    <span class="font-semibold text-white">07 items</span>
                  </div>
                  <div class="mt-2 h-2 rounded-full bg-white/10">
                    <div class="h-2 w-3/4 rounded-full bg-[#c2410c]" />
                  </div>
                </div>

                <div class="rounded-2xl bg-white/10 px-4 py-3">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-white/70">Client requests</span>
                    <span class="font-semibold text-white">03 open</span>
                  </div>
                  <div class="mt-2 h-2 rounded-full bg-white/10">
                    <div class="h-2 w-1/2 rounded-full bg-[#f59e0b]" />
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-[28px] border border-slate-200 bg-[#f8fafc] p-5">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                    Recent activity
                  </p>
                  <p class="mt-1 text-lg font-semibold text-slate-950">
                    Assignment feed
                  </p>
                </div>
                <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold text-white">
                  Realtime
                </span>
              </div>

              <div class="mt-5 space-y-3">
                <div
                  v-for="item in activity"
                  :key="item.title"
                  class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-[0_8px_20px_rgba(15,23,42,0.04)]"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-slate-950">
                        {{ item.title }}
                      </p>
                      <p class="mt-1 text-sm leading-6 text-slate-600">
                        {{ item.description }}
                      </p>
                    </div>
                    <span class="rounded-full bg-[#fff7ed] px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#b45309]">
                      {{ item.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div
              v-for="stat in stats"
              :key="stat.label"
              class="rounded-[24px] border border-slate-200 bg-[#fffdf9] px-4 py-4"
            >
              <p class="text-2xl font-semibold tracking-[-0.05em] text-slate-950">
                {{ stat.value }}
              </p>
              <p class="mt-1 text-sm text-slate-600">
                {{ stat.label }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
