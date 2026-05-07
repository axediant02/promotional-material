import { ref } from 'vue'
import { updateAdminRequestDueDate } from '../../../services/adminService'

export const useAdminRequestDueDates = ({ requestsPayload }) => {
  const editingRequestId = ref('')
  const dueDateDrafts = ref({})
  const requestDueDateSavingId = ref('')
  const requestDueDateErrors = ref({})
  const requestDueDateFeedback = ref({})

  const beginRequestDueDateEdit = (row) => {
    if (!row?.id || requestDueDateSavingId.value) {
      return
    }

    editingRequestId.value = row.id
    dueDateDrafts.value = {
      ...dueDateDrafts.value,
      [row.id]: row.dueDate ? String(row.dueDate).slice(0, 10) : '',
    }
    requestDueDateErrors.value = {
      ...requestDueDateErrors.value,
      [row.id]: '',
    }
    requestDueDateFeedback.value = {
      ...requestDueDateFeedback.value,
      [row.id]: '',
    }
  }

  const cancelRequestDueDateEdit = (requestId) => {
    if (!requestId || requestDueDateSavingId.value === requestId) {
      return
    }

    if (editingRequestId.value === requestId) {
      editingRequestId.value = ''
    }

    dueDateDrafts.value = {
      ...dueDateDrafts.value,
      [requestId]: '',
    }
    requestDueDateErrors.value = {
      ...requestDueDateErrors.value,
      [requestId]: '',
    }
  }

  const updateRequestDueDateDraft = (requestId, dueDate) => {
    dueDateDrafts.value = {
      ...dueDateDrafts.value,
      [requestId]: dueDate,
    }
    requestDueDateErrors.value = {
      ...requestDueDateErrors.value,
      [requestId]: '',
    }
    requestDueDateFeedback.value = {
      ...requestDueDateFeedback.value,
      [requestId]: '',
    }
  }

  const saveRequestDueDate = async (requestId) => {
    const dueDate = dueDateDrafts.value[requestId]?.trim?.() ?? dueDateDrafts.value[requestId] ?? ''
    if (!dueDate) {
      requestDueDateErrors.value = {
        ...requestDueDateErrors.value,
        [requestId]: 'Select a due date before saving.',
      }
      return
    }

    requestDueDateSavingId.value = requestId
    requestDueDateErrors.value = {
      ...requestDueDateErrors.value,
      [requestId]: '',
    }

    try {
      const response = await updateAdminRequestDueDate(requestId, { due_date: dueDate })
      const updatedRequest = response.data.data.request

      requestsPayload.value = requestsPayload.value.map((request) =>
        request.request_id === requestId ? { ...request, ...updatedRequest } : request
      )

      editingRequestId.value = ''
      requestDueDateFeedback.value = {
        ...requestDueDateFeedback.value,
        [requestId]: 'Due date saved.',
      }
    } catch (err) {
      requestDueDateErrors.value = {
        ...requestDueDateErrors.value,
        [requestId]:
          err.response?.data?.errors?.due_date?.[0]
          ?? err.response?.data?.message
          ?? 'Unable to update the due date.',
      }
    } finally {
      requestDueDateSavingId.value = ''
    }
  }

  return {
    editingRequestId,
    dueDateDrafts,
    requestDueDateSavingId,
    requestDueDateErrors,
    requestDueDateFeedback,
    beginRequestDueDateEdit,
    cancelRequestDueDateEdit,
    updateRequestDueDateDraft,
    saveRequestDueDate,
  }
}
