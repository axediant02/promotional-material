<script setup>
defineProps({
  folder: { type: Object, default: null },
  user: { type: Object, default: null },
  eyebrow: { type: String, default: 'Assigned folder active' },
  title: { type: String, default: 'Files ready for review' },
  accent: { type: String, default: 'Select an asset or download directly' },
  subtitle: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
  actionTarget: { type: String, default: '#asset-catalog' },
})

const emit = defineEmits(['action-click'])
</script>

<template>
  <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(135deg,rgba(56,26,103,0.96),rgba(15,18,28,0.98))] shadow-[0_24px_60px_rgba(0,0,0,0.28)]">
    <div class="px-6 py-7 sm:px-7 sm:py-8">
      <div class="max-w-3xl">
        <div class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.34em] text-violet-300">
          <span class="h-2 w-2 rounded-full bg-violet-400"></span>
          <span>{{ eyebrow }}</span>
        </div>
        <h2 class="mt-4 max-w-3xl text-[clamp(2.25rem,4vw,4rem)] font-semibold tracking-tight text-white">
          {{ title }}
        </h2>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-zinc-300 sm:text-[15px]">
          {{ subtitle }}
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
          <button
            type="button"
            class="pm-gradient-primary rounded-[0.9rem] px-5 py-3 text-sm font-semibold transition hover:brightness-110"
            @click="emit('action-click')"
          >
            {{ actionLabel }}
          </button>
          <button
            type="button"
            class="rounded-[0.9rem] border border-white/12 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:border-white/20 hover:bg-white/10"
            @click="emit('action-click')"
          >
            Browse Catalog
          </button>
        </div>
      </div>
      <div class="mt-8 border-t border-white/10 pt-4 text-sm text-zinc-400">
        {{ folder?.folder_name ?? 'Awaiting first request' }}
      </div>
    </div>
  </section>
</template>
