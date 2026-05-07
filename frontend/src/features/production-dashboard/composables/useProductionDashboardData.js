import { ref } from 'vue'
import { fetchProductionFolder, fetchProductionWorkspace } from '../../../services/productionWorkspaceService.js'
import {
  mergeFolderSnapshot,
  removeFolderSnapshot,
  replaceWorkspaceSnapshot,
} from '../utils/productionWorkspaceSync.js'

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

  const applyWorkspaceState = (workspace) => {
    const nextState = replaceWorkspaceSnapshot(workspace, {
      dashboardData: dashboardData.value,
      folders: folders.value,
      productionRequests: productionRequests.value,
      files: files.value,
      recycleBinFiles: recycleBinFiles.value,
    })

    dashboardData.value = nextState.dashboardData
    folders.value = nextState.folders
    productionRequests.value = nextState.productionRequests
    files.value = nextState.files
    recycleBinFiles.value = nextState.recycleBinFiles
  }

  const applyFolderState = (folder) => {
    if (!folder?.folder_id) {
      return
    }

    const nextState = mergeFolderSnapshot({
      dashboardData: dashboardData.value,
      folders: folders.value,
      productionRequests: productionRequests.value,
      files: files.value,
      recycleBinFiles: recycleBinFiles.value,
    }, folder)

    dashboardData.value = nextState.dashboardData
    folders.value = nextState.folders
    productionRequests.value = nextState.productionRequests
    files.value = nextState.files
    recycleBinFiles.value = nextState.recycleBinFiles
  }

  const removeFolderState = (folderId) => {
    if (!folderId) {
      return
    }

    const nextState = removeFolderSnapshot({
      dashboardData: dashboardData.value,
      folders: folders.value,
      productionRequests: productionRequests.value,
      files: files.value,
      recycleBinFiles: recycleBinFiles.value,
    }, folderId)

    dashboardData.value = nextState.dashboardData
    folders.value = nextState.folders
    productionRequests.value = nextState.productionRequests
    files.value = nextState.files
    recycleBinFiles.value = nextState.recycleBinFiles
  }

  const loadData = async () => {
    loading.value = true
    error.value = ''

    try {
      const workspaceResponse = await fetchProductionWorkspace()
      const workspace = workspaceResponse.data.data ?? {}

      applyWorkspaceState(workspace)
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to load the production dashboard.'
    } finally {
      loading.value = false
    }
  }

  const syncFolderWorkspace = async (folderId) => {
    if (!folderId) {
      return null
    }

    const response = await fetchProductionFolder(folderId)
    const folder = response.data.data?.folder ?? null

    if (folder) {
      applyFolderState(folder)
    }

    return folder
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
    syncFolderWorkspace,
    removeFolderState,
    applyFolderState,
    applyWorkspaceState,
  }
}
