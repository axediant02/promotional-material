import { computed, watch } from 'vue'

const normalizeBrowserMode = (value) => (value === 'list' ? 'list' : 'grid')
const normalizeFilter = (value) => (['all', 'needs_action', 'has_requests', 'recently_updated', 'empty'].includes(value) ? value : 'all')
const normalizeSort = (value) => (['recent', 'client_name', 'due_date', 'request_volume'].includes(value) ? value : 'recent')

export const useProductionRouting = ({
  route,
  router,
  activeSection,
  searchQuery,
  folderBrowserMode,
  folderBrowserFilter,
  folderBrowserSort,
}) => {
  const isFolderRoute = computed(() => ['production-folder-index', 'production-folder-detail'].includes(route.name))
  const selectedFolderId = computed(() => (typeof route.params.folderId === 'string' ? route.params.folderId : ''))
  let syncingFolderQuery = false

  const buildFolderQuery = () => {
    const query = {}

    if (folderBrowserMode.value) {
      query.view = folderBrowserMode.value
    }

    if (folderBrowserFilter.value !== 'all') {
      query.filter = folderBrowserFilter.value
    }

    if (folderBrowserSort.value !== 'recent') {
      query.sort = folderBrowserSort.value
    }

    if (searchQuery.value.trim()) {
      query.q = searchQuery.value.trim()
    }

    return query
  }

  const syncFolderStateFromRoute = () => {
    if (!isFolderRoute.value) {
      return
    }

    syncingFolderQuery = true
    folderBrowserMode.value = normalizeBrowserMode(route.query.view ?? folderBrowserMode.value)
    folderBrowserFilter.value = normalizeFilter(route.query.filter)
    folderBrowserSort.value = normalizeSort(route.query.sort)
    searchQuery.value = typeof route.query.q === 'string' ? route.query.q : ''
    syncingFolderQuery = false

    if (!route.query.view) {
      router.replace({
        name: route.name,
        params: route.params,
        query: buildFolderQuery(),
      })
    }
  }

  watch(
    () => [route.name, route.query.view, route.query.filter, route.query.sort, route.query.q],
    () => {
      if (isFolderRoute.value) {
        activeSection.value = 'files'
        syncFolderStateFromRoute()
      }
    },
    { immediate: true }
  )

  watch(searchQuery, (value) => {
    if (syncingFolderQuery || !isFolderRoute.value || activeSection.value !== 'files') {
      return
    }

    const nextQuery = { ...buildFolderQuery(), q: value.trim() || undefined }
    if (!nextQuery.q) {
      delete nextQuery.q
    }

    router.replace({ name: route.name, params: route.params, query: nextQuery })
  })

  watch([folderBrowserMode, folderBrowserFilter, folderBrowserSort], () => {
    if (syncingFolderQuery || !isFolderRoute.value) {
      return
    }

    router.replace({ name: route.name, params: route.params, query: buildFolderQuery() })
  })

  const openFolder = (folderId) =>
    router.push({
      name: 'production-folder-detail',
      params: { folderId },
      query: buildFolderQuery(),
    })

  const goToFolderIndex = () =>
    router.push({
      name: 'production-folder-index',
      query: buildFolderQuery(),
    })

  return {
    isFolderRoute,
    selectedFolderId,
    buildFolderQuery,
    syncFolderStateFromRoute,
    openFolder,
    goToFolderIndex,
  }
}
