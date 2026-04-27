<script setup>
import { computed, ref } from 'vue'
import AdminDashboardRequestsSection from './AdminDashboardRequestsSection.vue'
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

const props = defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
  editingRequestId: {
    type: String,
    default: '',
  },
  dueDateDrafts: {
    type: Object,
    default: () => ({}),
  },
  savingRequestId: {
    type: String,
    default: '',
  },
  requestErrors: {
    type: Object,
    default: () => ({}),
  },
  requestFeedback: {
    type: Object,
    default: () => ({}),
  },
  startEditAction: {
    type: Function,
    required: true,
  },
  cancelEditAction: {
    type: Function,
    required: true,
  },
  updateDraftAction: {
    type: Function,
    required: true,
  },
  saveDueDateAction: {
    type: Function,
    required: true,
  },
})

const searchQuery = ref('')
const activeFilter = ref('all')

const requestFilters = [
  { id: 'all', label: 'All' },
  { id: 'needs_due_date', label: 'Needs due date' },
  { id: 'unassigned', label: 'Unassigned' },
  { id: 'needs_attention', label: 'Needs attention' },
]

const filterCounts = computed(() => ({
  all: props.rows.length,
  needs_due_date: props.rows.filter((row) => row.isMissingDueDate).length,
  unassigned: props.rows.filter((row) => row.isUnassigned).length,
  needs_attention: props.rows.filter((row) => row.needsAttention).length,
}))

const filteredEmptyCopy = computed(() => {
  if (!props.rows.length) {
    return {
      eyebrow: 'No requests',
      title: 'The request queue is clear.',
      description: 'New client requests will appear here for due-date assignment and triage.',
    }
  }

  return {
    eyebrow: 'No matching requests',
    title: 'No requests match this view.',
    description: 'Try another quick filter or adjust the search terms.',
  }
})

const filteredRows = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return props.rows.filter((row) => {
    const matchesFilter =
      activeFilter.value === 'all'
      || (activeFilter.value === 'needs_due_date' && row.isMissingDueDate)
      || (activeFilter.value === 'unassigned' && row.isUnassigned)
      || (activeFilter.value === 'needs_attention' && row.needsAttention)

    if (!matchesFilter) {
      return false
    }

    if (!query) {
      return true
    }

    const haystack = [
      row.reference,
      row.title,
      row.clientName,
      row.folderName,
      row.requestTypeLabel,
      row.status,
      row.dueLabel,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(query)
  })
})
</script>

<template>
  <section class="space-y-6">
    <DashboardSectionHeader
      eyebrow="Queue view"
      title="All requests"
      description="Full request queue view for due-date review, assignment checks, and admin triage."
      :badge="`${filteredRows.length} of ${rows.length} entries`"
      compact
    >
      <template #actions>
        <label class="relative w-full min-w-[16rem] lg:w-[21rem]">
          <span class="absolute inset-y-0 left-3 flex items-center text-zinc-400 dark:text-zinc-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="m21 21-4.35-4.35m1.6-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
            </svg>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search requests, client, folder..."
            class="w-full rounded-[1rem] border border-zinc-300 bg-white py-3 pl-9 pr-3 text-sm text-zinc-900 shadow-[0_2px_8px_rgba(15,23,42,0.04)] outline-none transition duration-180 placeholder:text-zinc-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100/80 dark:border-white/15 dark:bg-white/[0.05] dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
          />
        </label>
      </template>
    </DashboardSectionHeader>

    <div class="rounded-[1.2rem] border border-zinc-200 bg-white p-1.5 shadow-[0_8px_24px_rgba(15,23,42,0.05)] dark:border-white/10 dark:bg-white/[0.04]">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="filter in requestFilters"
          :key="filter.id"
          type="button"
          :class="[
            'inline-flex min-h-[2.6rem] items-center gap-2 rounded-[0.95rem] border px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] transition duration-180',
            activeFilter === filter.id
              ? 'border-brand-600 bg-brand-600 text-white shadow-[0_8px_18px_rgba(109,80,162,0.22)]'
              : 'border-transparent bg-transparent text-zinc-600 hover:border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900 dark:text-zinc-300 dark:hover:border-white/10 dark:hover:bg-white/[0.06] dark:hover:text-white',
          ]"
          @click="activeFilter = filter.id"
        >
          <span>{{ filter.label }}</span>
          <span
            :class="[
              'rounded-full px-2 py-0.5 text-[10px]',
              activeFilter === filter.id
                ? 'bg-white/18 text-white'
                : 'bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300',
            ]"
          >
            {{ filterCounts[filter.id] }}
          </span>
        </button>
      </div>
    </div>

    <AdminDashboardRequestsSection
      :requests="filteredRows"
      :show-header="false"
      :editing-request-id="editingRequestId"
      :due-date-drafts="dueDateDrafts"
      :saving-request-id="savingRequestId"
      :request-errors="requestErrors"
      :request-feedback="requestFeedback"
      :start-edit-action="startEditAction"
      :cancel-edit-action="cancelEditAction"
      :update-draft-action="updateDraftAction"
      :save-due-date-action="saveDueDateAction"
      :empty-eyebrow="filteredEmptyCopy.eyebrow"
      :empty-title="filteredEmptyCopy.title"
      :empty-description="filteredEmptyCopy.description"
    />
  </section>
</template>
