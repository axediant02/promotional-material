import { ref, watch } from 'vue'

const FILE_BROWSER_STORAGE_KEY = 'production_folder_view_mode'
const FOLDER_FILTERS = ['all', 'needs_action', 'has_requests', 'recently_updated', 'empty']
const FOLDER_SORTS = ['recent', 'client_name', 'due_date', 'request_volume']

const getSavedBrowserMode = () => {
  if (typeof window === 'undefined') {
    return 'grid'
  }

  const savedMode = window.localStorage.getItem(FILE_BROWSER_STORAGE_KEY)
  return savedMode === 'list' ? 'list' : 'grid'
}

const normalizeBrowserMode = (value) => (value === 'list' ? 'list' : 'grid')
const normalizeFilter = (value) => (FOLDER_FILTERS.includes(value) ? value : 'all')
const normalizeSort = (value) => (FOLDER_SORTS.includes(value) ? value : 'recent')

export const useProductionFilters = () => {
  const searchQuery = ref('')
  const folderBrowserMode = ref(getSavedBrowserMode())
  const folderBrowserFilter = ref('all')
  const folderBrowserSort = ref('recent')

  watch(folderBrowserMode, (value) => {
    if (typeof window === 'undefined') {
      return
    }

    window.localStorage.setItem(FILE_BROWSER_STORAGE_KEY, value)
  })

  const setFolderBrowserMode = (value) => {
    folderBrowserMode.value = normalizeBrowserMode(value)
  }

  const setFolderBrowserFilter = (value) => {
    folderBrowserFilter.value = normalizeFilter(value)
  }

  const setFolderBrowserSort = (value) => {
    folderBrowserSort.value = normalizeSort(value)
  }

  return {
    searchQuery,
    folderBrowserMode,
    folderBrowserFilter,
    folderBrowserSort,
    setFolderBrowserMode,
    setFolderBrowserFilter,
    setFolderBrowserSort,
  }
}
