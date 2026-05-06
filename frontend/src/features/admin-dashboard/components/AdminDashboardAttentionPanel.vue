<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <section class="grid gap-4 lg:grid-cols-3">
    <article
      v-for="item in items"
      :key="item.id"
      :class="[
        'relative overflow-hidden rounded-2xl border px-5 py-5 transition-all duration-200',
        item.tone === 'danger'
          ? 'border-red-200/80 bg-gradient-to-br from-white via-red-50/50 to-red-100/30 shadow-[0_6px_24px_rgba(239,68,68,0.1)] hover:shadow-[0_10px_32px_rgba(239,68,68,0.15)] hover:-translate-y-0.5 dark:border-red-400/30 dark:from-white/[0.03] dark:via-red-500/5 dark:to-red-400/10 dark:shadow-[0_6px_24px_rgba(239,68,68,0.15)]'
          : item.tone === 'warning'
            ? 'border-amber-200/80 bg-gradient-to-br from-white via-amber-50/50 to-amber-100/30 shadow-[0_6px_24px_rgba(245,158,11,0.1)] hover:shadow-[0_10px_32px_rgba(245,158,11,0.15)] hover:-translate-y-0.5 dark:border-amber-400/30 dark:from-white/[0.03] dark:via-amber-500/5 dark:to-amber-400/10 dark:shadow-[0_6px_24px_rgba(245,158,11,0.15)]'
            : 'border-[#e0d8ec] bg-white/80 shadow-[0_4px_16px_rgba(75,61,116,0.06)] hover:shadow-[0_8px_28px_rgba(75,61,116,0.1)] hover:-translate-y-0.5 dark:border-white/12 dark:bg-white/[0.04] dark:shadow-[0_4px_20px_rgba(0,0,0,0.2)]',
      ]"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[10px] font-medium uppercase tracking-[0.3em] text-[#8a7a9e] dark:text-zinc-400">{{ item.label }}</p>
          <p
            :class="[
              'mt-3 text-3xl font-semibold tracking-[-0.03em]',
              item.tone === 'danger'
                ? 'text-red-600 dark:text-red-300'
                : item.tone === 'warning'
                  ? 'text-amber-600 dark:text-amber-300'
                  : 'text-[#1a1525] dark:text-white',
            ]"
          >
            {{ item.value }}
          </p>
        </div>
        <span
          :class="[
            'inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]',
            item.tone === 'danger'
              ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-400/40 dark:bg-red-500/15 dark:text-red-300'
              : item.tone === 'warning'
                ? 'border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-400/40 dark:bg-amber-500/15 dark:text-amber-300'
                : 'border-[#e0d8ec] bg-white/80 text-[#6b5f7a] dark:border-white/15 dark:bg-white/[0.06] dark:text-zinc-300',
          ]"
        >
          {{ item.badge }}
        </span>
      </div>
      <p class="mt-3 text-sm leading-relaxed text-[#7a6a8e] dark:text-zinc-400">{{ item.detail }}</p>

      <div
        v-if="item.tone === 'danger'"
        class="absolute -right-3 -top-3 h-20 w-20 rounded-full bg-red-200/25 dark:bg-red-400/10"
      />
      <div
        v-if="item.tone === 'warning'"
        class="absolute -right-3 -top-3 h-20 w-20 rounded-full bg-amber-200/25 dark:bg-amber-400/10"
      />
    </article>
  </section>
</template>
