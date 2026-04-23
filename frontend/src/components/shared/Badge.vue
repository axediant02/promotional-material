<script setup>
defineProps({
  variant: {
    type: String,
    default: 'default', // default, success, warning, danger, info, purple
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
  },
})
</script>

<template>
  <span :class="[
    'inline-flex items-center rounded-full font-medium capitalize',
    {
      'bg-slate-100 text-slate-700': variant === 'default',
      'bg-brand-50 text-brand-700': variant === 'success',
      'bg-brand-100 text-brand-800': variant === 'warning',
      'bg-red-100 text-red-700': variant === 'danger',
      'bg-brand-100 text-brand-700': variant === 'info',
      'bg-brand-50 text-brand-700': variant === 'purple',
      'bg-brand-200 text-brand-800': variant === 'orange',
      'px-2 py-0.5 text-xs': size === 'sm',
      'px-3 py-1 text-xs': size === 'md',
      'px-4 py-1.5 text-sm': size === 'lg',
    }
  ]">
    <slot />
  </span>
</template>
