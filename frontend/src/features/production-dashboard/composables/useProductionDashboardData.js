import { ref } from 'vue'
import { fetchRecycleBin } from '../../../services/activityLogService'
import { fetchDashboard } from '../../../services/dashboardService'
import { fetchFiles } from '../../../services/fileService'
import { fetchFolders } from '../../../services/folderService'
import { fetchProductionRequests } from '../../../services/requestService'

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
      const [dashboardResponse, folderResponse, requestsResponse, filesResponse, recycleResponse] = await Promise.all([
        fetchDashboard(),
        fetchFolders(),
        fetchProductionRequests(),
        fetchFiles(),
        fetchRecycleBin(),
      ])

      dashboardData.value = dashboardResponse.data.data
      folders.value = folderResponse.data.data.folders ?? []
      productionRequests.value = requestsResponse.data.data.requests ?? []
      files.value = filesResponse.data.data.files ?? []
      recycleBinFiles.value = recycleResponse.data.data.files ?? []
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
