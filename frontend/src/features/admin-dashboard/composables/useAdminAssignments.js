import { ref } from 'vue'
import {
  removeAdminAssignment,
  saveAdminAssignment,
} from '../../../services/adminService'

export const useAdminAssignments = ({ clearError, setError, loadAssignments }) => {
  const assignmentsSaving = ref(false)
  const assignmentDeletingId = ref('')

  const handleAssignmentSave = async (payload) => {
    assignmentsSaving.value = true
    clearError()

    try {
      await saveAdminAssignment(payload)
      await loadAssignments()
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to save the client assignment.')
      throw err
    } finally {
      assignmentsSaving.value = false
    }
  }

  const handleAssignmentRemove = async (assignmentId) => {
    assignmentDeletingId.value = assignmentId
    clearError()

    try {
      await removeAdminAssignment(assignmentId)
      await loadAssignments()
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to remove the client assignment.')
      throw err
    } finally {
      assignmentDeletingId.value = ''
    }
  }

  return {
    assignmentsSaving,
    assignmentDeletingId,
    handleAssignmentSave,
    handleAssignmentRemove,
  }
}
