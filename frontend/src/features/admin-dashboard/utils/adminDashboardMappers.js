import {
  formatDateUs,
  formatIdLabel,
  formatShortMonthDay,
  toSlug,
} from './adminDashboardFormatters'

export const buildAssignedClientIdSet = (assignments = []) =>
  new Set(assignments.map((assignment) => assignment.client_id).filter(Boolean))

export const getOpenRequests = (requests = []) =>
  requests.filter((request) => request.status !== 'done')

export const isUnassignedRequest = (request, assignedClientIds) =>
  Boolean(request?.client_id) && !assignedClientIds.has(request.client_id)

export const mapFolderLookup = (folders = []) => {
  const map = new Map()

  for (const folder of folders) {
    map.set(folder.folder_id ?? folder.id, folder)
  }

  return map
}

export const mapProductionUserLookup = ({ productionUsers = [], activityLogs = [], assignments = [] }) => {
  const map = new Map()

  for (const user of productionUsers) {
    if (user?.user_id) {
      map.set(user.user_id, {
        id: user.user_id,
        name: user.name ?? formatIdLabel(user.user_id, 'Production'),
        email: user.email ?? '',
      })
    }
  }

  for (const log of activityLogs) {
    const user = log.user
    if (user?.role === 'production' && user.user_id) {
      map.set(user.user_id, {
        id: user.user_id,
        name: user.name ?? formatIdLabel(user.user_id, 'Production'),
        email: user.email ?? '',
      })
    }
  }

  for (const assignment of assignments) {
    if (assignment.production_id && !map.has(assignment.production_id)) {
      map.set(assignment.production_id, {
        id: assignment.production_id,
        name: formatIdLabel(assignment.production_id, 'Production'),
        email: '',
      })
    }
  }

  return map
}

export const mapClientLookup = ({ folders = [], requests = [], assignments = [] }) => {
  const map = new Map()

  for (const folder of folders) {
    const client = folder.client
    if (client?.user_id) {
      map.set(client.user_id, {
        id: client.user_id,
        name: client.name ?? folder.folder_name ?? formatIdLabel(client.user_id, 'Client'),
        email: client.email ?? '',
        folderName: folder.folder_name ?? 'Assigned Folder',
      })
    } else if (folder.client_id) {
      map.set(folder.client_id, {
        id: folder.client_id,
        name: folder.folder_name ?? formatIdLabel(folder.client_id, 'Client'),
        email: '',
        folderName: folder.folder_name ?? 'Assigned Folder',
      })
    }
  }

  for (const request of requests) {
    if (request.client_id && !map.has(request.client_id)) {
      map.set(request.client_id, {
        id: request.client_id,
        name: formatIdLabel(request.client_id, 'Client'),
        email: '',
        folderName: request.folder_id ? formatIdLabel(request.folder_id, 'Folder') : 'Assigned Folder',
      })
    }
  }

  for (const assignment of assignments) {
    if (assignment.client_id && !map.has(assignment.client_id)) {
      map.set(assignment.client_id, {
        id: assignment.client_id,
        name: formatIdLabel(assignment.client_id, 'Client'),
        email: '',
        folderName: 'Assigned Folder',
      })
    }
  }

  return map
}

export const mapQueueRows = ({ requests = [], folderLookup, assignedClientIds }) =>
  requests.slice(0, 12).map((request, index) => {
    const requestId = request.request_id ?? ''
    const folder = folderLookup.get(request.folder_id)
    const reference = requestId ? requestId.slice(0, 8).toUpperCase() : `REQ-${index + 1000}`
    const clientName = folder?.client?.name ?? folder?.folder_name ?? `Client ${index + 1}`
    const folderName = folder?.folder_name ?? folder?.name ?? request.folder_id ?? 'Unassigned folder'
    const requestTypeLabel = request.request_type === 'new_asset' ? 'New asset' : 'Update asset'
    const isUnassigned = isUnassignedRequest(request, assignedClientIds)
    const isMissingDueDate = !request.due_date
    const needsAttention = isUnassigned || isMissingDueDate || request.status === 'pending'

    return {
      id: requestId,
      reference,
      title: request.title ?? 'Untitled request',
      clientName,
      folderName,
      requestTypeLabel,
      status: request.status ?? 'pending',
      dueDate: request.due_date ?? '',
      dueLabel: formatShortMonthDay(request.due_date),
      isUnassigned,
      isMissingDueDate,
      needsAttention,
    }
  })

