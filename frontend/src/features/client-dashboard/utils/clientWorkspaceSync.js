const getFolderId = (folder) => folder?.folder_id ?? folder?.id ?? ''

const getFileId = (file) => file?.file_id ?? file?.id ?? ''

const getRequestId = (request) => request?.request_id ?? request?.id ?? ''

const upsertItem = (items = [], nextItem, getId) => {
  const nextId = getId(nextItem)

  if (!nextId) {
    return items.slice()
  }

  return [nextItem, ...items.filter((item) => getId(item) !== nextId)]
}

const removeItemsById = (items = [], id, getId) => items.filter((item) => getId(item) !== id)

const trimRecentFiles = (files = []) => files.slice(0, 8)

export const normalizeClientWorkspaceEvent = (payload) => ({
  kind: payload?.kind ?? 'client_workspace_sync',
  action: payload?.action ?? 'refresh',
  fileId: payload?.file_id ?? payload?.fileId ?? null,
  folderId: payload?.folder_id ?? payload?.folderId ?? null,
  clientId: payload?.client_id ?? payload?.clientId ?? null,
  productionId: payload?.production_id ?? payload?.productionId ?? null,
  previousFolderId: payload?.previous_folder_id ?? payload?.previousFolderId ?? null,
  createdAt: payload?.created_at ?? payload?.createdAt ?? new Date().toISOString(),
})

export const replaceWorkspaceSnapshot = (workspace = {}, currentState = {}) => ({
  dashboardData: {
    ...(currentState.dashboardData ?? {}),
    ...(workspace.dashboard ?? {}),
    folders: workspace.dashboard?.folders ?? workspace.folders ?? [],
    recentFiles: workspace.dashboard?.recentFiles ?? [],
  },
  files: workspace.files ?? [],
  requests: workspace.requests ?? [],
})

export const applyClientFileSnapshot = (currentState = {}, file = {}) => {
  const nextFiles = upsertItem(currentState.files ?? [], file, getFileId)
  const nextRecentFiles = trimRecentFiles(
    upsertItem(currentState.dashboardData?.recentFiles ?? [], file, getFileId)
  )

  return {
    dashboardData: {
      ...(currentState.dashboardData ?? {}),
      stats: {
        ...(currentState.dashboardData?.stats ?? {}),
        files: nextFiles.length,
      },
      recentFiles: nextRecentFiles,
    },
    files: nextFiles,
    requests: (currentState.requests ?? []).slice(),
  }
}

export const removeClientFileSnapshot = (currentState = {}, fileId = '') => {
  if (!fileId) {
    return {
      dashboardData: {
        ...(currentState.dashboardData ?? {}),
        recentFiles: (currentState.dashboardData?.recentFiles ?? []).slice(),
      },
      files: (currentState.files ?? []).slice(),
      requests: (currentState.requests ?? []).slice(),
    }
  }

  const nextFiles = removeItemsById(currentState.files ?? [], fileId, getFileId)
  const nextRecentFiles = removeItemsById(
    currentState.dashboardData?.recentFiles ?? [],
    fileId,
    getFileId
  )

  return {
    dashboardData: {
      ...(currentState.dashboardData ?? {}),
      stats: {
        ...(currentState.dashboardData?.stats ?? {}),
        files: nextFiles.length,
      },
      recentFiles: nextRecentFiles,
    },
    files: nextFiles,
    requests: (currentState.requests ?? []).slice(),
  }
}

export const removeRequestSnapshot = (items = [], requestId = '') => removeItemsById(items, requestId, getRequestId)

export const upsertFolderSnapshot = (items = [], folder = {}) => upsertItem(items, folder, getFolderId)
