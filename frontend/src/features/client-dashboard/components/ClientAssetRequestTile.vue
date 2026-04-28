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
    class="flex min-h-[20rem] w-full flex-col items-center justify-center rounded-[1.2rem] border border-dashed border-white/18 bg-[#10131c] px-5 py-5 text-center transition hover:-translate-y-0.5 hover:border-white/30 hover:bg-[#131827]"
    @click="emit('click')"
  >
    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-violet-500/35 bg-violet-500/10 text-violet-200">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M12 5v14" />
        <path d="M5 12h14" />
      </svg>
    </span>
    <h4 class="mt-4 text-base font-semibold text-white">{{ title }}</h4>
    <p class="mt-1 text-sm text-zinc-400">{{ subtitle }}</p>
  </button>
</template>
