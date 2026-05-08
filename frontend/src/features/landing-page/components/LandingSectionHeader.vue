<template>
  <div :class="['max-w-[760px]', alignClass]">
    <span
      v-if="kicker"
      class="inline-flex w-fit items-center rounded-full border border-[#e2c18f] bg-[#fff7ed] px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-[#b45309]"
    >
      {{ kicker }}
    </span>

    <h2
      class="mt-6 text-[clamp(2.6rem,6vw,4.8rem)] font-semibold leading-[0.98] tracking-[-0.055em] text-slate-950"
      :class="titleClass"
    >
      {{ title }}
    </h2>

    <p
      v-if="description"
      class="mt-5 text-[18px] leading-8 text-slate-600 sm:text-[20px]"
      :class="descriptionClass"
    >
      {{ description }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  kicker: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  align: {
    type: String,
    default: 'left',
  },
})

const alignClass = computed(() => (props.align === 'center' ? 'mx-auto text-center' : ''))
const titleClass = computed(() => (props.align === 'center' ? 'mx-auto max-w-[900px]' : ''))
const descriptionClass = computed(() => (props.align === 'center' ? 'mx-auto max-w-[760px]' : ''))
</script>
