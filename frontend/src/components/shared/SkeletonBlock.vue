<script setup>
defineProps({
  width: {
    type: String,
    default: 'w-full',
  },
  height: {
    type: String,
    default: 'h-4',
  },
  rounded: {
    type: String,
    default: 'rounded',
  },
  tone: {
    type: String,
    default: 'bg-brand-50 dark:bg-white/10',
  },
  className: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div :class="['animate-pulse', width, height, rounded, tone, className]"></div>
</template>
