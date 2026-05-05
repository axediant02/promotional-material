import assert from 'node:assert/strict'
import { effectScope, nextTick, ref } from 'vue'
import {
  mergeFolderSnapshot,
  normalizeWorkspaceEvent,
  removeFolderSnapshot,
} from '../src/features/production-dashboard/utils/productionWorkspaceSync.js'
import { useProductionRealtimeRefresh } from '../src/features/production-dashboard/composables/useProductionRealtimeRefresh.js'

const flush = async () => {
  await Promise.resolve()
  await Promise.resolve()
}

const testNormalizeWorkspaceEvent = () => {
  const event = normalizeWorkspaceEvent({
    kind: 'production_request_created',
    action: 'upsert',
    request_id: 'request-1',
    assignment_id: 'assignment-1',
    client_id: 'client-1',
    production_id: 'production-1',
    previous_production_id: 'production-2',
    folder_id: 'folder-1',
    created_at: '2026-05-05T00:00:00.000Z',
  })

  assert.equal(event.kind, 'production_request_created')
  assert.equal(event.action, 'upsert')
  assert.equal(event.requestId, 'request-1')
  assert.equal(event.assignmentId, 'assignment-1')
  assert.equal(event.clientId, 'client-1')
  assert.equal(event.productionId, 'production-1')
  assert.equal(event.previousProductionId, 'production-2')
  assert.equal(event.folderId, 'folder-1')
  assert.equal(event.createdAt, '2026-05-05T00:00:00.000Z')
}

const testMergeAndRemoveFolderSnapshots = () => {
  const state = {
    dashboardData: {
      folders: [
        { folder_id: 'folder-1', folder_name: 'Old folder' },
        { folder_id: 'folder-2', folder_name: 'Other folder' },
      ],
    },
    folders: [
      { folder_id: 'folder-1', folder_name: 'Old folder' },
      { folder_id: 'folder-2', folder_name: 'Other folder' },
    ],
    productionRequests: [
      { request_id: 'request-1', folder_id: 'folder-1', title: 'Old request' },
      { request_id: 'request-2', folder_id: 'folder-2', title: 'Keep request' },
    ],
    files: [
      { file_id: 'file-1', folder_id: 'folder-1', file_name: 'Old file' },
      { file_id: 'file-2', folder_id: 'folder-2', file_name: 'Keep file' },
    ],
    recycleBinFiles: [{ file_id: 'trash-1', folder_id: 'folder-1', file_name: 'Trash file' }],
  }

  const merged = mergeFolderSnapshot(state, {
    folder_id: 'folder-1',
    folder_name: 'Fresh folder',
    client_requests: [{ request_id: 'request-3', folder_id: 'folder-1', title: 'Fresh request' }],
    files: [{ file_id: 'file-3', folder_id: 'folder-1', file_name: 'Fresh file' }],
  })

  assert.equal(merged.folders[0].folder_name, 'Fresh folder')
  assert.deepEqual(
    merged.productionRequests.map((request) => request.request_id),
    ['request-3', 'request-2']
  )
  assert.deepEqual(
    merged.files.map((file) => file.file_id),
    ['file-3', 'file-2']
  )

  const removed = removeFolderSnapshot(merged, 'folder-1')

  assert.deepEqual(
    removed.folders.map((folder) => folder.folder_id),
    ['folder-2']
  )
  assert.deepEqual(
    removed.productionRequests.map((request) => request.request_id),
    ['request-2']
  )
  assert.deepEqual(
    removed.files.map((file) => file.file_id),
    ['file-2']
  )
  assert.deepEqual(removed.recycleBinFiles, [])
}

const testRealtimeWatcher = async () => {
  const realtimeStore = {
    lastWorkspaceEvent: ref(null),
    isLiveConnected: ref(false),
  }

  let refreshCount = 0
  let syncFolderId = ''
  let removeFolderId = ''
  let errorMessage = ''

  const scope = effectScope()

  scope.run(() => {
    useProductionRealtimeRefresh({
      realtimeStore,
      refreshAction: async () => {
        refreshCount += 1
      },
      syncFolderAction: async (folderId) => {
        syncFolderId = folderId
      },
      removeFolderAction: (folderId) => {
        removeFolderId = folderId
      },
      setError: (message) => {
        errorMessage = message
      },
    })
  })

  realtimeStore.lastWorkspaceEvent.value = {
    kind: 'production_request_created',
    action: 'upsert',
    folderId: 'folder-1',
  }
  await nextTick()
  await flush()

  assert.equal(syncFolderId, 'folder-1')
  assert.equal(refreshCount, 0)
  assert.equal(removeFolderId, '')

  realtimeStore.lastWorkspaceEvent.value = {
    kind: 'production_assignment_deleted',
    action: 'remove',
    folderId: 'folder-2',
  }
  await nextTick()
  await flush()

  assert.equal(removeFolderId, 'folder-2')

  realtimeStore.isLiveConnected.value = true
  await nextTick()
  await flush()

  assert.equal(refreshCount, 1)
  assert.equal(errorMessage, '')

  scope.stop()
}

const main = async () => {
  testNormalizeWorkspaceEvent()
  testMergeAndRemoveFolderSnapshots()
  await testRealtimeWatcher()
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
