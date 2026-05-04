const DAY_IN_MS = 24 * 60 * 60 * 1000

export const normalizeTimestamp = (value) => {
  if (!value) {
    return 0
  }

  return new Date(value).getTime()
}

export const formatShortId = (value, prefix = 'REQ') => {
  if (!value) {
    return prefix
  }

  return `${prefix}-${value.slice(0, 4).toUpperCase()}`
}

export const formatDateLabel = (value) => {
  if (!value) {
    return 'No date set'
  }

  return new Date(value).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

export const formatRequestType = (value) => (value === 'update_asset' ? 'Update asset' : 'New asset')

export const getDueSoonState = (value, dueSoonDays = 3) => {
  if (!value) {
    return { isDueSoon: false, isOverdue: false }
  }

  const dueAt = normalizeTimestamp(value)
  const now = Date.now()
  const diff = dueAt - now
  const threshold = dueSoonDays * DAY_IN_MS

  return {
    isDueSoon: diff >= 0 && diff <= threshold,
    isOverdue: diff < 0,
  }
}

export const getFileFolderId = (file) => file.folder?.folder_id ?? file.folder_id ?? null

export const getFileFolderName = (file, folderLookup) =>
  file.folder?.folder_name ?? folderLookup.get(getFileFolderId(file))?.folder_name ?? 'Workspace'

export const getFileClientName = (file, folderLookup) =>
  folderLookup.get(getFileFolderId(file))?.client?.name ?? 'Assigned client'

export const getFileUploaderName = (file, currentUserName = 'Uploader') =>
  file.uploader?.name ?? currentUserName ?? 'Uploader'

const buildFileLookup = (files = []) => {
  const map = new Map()

  for (const file of files) {
    const folderId = getFileFolderId(file)
    const bucket = map.get(folderId) ?? []
    bucket.push(file)
    map.set(folderId, bucket)
  }

  for (const bucket of map.values()) {
    bucket.sort((left, right) => normalizeTimestamp(right.updated_at) - normalizeTimestamp(left.updated_at))
  }

  return map
}

const buildRequestLookup = (requests = []) => {
  const map = new Map()

  for (const request of requests) {
    const folderId = request.folder_id ?? null
    const bucket = map.get(folderId) ?? []
    bucket.push(request)
    map.set(folderId, bucket)
  }

  for (const bucket of map.values()) {
    bucket.sort((left, right) =>
      normalizeTimestamp(left.due_date ?? left.updated_at ?? left.created_at) -
      normalizeTimestamp(right.due_date ?? right.updated_at ?? right.created_at)
    )
  }

  return map
}

export const buildQueueRows = ({ productionRequests = [], files = [], folderLookup }) => {
  const filesByFolder = buildFileLookup(files)

  return productionRequests.map((request) => {
    const folder = folderLookup.get(request.folder_id)
    const relatedFiles = filesByFolder.get(request.folder_id) ?? []
    const { isDueSoon, isOverdue } = getDueSoonState(request.due_date)

    return {
      id: request.request_id,
      folderId: request.folder_id ?? '',
      reference: formatShortId(request.request_id),
      title: request.title ?? 'Untitled request',
      description: request.description ?? 'No request description provided.',
      sortTimestamp: normalizeTimestamp(request.updated_at ?? request.created_at ?? request.due_date ?? ''),
      clientName: folder?.client?.name ?? 'Assigned client',
      workspace: folder?.folder_name ?? 'Assigned workspace',
      requestType: formatRequestType(request.request_type),
      status: request.status ?? 'pending',
      statusTone: request.status ?? 'pending',
      statusLabel: (request.status ?? 'pending').replaceAll('_', ' '),
      dueLabel: request.due_date ? `Due ${formatDateLabel(request.due_date)}` : 'No due date set',
      isHighPriority: isDueSoon || isOverdue,
      priorityLabel: isDueSoon || isOverdue ? 'High Priority' : '',
      fileCount: relatedFiles.length,
      fileNames: relatedFiles.slice(0, 3).map((file) => file.file_name),
    }
  })
}

export const buildFolderWorkspaceRows = ({ folders = [], files = [], productionRequests = [] }) => {
  const filesByFolder = buildFileLookup(files)
  const requestsByFolder = buildRequestLookup(productionRequests)

  return folders.map((folder) => {
    const folderFiles = filesByFolder.get(folder.folder_id) ?? []
    const folderRequests = requestsByFolder.get(folder.folder_id) ?? []
    const activeRequestCount = folderRequests.filter((request) => request.status !== 'done').length
    const dueSoonRequests = folderRequests.filter((request) => {
      const { isDueSoon, isOverdue } = getDueSoonState(request.due_date)
      return request.status !== 'done' && (isDueSoon || isOverdue)
    })

    const latestFileAt = folderFiles[0]?.updated_at ?? ''
    const latestRequestAt = folderRequests[0]
      ? folderRequests.reduce((latest, request) => {
          const candidate = request.updated_at ?? request.created_at ?? request.due_date ?? ''
          return normalizeTimestamp(candidate) > normalizeTimestamp(latest) ? candidate : latest
        }, '')
      : ''
    const latestActivityAt =
      normalizeTimestamp(latestFileAt) >= normalizeTimestamp(latestRequestAt) ? latestFileAt : latestRequestAt

    let statusTone = 'ready'
    let statusLabel = 'Ready'

    if (!folderFiles.length && !folderRequests.length) {
      statusTone = 'empty'
      statusLabel = 'Empty'
    } else if (dueSoonRequests.length) {
      statusTone = 'needs_action'
      statusLabel = 'Needs action'
    } else if (activeRequestCount) {
      statusTone = 'in_progress'
      statusLabel = 'In progress'
    }

    const nextDueDate = folderRequests
      .filter((request) => request.status !== 'done' && request.due_date)
      .sort((left, right) => normalizeTimestamp(left.due_date) - normalizeTimestamp(right.due_date))[0]?.due_date

    return {
      id: folder.folder_id,
      clientId: folder.client?.user_id ?? folder.client_id ?? '',
      clientName: folder.client?.name ?? 'Assigned client',
      email: folder.client?.email ?? '',
      workspace: folder.folder_name ?? 'Assigned workspace',
      requestCount: folderRequests.length,
      activeRequestCount,
      fileCount: folderFiles.length,
      latestActivityAt,
      latestActivityLabel: latestActivityAt ? `Updated ${formatDateLabel(latestActivityAt)}` : 'No activity yet',
      dueSoonCount: dueSoonRequests.length,
      dueDate: nextDueDate ?? '',
      dueDateLabel: nextDueDate ? `Next due ${formatDateLabel(nextDueDate)}` : 'No due date set',
      statusTone,
      statusLabel,
      fileNamesSearch: folderFiles.map((file) => file.file_name).join(' '),
    }
  })
}

export const buildWorkspaceSummaryStats = ({ folderCount, openRequests, totalRequests, filesCount, dueSoonCount }) => [
  {
    id: 'assigned_folders',
    label: 'Assigned folders',
    value: folderCount,
    detail: 'Visible client workspaces',
    accent: 'from-brand-500/16 via-brand-500/8 to-transparent',
    chipTone: 'border-brand-300/40 bg-brand-50 text-brand-700 dark:border-white/10 dark:bg-white/10 dark:text-brand-100',
    numberTone: 'text-brand-700 dark:text-brand-100',
  },
  {
    id: 'open_requests',
    label: 'Open requests',
    value: openRequests,
    detail: `${totalRequests} total tracked`,
    accent: 'from-amber-500/16 via-amber-500/8 to-transparent',
    chipTone: 'border-amber-300/40 bg-amber-50 text-amber-900 dark:border-white/10 dark:bg-white/10 dark:text-amber-100',
    numberTone: 'text-amber-900 dark:text-amber-100',
  },
  {
    id: 'files_in_scope',
    label: 'Files in scope',
    value: filesCount,
    detail: 'Accessible production assets',
    accent: 'from-slate-400/14 via-white/10 to-transparent',
    chipTone: 'border-border/80 bg-white/80 text-muted dark:border-white/10 dark:bg-white/10 dark:text-zinc-300',
    numberTone: 'text-ink dark:text-white',
  },
  {
    id: 'due_soon',
    label: 'High Priority',
    value: dueSoonCount,
    detail: 'Active requests nearing deadline',
    accent: 'from-orange-400/16 via-orange-400/8 to-transparent',
    chipTone: 'border-orange-300/40 bg-orange-50 text-orange-900 dark:border-white/10 dark:bg-white/10 dark:text-orange-100',
    numberTone: 'text-orange-900 dark:text-orange-100',
  },
]

export const buildFileCategoryStats = (files = []) =>
  ['image', 'video', 'pdf'].map((category) => ({
    id: category,
    label: category.toUpperCase(),
    value: files.filter((file) => file.category === category).length,
  }))

export const buildRecentActivityFiles = ({ recentFiles = [], folderLookup }) =>
  recentFiles.map((file) => ({
    id: file.file_id,
    name: file.file_name,
    folderName: file.folder?.folder_name ?? folderLookup.get(file.folder_id)?.folder_name ?? 'Workspace',
    updatedLabel: formatDateLabel(file.updated_at),
    category: file.category ?? 'asset',
  }))

export const buildFilteredRecycleBinFiles = ({ recycleBinFiles = [], query = '', folderLookup }) => {
  const normalizedQuery = query.trim().toLowerCase()

  return recycleBinFiles.filter((file) => {
    if (!normalizedQuery) {
      return true
    }

    return [
      file.file_name,
      file.folder?.folder_name,
      file.uploader?.name,
      folderLookup.get(file.folder?.folder_id ?? file.folder_id)?.client?.name,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(normalizedQuery)
  })
}

export const buildVisibleFolderRows = ({ folderWorkspaceRows = [], query = '', folderBrowserFilter = 'all', folderBrowserSort = 'recent' }) => {
  const normalizedQuery = query.trim().toLowerCase()
  const now = Date.now()
  const recentThreshold = now - 7 * DAY_IN_MS

  return folderWorkspaceRows
    .filter((folder) => {
      if (normalizedQuery) {
        const haystack = `${folder.clientName} ${folder.workspace} ${folder.email} ${folder.fileNamesSearch}`.toLowerCase()
        if (!haystack.includes(normalizedQuery)) {
          return false
        }
      }

      if (folderBrowserFilter === 'needs_action') {
        return folder.statusTone === 'needs_action'
      }

      if (folderBrowserFilter === 'has_requests') {
        return folder.requestCount > 0
      }

      if (folderBrowserFilter === 'recently_updated') {
        return folder.latestActivityAt && normalizeTimestamp(folder.latestActivityAt) >= recentThreshold
      }

      if (folderBrowserFilter === 'empty') {
        return folder.statusTone === 'empty'
      }

      return true
    })
    .slice()
    .sort((left, right) => {
      if (folderBrowserSort === 'client_name') {
        return left.clientName.localeCompare(right.clientName)
      }

      if (folderBrowserSort === 'due_date') {
        const leftDue = left.dueDate ? normalizeTimestamp(left.dueDate) : Number.MAX_SAFE_INTEGER
        const rightDue = right.dueDate ? normalizeTimestamp(right.dueDate) : Number.MAX_SAFE_INTEGER
        return leftDue - rightDue
      }

      if (folderBrowserSort === 'request_volume') {
        return right.requestCount - left.requestCount || right.activeRequestCount - left.activeRequestCount
      }

      return normalizeTimestamp(right.latestActivityAt) - normalizeTimestamp(left.latestActivityAt)
    })
}
