import { ref } from 'vue'
import { fetchAdminWorkspace } from '../../../services/adminWorkspaceService'
import {
  fetchAdminAssignments,
} from '../../../services/adminService'

export const useAdminDashboardData = () => {
  const loading = ref(true)
  const error = ref('')
  const dashboardPayload = ref({
    stats: { folders: 0, files: 0 },
    folders: [],
    recentFiles: [],
  })
  const requestsPayload = ref([])
  const activityLogs = ref([])
  const assignmentsPayload = ref([])
  const productionUsersPayload = ref([])
  const usersPayload = ref([])

  const setError = (message) => {
    error.value = message
  }

  const clearError = () => {
    error.value = ''
  }

  const loadAssignments = async () => {
    const response = await fetchAdminAssignments()
    assignmentsPayload.value = response.data.data.assignments ?? []
    productionUsersPayload.value = response.data.data.production_users ?? []
  }

  const loadAdminDashboard = async () => {
    loading.value = true
    clearError()

    try {
      const response = await fetchAdminWorkspace()
      const workspace = response.data.data ?? {}

      dashboardPayload.value = workspace.dashboard ?? dashboardPayload.value
      requestsPayload.value = workspace.requests ?? []
      activityLogs.value = workspace.activityLogs ?? []
      assignmentsPayload.value = workspace.assignments ?? []
      productionUsersPayload.value = workspace.productionUsers ?? []
      usersPayload.value = workspace.users ?? []
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to load the admin dashboard.')
    } finally {
      loading.value = false
    }
  }

  const refreshDashboardAndRequests = async () => {
    const response = await fetchAdminWorkspace()
    const workspace = response.data.data ?? {}

    dashboardPayload.value = workspace.dashboard ?? dashboardPayload.value
    requestsPayload.value = workspace.requests ?? []
    activityLogs.value = workspace.activityLogs ?? []
    assignmentsPayload.value = workspace.assignments ?? []
    productionUsersPayload.value = workspace.productionUsers ?? []
    usersPayload.value = workspace.users ?? []
  }

  return {
    loading,
    error,
    dashboardPayload,
    requestsPayload,
    activityLogs,
    assignmentsPayload,
    productionUsersPayload,
    usersPayload,
    setError,
    clearError,
    loadAssignments,
    loadAdminDashboard,
    refreshDashboardAndRequests,
  }
}
