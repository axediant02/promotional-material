import { computed, ref } from 'vue'
import { fetchClientFile, fetchClientWorkspace } from '../../../services/clientWorkspaceService.js'
import {
  applyClientFileSnapshot,
  removeClientFileSnapshot,
  replaceWorkspaceSnapshot,
} from '../utils/clientWorkspaceSync.js'

export const useClientDashboardData = () => {
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
  const files = ref([])
  const requests = ref([])

  const assignedFolder = computed(() => dashboardData.value.folders?.[0] ?? dashboardData.value.user?.assigned_folder ?? null)
  const assignedFolderId = computed(() => assignedFolder.value?.folder_id ?? dashboardData.value.user?.assigned_folder_id ?? '')

  const currentState = () => ({
    dashboardData: dashboardData.value,
    files: files.value,
    requests: requests.value,
  })

  const applyWorkspaceState = (workspace) => {
    const nextState = replaceWorkspaceSnapshot(workspace, currentState())

    dashboardData.value = nextState.dashboardData
    files.value = nextState.files
    requests.value = nextState.requests
  }

  const applyFileState = (file) => {
    if (!file?.file_id) {
      return null
    }

    const nextState = file.folder_id && assignedFolderId.value && file.folder_id === assignedFolderId.value
      ? applyClientFileSnapshot(currentState(), file)
      : removeClientFileSnapshot(currentState(), file.file_id)

    dashboardData.value = nextState.dashboardData
    files.value = nextState.files
    requests.value = nextState.requests

    return file
  }

  const removeFileState = (fileId) => {
    if (!fileId) {
      return
    }

    const nextState = removeClientFileSnapshot(currentState(), fileId)

    dashboardData.value = nextState.dashboardData
    files.value = nextState.files
    requests.value = nextState.requests
  }

  const loadData = async () => {
    loading.value = true
    error.value = ''

    try {
      const response = await fetchClientWorkspace()
      const workspace = response.data.data ?? {}

      applyWorkspaceState(workspace)
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to load the client dashboard.'
    } finally {
      loading.value = false
    }
  }

  const syncFileWorkspace = async (fileId) => {
    if (!fileId) {
      return null
    }

    const response = await fetchClientFile(fileId)
    const file = response.data.data?.file ?? null

    if (file) {
      applyFileState(file)
    }

    return file
  }

  return {
    loading,
    error,
    dashboardData,
    files,
    requests,
    assignedFolder,
    assignedFolderId,
    loadData,
    applyWorkspaceState,
    applyFileState,
    removeFileState,
    syncFileWorkspace,
  }
}
