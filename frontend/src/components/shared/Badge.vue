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
      /* Default - neutral gray */
      'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/80': variant === 'default',
      
      /* Success - semantic green (Policy: use success colors when they mean something) */
      'bg-success-50 text-success-700 border border-success-200 dark:bg-success-500/10 dark:text-success-100 dark:border-success-500/20': variant === 'success',
      
      /* Warning - semantic amber (Policy: use warning colors when they mean something) */
      'bg-warning-50 text-warning-700 border border-warning-200 dark:bg-warning-500/10 dark:text-warning-100 dark:border-warning-500/20': variant === 'warning',
      
      /* Danger - semantic red */
      'bg-danger-50 text-danger-700 border border-danger-200 dark:bg-danger-500/10 dark:text-danger-100 dark:border-danger-500/20': variant === 'danger',
      
      /* Info - brand blue-gray */
      'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-white/80': variant === 'info',
      
      /* Purple - brand primary (use when brand purple means something specific) */
      'bg-brand-50 text-brand-700 border border-brand-200 dark:bg-brand-500/10 dark:text-brand-100 dark:border-brand-500/20': variant === 'purple',
      
      /* Orange - brand warm accent */
      'bg-brand-100 text-brand-800 border border-brand-200 dark:bg-brand-500/10 dark:text-brand-100 dark:border-brand-500/20': variant === 'orange',
      
      /* Size variants - Policy: badges use xs to sm radius, status pills use full */
      'px-2 py-0.5 text-[10px]': size === 'sm',
      'px-3 py-1 text-[11px]': size === 'md',
      'px-4 py-1.5 text-sm': size === 'lg',
    }
  ]">
    <slot />
  </span>
</template>
