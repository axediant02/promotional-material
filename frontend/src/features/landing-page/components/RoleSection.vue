<script setup>
import LandingSectionHeader from './LandingSectionHeader.vue'

const UsersIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      <circle cx="10" cy="7" r="4" stroke="currentColor" stroke-width="2" />
      <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  `,
}

const FileIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>
  `,
}

const ShieldIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 3 5 6v6c0 5 3.5 8 7 9 3.5-1 7-4 7-9V6l-7-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </svg>
  `,
}

const DownloadIcon = {
  template: `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M5 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </svg>
  `,
}

const roles = [
  {
    title: 'Clients',
    kicker: 'Requesting',
    accentBar: 'bg-[#c2410c]',
    icon: UsersIcon,
    items: [
      'Submit requests instantly',
      'Track progress in real time',
      'Download approved files',
      'Message the assigned team',
    ],
  },
  {
    title: 'Production',
    kicker: 'Execution',
    accentBar: 'bg-[#9a3412]',
    icon: FileIcon,
    items: [
      'Manage assigned work',
      'Upload and organize files',
      'Update task progress',
      'Recover deleted items',
    ],
  },
  {
    title: 'Administrators',
    kicker: 'Governance',
    accentBar: 'bg-[#b45309]',
    icon: ShieldIcon,
    items: [
      'Assign work and deadlines',
      'Monitor system activity',
      'Manage user permissions',
      'Oversee all requests',
    ],
  },
  {
    title: 'Agents',
    kicker: 'Access',
    accentBar: 'bg-[#7c2d12]',
    icon: DownloadIcon,
    items: [
      'Browse approved materials',
      'Secure file downloads',
      'Simple focused interface',
      'Controlled access only',
    ],
  },
]
</script>

<template>
  <section id="teams" class="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="mx-auto max-w-[1260px]">
      <LandingSectionHeader
        kicker="Teams"
        title="Built to match how each team actually works."
        description="Clients request and download, production manages delivery, admins oversee access, and agents stay focused on approved materials only."
      />

      <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div
          v-for="role in roles"
          :key="role.title"
          class="rounded-[30px] border border-slate-200 bg-white/90 p-6 shadow-[0_16px_34px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_20px_42px_rgba(15,23,42,0.1)]"
        >
          <div class="h-1.5 rounded-full" :class="role.accentBar" />

          <div class="mt-5 flex items-start gap-4">
            <div class="flex h-14 w-14 flex-none items-center justify-center rounded-[18px] bg-[#fff7ed] text-[#c2410c] shadow-[0_0_0_1px_rgba(194,65,12,0.08)]">
              <component :is="role.icon" class="h-7 w-7" />
            </div>

            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">
                {{ role.kicker }}
              </p>
              <h3 class="mt-2 text-[24px] font-semibold tracking-[-0.03em] text-slate-950">
                {{ role.title }}
              </h3>
            </div>
          </div>

          <ul class="mt-6 space-y-3">
            <li
              v-for="item in role.items"
              :key="item"
              class="flex items-start gap-3 text-[15px] leading-7 text-slate-600"
            >
              <span class="mt-2 flex h-5 w-5 flex-none items-center justify-center rounded-full border border-[#f2c38b] bg-[#fff7ed] text-[#c2410c]">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-3 w-3"
                >
                  <path
                    d="M7 12.5 10 15.5 17 8.5"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>

              <span>{{ item }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>
