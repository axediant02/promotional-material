<script setup>
import AdminDashboardRequestsSection from './AdminDashboardRequestsSection.vue'
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

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
    <DashboardSectionHeader
      eyebrow="Queue view"
      title="All requests"
      description="Full request queue view for due-date review, assignment checks, and admin triage."
      :badge="`${rows.length} entries`"
      compact
    />

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
