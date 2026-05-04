import { ref } from 'vue'
import { updateProductionRequestStatus } from '../../../services/requestService'

export const useProductionRequestActions = ({ productionRequests, error }) => {
  const updatingRequestId = ref('')

  const updateRequestStatus = async (requestId, status) => {
    if (!status) {
      return
    }

    updatingRequestId.value = requestId
    error.value = ''

    try {
      const response = await updateProductionRequestStatus(requestId, { status })
      const updatedRequest = response.data.data.request

      productionRequests.value = productionRequests.value.map((request) =>
        request.request_id === requestId ? updatedRequest : request
      )
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to update request status.'
    } finally {
      updatingRequestId.value = ''
    }
  }

  return {
    updatingRequestId,
    updateRequestStatus,
  }
}
