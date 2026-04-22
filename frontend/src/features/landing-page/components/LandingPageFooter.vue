<script setup>
import { revealClass } from '../utils/motion'
import { useScrollReveal } from '../utils/useScrollReveal'

const { element, isVisible } = useScrollReveal()
</script>

<template>
  <footer
    ref="element"
    :class="[
      revealClass(isVisible),
      'flex flex-col gap-4 py-8 text-[11px] uppercase tracking-[0.36em] text-zinc-600 transition-all duration-[900ms] ease-[cubic-bezier(0.22,1,0.36,1)] sm:flex-row sm:items-center sm:justify-between motion-reduce:transform-none motion-reduce:transition-none',
    ]"
  >
    <p>&copy; PROWEAVER. All rights reserved</p>
    <p>PROWEAVER 2026</p>
  </footer>
</template>
