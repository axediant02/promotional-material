import { ref, watch } from 'vue'
import { fetchAgentWorkspace } from '../../../services/agentWorkspaceService'
import {
  FOLDER_FILTERS,
  FOLDER_SORTS,
  normalizeFilter,
  normalizeSort,
  normalizeViewMode,
} from '../utils/agentDashboardHelpers'
import { useAgentDashboardDerivedData } from './useAgentDashboardDerivedData'

const VIEW_STORAGE_KEY = 'agent_file_browser_view_mode'

const getSavedBrowserMode = () => {
  if (typeof window === 'undefined') {
    return 'grid'
  }

  return normalizeViewMode(window.localStorage.getItem(VIEW_STORAGE_KEY))
}

export const useAgentDashboardData = () => {
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
  const files = ref([])
  const loading = ref(true)
  const error = ref('')
  const searchQuery = ref('')
  const folderBrowserMode = ref(getSavedBrowserMode())
  const folderBrowserFilter = ref('all')
  const folderBrowserSort = ref('recent')
  const selectedFolderId = ref('')
  const activeView = ref('folders')

  const derived = useAgentDashboardDerivedData({
    dashboardData,
    folders,
    files,
    searchQuery,
    folderBrowserMode,
    folderBrowserFilter,
    folderBrowserSort,
    selectedFolderId,
    activeView,
  })

  const setFolderBrowserMode = (value) => {
    folderBrowserMode.value = normalizeViewMode(value)
  }

  const setFolderBrowserFilter = (value) => {
    folderBrowserFilter.value = normalizeFilter(value)
  }

  const setFolderBrowserSort = (value) => {
    folderBrowserSort.value = normalizeSort(value)
  }

  const selectFolder = (folderId) => {
    selectedFolderId.value = folderId
    activeView.value = 'folder'
  }

  const goToFolders = () => {
    activeView.value = 'folders'
  }

  watch(folderBrowserMode, (value) => {
    if (typeof window !== 'undefined') {
      window.localStorage.setItem(VIEW_STORAGE_KEY, normalizeViewMode(value))
    }
  })

  watch(derived.visibleFolderRows, (rows) => {
    if (!rows.length) {
      selectedFolderId.value = ''
      return
    }

    if (!rows.some((folder) => folder.id === selectedFolderId.value)) {
      selectedFolderId.value = rows[0].id
    }
  })

  const loadData = async () => {
    loading.value = true
    error.value = ''

    try {
      const response = await fetchAgentWorkspace()
      const workspace = response.data.data ?? {}

      dashboardData.value = workspace.dashboard ?? dashboardData.value
      folders.value = workspace.folders ?? []
      files.value = workspace.files ?? []
      selectedFolderId.value = derived.visibleFolderRows.value[0]?.id ?? ''
    } catch (err) {
      error.value = err.response?.data?.message ?? 'Unable to load the agent dashboard.'
    } finally {
      loading.value = false
    }
  }

  return {
    dashboardData,
    folders,
    files,
    loading,
    error,
    searchQuery,
    folderBrowserMode,
    folderBrowserFilter,
    folderBrowserSort,
    selectedFolderId,
    activeView,
    loadData,
    setFolderBrowserMode,
    setFolderBrowserFilter,
    setFolderBrowserSort,
    selectFolder,
    goToFolders,
    FOLDER_FILTERS,
    FOLDER_SORTS,
    ...derived,
  }
}
