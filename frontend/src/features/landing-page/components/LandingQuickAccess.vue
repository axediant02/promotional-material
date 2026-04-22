<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <aside class="border border-white/10 bg-white/[0.04] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.18)] backdrop-blur-sm">
    <p class="text-[11px] uppercase tracking-[0.42em] text-zinc-500">Quick Access</p>

    <div class="mt-5 space-y-3">
      <RouterLink
        class="flex items-center justify-center bg-[#f03b34] px-5 py-4 text-base font-semibold text-white transition hover:bg-[#ff4c45]"
        to="/login"
      >
        Sign in
      </RouterLink>

      <RouterLink
        class="flex items-center justify-center border border-white/10 px-5 py-4 text-base font-medium text-zinc-200 transition hover:border-white/25 hover:bg-white/[0.03]"
        to="/register"
      >
        Create an account
      </RouterLink>
    </div>
  </aside>
</template>
