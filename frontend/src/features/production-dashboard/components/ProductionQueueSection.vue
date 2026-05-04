<script setup>
defineProps({
  queueFilterMeta: {
    type: Array,
    default: () => [],
  },
  activeQueueFilter: {
    type: String,
    default: 'all',
  },
  queueRows: {
    type: Array,
    default: () => [],
  },
  updatingRequestId: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:activeQueueFilter', 'update-request-status'])
</script>

<template>
  <section class="grid gap-8 xl:grid-cols-[minmax(0,1.45fr)_22rem]">
    <div class="space-y-6">
      <section class="flex flex-wrap gap-3">
        <button
          v-for="filter in queueFilterMeta"
          :key="filter.id"
          :class="[
            'rounded-full border px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.24em] transition',
            activeQueueFilter === filter.id
              ? 'border-brand-500 bg-brand-100 text-brand-700 dark:border-white/20 dark:bg-white/10 dark:text-white'
              : 'border-border bg-white/70 text-muted hover:border-brand-500 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white',
          ]"
          @click="emit('update:activeQueueFilter', filter.id)"
        >
          {{ filter.label }}
        </button>
      </section>

      <section class="space-y-3">
        <article
          v-for="row in queueRows"
          :key="row.id"
          class="pm-surface rounded-[1.8rem] px-5 py-5 transition hover:border-brand-500 sm:px-6"
        >
          <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-3">
                <span class="text-[11px] uppercase tracking-[0.26em] text-muted dark:text-zinc-400">{{ row.reference }}</span>
                <span class="inline-flex items-center rounded-full border border-brand-300/20 bg-brand-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 dark:bg-white/10 dark:text-white">
                  {{ row.statusLabel }}
                </span>
                <span class="inline-flex items-center rounded-full border border-border bg-white/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                  {{ row.requestType }}
                </span>
              </div>

              <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-ink dark:text-white ">
                {{ row.title }}
              </h2>
              <p class="mt-3 max-w-3xl text-sm leading-7 text-muted dark:text-zinc-300">
                {{ row.description }}
              </p>

              <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px] text-muted dark:text-zinc-400">
                <span>{{ row.clientName }}</span>
                <span>/</span>
                <span>{{ row.workspace }}</span>
                <span>{{ row.fileCount }} files in workspace</span>
                <span>{{ row.dueLabel }}</span>
              </div>

              <div v-if="row.fileNames.length" class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="fileName in row.fileNames"
                  :key="fileName"
                  class="rounded-full border border-border bg-white/70 px-3 py-1 text-[11px] text-muted dark:border-white/10 dark:bg-white/5 dark:text-zinc-300"
                >
                  {{ fileName }}
                </span>
              </div>
            </div>

            <div class="w-full xl:w-[180px]">
              <select
                class="pm-input w-full rounded-2xl px-4 py-3 text-[12px] font-semibold uppercase tracking-[0.22em]"
                :disabled="updatingRequestId === row.id"
                :value="row.status"
                @change="emit('update-request-status', row.id, $event.target.value)"
              >
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
              </select>
            </div>
          </div>
        </article>

        <article
          v-if="!queueRows.length"
          class="pm-surface rounded-[1.8rem] border-dashed px-6 py-10 text-center"
        >
          <p class="text-[10px] uppercase tracking-[0.32em] text-brand-600 dark:text-brand-100">Queue clear</p>
          <h2 class="mt-3 text-2xl font-semibold text-ink dark:text-white ">
            No assigned requests match the current filter.
          </h2>
        </article>
      </section>
    </div>

    <aside class="space-y-4">
      <slot name="sidebar" />
    </aside>
  </section>
</template>
