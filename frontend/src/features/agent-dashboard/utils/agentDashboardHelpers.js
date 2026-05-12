export const FOLDER_FILTERS = ['all', 'recently_updated', 'has_files', 'empty']
export const FOLDER_SORTS = ['recent', 'client_name', 'file_volume']

export const normalizeViewMode = (value) => (value === 'list' ? 'list' : 'grid')
export const normalizeFilter = (value) => (FOLDER_FILTERS.includes(value) ? value : 'all')
export const normalizeSort = (value) => (FOLDER_SORTS.includes(value) ? value : 'recent')

export const formatDateLabel = (value) => {
  if (!value) {
    return 'No activity yet'
  }

  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

export const formatShortId = (value, prefix = 'FILE') => {
  if (!value) {
    return prefix
  }

  return `${prefix}-${String(value).slice(0, 4).toUpperCase()}`
}

export const normalizeTimestamp = (value) => {
  if (!value) {
    return 0
  }

  const timestamp = new Date(value).getTime()
  return Number.isFinite(timestamp) ? timestamp : 0
}

export const getFolderId = (folder) => folder?.folder_id ?? folder?.id ?? ''
export const getFolderName = (folder) => folder?.folder_name ?? folder?.name ?? 'Client folder'
export const getFolderClientName = (folder) => folder?.client?.name ?? folder?.client_name ?? 'Client workspace'
export const getFolderClientEmail = (folder) => folder?.client?.email ?? folder?.client_email ?? ''

export const getFileId = (file) => file?.file_id ?? file?.id ?? ''
export const getFileFolderId = (file) => file?.folder?.folder_id ?? file?.folder_id ?? file?.folder?.id ?? ''
export const getFileName = (file) => file?.file_name ?? file?.original_name ?? 'Untitled file'
export const getFileCategory = (file) => file?.category ?? file?.mime_type ?? 'asset'
export const getFileUpdatedAt = (file) => file?.updated_at ?? file?.created_at ?? ''
export const getFileUploaderName = (file) => file?.uploader?.name ?? file?.uploaded_by?.name ?? 'Production'
