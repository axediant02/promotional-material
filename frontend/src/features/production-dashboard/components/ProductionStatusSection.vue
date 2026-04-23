<script setup>
defineProps({
  systemStatus: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <section class="grid grid-cols-1 gap-6 xl:grid-cols-5">
    <article class="xl:col-span-3 rounded-[2rem] border border-border/80 bg-surface p-8 shadow-[0_18px_40px_rgba(75,61,116,0.05)]">
      <div class="flex gap-6">
        <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
        </div>
        <div>
          <h3 class="mb-2 text-lg font-bold text-ink">Enterprise Grade Security</h3>
          <p class="mb-6 max-w-2xl text-sm leading-6 text-muted">
            End-to-end access controls remain active across file delivery, approvals, and production review flows. Critical actions stay role-scoped and auditable.
          </p>
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-600">
              <span class="h-2 w-2 rounded-full bg-brand-600" />
              SSL Certified
            </div>
            <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-600">
              <span class="h-2 w-2 rounded-full bg-brand-600" />
              Role Audited
            </div>
          </div>
        </div>
      </div>
    </article>

    <article class="xl:col-span-2 rounded-[2rem] border border-border/80 bg-surface p-8 shadow-[0_18px_40px_rgba(75,61,116,0.05)]">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h3 class="font-bold text-ink">System Status</h3>
          <p class="text-[10px] text-muted">Global Network: <span class="text-brand-600">Optimal</span></p>
        </div>
        <span class="rounded-full bg-brand-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-brand-600">Live</span>
      </div>
      <div class="space-y-4">
        <div v-for="item in systemStatus" :key="item.label" class="flex items-center justify-between text-xs">
          <span class="text-muted">{{ item.label }}</span>
          <span class="font-bold text-ink">{{ item.value }}</span>
        </div>
      </div>
    </article>
  </section>
</template>
