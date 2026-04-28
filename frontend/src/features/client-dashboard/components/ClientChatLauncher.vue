<script setup>
defineProps({
  badgeCount: { type: Number, default: 0 },
  open: { type: Boolean, default: false },
})

const emit = defineEmits(['toggle'])
</script>

<template>
  <button
    type="button"
    class="fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full shadow-[0_16px_40px_rgba(124,58,237,0.38)] transition hover:-translate-y-0.5 hover:brightness-110"
    :class="open ? 'bg-emerald-500 text-white' : 'bg-[#7c3aed] text-white'"
    @pointerdown.stop
    @click="emit('toggle')"
  >
    <svg v-if="open" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M6 6l12 12" />
      <path d="M18 6 6 18" />
    </svg>
    <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M21 15a4 4 0 0 1-4 4H8l-5 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" />
    </svg>
    <span
      v-if="badgeCount > 0"
      class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white shadow-[0_6px_16px_rgba(225,29,72,0.35)]"
    >
      {{ badgeCount > 99 ? '99+' : badgeCount }}
    </span>
  </button>
</template>
