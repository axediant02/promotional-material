import { ref } from 'vue'
import { fetchDashboard } from '../../../services/dashboardService'
import {
  fetchAdminActivityLogs,
  fetchAdminAssignments,
  fetchAdminRequests,
  fetchAdminUsers,
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
      const [dashboardResponse, requestsResponse, logsResponse, assignmentsResponse, usersResponse] = await Promise.all([
        fetchDashboard(),
        fetchAdminRequests(),
        fetchAdminActivityLogs(),
        fetchAdminAssignments(),
        fetchAdminUsers(),
      ])

      dashboardPayload.value = dashboardResponse.data.data
      requestsPayload.value = requestsResponse.data.data.requests ?? []
      activityLogs.value = logsResponse.data.data.logs ?? []
      assignmentsPayload.value = assignmentsResponse.data.data.assignments ?? []
      productionUsersPayload.value = assignmentsResponse.data.data.production_users ?? []
      usersPayload.value = usersResponse.data.data.users ?? []
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to load the admin dashboard.')
    } finally {
      loading.value = false
    }
  }

  const refreshDashboardAndRequests = async () => {
    const [dashboardResponse, requestsResponse] = await Promise.all([fetchDashboard(), fetchAdminRequests()])
    dashboardPayload.value = dashboardResponse.data.data
    requestsPayload.value = requestsResponse.data.data.requests ?? []
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
