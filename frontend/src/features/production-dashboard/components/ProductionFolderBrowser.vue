<script setup>
import { computed } from 'vue'

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
  needs_action: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-100',
  in_progress: 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-300/25 dark:bg-brand-500/10 dark:text-brand-100',
  ready: 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-300/25 dark:bg-emerald-400/10 dark:text-emerald-100',
  empty: 'border-border bg-white/70 text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300',
}

const statusAccentClasses = {
  needs_action: 'from-amber-200/60 via-amber-200/20 to-transparent dark:from-amber-300/18 dark:via-amber-400/8',
  in_progress: 'from-brand-200/70 via-brand-200/25 to-transparent dark:from-brand-300/18 dark:via-brand-400/8',
  ready: 'from-emerald-200/70 via-emerald-200/25 to-transparent dark:from-emerald-300/18 dark:via-emerald-400/8',
  empty: 'from-border/70 via-border/20 to-transparent dark:from-white/8 dark:via-white/3',
}

const summaryMetrics = computed(() => {
  const dueSoon = props.folders.filter((folder) => folder.dueSoonCount > 0).length
  const active = props.folders.filter((folder) => folder.requestCount > 0).length
  const ready = props.folders.filter((folder) => folder.statusTone === 'ready').length

  return [
    {
      id: 'visible',
      label: 'Assigned folders',
      value: props.folders.length,
      tone: 'text-sky-700 dark:text-sky-100',
      border: 'border-sky-200/90 dark:border-sky-400/20',
      fill: 'bg-sky-50/80 dark:bg-sky-500/10',
      accent: 'from-sky-100 via-sky-50 to-transparent dark:from-sky-400/20 dark:via-sky-400/8',
      chip: 'text-sky-700 bg-sky-100/90 border-sky-200 dark:text-sky-100 dark:bg-sky-500/15 dark:border-sky-400/20',
    },
    {
      id: 'active',
      label: 'With requests',
      value: active,
      tone: 'text-brand-700 dark:text-brand-100',
      border: 'border-brand-200/90 dark:border-brand-400/20',
      fill: 'bg-brand-50/80 dark:bg-brand-500/10',
      accent: 'from-brand-100 via-brand-50 to-transparent dark:from-brand-400/20 dark:via-brand-400/8',
      chip: 'text-brand-700 bg-brand-100/90 border-brand-200 dark:text-brand-100 dark:bg-brand-500/15 dark:border-brand-400/20',
    },
    {
      id: 'due',
      label: 'High Priority',
      value: dueSoon,
      tone: 'text-rose-700 dark:text-rose-100',
      border: 'border-rose-200/90 dark:border-rose-400/20',
      fill: 'bg-rose-50/80 dark:bg-rose-500/10',
      accent: 'from-rose-100 via-rose-50 to-transparent dark:from-rose-400/20 dark:via-rose-400/8',
      chip: 'text-rose-700 bg-rose-100/90 border-rose-200 dark:text-rose-100 dark:bg-rose-500/15 dark:border-rose-400/20',
    },
    {
      id: 'ready',
      label: 'Ready workspaces',
      value: ready,
      tone: 'text-emerald-700 dark:text-emerald-100',
      border: 'border-emerald-200/90 dark:border-emerald-400/20',
      fill: 'bg-emerald-50/80 dark:bg-emerald-500/10',
      accent: 'from-emerald-100 via-emerald-50 to-transparent dark:from-emerald-400/20 dark:via-emerald-400/8',
      chip: 'text-emerald-700 bg-emerald-100/90 border-emerald-200 dark:text-emerald-100 dark:bg-emerald-500/15 dark:border-emerald-400/20',
    },
  ]
})
</script>

