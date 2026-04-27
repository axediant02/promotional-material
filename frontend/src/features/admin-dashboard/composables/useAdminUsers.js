import { ref } from 'vue'
import {
  createAdminAgent,
  updateAdminUserRole,
} from '../../../services/adminService'

export const useAdminUsers = ({
  usersPayload,
  currentUser,
  authStore,
  clearError,
  setError,
}) => {
  const userRoleSavingId = ref('')
  const creatingAgent = ref(false)

  const handleUserRoleUpdate = async (userId, role) => {
    userRoleSavingId.value = userId
    clearError()

    try {
      const response = await updateAdminUserRole(userId, { role })
      const updatedUser = response.data.data.user

      usersPayload.value = usersPayload.value.map((user) =>
        user.user_id === userId ? { ...user, ...updatedUser } : user
      )

      if (currentUser.value?.user_id === userId) {
        authStore.user = {
          ...authStore.user,
          ...updatedUser,
        }
      }
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to update the user role.')
      throw err
    } finally {
      userRoleSavingId.value = ''
    }
  }

  const handleAgentCreate = async (payload) => {
    creatingAgent.value = true
    clearError()

    try {
      const response = await createAdminAgent(payload)
      const createdAgent = response.data.data.agent

      usersPayload.value = [
        createdAgent,
        ...usersPayload.value,
      ]

      return createdAgent
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to create the agent account.')
      throw err
    } finally {
      creatingAgent.value = false
    }
  }

  return {
    userRoleSavingId,
    creatingAgent,
    handleUserRoleUpdate,
    handleAgentCreate,
  }
}
