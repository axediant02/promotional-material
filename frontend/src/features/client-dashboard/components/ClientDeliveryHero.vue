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
  <section class="pm-surface-strong mb-8 rounded-[2rem] p-7 sm:p-8">
    <div class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
      <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-muted dark:text-zinc-400">
          {{ eyebrow }} <span class="mx-2 text-brand-200 dark:text-zinc-500">/</span> {{ folder?.folder_name ?? 'Awaiting first request' }}
        </p>
        <h2 class="mt-3 text-4xl font-semibold tracking-tight text-ink dark:text-white sm:text-5xl">
          {{ title }}
          <span class="block bg-gradient-to-r from-brand-700 via-brand-600 to-brand-400 bg-clip-text text-transparent">
            {{ accent }}
          </span>
        </h2>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-muted dark:text-zinc-300">
          {{ subtitle }}
        </p>
        <div class="mt-6 flex flex-wrap items-center gap-3">
          <button
            type="button"
            class="pm-gradient-primary inline-flex items-center rounded-full px-5 py-3 text-sm font-semibold transition hover:brightness-110"
            @click="emit('action-click')"
          >
            {{ actionLabel }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
