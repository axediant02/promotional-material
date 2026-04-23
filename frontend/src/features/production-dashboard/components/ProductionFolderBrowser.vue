<script setup>
const props = defineProps({
  folders: {
    type: Array,
    default: () => [],
  },
  selectedFolderId: {
    type: String,
    default: '',
  },
  browserMode: {
    type: String,
    default: 'grid',
  },
  activeFilter: {
    type: String,
    default: 'all',
  },
  activeSort: {
    type: String,
    default: 'recent',
  },
})

const emit = defineEmits([
  'select-folder',
  'update:browserMode',
  'update:activeFilter',
  'update:activeSort',
])

const filterOptions = [
  { id: 'all', label: 'All' },
  { id: 'needs_action', label: 'Needs action' },
  { id: 'has_requests', label: 'Has requests' },
  { id: 'recently_updated', label: 'Recently updated' },
  { id: 'empty', label: 'Empty' },
]

const sortOptions = [
  { id: 'recent', label: 'Recent activity' },
  { id: 'client_name', label: 'Client name' },
  { id: 'due_date', label: 'Due date' },
  { id: 'request_volume', label: 'Request volume' },
]

const statusClasses = {
  needs_action: 'border-amber-400/25 bg-amber-400/15 text-amber-100',
  in_progress: 'border-sky-400/25 bg-sky-400/15 text-sky-100',
  ready: 'border-emerald-400/25 bg-emerald-400/15 text-emerald-100',
  empty: 'border-white/10 bg-white/5 text-zinc-300',
}

const statusAccentClasses = {
  needs_action: 'from-amber-300/18 via-amber-400/8 to-transparent',
  in_progress: 'from-sky-300/18 via-sky-400/8 to-transparent',
  ready: 'from-emerald-300/18 via-emerald-400/8 to-transparent',
  empty: 'from-white/8 via-white/3 to-transparent',
}
</script>