<template>
  <section class="pm-surface overflow-hidden rounded-[2rem]">
    <div class="border-b border-border/70 px-6 py-5 dark:border-white/10">
      <div class="flex flex-col gap-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
          <div class="min-w-0">
            <p class="text-[11px] uppercase tracking-[0.42em] text-brand-600 dark:text-brand-100">Assigned folders</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-ink dark:text-white">Client workspaces</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-muted dark:text-zinc-300">
              Browse your assigned client folders. Open a workspace to upload files and keep requests moving.
            </p>
          </div>

          <div class="inline-flex self-start rounded-xl border border-border/70 bg-white/70 p-1 dark:border-white/10 dark:bg-white/5">
            <button
              :class="[
                'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                props.browserMode === 'grid'
                  ? 'bg-brand-700 text-white shadow-[0_10px_24px_rgba(75,61,116,0.22)]'
                  : 'text-muted hover:text-brand-700 dark:text-zinc-300 dark:hover:text-white',
              ]"
              type="button"
              @click="emit('update:browserMode', 'grid')"
            >
              Grid
            </button>
            <button
              :class="[
                'rounded-lg px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] transition',
                props.browserMode === 'list'
                  ? 'bg-brand-700 text-white shadow-[0_10px_24px_rgba(75,61,116,0.22)]'
                  : 'text-muted hover:text-brand-700 dark:text-zinc-300 dark:hover:text-white',
              ]"
              type="button"
              @click="emit('update:browserMode', 'list')"
            >
              List
            </button>
          </div>
        </div>

        <div class="grid gap-3 lg:grid-cols-4">
          <article
            v-for="metric in summaryMetrics"
            :key="metric.id"
            :class="[
              'relative overflow-hidden rounded-[1.2rem] border bg-white/80 px-4 py-4 shadow-[0_10px_24px_rgba(15,23,42,0.05)] backdrop-blur-sm transition duration-200 hover:-translate-y-[1px] hover:shadow-[0_16px_30px_rgba(15,23,42,0.08)] dark:bg-white/[0.04]',
              metric.border,
            ]"
          >
            <div :class="['absolute inset-x-0 top-0 h-1 bg-gradient-to-r', metric.accent]" aria-hidden="true" />
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-[10px] uppercase tracking-[0.26em] text-muted dark:text-zinc-400">{{ metric.label }}</p>
                <p :class="['mt-3 text-2xl font-semibold tracking-[-0.03em]', metric.tone]">
                  {{ metric.value }}
                </p>
              </div>

              <span :class="['inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', metric.chip]">
                Live
              </span>
            </div>
          </article>
        </div>

        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="filter in filterOptions"
              :key="filter.id"
              :class="[
                'rounded-full border px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] transition',
                props.activeFilter === filter.id
                  ? 'border-brand-500 bg-brand-100 text-brand-700 dark:border-white/20 dark:bg-white/10 dark:text-white'
                  : 'border-border bg-white/70 text-muted hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white',
              ]"
              type="button"
              @click="emit('update:activeFilter', filter.id)"
            >
              {{ filter.label }}
            </button>
          </div>

          <label class="flex items-center gap-3 text-[11px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">
            <span>Sort</span>
            <select
              class="pm-input rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em]"
              :value="props.activeSort"
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

    <div class="px-6 py-6">
      <div v-if="props.browserMode === 'grid'" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
        <button
          v-for="folder in props.folders"
          :key="folder.id"
          :class="[
            'relative w-full overflow-hidden rounded-[1.7rem] border p-5 text-left shadow-[0_18px_40px_rgba(15,23,42,0.05)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_45px_rgba(15,23,42,0.08)] dark:shadow-[0_24px_70px_rgba(0,0,0,0.22)]',
            props.selectedFolderId === folder.id
              ? 'border-brand-400/60 bg-[linear-gradient(180deg,rgba(95,80,155,0.12),rgba(255,255,255,0.6))] dark:border-white/20 dark:bg-white/[0.06]'
              : 'border-border bg-white/70 hover:border-brand-500 dark:border-white/10 dark:bg-white/5 dark:hover:border-white/20',
          ]"
          type="button"
          @click="emit('select-folder', folder.id)"
        >
          <div :class="['absolute inset-0 bg-gradient-to-br opacity-80', statusAccentClasses[folder.statusTone]]" aria-hidden="true" />
          <div class="relative">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span :class="['inline-flex rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', statusClasses[folder.statusTone]]">
                    {{ folder.statusLabel }}
                  </span>
                  <span
                    v-if="folder.dueSoonCount"
                    class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-amber-900 dark:border-amber-400/25 dark:bg-amber-400/10 dark:text-amber-100"
                  >
                    {{ folder.dueSoonCount }} High Priority
                  </span>
                </div>

                <h3 class="mt-4 truncate text-xl font-semibold tracking-[-0.03em] text-ink dark:text-white">
                  {{ folder.workspace }}
                </h3>
                <p class="mt-2 truncate text-sm text-muted dark:text-zinc-300">{{ folder.clientName }}</p>
                <p class="mt-3 text-xs text-muted dark:text-zinc-400">{{ folder.latestActivityLabel }}</p>
                <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ folder.dueDateLabel }}</p>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3 border-y border-border/70 py-4 dark:border-white/10">
              <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-muted dark:text-zinc-400">Files</p>
                <p class="mt-2 text-lg font-semibold text-ink dark:text-white">{{ folder.fileCount }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-muted dark:text-zinc-400">Requests</p>
                <p class="mt-2 text-lg font-semibold text-ink dark:text-white">{{ folder.requestCount }}</p>
              </div>
              <div>
                <p class="text-[10px] uppercase tracking-[0.24em] text-muted dark:text-zinc-400">Due soon</p>
                <p class="mt-2 text-lg font-semibold text-ink dark:text-white">{{ folder.dueSoonCount }}</p>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
              <span class="truncate text-[11px] uppercase tracking-[0.22em] text-muted dark:text-zinc-400">
                {{ folder.email || 'No client email' }}
              </span>
              <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-brand-700 dark:text-brand-100">
                {{ props.selectedFolderId === folder.id ? 'Selected' : 'Open folder' }}
              </span>
            </div>
          </div>
        </button>
      </div>

      <div v-else class="overflow-hidden rounded-[1.6rem] border border-border bg-white/60 dark:border-white/10 dark:bg-white/5">
        <div class="hidden grid-cols-[minmax(0,2fr)_minmax(0,1.15fr)_6rem_7rem_9rem] gap-4 border-b border-border/70 bg-white/70 px-5 py-4 text-[11px] font-semibold uppercase tracking-[0.26em] text-muted dark:border-white/10 dark:bg-white/[0.06] dark:text-zinc-300 md:grid">
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
            'grid w-full gap-3 border-b border-border/70 px-5 py-4 text-left transition duration-200 last:border-b-0 hover:bg-brand-50/60 dark:border-white/10 dark:hover:bg-white/[0.04] md:grid-cols-[minmax(0,2fr)_minmax(0,1.15fr)_6rem_7rem_9rem] md:items-center md:gap-4',
            props.selectedFolderId === folder.id ? 'bg-brand-100/70 dark:bg-white/[0.06]' : '',
          ]"
          type="button"
          @click="emit('select-folder', folder.id)"
        >
          <div class="flex min-w-0 items-start gap-4">
            <div class="mt-0.5 flex h-11 w-12 shrink-0 items-center justify-center rounded-[0.9rem] border border-border bg-white/70 text-brand-700 shadow-[0_10px_18px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/5 dark:text-brand-100">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l1.8 2H18.5A2.5 2.5 0 0 1 21 9.5v7A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5z" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>

            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ folder.workspace }}</p>
                <span
                  v-if="props.selectedFolderId === folder.id"
                  class="rounded-full border border-brand-300/40 bg-brand-50 px-2 py-0.5 text-[10px] uppercase tracking-[0.2em] text-brand-700 dark:border-white/15 dark:bg-white/10 dark:text-white"
                >
                  Selected
                </span>
              </div>
              <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ folder.latestActivityLabel }}</p>
              <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ folder.dueDateLabel }}</p>
            </div>
          </div>

          <div class="min-w-0">
            <p class="truncate text-sm text-ink dark:text-white">{{ folder.clientName }}</p>
            <p class="mt-1 truncate text-xs text-muted dark:text-zinc-400">{{ folder.email || 'No client email' }}</p>
          </div>

          <p class="text-sm font-semibold text-ink dark:text-white">{{ folder.fileCount }}</p>
          <div>
            <p class="text-sm font-semibold text-ink dark:text-white">{{ folder.requestCount }}</p>
            <p class="mt-1 text-xs text-muted dark:text-zinc-400">{{ folder.dueSoonCount }} due soon</p>
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
        class="rounded-[1.6rem] border border-dashed border-border bg-white/50 px-6 py-12 text-center dark:border-white/10 dark:bg-white/[0.03]"
      >
        <p class="text-[10px] uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">No folders found</p>
        <h3 class="mt-3 text-xl font-semibold text-ink dark:text-white">No assigned folders match the current search.</h3>
      </article>
    </div>
  </section>
</template>
