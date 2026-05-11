<script setup>
import LandingSectionHeader from './LandingSectionHeader.vue'

const stats = [
  {
    value: '10x',
    label: 'Faster file access when everything lives in one portal.',
  },
  {
    value: '85%',
    label: 'Less email clutter from requests, follow-ups, and approvals.',
  },
  {
    value: '100%',
    label: 'Request visibility across ownership, status, and delivery.',
  },
  {
    value: '24/7',
    label: 'Secure access for teams that need files at any hour.',
  },
]
</script>

<template>
  <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <div class="mx-auto max-w-[1260px] rounded-[32px] border border-slate-200 bg-white/80 px-5 py-6 shadow-[0_16px_40px_rgba(15,23,42,0.06)] backdrop-blur-sm sm:px-6">
      <LandingSectionHeader
        kicker="At a glance"
        title="The portal pays off quickly."
        description="The metrics below show why a single workspace matters for file delivery, request intake, and role-based access."
      />

      <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-[24px] border border-slate-200 bg-[#fffdf9] p-5 shadow-[0_10px_24px_rgba(15,23,42,0.04)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(15,23,42,0.08)]"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
                Metric
              </p>
              <h3 class="mt-3 text-[clamp(2.4rem,5vw,3.8rem)] font-semibold leading-none tracking-[-0.06em] text-slate-950">
                {{ stat.value }}
              </h3>
            </div>

            <div class="mt-1 h-3.5 w-3.5 rounded-full bg-[#c2410c] shadow-[0_0_0_6px_rgba(194,65,12,0.12)]" />
          </div>

          <p class="mt-4 max-w-[15rem] text-sm leading-6 text-slate-600">
            {{ stat.label }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
