<script setup>
import { revealClass } from '../utils/motion'
import { useScrollReveal } from '../utils/useScrollReveal'

const showcaseItems = [
  {
    category: 'Featured Work',
    title: 'Campaign systems for multi-format brand launches.',
    copy: 'Packaging stills, motion previews, and release-ready exports in one governed delivery stream.',
  },
  {
    category: 'Editorial Assets',
    title: 'Lookbooks, product catalogs, and seasonal visual sets.',
    copy: 'Structured delivery for marketing teams that need revisions, approvals, and final files without scattered handoff.',
  },
  {
    category: 'Studio Delivery',
    title: 'Architectural, retail, and experiential presentation kits.',
    copy: 'A public-facing showcase narrative that mirrors the kind of polished work your clients expect to receive.',
  },
  {
    category: 'Production Pipeline',
    title: 'Operational visibility from request to final download.',
    copy: 'Production teams can keep uploads, revisions, and delivery history aligned inside a single controlled system.',
  },
]

const { element, isVisible } = useScrollReveal()
</script>

<template>
  <section ref="element" class="border-b border-white/10 py-16 sm:py-20">
    <div
      :class="[
        revealClass(isVisible),
        'flex flex-col gap-4 transition-all duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] md:flex-row md:items-end md:justify-between motion-reduce:transform-none motion-reduce:transition-none',
      ]"
    >
      <div>
        <p class="text-[11px] uppercase tracking-[0.42em] text-zinc-500">PROJECT GALLERY</p>
        <h2 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          Our works, presented as deliverable systems.
        </h2>
      </div>
    </div>

    <div class="mt-10 grid border border-white/10 md:grid-cols-2">
      <article
        v-for="(item, index) in showcaseItems"
        :key="item.title"
        :class="[
          revealClass(isVisible),
          'group border-b border-white/10 p-8 transition-all duration-[850ms] ease-[cubic-bezier(0.22,1,0.36,1)] hover:bg-white/[0.025] md:border-r md:[&:nth-child(2n)]:border-r-0 md:[&:nth-last-child(-n+2)]:border-b-0 motion-reduce:transform-none motion-reduce:transition-none',
        ]"
        :style="{ transitionDelay: `${160 + index * 110}ms` }"
      >
        <div class="flex items-start justify-between gap-4">
          <p class="text-[11px] uppercase tracking-[0.42em] text-[#f03b34]">{{ item.category }}</p>
          <span class="text-xl text-zinc-500 transition group-hover:text-white">&nearr;</span>
        </div>
        <h3 class="mt-6 max-w-md text-3xl font-semibold leading-tight tracking-[-0.03em] text-white [font-family:'Iowan_Old_Style','Palatino_Linotype','Book_Antiqua',Palatino,serif]">
          {{ item.title }}
        </h3>
        <p class="mt-4 max-w-xl text-lg leading-8 text-zinc-400">
          {{ item.copy }}
        </p>
      </article>
    </div>
  </section>
</template>
