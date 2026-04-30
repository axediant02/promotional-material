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
  <section class="relative overflow-hidden rounded-[2rem] border border-border/70 bg-[linear-gradient(135deg,rgba(255,255,255,0.96),rgba(245,239,252,0.98))] shadow-[0_24px_60px_rgba(75,61,116,0.12)] dark:border-white/10 dark:bg-[linear-gradient(135deg,rgba(56,26,103,0.96),rgba(15,18,28,0.98))] dark:shadow-[0_24px_60px_rgba(0,0,0,0.28)]">
    <div class="pointer-events-none absolute -right-20 top-[-5rem] h-56 w-56 rounded-full bg-brand-100/70 blur-3xl dark:bg-violet-500/15"></div>
    <div class="pointer-events-none absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-violet-200/70 blur-3xl dark:bg-cyan-500/10"></div>

    <div class="relative px-6 py-7 sm:px-7 sm:py-8">
      <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_18rem] xl:items-end">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.34em] text-brand-700 shadow-[0_8px_20px_rgba(75,61,116,0.08)] backdrop-blur dark:border-white/10 dark:bg-white/5 dark:text-violet-200">
            <span class="h-2 w-2 rounded-full bg-brand-500 dark:bg-violet-400"></span>
            <span>{{ eyebrow }}</span>
          </div>
          <h2 class="mt-5 max-w-3xl text-[clamp(2.2rem,4vw,4rem)] font-semibold tracking-tight text-ink dark:text-white">
            {{ title }}
          </h2>
          <p class="mt-4 max-w-2xl text-sm leading-7 text-muted sm:text-[15px] dark:text-zinc-300">
            {{ subtitle }}
          </p>
          <p class="mt-4 max-w-2xl text-sm font-medium text-brand-700 dark:text-violet-200">
            {{ accent }}
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
              class="rounded-[0.9rem] border border-border/80 bg-white/80 px-5 py-3 text-sm font-semibold text-muted shadow-[0_8px_20px_rgba(75,61,116,0.08)] transition hover:border-brand-300 hover:text-brand-700 hover:shadow-[0_10px_24px_rgba(75,61,116,0.12)] dark:border-white/12 dark:bg-white/5 dark:text-white dark:hover:border-white/20 dark:hover:bg-white/10"
              @click="emit('action-click')"
            >
              Browse Catalog
            </button>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
          <div class="rounded-[1.4rem] border border-border/70 bg-white/80 p-4 shadow-[0_12px_26px_rgba(75,61,116,0.08)] backdrop-blur dark:border-white/10 dark:bg-white/5">
            <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Folder</p>
            <p class="mt-2 truncate text-sm font-semibold text-ink dark:text-white">
              {{ folder?.folder_name ?? 'Awaiting first request' }}
            </p>
          </div>
          <div class="rounded-[1.4rem] border border-brand-200 bg-brand-50/80 p-4 shadow-[0_12px_26px_rgba(75,61,116,0.08)] backdrop-blur dark:border-white/10 dark:bg-white/5">
            <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-brand-700 dark:text-zinc-400">Next step</p>
            <p class="mt-2 text-sm font-semibold text-ink dark:text-white">Browse approved files or start a request</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
