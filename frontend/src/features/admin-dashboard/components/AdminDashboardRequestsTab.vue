<script setup>
import AdminDashboardRequestsSection from './AdminDashboardRequestsSection.vue'

defineProps({
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
</script>

<template>
  <section class="space-y-6">
    <header class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-zinc-950 dark:text-white">All requests</h2>
      <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Full request queue view for due-date review and governance triage.
      </p>
    </header>

    <AdminDashboardRequestsSection
      :requests="rows"
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
