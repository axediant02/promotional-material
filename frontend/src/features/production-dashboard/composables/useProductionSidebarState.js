import { ref, watch } from 'vue'

const SIDEBAR_COLLAPSED_STORAGE_KEY = 'production_sidebar_collapsed'

const getSavedSidebarCollapsed = () => {
  if (typeof window === 'undefined') {
    return false
  }

  return window.localStorage.getItem(SIDEBAR_COLLAPSED_STORAGE_KEY) === 'true'
}

export const useProductionSidebarState = () => {
  const sidebarCollapsed = ref(getSavedSidebarCollapsed())

  watch(sidebarCollapsed, (value) => {
    if (typeof window === 'undefined') {
      return
    }

    window.localStorage.setItem(SIDEBAR_COLLAPSED_STORAGE_KEY, String(value))
  })

  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  return {
    sidebarCollapsed,
    toggleSidebar,
  }
}
