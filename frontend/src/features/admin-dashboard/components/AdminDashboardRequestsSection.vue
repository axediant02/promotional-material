<script setup>
import AdminDashboardQueueTable from './AdminDashboardQueueTable.vue'
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

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
    <div class="mb-4">
      <DashboardSectionHeader
        eyebrow="Priority queue"
        title="All requests"
        description="Fresh queue items that need due dates, assignment, or admin review."
        :badge="`${requests.length} entries`"
        compact
      />
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
