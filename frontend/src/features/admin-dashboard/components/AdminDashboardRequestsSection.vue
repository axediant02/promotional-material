<script setup>
import AdminDashboardQueueTable from './AdminDashboardQueueTable.vue'

defineProps({
  requests: {
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
</script>

<template>
  <section>
    <div class="mb-4 flex items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-semibold text-ink dark:text-white">All requests</h2>
        <p class="mt-1 text-sm text-muted dark:text-zinc-300">Fresh queue items that need due dates, assignment, or governance review.</p>
      </div>
      <p class="rounded-full bg-brand-50 px-3 py-1 text-[11px] uppercase tracking-[0.34em] text-brand-700 dark:bg-white/10 dark:text-white">{{ requests.length }} entries</p>
    </div>

    <AdminDashboardQueueTable
      :rows="requests"
      :editing-request-id="editingRequestId"
      :due-date-drafts="dueDateDrafts"
      :saving-request-id="savingRequestId"
      :request-errors="requestErrors"
      :request-feedback="requestFeedback"
      :start-edit-action="startEditAction"
      :cancel-edit-action="cancelEditAction"
      :update-draft-action="updateDraftAction"
      :save-due-date-action="saveDueDateAction"
    />
  </section>
</template>
