const getFolderId = (folder) => folder?.folder_id ?? folder?.id ?? ''

const getRequestId = (request) => request?.request_id ?? request?.id ?? ''

const getFileId = (file) => file?.file_id ?? file?.id ?? ''

const upsertItem = (items = [], nextItem, getId) => {
  const nextId = getId(nextItem)

  if (!nextId) {
    return items.slice()
  }

  return [nextItem, ...items.filter((item) => getId(item) !== nextId)]
}

const removeItemsById = (items = [], id, getId) => items.filter((item) => getId(item) !== id)

const removeItemsByFolderId = (items = [], folderId, getItemFolderId) =>
  items.filter((item) => getItemFolderId(item) !== folderId)

export const normalizeWorkspaceEvent = (payload) => ({
  kind: payload?.kind ?? 'production_workspace_sync',
  action: payload?.action ?? 'refresh',
  requestId: payload?.request_id ?? payload?.requestId ?? null,
  assignmentId: payload?.assignment_id ?? payload?.assignmentId ?? null,
  clientId: payload?.client_id ?? payload?.clientId ?? null,
  productionId: payload?.production_id ?? payload?.productionId ?? null,
  previousProductionId: payload?.previous_production_id ?? payload?.previousProductionId ?? null,
  folderId: payload?.folder_id ?? payload?.folderId ?? null,
  createdAt: payload?.created_at ?? payload?.createdAt ?? new Date().toISOString(),
})

export const replaceWorkspaceSnapshot = (workspace = {}, currentState = {}) => ({
  dashboardData: {
    ...(currentState.dashboardData ?? {}),
    ...(workspace.dashboard ?? {}),
    folders: workspace.folders ?? [],
  },
  folders: workspace.folders ?? [],
  productionRequests: workspace.requests ?? [],
  files: workspace.files ?? [],
  recycleBinFiles: workspace.recycleBinFiles ?? [],
})

export const mergeFolderSnapshot = (currentState = {}, folder = {}) => {
  const folderId = getFolderId(folder)
  const folderRequests = folder.client_requests ?? folder.clientRequests ?? []
  const folderFiles = folder.files ?? []

  return {
    dashboardData: {
      ...(currentState.dashboardData ?? {}),
      folders: upsertItem(currentState.dashboardData?.folders ?? currentState.folders ?? [], folder, getFolderId),
    },
    folders: upsertItem(currentState.folders ?? [], folder, getFolderId),
    productionRequests: [
      ...folderRequests,
      ...removeItemsByFolderId(currentState.productionRequests ?? [], folderId, (request) => request.folder_id ?? ''),
    ],
    files: [
      ...folderFiles,
      ...removeItemsByFolderId(currentState.files ?? [], folderId, (file) => file.folder?.folder_id ?? file.folder_id ?? ''),
    ],
    recycleBinFiles: (currentState.recycleBinFiles ?? []).slice(),
  }
}

export const removeFolderSnapshot = (currentState = {}, folderId = '') => ({
  dashboardData: {
    ...(currentState.dashboardData ?? {}),
    folders: removeItemsById(currentState.dashboardData?.folders ?? currentState.folders ?? [], folderId, getFolderId),
  },
  folders: removeItemsById(currentState.folders ?? [], folderId, getFolderId),
  productionRequests: removeItemsByFolderId(currentState.productionRequests ?? [], folderId, (request) => request.folder_id ?? ''),
  files: removeItemsByFolderId(currentState.files ?? [], folderId, (file) => file.folder?.folder_id ?? file.folder_id ?? ''),
  recycleBinFiles: removeItemsByFolderId(currentState.recycleBinFiles ?? [], folderId, (file) => file.folder?.folder_id ?? file.folder_id ?? ''),
})

export const upsertRequestSnapshot = (items = [], request = {}) => upsertItem(items, request, getRequestId)

export const removeRequestSnapshot = (items = [], requestId = '') => removeItemsById(items, requestId, getRequestId)

export const upsertFileSnapshot = (items = [], file = {}) => upsertItem(items, file, getFileId)

export const removeFileSnapshot = (items = [], fileId = '') => removeItemsById(items, fileId, getFileId)
