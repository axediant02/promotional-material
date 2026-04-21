<script setup>
defineProps({
  currentUser: {
    type: Object,
    default: () => ({}),
  },
})

defineEmits(['legacy-workspace', 'sign-out'])
</script>

<template>
  <aside class="flex h-full min-h-screen flex-col border-r border-slate-200/80 bg-white/92 px-6 py-7 shadow-[0_18px_45px_rgba(15,23,42,0.06)] backdrop-blur xl:sticky xl:top-0">
    <div>
      <div class="mb-8 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm">
          P
        </div>
        <div>
          <p class="font-semibold text-slate-900">Promotional Materials</p>
          <p class="text-xs text-slate-400">Production Operations</p>
        </div>
      </div>

      <div class="mb-8 flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-800">The Vault</p>
          <p class="text-[10px] uppercase tracking-[0.18em] text-slate-400">Secure Storage</p>
        </div>
      </div>

      <nav class="space-y-1.5">
        <button class="flex w-full items-center gap-3 rounded-xl bg-slate-100 px-4 py-3 text-left text-sm font-medium text-blue-600">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          Dashboard
        </button>
        <button class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          Client Folders
        </button>
        <button class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M8 7h12m0 0-4-4m4 4-4 4M16 17H4m0 0 4 4m-4-4 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          Transfers
        </button>
        <button
          class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
          @click="$emit('legacy-workspace')"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
          </svg>
          Legacy Workspace
        </button>
      </nav>
    </div>

    <div class="mt-auto space-y-4">
      <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
        <span class="text-lg leading-none">+</span>
        New Transfer
      </button>
      <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
        <p class="text-xs font-semibold text-slate-900">{{ currentUser.name || 'Production User' }}</p>
        <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-slate-400">{{ currentUser.role || 'production' }}</p>
        <button class="mt-4 text-sm font-medium text-slate-600 transition hover:text-slate-950" @click="$emit('sign-out')">Sign out</button>
      </div>
    </div>
  </aside>
</template>
