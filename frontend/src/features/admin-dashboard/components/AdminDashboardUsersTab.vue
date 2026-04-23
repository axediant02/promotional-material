<script setup>
defineProps({
  users: {
    type: Array,
    default: () => [],
  },
})

const roleStyles = {
  admin: 'bg-[#fbe1de] text-[#d73931] dark:bg-[#3b1715] dark:text-[#f06753]',
  production: 'bg-[#e7eef9] text-[#2d5b9d] dark:bg-[#162235] dark:text-[#91b7ff]',
  agent: 'bg-[#e8f4eb] text-[#2f7a45] dark:bg-[#15281d] dark:text-[#77d18f]',
  client: 'bg-black/[0.04] text-zinc-700 dark:bg-white/[0.04] dark:text-zinc-300',
}
</script>

<template>
  <section class="space-y-6">
    <header class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-zinc-950 dark:text-white">Users &amp; roles</h2>
      <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Governance view for role ownership, account state, and user access posture.
      </p>
    </header>

    <section class="overflow-hidden border border-black/10 bg-white/65 dark:border-white/10 dark:bg-[#181818]">
      <header class="hidden grid-cols-[minmax(0,1.4fr)_minmax(7rem,0.6fr)_minmax(7rem,0.6fr)_minmax(0,1fr)] gap-4 border-b border-black/10 px-5 py-4 text-[10px] font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:border-white/10 lg:grid">
        <span>User</span>
        <span>Role</span>
        <span>Status</span>
        <span>Notes</span>
      </header>

      <article
        v-for="user in users"
        :key="user.id"
        class="border-b border-black/10 px-5 py-5 last:border-b-0 dark:border-white/10 lg:grid lg:grid-cols-[minmax(0,1.4fr)_minmax(7rem,0.6fr)_minmax(7rem,0.6fr)_minmax(0,1fr)] lg:gap-4"
      >
        <div>
          <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ user.name }}</p>
          <p class="mt-1 text-sm text-zinc-500">{{ user.email }}</p>
        </div>

        <div class="mt-4 lg:mt-0">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-500 lg:hidden">Role</p>
          <span :class="['inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', roleStyles[user.role] ?? roleStyles.client]">
            {{ user.role }}
          </span>
        </div>

        <div class="mt-4 lg:mt-0">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-500 lg:hidden">Status</p>
          <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ user.status }}</p>
        </div>

        <div class="mt-4 lg:mt-0">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-500 lg:hidden">Notes</p>
          <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ user.note }}</p>
        </div>
      </article>
    </section>
  </section>
</template>
