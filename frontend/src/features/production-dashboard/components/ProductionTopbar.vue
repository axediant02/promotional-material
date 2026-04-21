<script setup>
defineProps({
  searchQuery: {
    type: String,
    default: '',
  },
  currentUser: {
    type: Object,
    default: () => ({}),
  },
})

defineEmits(['update:searchQuery'])
</script>

<template>
  <header class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div class="relative w-full max-w-xl">
      <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="m21 21-4.35-4.35m1.6-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
      </span>
      <input
        :value="searchQuery"
        type="text"
        placeholder="Search client folders or projects..."
        class="w-full rounded-2xl border border-white/70 bg-white/80 py-3 pl-10 pr-4 text-sm text-slate-700 shadow-[0_10px_30px_rgba(15,23,42,0.04)] outline-none transition focus:border-blue-200 focus:ring-2 focus:ring-blue-100"
        @input="$emit('update:searchQuery', $event.target.value)"
      >
    </div>

    <div class="flex items-center justify-end gap-5">
      <button class="text-slate-400 transition hover:text-slate-600">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.66V5a2 2 0 1 0-4 0v.34A6 6 0 0 0 6 11v3.16c0 .53-.21 1.04-.59 1.42L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
      </button>
      <button class="text-slate-400 transition hover:text-slate-600">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="m10.33 4.32.3 1.19a1.72 1.72 0 0 1-1.05 2.05l-1.14.44a1.72 1.72 0 0 0-.97 2.23l.42 1.08a1.72 1.72 0 0 1-.97 2.23l-1.13.44a1.72 1.72 0 0 0-1.05 2.05l.3 1.19m8.62-14.9a1.72 1.72 0 0 1 2.05 1.05l.44 1.14a1.72 1.72 0 0 0 2.23.97l1.08-.42a1.72 1.72 0 0 1 2.23.97l.44 1.13a1.72 1.72 0 0 1-1.05 2.05l-1.19.3m-7.18 5.38a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
        </svg>
      </button>
      <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-slate-900 to-blue-600 text-sm font-semibold text-white">
        {{ (currentUser.name || 'P').charAt(0).toUpperCase() }}
      </div>
    </div>
  </header>
</template>