export const mapFolderCards = ({ folders = [], recentFiles = [] }) =>
  folders.slice(0, 4).map((folder) => {
    const folderId = folder.folder_id ?? folder.id
    const relatedFiles = recentFiles.filter((file) => (file.folder?.folder_id ?? file.folder_id) === folderId)

    return {
      id: folderId,
      clientName: folder.client?.name ?? folder.folder_name ?? 'Client Folder',
      slug: toSlug(folder.folder_name ?? folder.name ?? 'folder'),
      fileCount: relatedFiles.length || 0,
      updatedLabel: formatDateUs(folder.updated_at),
    }
  })

export const mapStats = ({ requests = [], assignedClientIds }) => {
  const openRequests = getOpenRequests(requests)
  const awaitingDueDate = openRequests.filter((request) => !request.due_date)
  const unassignedRequests = openRequests.filter((request) => isUnassignedRequest(request, assignedClientIds))
  const requestingClients = new Set(openRequests.map((request) => request.client_id).filter(Boolean))

  return [
    { label: 'Open Requests', value: openRequests.length, help: 'Pending admin review', emphasis: true },
    { label: 'Awaiting Due Date', value: awaitingDueDate.length, help: 'Need admin review' },
    { label: 'Unassigned Requests', value: unassignedRequests.length, help: 'Need assignment context' },
    { label: 'Requesting Clients', value: requestingClients.size, help: 'Currently in queue' },
  ]
}

export const mapAttentionItems = ({ requests = [], assignedClientIds }) => {
  const openRequests = getOpenRequests(requests)
  const awaitingDueDate = openRequests.filter((request) => !request.due_date)
  const unassignedRequests = openRequests.filter((request) => isUnassignedRequest(request, assignedClientIds))
  const pendingReview = openRequests.filter((request) => request.status === 'pending')

  return [
    {
      id: 'due-dates',
      label: 'Missing due dates',
      value: awaitingDueDate.length,
      badge: 'Review',
      tone: awaitingDueDate.length ? 'warning' : 'default',
      detail: 'Requests still waiting for schedule decisions before production can plan delivery.',
    },
    {
      id: 'assignments',
      label: 'Unassigned requests',
      value: unassignedRequests.length,
      badge: 'Action',
      tone: unassignedRequests.length ? 'danger' : 'default',
      detail: 'Requests missing enough context to confirm assignment or routing.',
    },
    {
      id: 'pending-review',
      label: 'Pending admin review',
      value: pendingReview.length,
      badge: 'Queue',
      tone: pendingReview.length ? 'warning' : 'default',
      detail: 'Queue items still waiting on an admin decision, due date, or admin follow-through.',
    },
  ]
}

export const mapUsersTabRows = ({ users = [], currentUserId }) =>
  users
    .map((user) => ({
      id: user.user_id,
      name: user.name ?? formatIdLabel(user.user_id, 'User'),
      email: user.email ?? '',
      role: user.role ?? 'client',
      status: user.status ?? '',
      note: user.user_id === currentUserId
        ? 'Signed-in admin account. Self role changes stay disabled.'
        : 'Live backend-driven account record for admin management.',
      isCurrentUser: user.user_id === currentUserId,
    }))
    .sort((left, right) => {
      if (left.isCurrentUser && !right.isCurrentUser) {
        return -1
      }
      if (!left.isCurrentUser && right.isCurrentUser) {
        return 1
      }
      return left.name.localeCompare(right.name)
    })

export const mapAssignmentsTabRows = ({ assignments = [], clientLookup, productionUserLookup, requests = [] }) =>
  assignments.map((assignment) => {
    const client = clientLookup.get(assignment.client_id)
    const production = productionUserLookup.get(assignment.production_id)
    const relatedRequests = requests.filter((request) => request.client_id === assignment.client_id)
    const openRequests = relatedRequests.filter((request) => request.status !== 'done').length

    return {
      id: assignment.id,
      clientId: assignment.client_id,
      productionId: assignment.production_id,
      clientName: client?.name ?? formatIdLabel(assignment.client_id, 'Client'),
      clientEmail: client?.email ?? '',
      folderName: client?.folderName ?? 'Assigned Folder',
      productionName: production?.name ?? formatIdLabel(assignment.production_id, 'Production'),
      productionEmail: production?.email ?? '',
      status: assignment.status ?? 'pending',
      workload: openRequests ? `${openRequests} active ${openRequests === 1 ? 'request' : 'requests'}` : 'No open requests',
      note: relatedRequests.length
        ? `Admin tracking currently includes ${relatedRequests.length} total ${relatedRequests.length === 1 ? 'request' : 'requests'} for this client.`
        : 'Assignment is active, but no request records are currently visible in the admin queue.',
    }
  })
