<script setup>
defineProps({
  eyebrow: {
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
  badge: {
    type: String,
    default: '',
  },
  compact: {
    type: Boolean,
    default: false,
  },
  tone: {
    type: String,
    default: 'default',
  },
})
</script>

<template>
  <div
    :class="[
      'flex flex-col gap-4',
      compact ? 'lg:flex-row lg:items-end lg:justify-between' : 'xl:flex-row xl:items-start xl:justify-between',
    ]"
  >
    <div class="min-w-0">
      <p
        v-if="eyebrow"
        :class="[
          'text-[10px] font-semibold uppercase tracking-[0.28em]',
          tone === 'inverse' ? 'text-[#9cdcfe]' : 'text-brand-600 dark:text-brand-100',
        ]"
      >
        {{ eyebrow }}
      </p>
      <h2
        :class="[
          tone === 'inverse' ? 'text-white' : 'text-ink dark:text-white',
          compact ? 'mt-2 text-2xl font-semibold tracking-[-0.03em]' : 'mt-2 text-[1.85rem] font-semibold tracking-[-0.04em]',
        ]"
      >
        {{ title }}
      </h2>
      <p
        v-if="description"
        :class="[
          'mt-2 max-w-3xl text-sm leading-6',
          tone === 'inverse' ? 'text-zinc-400' : 'text-muted dark:text-zinc-300',
        ]"
      >
        {{ description }}
      </p>
    </div>

    <div v-if="badge || $slots.actions" class="flex flex-wrap items-center gap-3 self-start">
      <slot name="actions" />
      <span
        v-if="badge"
        :class="[
          'inline-flex rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.22em]',
          tone === 'inverse'
            ? 'border border-white/10 bg-white/5 text-white'
            : 'border border-border/80 bg-white/70 text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-brand-100',
        ]"
      >
        {{ badge }}
      </span>
    </div>
  </div>
</template>