<template>
  <section class="overflow-hidden rounded-[2rem] border border-slate-900/70 bg-[linear-gradient(180deg,#252526_0%,#1e1e1e_100%)] shadow-[0_24px_80px_rgba(15,23,42,0.3)]">
    <div class="border-b border-white/10 px-5 py-5">
      <div class="flex flex-col gap-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
          <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-[0.36em] text-[#9cdcfe]">Assigned browser</p>
            <h2 class="mt-2 text-2xl font-semibold text-white">Client folders</h2>
            <p class="mt-2 text-sm text-zinc-400">
              Browse every assigned workspace without losing the current selection.
            </p>
          </div>

          <div class="inline-flex rounded-xl border border-white/10 bg-black/20 p-1 self-start">
            <button
              :class="[
                'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                browserMode === 'grid'
                  ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]'
                  : 'text-zinc-300 hover:text-white',
              ]"
              @click="emit('update:browserMode', 'grid')"
            >
              Grid
            </button>
            <button
              :class="[
                'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                browserMode === 'list'
                  ? 'bg-white text-slate-900 shadow-[0_10px_24px_rgba(255,255,255,0.14)]'
                  : 'text-zinc-300 hover:text-white',
              ]"
              @click="emit('update:browserMode', 'list')"
            >
              List
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="filter in filterOptions"
              :key="filter.id"
              :class="[
                'rounded-full border px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] transition',
                activeFilter === filter.id
                  ? 'border-[#9cdcfe]/40 bg-[#9cdcfe]/15 text-[#d7f0ff]'
                  : 'border-white/10 bg-white/5 text-zinc-300 hover:border-white/20 hover:text-white',
              ]"
              @click="emit('update:activeFilter', filter.id)"
            >
              {{ filter.label }}
            </button>
          </div>

          <label class="flex items-center gap-3 text-[11px] uppercase tracking-[0.22em] text-zinc-400">
            <span>Sort</span>
            <select
              class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white outline-none"
              :value="activeSort"
              @change="emit('update:activeSort', $event.target.value)"
            >
              <option v-for="option in sortOptions" :key="option.id" :value="option.id">
                {{ option.label }}
              </option>
            </select>
          </label>
        </div>
      </div>
    </div>

    <div class="px-5 py-5">
      <div v-if="browserMode === 'grid'" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
        <button
          v-for="folder in props.folders"
          :key="folder.id"
          :class="[
            'group relative overflow-hidden rounded-[1.55rem] border p-4 text-left transition duration-200',
            selectedFolderId === folder.id
              ? 'border-[#9cdcfe]/40 bg-[linear-gradient(180deg,rgba(19,24,34,0.98),rgba(35,50,72,0.92))] shadow-[0_20px_44px_rgba(0,0,0,0.3)]'
              : 'border-white/8 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.02))] hover:-translate-y-0.5 hover:border-white/20 hover:bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.03))]',
          ]"
          @click="emit('select-folder', folder.id)"
        >
          <div :class="['pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-br opacity-90', statusAccentClasses[folder.statusTone]]" />

          <div class="relative flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="flex h-14 w-16 shrink-0 items-center justify-center rounded-[1rem] bg-[linear-gradient(180deg,#f9d978_0%,#f3c54c_62%,#ddac2f_100%)] shadow-[inset_0_1px_0_rgba(255,255,255,0.35),0_12px_24px_rgba(0,0,0,0.18)]">
                <svg class="h-9 w-9 text-[#a56a00]" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-[0.28em] text-zinc-500">Assigned workspace</p>
                <p class="mt-1 truncate text-sm font-medium text-zinc-300">{{ folder.clientName }}</p>
              </div>
            </div>
            <span :class="['rounded-full border px-2.5 py-1 text-[10px] uppercase tracking-[0.22em]', statusClasses[folder.statusTone]]">
              {{ folder.statusLabel }}
            </span>
          </div>

          <div class="relative mt-5">
            <p class="line-clamp-2 text-lg font-semibold leading-6 text-white">{{ folder.workspace }}</p>
            <p class="mt-2 max-w-[22rem] text-sm leading-6 text-zinc-400">
              {{ folder.latestActivityLabel }}{{ folder.dueDate ? ` · ${folder.dueDateLabel}` : '' }}
            </p>

            <div class="mt-5 grid grid-cols-3 gap-2">
              <div class="rounded-2xl border border-white/8 bg-black/20 px-3 py-3">
                <p class="text-[10px] uppercase tracking-[0.24em] text-zinc-500">Files</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ folder.fileCount }}</p>
              </div>
              <div class="rounded-2xl border border-white/8 bg-black/20 px-3 py-3">
                <p class="text-[10px] uppercase tracking-[0.24em] text-zinc-500">Requests</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ folder.requestCount }}</p>
              </div>
              <div class="rounded-2xl border border-white/8 bg-black/20 px-3 py-3">
                <p class="text-[10px] uppercase tracking-[0.24em] text-zinc-500">Due soon</p>
                <p class="mt-2 text-lg font-semibold text-white">{{ folder.dueSoonCount }}</p>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3 border-t border-white/8 pt-4">
              <div class="flex flex-wrap gap-2 text-[10px] uppercase tracking-[0.22em] text-zinc-500">
                <span class="rounded-full border border-white/8 bg-white/[0.04] px-2.5 py-1">{{ folder.email || 'No client email' }}</span>
              </div>
              <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9cdcfe]">
                {{ selectedFolderId === folder.id ? 'Selected' : 'Open folder' }}
              </span>
            </div>
          </div>
        </button>
      </div>

      <div v-else class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-black/20">
        <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1.15fr)_6rem_7rem_9rem] gap-4 border-b border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(255,255,255,0.03))] px-5 py-4 text-[11px] font-semibold uppercase tracking-[0.26em] text-zinc-400 md:grid">
          <span>Folder</span>
          <span>Client</span>
          <span>Files</span>
          <span>Requests</span>
          <span>Status</span>
        </div>

        <button
          v-for="folder in props.folders"
          :key="folder.id"
          :class="[
            'grid w-full gap-3 border-b border-white/8 px-5 py-4 text-left transition duration-200 last:border-b-0 md:grid-cols-[minmax(0,2fr)_minmax(0,1.15fr)_6rem_7rem_9rem] md:items-center md:gap-4',
            selectedFolderId === folder.id
              ? 'bg-[linear-gradient(90deg,rgba(156,220,254,0.12),rgba(156,220,254,0.03))]'
              : 'hover:bg-white/[0.04]',
          ]"
          @click="emit('select-folder', folder.id)"
        >
          <div class="flex min-w-0 items-start gap-4">
            <div class="mt-0.5 flex h-11 w-12 shrink-0 items-center justify-center rounded-[0.9rem] bg-[linear-gradient(180deg,#f9d978_0%,#f3c54c_62%,#ddac2f_100%)] shadow-[inset_0_1px_0_rgba(255,255,255,0.35),0_10px_20px_rgba(0,0,0,0.16)]">
              <svg class="h-7 w-7 text-[#a56a00]" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" />
              </svg>
            </div>

            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <p class="truncate text-sm font-semibold text-white">{{ folder.workspace }}</p>
                <span
                  v-if="selectedFolderId === folder.id"
                  class="rounded-full border border-[#9cdcfe]/30 bg-[#9cdcfe]/10 px-2 py-0.5 text-[10px] uppercase tracking-[0.2em] text-[#d7f0ff]"
                >
                  Selected
                </span>
              </div>
              <p class="mt-1 text-xs text-zinc-500">{{ folder.latestActivityLabel }}</p>
              <p class="mt-1 text-xs text-zinc-600">{{ folder.dueDate ? folder.dueDateLabel : 'No due date set' }}</p>
            </div>
          </div>

          <div class="min-w-0">
            <p class="truncate text-sm text-zinc-200">{{ folder.clientName }}</p>
            <p class="mt-1 truncate text-xs text-zinc-500">{{ folder.email || 'No client email' }}</p>
          </div>

          <p class="text-sm font-semibold text-white">{{ folder.fileCount }}</p>
          <div>
            <p class="text-sm font-semibold text-white">{{ folder.requestCount }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ folder.dueSoonCount }} due soon</p>
          </div>

          <div>
            <span :class="['inline-flex rounded-full border px-2.5 py-1 text-[10px] uppercase tracking-[0.22em]', statusClasses[folder.statusTone]]">
              {{ folder.statusLabel }}
            </span>
          </div>
        </button>
      </div>

      <article
        v-if="!props.folders.length"
        class="rounded-[1.6rem] border border-dashed border-white/15 bg-white/[0.03] px-6 py-12 text-center"
      >
        <p class="text-[10px] uppercase tracking-[0.32em] text-[#9cdcfe]">No folders found</p>
        <h3 class="mt-3 text-xl font-semibold text-white">No assigned folders match the current search.</h3>
      </article>
    </div>
  </section>
</template>
