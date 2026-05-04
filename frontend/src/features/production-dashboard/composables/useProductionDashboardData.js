import { ref } from 'vue'
import { fetchProductionWorkspace } from '../../../services/productionWorkspaceService'

export const useProductionDashboardData = () => {
  const loading = ref(true)
  const error = ref('')
  const dashboardData = ref({
    user: null,
    stats: {
      folders: 0,
      files: 0,
    },
    folders: [],
    recentFiles: [],
  })
  const folders = ref([])
  const productionRequests = ref([])
  const files = ref([])
  const recycleBinFiles = ref([])

  const loadData = async () => {
    loading.value = true
    error.value = ''

    try {
      const workspaceResponse = await fetchProductionWorkspace()
      const workspace = workspaceResponse.data.data ?? {}

      dashboardData.value = workspace.dashboard ?? dashboardData.value
      folders.value = workspace.folders ?? []
      productionRequests.value = workspace.requests ?? []
      files.value = workspace.files ?? []
      recycleBinFiles.value = workspace.recycleBinFiles ?? []
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to load the production dashboard.'
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    dashboardData,
    folders,
    productionRequests,
    files,
    recycleBinFiles,
    loadData,
  }
}
