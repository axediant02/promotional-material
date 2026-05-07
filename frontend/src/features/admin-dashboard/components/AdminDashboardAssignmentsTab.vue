<script setup>
import { computed, reactive, ref, watch } from 'vue'
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

const props = defineProps({
  assignments: {
    type: Array,
    default: () => [],
  },
  clientOptions: {
    type: Array,
    default: () => [],
  },
  productionOptions: {
    type: Array,
    default: () => [],
  },
  saving: {
    type: Boolean,
    default: false,
  },
  deletingId: {
    type: String,
    default: '',
  },
  saveAssignmentAction: {
    type: Function,
    required: true,
  },
  removeAssignmentAction: {
    type: Function,
    required: true,
  },
})

const form = reactive({
  client_id: '',
  production_id: '',
  manual_production_id: '',
  status: 'pending',
})
const feedback = ref('')
const submitError = ref('')

const statusStyles = {
  pending: 'bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-100',
  in_progress: 'bg-brand-100 text-brand-800 dark:bg-brand-600/25 dark:text-brand-100',
  done: 'bg-brand-200 text-brand-800 dark:bg-brand-700/30 dark:text-brand-100',
}

const selectedClient = computed(() =>
  props.clientOptions.find((client) => client.id === form.client_id) ?? null
)

const assignmentCountLabel = computed(() =>
  props.assignments.length === 1 ? '1 live assignment' : `${props.assignments.length} live assignments`
)

watch(
  () => form.client_id,
  (clientId) => {
    const existingAssignment = props.assignments.find((assignment) => assignment.clientId === clientId)

    if (existingAssignment) {
      form.production_id = existingAssignment.productionId
      form.manual_production_id = ''
      form.status = existingAssignment.status
      feedback.value = `Editing the current assignment for ${existingAssignment.clientName}. Saving will update the live ownership record.`
      submitError.value = ''
      return
    }

    if (clientId) {
      feedback.value = 'Creating a new assignment for this client.'
      submitError.value = ''
      return
    }

    feedback.value = ''
    submitError.value = ''
  }
)

const resetForm = () => {
  form.client_id = ''
  form.production_id = ''
  form.manual_production_id = ''
  form.status = 'pending'
  feedback.value = ''
  submitError.value = ''
}

const submitAssignment = async () => {
  submitError.value = ''

  const productionId = form.manual_production_id.trim() || form.production_id

  if (!form.client_id || !productionId || !form.status) {
    submitError.value = 'Select a client, choose a production owner, and set the assignment status.'
    return
  }

  try {
    await props.saveAssignmentAction({
      client_id: form.client_id,
      production_id: productionId,
      status: form.status,
    })

    const productionName =
      props.productionOptions.find((option) => option.id === productionId)?.name ??
      `Production ${productionId.slice(0, 8).toUpperCase()}`

    feedback.value = `${selectedClient.value?.name ?? 'Client'} is now assigned to ${productionName}.`
    resetForm()
  } catch (error) {
    submitError.value = error.response?.data?.message ?? 'Unable to save the assignment.'
  }
}

const removeAssignment = async (assignmentId) => {
  submitError.value = ''

  try {
    await props.removeAssignmentAction(assignmentId)
  } catch (error) {
    submitError.value = error.response?.data?.message ?? 'Unable to remove the assignment.'
  }
}
</script>

