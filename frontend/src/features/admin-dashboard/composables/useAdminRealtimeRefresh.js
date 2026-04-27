import { ref, watch } from 'vue'

export const useAdminRealtimeRefresh = ({
  notificationStore,
  authStore,
  refreshAction,
  setError,
}) => {
  const liveRefreshQueued = ref(false)
  const liveRefreshInFlight = ref(false)

  const refreshAdminRealtimeData = async () => {
    if (liveRefreshInFlight.value) {
      liveRefreshQueued.value = true
      return
    }

    liveRefreshInFlight.value = true

    try {
      await refreshAction()
    } catch (err) {
      setError(err.response?.data?.message ?? 'Unable to refresh the admin dashboard.')
    } finally {
      liveRefreshInFlight.value = false

      if (liveRefreshQueued.value) {
        liveRefreshQueued.value = false
        await refreshAdminRealtimeData()
      }
    }
  }

  watch(
    () => notificationStore.lastRealtimeNotification,
    (notification) => {
      if (!notification?.kind) {
        return
      }

      if (authStore.user?.role !== 'admin') {
        return
      }

      if (notification.kind !== 'client_request_created') {
        return
      }

      void refreshAdminRealtimeData()
    }
  )

  return {
    liveRefreshQueued,
    liveRefreshInFlight,
    refreshAdminRealtimeData,
  }
}
