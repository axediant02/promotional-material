<template>
  <section class="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="mx-auto max-w-[1260px] overflow-hidden rounded-[36px] border border-slate-200 bg-[linear-gradient(135deg,#111827,#1f2937_48%,#c2410c_140%)] px-6 py-12 text-white shadow-[0_24px_60px_rgba(15,23,42,0.16)] sm:px-8 lg:px-12 lg:py-16">
      <div class="grid gap-10 lg:grid-cols-[1fr_auto] lg:items-center">
        <div class="max-w-[760px]">
          <span class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-white/70">
            Ready to launch
          </span>

          <h2 class="mt-6 text-[clamp(2.7rem,6vw,4.8rem)] font-semibold leading-[0.98] tracking-[-0.055em]">
            Bring requests, files, and approvals into one clean workspace.
          </h2>

          <p class="mt-5 max-w-[46rem] text-[18px] leading-8 text-white/80 sm:text-[20px]">
            Give every role a clearer path from request to delivery, with less back and forth and fewer places for work to get lost.
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <a
              href="#workspace"
              class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-white px-6 text-sm font-semibold text-slate-950 transition duration-200 hover:-translate-y-0.5 hover:bg-[#fff7ed]"
            >
              Explore the workspace
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>

            <a
              href="#guide"
              class="inline-flex h-14 items-center justify-center rounded-full border border-white/20 bg-white/10 px-6 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-white/15"
            >
              View how it works
            </a>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3 lg:max-w-[360px] lg:grid-cols-1">
          <div
            v-for="item in trustPoints"
            :key="item"
            class="rounded-[24px] border border-white/10 bg-white/10 px-4 py-4 text-sm font-medium text-white/80 shadow-[0_10px_24px_rgba(15,23,42,0.08)]"
          >
            {{ item }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const trustPoints = [
  'Centralized requests',
  'Clear ownership and due dates',
  'Faster handoffs with fewer gaps',
]
</script>