<template>
  <section class="space-y-6">
    <DashboardSectionHeader
      eyebrow="Ownership map"
      title="Assignments"
      description="Manage client-to-production ownership, reassignment, and operational coverage without leaving the dashboard."
      :badge="assignmentCountLabel"
      compact
    />

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,1.85fr)]">
      <article class="pm-surface rounded-[1.85rem] p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-brand-500">Assignment desk</p>
            <h3 class="mt-3 text-xl font-semibold text-ink dark:text-white">Assign or reassign a client</h3>
          </div>
          <div class="rounded-full bg-brand-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 dark:bg-brand-500/20 dark:text-brand-100">
            Live
          </div>
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="submitAssignment">
          <label class="block">
            <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.24em] text-muted">Client</span>
            <select v-model="form.client_id" class="pm-input w-full rounded-2xl px-4 py-3">
              <option value="">Select a client</option>
              <option v-for="client in clientOptions" :key="client.id" :value="client.id">
                {{ client.name }}{{ client.folderName ? ` - ${client.folderName}` : '' }}
              </option>
            </select>
          </label>

          <label class="block">
            <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.24em] text-muted">Production owner</span>
            <select v-model="form.production_id" class="pm-input w-full rounded-2xl px-4 py-3">
              <option value="">Select a production owner</option>
              <option v-for="production in productionOptions" :key="production.id" :value="production.id">
                {{ production.name }}{{ production.email ? ` - ${production.email}` : '' }}
              </option>
            </select>
          </label>

          <label class="block">
            <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.24em] text-muted">Manual production UUID</span>
            <input
              v-model="form.manual_production_id"
              type="text"
              class="pm-input w-full rounded-2xl px-4 py-3"
              placeholder="Use when the owner is not yet visible in the dashboard data"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-[10px] font-semibold uppercase tracking-[0.24em] text-muted">Assignment status</span>
            <select v-model="form.status" class="pm-input w-full rounded-2xl px-4 py-3">
              <option value="pending">Pending</option>
              <option value="in_progress">In progress</option>
              <option value="done">Done</option>
            </select>
          </label>

          <p v-if="feedback" class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-100">
            {{ feedback }}
          </p>
          <p v-if="submitError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ submitError }}
          </p>

          <div class="flex flex-col gap-3 sm:flex-row">
            <button
              class="pm-gradient-primary rounded-2xl px-5 py-3 text-sm font-semibold transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="saving"
            >
              {{ saving ? 'Saving assignment...' : 'Save assignment' }}
            </button>
            <button
              class="pm-button-secondary rounded-2xl px-5 py-3 text-sm font-semibold"
              type="button"
              :disabled="saving"
              @click="resetForm"
            >
              Clear
            </button>
          </div>
        </form>
      </article>

      <section class="grid gap-4 xl:grid-cols-2">
        <article
          v-for="assignment in assignments"
          :key="assignment.id"
          class="pm-surface rounded-[1.75rem] px-5 py-5 dark:border-white/10 dark:bg-[#181818]"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[10px] uppercase tracking-[0.28em] text-brand-500">Client</p>
              <h3 class="mt-2 text-lg font-semibold text-ink dark:text-white">{{ assignment.clientName }}</h3>
              <p v-if="assignment.clientEmail" class="mt-1 text-sm text-muted">{{ assignment.clientEmail }}</p>
            </div>
            <span :class="['inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', statusStyles[assignment.status] ?? statusStyles.pending]">
              {{ assignment.status.replaceAll('_', ' ') }}
            </span>
          </div>

          <div class="mt-5 space-y-3 text-sm">
            <div>
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted">Production owner</p>
              <p class="mt-1 text-ink dark:text-zinc-200">{{ assignment.productionName }}</p>
              <p v-if="assignment.productionEmail" class="mt-1 text-sm text-muted">{{ assignment.productionEmail }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted">Folder</p>
              <p class="mt-1 text-ink dark:text-zinc-200">{{ assignment.folderName }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted">Workload</p>
              <p class="mt-1 text-ink dark:text-zinc-200">{{ assignment.workload }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-[0.22em] text-muted">Notes</p>
              <p class="mt-1 leading-6 text-muted dark:text-zinc-400">{{ assignment.note }}</p>
            </div>
          </div>

          <div class="mt-5 flex items-center justify-between gap-3 border-t border-border/70 pt-4 dark:border-white/10">
            <p class="text-[10px] uppercase tracking-[0.22em] text-muted">Assignment ID: {{ assignment.id.slice(0, 8).toUpperCase() }}</p>
            <button
              class="rounded-full border border-red-200 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.22em] text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10"
              type="button"
              :disabled="deletingId === assignment.id"
              @click="removeAssignment(assignment.id)"
            >
              {{ deletingId === assignment.id ? 'Removing...' : 'Unassign' }}
            </button>
          </div>
        </article>

        <article
          v-if="!assignments.length"
          class="pm-surface rounded-[1.75rem] px-5 py-8 text-center xl:col-span-2"
        >
          <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-brand-500">No assignments yet</p>
          <h3 class="mt-3 text-xl font-semibold text-ink dark:text-white">Start by linking a client to a production owner.</h3>
          <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted dark:text-zinc-400">
            The save form on the left creates the first live assignment. Once saved, production access for that client takes effect immediately.
          </p>
        </article>
      </section>
    </section>
  </section>
</template>
