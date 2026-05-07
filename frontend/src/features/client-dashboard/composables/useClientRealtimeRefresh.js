import { ref, watch } from 'vue'
import { normalizeClientWorkspaceEvent } from '../utils/clientWorkspaceSync.js'

const readStoreValue = (value) => (value && typeof value === 'object' && 'value' in value ? value.value : value)

export const useClientRealtimeRefresh = ({
  realtimeStore,
  getAssignedFolderId,
  refreshAction,
  syncFileAction,
  removeFileAction,
  setError,
}) => {
  const refreshQueued = ref(false)
  const refreshInFlight = ref(false)
  let observedConnectionState = false
  let hasObservedConnection = false

  const refreshWorkspace = async () => {
    if (refreshInFlight.value) {
      refreshQueued.value = true
      return
    }

    refreshInFlight.value = true

    try {
      await refreshAction()
    } catch (err) {
      setError(err?.response?.data?.message ?? 'Unable to refresh the client dashboard.')
    } finally {
      refreshInFlight.value = false

      if (refreshQueued.value) {
        refreshQueued.value = false
        await refreshWorkspace()
      }
    }
  }

  const syncWorkspaceEvent = async (event) => {
    const normalizedEvent = normalizeClientWorkspaceEvent(event)

    if (normalizedEvent.kind !== 'client_workspace_sync') {
      return
    }

    const assignedFolderId = getAssignedFolderId?.()

    if (
      normalizedEvent.action === 'refresh'
      || !normalizedEvent.fileId
      || !normalizedEvent.clientId
      || !assignedFolderId
    ) {
      await refreshWorkspace()
      return
    }

    const folderMatches = normalizedEvent.folderId === assignedFolderId
    const previousFolderMatches = normalizedEvent.previousFolderId === assignedFolderId

    if (normalizedEvent.action === 'remove') {
      if (folderMatches || previousFolderMatches) {
        removeFileAction?.(normalizedEvent.fileId)
        return
      }

      await refreshWorkspace()
      return
    }

    if (normalizedEvent.action === 'upsert') {
      if (folderMatches) {
        try {
          await syncFileAction?.(normalizedEvent.fileId)
          return
        } catch (err) {
          setError(err?.response?.data?.message ?? 'Unable to sync the client dashboard.')
          return
        }
      }

      if (previousFolderMatches) {
        removeFileAction?.(normalizedEvent.fileId)
        return
      }

      await refreshWorkspace()
      return
    }

    await refreshWorkspace()
  }

  watch(
    () => readStoreValue(realtimeStore.lastWorkspaceEvent),
    (event) => {
      if (!event?.kind || event.kind !== 'client_workspace_sync') {
        return
      }

      void syncWorkspaceEvent(event)
    }
  )

  watch(
    () => readStoreValue(realtimeStore.isLiveConnected),
    (isConnected) => {
      if (!hasObservedConnection) {
        observedConnectionState = isConnected
        hasObservedConnection = true
        return
      }

      const wasConnected = observedConnectionState
      observedConnectionState = isConnected

      if (!wasConnected && isConnected) {
        void refreshWorkspace()
      }
    },
    { immediate: true }
  )

  return {
    refreshQueued,
    refreshInFlight,
    refreshWorkspace,
    syncWorkspaceEvent,
  }
}
