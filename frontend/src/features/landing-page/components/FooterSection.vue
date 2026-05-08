<template>
  <footer class="border-t border-slate-200 bg-[#0f172a] px-4 py-14 text-white sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1260px]">
      <div class="flex flex-col gap-12 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-[520px]">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-[12px] bg-[linear-gradient(145deg,#1f2937,#c2410c)] text-white">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
              >
                <path
                  d="M7 3.75h7.25L19 8.5v11.75H7V3.75Z"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linejoin="round"
                />
                <path
                  d="M14 4v5h5"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linejoin="round"
                />
                <path
                  d="M10 12h6M10 15h6M10 18h4"
                  stroke="currentColor"
                  stroke-width="1.7"
                  stroke-linecap="round"
                />
              </svg>
            </div>

            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/55">
                Promotional
              </p>
              <h2 class="text-[20px] font-semibold text-white">
                Materials Portal
              </h2>
            </div>
          </div>

          <p class="mt-6 max-w-[34rem] text-[16px] leading-8 text-white/70">
            A cleaner way to manage promotional materials, requests, and delivery for teams that need to move quickly.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
          <div v-for="group in links" :key="group.title">
            <h3 class="text-[13px] font-semibold uppercase tracking-[0.22em] text-white/55">
              {{ group.title }}
            </h3>

            <ul class="mt-4 space-y-3">
              <li v-for="item in group.items" :key="item">
                <a
                  href="#"
                  class="text-sm text-white/70 transition hover:text-white"
                >
                  {{ item }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="mt-12 border-t border-white/10 pt-6 text-sm text-white/45">
        <p>(c) 2026 Promotional Materials Portal. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
const links = [
  {
    title: 'Product',
    items: ['Features', 'Pricing', 'Security'],
  },
  {
    title: 'Company',
    items: ['About', 'Blog', 'Careers'],
  },
  {
    title: 'Resources',
    items: ['Documentation', 'Help Center', 'Contact'],
  },
  {
    title: 'Legal',
    items: ['Privacy', 'Terms'],
  },
]
</script>
