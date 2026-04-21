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
      'bg-green-100 text-green-700': variant === 'success',
      'bg-yellow-100 text-yellow-700': variant === 'warning',
      'bg-red-100 text-red-700': variant === 'danger',
      'bg-blue-100 text-blue-700': variant === 'info',
      'bg-purple-100 text-purple-700': variant === 'purple',
      'bg-orange-100 text-orange-700': variant === 'orange',
      'px-2 py-0.5 text-xs': size === 'sm',
      'px-3 py-1 text-xs': size === 'md',
      'px-4 py-1.5 text-sm': size === 'lg',
    }
  ]">
    <slot />
  </span>
</template>
