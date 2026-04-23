<template>
  <section class="mt-auto rounded-[1.75rem] border border-border/70 bg-white/80 p-5 shadow-[0_18px_45px_rgba(75,61,116,0.08)] dark:border-white/10 dark:bg-white/5">
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="flex -space-x-2">
          <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-sky-500 to-blue-700 text-xs font-semibold text-white">SA</div>
          <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-400 to-emerald-500 text-xs font-semibold text-white">JR</div>
        </div>
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-muted dark:text-zinc-400">Active Support</p>
          <p class="mt-1 text-sm font-semibold text-ink dark:text-white">2 production contacts online</p>
        </div>
      </div>

      <button class="rounded-full border border-border p-3 text-muted transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:text-white dark:hover:border-white/20">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.4-4 8-9 8a9.9 9.9 0 0 1-4.3-.9L3 20l1.4-3.7A7.9 7.9 0 0 1 3 12c0-4.4 4-8 9-8s9 3.6 9 8Z" />
        </svg>
      </button>
    </div>
  </section>
</template>
