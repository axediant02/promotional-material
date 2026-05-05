import { ref, watch } from 'vue'
import { normalizeWorkspaceEvent } from '../utils/productionWorkspaceSync.js'

const REFRESH_EVENT_KINDS = new Set([
  'production_request_created',
  'production_assignment_changed',
  'production_assignment_deleted',
])

const readStoreValue = (value) => (value && typeof value === 'object' && 'value' in value ? value.value : value)

export const useProductionRealtimeRefresh = ({
  realtimeStore,
  refreshAction,
  syncFolderAction,
  removeFolderAction,
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
      setError(err?.response?.data?.message ?? 'Unable to refresh the production workspace.')
    } finally {
      refreshInFlight.value = false

      if (refreshQueued.value) {
        refreshQueued.value = false
        await refreshWorkspace()
      }
    }
  }

  const syncWorkspaceEvent = async (event) => {
    const normalizedEvent = normalizeWorkspaceEvent(event)

    if (!normalizedEvent.kind || !REFRESH_EVENT_KINDS.has(normalizedEvent.kind)) {
      return
    }

    if (normalizedEvent.action === 'remove') {
      if (normalizedEvent.folderId) {
        removeFolderAction?.(normalizedEvent.folderId)
        return
      }

      await refreshWorkspace()
      return
    }

    if (normalizedEvent.folderId) {
      try {
        await syncFolderAction?.(normalizedEvent.folderId)
        return
      } catch (err) {
        setError(err?.response?.data?.message ?? 'Unable to sync the production workspace.')
      }
    }

    await refreshWorkspace()
  }

  watch(
    () => readStoreValue(realtimeStore.lastWorkspaceEvent),
    (event) => {
      if (!event?.kind || !REFRESH_EVENT_KINDS.has(event.kind)) {
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
