<script setup>
import LandingQuickAccess from './LandingQuickAccess.vue'
import { revealClass } from '../utils/motion'
import { useScrollReveal } from '../utils/useScrollReveal'

const { element, isVisible } = useScrollReveal()
</script>

<template>
  <section ref="element" class="border-b border-border/80 py-16 dark:border-white/10 sm:py-20 lg:py-24">
    <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_22.5rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_32rem] xl:gap-16">
      <div
        :class="[
          revealClass(isVisible),
          'max-w-4xl transition-all duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transform-none motion-reduce:transition-none',
        ]"
      >
        <p
          :class="[
            revealClass(isVisible),
            'text-[11px] uppercase tracking-[0.5em] text-brand-500 transition-all duration-[800ms] ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transform-none motion-reduce:transition-none',
          ]"
          style="transition-delay: 80ms"
        >
          PROWEAVER - 2026
        </p>

        <h1
          :class="[
            revealClass(isVisible),
            'mt-6 max-w-5xl text-5xl font-semibold leading-[0.95] tracking-[-0.04em] text-ink transition-all duration-[950ms] ease-[cubic-bezier(0.22,1,0.36,1)] dark:text-white sm:text-6xl xl:text-[5.75rem] [font-family:\'Iowan_Old_Style\',\'Palatino_Linotype\',\'Book_Antiqua\',Palatino,serif] motion-reduce:transform-none motion-reduce:transition-none',
          ]"
          style="transition-delay: 160ms"
        >
          Keep
          <br />
          everything
          <br />
          in one place,
          <br />
          and <span class="text-brand-500">get work done faster</span>.
        </h1>

        <p
          :class="[
            revealClass(isVisible),
            'mt-8 max-w-2xl text-lg leading-9 text-muted transition-all duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] dark:text-zinc-400 sm:text-xl motion-reduce:transform-none motion-reduce:transition-none',
          ]"
          style="transition-delay: 260ms"
        >
          Let clients request what they need, track the progress, and receive their files without the back-and-forth.
        </p>
      </div>

      <LandingQuickAccess
        :class="[
          revealClass(isVisible),
          'self-start lg:mt-0.5 transition-all duration-[1000ms] ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transform-none motion-reduce:transition-none',
        ]"
        style="transition-delay: 320ms"
      />
    </div>
  </section>
</template>
