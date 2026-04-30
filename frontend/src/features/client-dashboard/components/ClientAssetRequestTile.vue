<script setup>
defineProps({
  title: { type: String, default: 'Request new asset' },
  subtitle: { type: String, default: 'Or ask for an update' },
})

const emit = defineEmits(['click'])
</script>

<template>
  <button
    type="button"
    class="flex min-h-[20rem] w-full flex-col items-center justify-center rounded-[1.2rem] border border-dashed border-border/70 bg-[linear-gradient(180deg,rgba(255,255,255,0.94),rgba(247,240,253,0.94))] px-5 py-5 text-center transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-white dark:border-white/18 dark:bg-[linear-gradient(180deg,rgba(16,19,28,0.98),rgba(16,19,28,0.98))] dark:hover:border-white/30 dark:hover:bg-[linear-gradient(180deg,rgba(19,24,39,0.98),rgba(19,24,39,0.98))]"
    @click="emit('click')"
  >
    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-brand-200 bg-brand-50 text-brand-700 dark:border-violet-500/35 dark:bg-violet-500/10 dark:text-violet-200">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M12 5v14" />
        <path d="M5 12h14" />
      </svg>
    </span>
    <h4 class="mt-4 text-base font-semibold text-ink dark:text-white">{{ title }}</h4>
    <p class="mt-1 text-sm text-muted dark:text-zinc-400">{{ subtitle }}</p>
  </button>
</template>
