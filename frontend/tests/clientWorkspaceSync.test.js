import assert from 'node:assert/strict'
import { effectScope, nextTick, ref } from 'vue'
import {
  applyClientFileSnapshot,
  normalizeClientWorkspaceEvent,
  removeClientFileSnapshot,
  replaceWorkspaceSnapshot,
} from '../src/features/client-dashboard/utils/clientWorkspaceSync.js'
import { useClientRealtimeRefresh } from '../src/features/client-dashboard/composables/useClientRealtimeRefresh.js'

const flush = async () => {
  await Promise.resolve()
  await Promise.resolve()
}

const testNormalizeClientWorkspaceEvent = () => {
  const event = normalizeClientWorkspaceEvent({
    kind: 'client_workspace_sync',
    action: 'upsert',
    file_id: 'file-1',
    folder_id: 'folder-1',
    client_id: 'client-1',
    production_id: 'production-1',
    previous_folder_id: 'folder-2',
    created_at: '2026-05-05T00:00:00.000Z',
  })

  assert.equal(event.kind, 'client_workspace_sync')
  assert.equal(event.action, 'upsert')
  assert.equal(event.fileId, 'file-1')
  assert.equal(event.folderId, 'folder-1')
  assert.equal(event.clientId, 'client-1')
  assert.equal(event.productionId, 'production-1')
  assert.equal(event.previousFolderId, 'folder-2')
  assert.equal(event.createdAt, '2026-05-05T00:00:00.000Z')
}

const testWorkspaceSnapshots = () => {
  const state = {
    dashboardData: {
      stats: { folders: 1, files: 1 },
      recentFiles: [{ file_id: 'file-1', file_name: 'Old file' }],
    },
    files: [{ file_id: 'file-1', file_name: 'Old file' }],
    requests: [{ request_id: 'request-1' }],
  }

  const upserted = applyClientFileSnapshot(state, {
    file_id: 'file-2',
    file_name: 'Fresh file',
  })

  assert.deepEqual(
    upserted.files.map((file) => file.file_id),
    ['file-2', 'file-1']
  )
  assert.equal(upserted.dashboardData.stats.files, 2)
  assert.deepEqual(
    upserted.dashboardData.recentFiles.map((file) => file.file_id),
    ['file-2', 'file-1']
  )

  const removed = removeClientFileSnapshot(upserted, 'file-1')

  assert.deepEqual(
    removed.files.map((file) => file.file_id),
    ['file-2']
  )
  assert.equal(removed.dashboardData.stats.files, 1)
  assert.deepEqual(
    removed.dashboardData.recentFiles.map((file) => file.file_id),
    ['file-2']
  )

  const replaced = replaceWorkspaceSnapshot({
    dashboard: {
      stats: { folders: 2, files: 3 },
      folders: [{ folder_id: 'folder-1' }],
      recentFiles: [{ file_id: 'file-3' }],
    },
    files: [{ file_id: 'file-3' }],
    requests: [{ request_id: 'request-2' }],
  }, state)

  assert.equal(replaced.dashboardData.stats.files, 3)
  assert.deepEqual(replaced.files.map((file) => file.file_id), ['file-3'])
  assert.deepEqual(replaced.requests.map((request) => request.request_id), ['request-2'])
}

const testRealtimeWatcher = async () => {
  const realtimeStore = {
    lastWorkspaceEvent: ref(null),
    isLiveConnected: ref(false),
  }

  let refreshCount = 0
  let syncCount = 0
  let removeCount = 0
  let errorMessage = ''
  let assignedFolderId = 'folder-1'

  const scope = effectScope()

  scope.run(() => {
    useClientRealtimeRefresh({
      realtimeStore,
      getAssignedFolderId: () => assignedFolderId,
      refreshAction: async () => {
        refreshCount += 1
      },
      syncFileAction: async () => {
        syncCount += 1
      },
      removeFileAction: () => {
        removeCount += 1
      },
      setError: (message) => {
        errorMessage = message
      },
    })
  })

  realtimeStore.lastWorkspaceEvent.value = {
    kind: 'client_workspace_sync',
    action: 'upsert',
    fileId: 'file-1',
    folderId: 'folder-1',
    clientId: 'client-1',
  }
  await nextTick()
  await flush()

  assert.equal(syncCount, 1)
  assert.equal(refreshCount, 0)
  assert.equal(removeCount, 0)

  realtimeStore.lastWorkspaceEvent.value = {
    kind: 'client_workspace_sync',
    action: 'remove',
    fileId: 'file-1',
    folderId: 'folder-1',
    clientId: 'client-1',
  }
  await nextTick()
  await flush()

  assert.equal(removeCount, 1)

  realtimeStore.lastWorkspaceEvent.value = {
    kind: 'client_workspace_sync',
    action: 'upsert',
    fileId: 'file-2',
    folderId: 'folder-2',
    previousFolderId: 'folder-1',
    clientId: 'client-1',
  }
  await nextTick()
  await flush()

  assert.equal(removeCount, 2)

  assignedFolderId = ''
  realtimeStore.lastWorkspaceEvent.value = {
    kind: 'client_workspace_sync',
    action: 'upsert',
    fileId: 'file-3',
    folderId: 'folder-2',
    clientId: 'client-1',
  }
  await nextTick()
  await flush()

  assert.equal(refreshCount, 1)

  realtimeStore.isLiveConnected.value = true
  await nextTick()
  await flush()

  assert.equal(refreshCount, 2)
  assert.equal(errorMessage, '')

  scope.stop()
}

const main = async () => {
  testNormalizeClientWorkspaceEvent()
  testWorkspaceSnapshots()
  await testRealtimeWatcher()
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
