<script setup>
import { computed, ref } from 'vue'

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
  emptyEyebrow: {
    type: String,
    default: 'No requests',
  },
  emptyTitle: {
    type: String,
    default: 'The request queue is clear.',
  },
  emptyDescription: {
    type: String,
    default: 'New client requests will appear here for due-date assignment and triage.',
  },
})

const statusStyles = {
  pending: 'border border-slate-200 bg-slate-50 text-slate-700 dark:border-white/15 dark:bg-white/[0.06] dark:text-slate-200',
  in_progress: 'border border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-400/40 dark:bg-sky-500/15 dark:text-sky-100',
  done: 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/50 dark:bg-emerald-500/15 dark:text-emerald-100',
}

const attentionStyles = {
  danger: 'border border-red-200 bg-red-50 text-red-700 dark:border-red-400/50 dark:bg-red-500/15 dark:text-red-100',
  warning: 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-400/50 dark:bg-amber-500/15 dark:text-amber-100',
  neutral: 'border border-slate-200 bg-slate-50 text-slate-700 dark:border-white/15 dark:bg-white/[0.06] dark:text-slate-200',
  default: 'border border-slate-200 bg-white text-slate-700 dark:border-white/15 dark:bg-white/[0.05] dark:text-slate-200',
}

const assignmentStyles = {
  assigned: 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-500/15 dark:text-emerald-100',
  unassigned: 'border border-red-200 bg-red-50 text-red-700 dark:border-red-400/50 dark:bg-red-500/15 dark:text-red-100',
}

const formatStatus = (status) => {
  const labels = {
    pending: 'Pending',
    in_progress: 'In progress',
    done: 'Completed',
  }

  return labels[status ?? 'pending'] ?? 'Pending'
}

const getAssignmentLabel = (row) => {
  if (row.isUnassigned) {
    return 'Needs assignment'
  }

  if (row.assignedProductionName) {
    return `Assigned to ${row.assignedProductionName}`
  }

  return 'Assignment pending'
}

const rowToneStyles = {
  danger: 'border-red-200/90 hover:shadow-[0_12px_26px_rgba(220,38,38,0.12)] dark:border-red-400/35',
  warning: 'border-amber-200/90 hover:shadow-[0_12px_26px_rgba(217,119,6,0.12)] dark:border-amber-400/35',
  neutral: 'border-slate-200 hover:border-slate-300 hover:shadow-[0_12px_26px_rgba(15,23,42,0.08)] dark:border-white/10 dark:hover:border-white/20',
}

const selectedAssignmentRowId = ref('')

const selectedAssignmentRow = computed(() =>
  props.rows.find((row) => row.id === selectedAssignmentRowId.value) ?? null
)

const openAssignmentModal = (row) => {
  selectedAssignmentRowId.value = row.id
}

const closeAssignmentModal = () => {
  selectedAssignmentRowId.value = ''
}

</script>

