import { computed, ref } from 'vue'
import {
  buildFileCategoryStats,
  buildFilteredRecycleBinFiles,
  buildFolderWorkspaceRows,
  buildQueueRows,
  buildRecentActivityFiles,
  buildVisibleFolderRows,
  buildWorkspaceSummaryStats,
  formatDateLabel,
  formatShortId,
  getDueSoonState,
  getFileFolderName,
  getFileUploaderName,
} from '../utils/productionDashboardHelpers'

export const useProductionDerivedData = ({
  authUser,
  dashboardData,
  folders,
  files,
  productionRequests,
  recycleBinFiles,
  searchQuery,
  activeSection,
  folderBrowserMode,
  folderBrowserFilter,
  folderBrowserSort,
  selectedFolderId,
  selectedOverviewRequestId,
}) => {
  const activeQueueFilter = ref('all')

  const currentUser = computed(() => dashboardData.value.user ?? authUser.value ?? {})
  const currentUserId = computed(() => currentUser.value?.user_id ?? currentUser.value?.id ?? '')
  const folderLookup = computed(() => {
    const map = new Map()

    for (const folder of folders.value) {
      map.set(folder.folder_id, folder)
    }

    for (const folder of dashboardData.value.folders ?? []) {
      const folderId = folder.folder_id ?? folder.id
      if (!map.has(folderId)) {
        map.set(folderId, folder)
      }
    }

    return map
  })

  const queueRows = computed(() =>
    buildQueueRows({
      productionRequests: productionRequests.value,
      files: files.value,
      folderLookup: folderLookup.value,
    })
  )

  const filteredQueueRows = computed(() =>
    queueRows.value.filter((row) => {
      const matchesFilter = activeQueueFilter.value === 'all' || row.status === activeQueueFilter.value

      if (!matchesFilter) {
        return false
      }

      const query = searchQuery.value.trim().toLowerCase()
      if (!query) {
        return true
      }

      return `${row.reference} ${row.title} ${row.description} ${row.clientName} ${row.workspace}`
        .toLowerCase()
        .includes(query)
    })
  )

  const folderWorkspaceRows = computed(() =>
    buildFolderWorkspaceRows({
      folders: folders.value,
      files: files.value,
      productionRequests: productionRequests.value,
    })
  )

  const selectedFolder = computed(() =>
    folderWorkspaceRows.value.find((folder) => folder.id === selectedFolderId.value) ?? null
  )

  const selectedFolderRequests = computed(() => {
    if (!selectedFolder.value) {
      return []
    }

    return queueRows.value.filter((request) => request.folderId === selectedFolder.value.id)
  })

  const selectedFolderFiles = computed(() => {
    if (!selectedFolder.value) {
      return []
    }

    return files.value
      .filter((file) => (file.folder?.folder_id ?? file.folder_id) === selectedFolder.value.id)
      .sort((left, right) => new Date(right.updated_at).getTime() - new Date(left.updated_at).getTime())
      .map((file) => ({
        ...file,
        shortId: formatShortId(file.file_id, 'FILE'),
        uploaderName: getFileUploaderName(file, currentUser.value?.name ?? 'Uploader'),
        updatedLabel: formatDateLabel(file.updated_at),
        folderName: getFileFolderName(file, folderLookup.value),
      }))
  })

  const workspaceSummaryStats = computed(() => {
    const openRequests = productionRequests.value.filter((request) => request.status !== 'done').length
    const dueSoon = productionRequests.value.filter((request) => {
      const { isDueSoon, isOverdue } = getDueSoonState(request.due_date)
      return request.status !== 'done' && (isDueSoon || isOverdue)
    }).length

    return buildWorkspaceSummaryStats({
      folderCount: folderWorkspaceRows.value.length,
      openRequests,
      totalRequests: productionRequests.value.length,
      filesCount: files.value.length,
      dueSoonCount: dueSoon,
    })
  })

  const visibleFolderRows = computed(() =>
    buildVisibleFolderRows({
      folderWorkspaceRows: folderWorkspaceRows.value,
      query: searchQuery.value,
      folderBrowserFilter: folderBrowserFilter.value,
      folderBrowserSort: folderBrowserSort.value,
    })
  )

  const fileCategoryStats = computed(() => buildFileCategoryStats(files.value))

  const recentActivityFiles = computed(() =>
    buildRecentActivityFiles({
      recentFiles: dashboardData.value.recentFiles ?? [],
      folderLookup: folderLookup.value,
    })
  )

  const latestAssignedRequests = computed(() =>
    queueRows.value
      .slice()
      .sort((left, right) => right.sortTimestamp - left.sortTimestamp)
  )

  const selectedOverviewRequest = computed(
    () => latestAssignedRequests.value.find((request) => request.id === selectedOverviewRequestId.value) ?? null
  )

  const filteredRecycleBinFiles = computed(() =>
    buildFilteredRecycleBinFiles({
      recycleBinFiles: recycleBinFiles.value,
      query: searchQuery.value,
      folderLookup: folderLookup.value,
    }).map((file) => ({
      ...file,
      shortId: formatShortId(file.file_id, 'FILE'),
      clientName: folderLookup.value.get(file.folder?.folder_id ?? file.folder_id)?.client?.name ?? 'Assigned client',
      folderName: file.folder?.folder_name ?? 'Unknown folder',
      uploaderName: file.uploader?.name ?? 'Unknown uploader',
      deletedLabel: formatDateLabel(file.deleted_at ?? file.updated_at),
    }))
  )

  const sectionCounts = computed(() => ({
    files: folders.value.length,
    queue: productionRequests.value.length,
    recycle: recycleBinFiles.value.length,
  }))

  const currentSectionMeta = computed(() => {
    if (activeSection.value === 'files') {
      return {
        eyebrow: currentUser.value?.name ? `${currentUser.value.name} / production` : 'Production workspace',
      }
    }

    if (activeSection.value === 'recycle') {
      return {
        eyebrow: `${recycleBinFiles.value.length} recoverable files`,
        title: 'Recovery queue.',
        description: 'Restore recently deleted files that still belong to your assigned client workspaces.',
      }
    }

    return {
      eyebrow: `${productionRequests.value.length} requests in scope`,
      title: 'Assigned work.',
      description: 'Track request movement across your assigned folders and keep delivery statuses aligned with production progress.',
    }
  })

  const recycleBinSummaryStats = computed(() => [
    {
      label: 'Deleted files',
      value: recycleBinFiles.value.length,
      detail: 'In assigned recycle scope',
    },
    {
      label: 'Recoverable clients',
      value: new Set(recycleBinFiles.value.map((file) => file.folder?.folder_id ?? file.folder_id)).size,
      detail: 'Workspaces with deleted assets',
    },
    {
      label: 'Live library',
      value: files.value.length,
      detail: 'Files still active outside recycle bin',
    },
  ])

  return {
    currentUser,
    currentUserId,
    activeQueueFilter,
    setActiveQueueFilter: (value) => {
      activeQueueFilter.value = value
    },
    folderLookup,
    queueRows,
    filteredQueueRows,
    folderWorkspaceRows,
    selectedFolder,
    selectedFolderRequests,
    selectedFolderFiles,
    workspaceSummaryStats,
    visibleFolderRows,
    fileCategoryStats,
    recentActivityFiles,
    latestAssignedRequests,
    selectedOverviewRequest,
    filteredRecycleBinFiles,
    sectionCounts,
    currentSectionMeta,
    recycleBinSummaryStats,
  }
}