<template>
  <section class="rounded-[1.4rem] border border-zinc-200/90 bg-white p-3 shadow-[0_14px_36px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/[0.03]">
    <header class="hidden gap-4 px-3 pb-3 pt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400 lg:grid lg:grid-cols-[minmax(0,2.2fr)_minmax(13rem,1fr)_minmax(10rem,0.85fr)_minmax(9rem,0.8fr)_minmax(10rem,10rem)]">
      <span>Request</span>
      <span>Attention</span>
      <span>Due date</span>
      <span>Workflow</span>
      <span class="text-right">Action</span>
    </header>

    <article
      v-for="row in rows"
      :key="row.id"
      :class="[
        'group mb-2.5 rounded-[1.1rem] border bg-white px-4 py-4 shadow-[0_2px_8px_rgba(15,23,42,0.04)] transition duration-180 last:mb-0 lg:grid lg:grid-cols-[minmax(0,2.2fr)_minmax(13rem,1fr)_minmax(10rem,0.85fr)_minmax(9rem,0.8fr)_minmax(10rem,10rem)] lg:items-start lg:gap-4',
        row.isUnassigned
          ? `${rowToneStyles.danger} hover:-translate-y-0.5 dark:bg-white/[0.04]`
          : row.isBlocked
            ? `${rowToneStyles.warning} hover:-translate-y-0.5 dark:bg-white/[0.04]`
            : `${rowToneStyles.neutral} hover:-translate-y-0.5 dark:bg-white/[0.04]`,
      ]"
    >
      <div class="min-w-0 lg:pr-4">
        <div class="flex flex-wrap items-center gap-2.5">
          <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-500 dark:text-zinc-400">#{{ row.reference }}</p>
          <span
            :class="[
              'inline-flex min-h-[1.7rem] items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]',
              statusStyles[row.status] ?? statusStyles.pending,
            ]"
          >
            {{ formatStatus(row.status) }}
          </span>
          <span
            v-if="row.needsAttention"
            :class="[
              'inline-flex min-h-[1.7rem] items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]',
              row.attentionTone === 'danger'
                ? attentionStyles.danger
                : row.attentionTone === 'warning'
                  ? attentionStyles.warning
                  : attentionStyles.neutral,
            ]"
          >
            {{ row.attentionLabel || 'Needs attention' }}
          </span>
        </div>

        <h3 class="mt-3 line-clamp-1 text-[1.02rem] font-semibold text-zinc-950 dark:text-white">{{ row.title }}</h3>
        <div class="mt-2 flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-sm">
          <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500">
            Client
            <span class="normal-case tracking-normal text-zinc-700 dark:text-zinc-200">{{ row.clientName }}</span>
          </span>
          <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500">
            Folder
            <span class="max-w-[16rem] truncate normal-case tracking-normal text-zinc-700 dark:text-zinc-200">{{ row.folderName }}</span>
          </span>
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-2">
          <span
            :class="[
              'inline-flex min-h-[1.7rem] items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]',
              assignmentStyles[row.assignmentTone] ?? assignmentStyles.assigned,
            ]"
          >
            {{ getAssignmentLabel(row) }}
          </span>
          <button
            v-if="!row.isUnassigned && row.assignedProductionName"
            type="button"
            class="inline-flex min-h-[1.7rem] items-center rounded-full border border-emerald-300 bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-700 transition duration-180 hover:border-emerald-400 hover:text-emerald-800 dark:border-emerald-400/40 dark:bg-white/[0.04] dark:text-emerald-200 dark:hover:border-emerald-300 dark:hover:text-white"
            @click="openAssignmentModal(row)"
          >
            View assignment
          </button>
          <span class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ row.requestTypeLabel }}
          </span>
        </div>
      </div>

      <div class="mt-4 text-sm lg:mt-0 lg:min-w-0">
        <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 lg:hidden">Attention</p>
        <div class="rounded-[1rem] border border-zinc-200 bg-white px-3 py-3 dark:border-white/10 dark:bg-white/[0.03]">
          <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">
            {{ row.attentionLabel || 'No attention state' }}
          </p>
          <p :class="['mt-2 leading-6', row.attentionTone === 'danger' ? 'text-red-700 dark:text-red-200' : row.attentionTone === 'warning' ? 'text-amber-700 dark:text-amber-200' : 'text-slate-700 dark:text-slate-200']">
            {{ row.attentionDetail || 'Workflow state is clear.' }}
          </p>
        </div>
      </div>

      <div class="mt-4 text-sm lg:mt-0 lg:min-w-0">
        <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 lg:hidden">Due date</p>
        <div class="rounded-[1rem] border border-zinc-200 bg-white px-3 py-3 dark:border-white/10 dark:bg-white/[0.03]">
          <template v-if="editingRequestId === row.id">
            <label class="block">
              <span class="sr-only">Due date</span>
              <input
                :value="dueDateDrafts[row.id] ?? ''"
                type="date"
                class="pm-input w-full rounded-xl border-zinc-300 bg-white px-3 py-2 text-sm shadow-[0_1px_2px_rgba(15,23,42,0.04)]"
                :disabled="savingRequestId === row.id"
                @input="updateDraftAction(row.id, $event.target.value)"
              />
            </label>
            <p v-if="requestErrors[row.id]" class="mt-2 text-xs font-medium text-red-600 dark:text-red-300">
              {{ requestErrors[row.id] }}
            </p>
            <p v-else-if="requestFeedback[row.id]" class="mt-2 text-xs font-medium text-emerald-700 dark:text-emerald-300">
              {{ requestFeedback[row.id] }}
            </p>
          </template>
          <template v-else>
            <p :class="['leading-6 font-medium', row.isMissingDueDate ? 'text-amber-700 dark:text-amber-200' : 'text-slate-700 dark:text-slate-200']">{{ row.dueLabel }}</p>
            <p v-if="row.isMissingDueDate" class="text-xs font-semibold uppercase tracking-[0.16em] text-red-700 dark:text-red-200">
              Needs due date
            </p>
            <p v-if="requestFeedback[row.id]" class="mt-2 text-xs font-medium text-emerald-700 dark:text-emerald-300">
              {{ requestFeedback[row.id] }}
            </p>
          </template>
        </div>
      </div>

      <div class="mt-4 text-sm lg:mt-0 lg:min-w-0">
        <p class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 lg:hidden">Workflow</p>
        <div class="rounded-[1rem] border border-zinc-200 bg-white px-3 py-3 dark:border-white/10 dark:bg-white/[0.03]">
          <span
            :class="[
              'inline-flex min-h-[1.7rem] items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]',
              statusStyles[row.status] ?? statusStyles.pending,
            ]"
          >
            {{ formatStatus(row.status) }}
          </span>
          <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
            {{ row.workflowStatusLabel }}
          </p>
        </div>
      </div>

      <div class="pt-1 lg:flex lg:justify-end lg:border-l lg:border-zinc-200 lg:pl-5 lg:pt-0 dark:lg:border-white/10">
        <div v-if="editingRequestId === row.id" class="flex flex-wrap items-center justify-end gap-2">
          <button
            type="button"
            class="inline-flex min-h-[2.6rem] items-center justify-center rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-700 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition duration-180 hover:scale-[1.01] hover:border-zinc-400 hover:shadow-[0_8px_18px_rgba(15,23,42,0.08)] disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/15 dark:bg-white/[0.04] dark:text-zinc-100 dark:hover:border-white/25 dark:hover:bg-white/[0.08]"
            :disabled="savingRequestId === row.id"
            @click="cancelEditAction(row.id)"
          >
            Cancel
          </button>
          <button
            type="button"
            class="inline-flex min-h-[2.6rem] items-center justify-center rounded-xl border border-brand-600 bg-brand-600 px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white shadow-[0_8px_18px_rgba(109,80,162,0.22)] transition duration-180 hover:scale-[1.01] hover:bg-brand-700 hover:shadow-[0_12px_22px_rgba(109,80,162,0.28)] disabled:cursor-not-allowed disabled:opacity-60 dark:border-brand-400 dark:bg-brand-500 dark:text-white dark:hover:bg-brand-400"
            :disabled="savingRequestId === row.id"
            @click="saveDueDateAction(row.id)"
          >
            {{ savingRequestId === row.id ? 'Saving...' : 'Save due' }}
          </button>
        </div>
        <button
          v-else
          type="button"
          :class="[
            'inline-flex min-h-[2.6rem] w-full min-w-[9rem] items-center justify-center rounded-xl px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] transition duration-180 disabled:cursor-not-allowed disabled:opacity-60 lg:w-auto',
            row.isMissingDueDate
              ? 'border border-amber-500 bg-amber-500 text-white shadow-[0_8px_18px_rgba(217,119,6,0.22)] hover:scale-[1.01] hover:bg-amber-600 hover:shadow-[0_12px_22px_rgba(217,119,6,0.28)] dark:border-amber-400 dark:bg-amber-500 dark:hover:bg-amber-400'
              : row.assignedProductionName
                ? 'border border-emerald-300 bg-white text-emerald-700 shadow-[0_1px_2px_rgba(15,23,42,0.04)] hover:scale-[1.01] hover:border-emerald-400 hover:text-emerald-800 hover:shadow-[0_8px_18px_rgba(15,23,42,0.08)] dark:border-emerald-400/40 dark:bg-white/[0.04] dark:text-emerald-200 dark:hover:border-emerald-300 dark:hover:bg-white/[0.08]'
                : 'border border-slate-300 bg-white text-slate-700 shadow-[0_1px_2px_rgba(15,23,42,0.04)] hover:scale-[1.01] hover:border-slate-400 hover:text-slate-900 hover:shadow-[0_8px_18px_rgba(15,23,42,0.08)] dark:border-white/15 dark:bg-white/[0.04] dark:text-slate-100 dark:hover:border-white/25 dark:hover:bg-white/[0.08]',
          ]"
          :disabled="Boolean(savingRequestId)"
          @click="startEditAction(row)"
        >
          {{ row.isMissingDueDate ? 'Set due date' : row.assignedProductionName ? 'View assignment' : 'Review row' }}
        </button>
      </div>
    </article>

    <div
      v-if="!rows.length"
      class="rounded-[1.15rem] border border-dashed border-zinc-300 bg-white px-6 py-14 text-center shadow-[0_2px_8px_rgba(15,23,42,0.04)] dark:border-white/15 dark:bg-white/[0.04]"
    >
      <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-zinc-400 dark:text-zinc-500">{{ emptyEyebrow }}</p>
      <h3 class="mt-3 text-xl font-semibold text-zinc-950 dark:text-white">{{ emptyTitle }}</h3>
      <p class="mx-auto mt-2 max-w-md text-sm text-zinc-500 dark:text-zinc-300">{{ emptyDescription }}</p>
    </div>

    <Teleport to="body">
      <transition
        enter-active-class="transition duration-180 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-120 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="selectedAssignmentRow"
          class="fixed inset-0 z-[140] flex items-center justify-center bg-black/45 px-4 py-8 backdrop-blur-[2px]"
          @click.self="closeAssignmentModal"
        >
          <section
            class="w-full max-w-xl rounded-[1.4rem] border border-zinc-200 bg-white p-6 shadow-[0_24px_60px_rgba(15,23,42,0.18)] dark:border-white/10 dark:bg-[#17171b]"
            role="dialog"
            aria-modal="true"
            aria-label="Assignment details"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-zinc-400 dark:text-zinc-500">Assignment details</p>
                <h3 class="mt-3 text-xl font-semibold text-zinc-950 dark:text-white">{{ selectedAssignmentRow.title }}</h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-300">Assigned to {{ selectedAssignmentRow.assignedProductionName }}</p>
              </div>

              <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-300 bg-white text-zinc-500 transition hover:border-zinc-400 hover:text-zinc-800 dark:border-white/15 dark:bg-white/[0.04] dark:text-zinc-300 dark:hover:border-white/25 dark:hover:text-white"
                aria-label="Close assignment details"
                @click="closeAssignmentModal"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path d="M6 6 18 18" />
                  <path d="m18 6-12 12" />
                </svg>
              </button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
              <div class="rounded-[1rem] border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.04]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Client</p>
                <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ selectedAssignmentRow.clientName }}</p>
              </div>
              <div class="rounded-[1rem] border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.04]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Assigned folder</p>
                <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ selectedAssignmentRow.folderName }}</p>
              </div>
              <div class="rounded-[1rem] border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.04]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Request type</p>
                <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ selectedAssignmentRow.requestTypeLabel }}</p>
              </div>
              <div class="rounded-[1rem] border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.04]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Due date</p>
                <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ selectedAssignmentRow.dueLabel }}</p>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-between rounded-[1rem] border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">Request reference</p>
                <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">#{{ selectedAssignmentRow.reference }}</p>
              </div>
              <span class="inline-flex min-h-[1.7rem] items-center rounded-full border border-zinc-300 bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-700 dark:border-white/15 dark:bg-white/[0.05] dark:text-zinc-200">
                Assigned status
              </span>
            </div>
          </section>
        </div>
      </transition>
    </Teleport>
  </section>
</template>
