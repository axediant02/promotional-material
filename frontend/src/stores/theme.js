import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

const THEME_STORAGE_KEY = 'pm_theme'

export const useThemeStore = defineStore('theme', () => {
  const mode = ref('light')
  const initialized = ref(false)

  const isDark = computed(() => mode.value === 'dark')

  const detectSystemTheme = () => {
    if (typeof window === 'undefined') {
      return 'light'
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }

  const applyTheme = (value) => {
    if (typeof document === 'undefined') {
      return
    }

    const root = document.documentElement
    root.classList.toggle('dark', value === 'dark')
    root.style.colorScheme = value
  }

  const setTheme = (value) => {
    mode.value = value
    applyTheme(value)

    if (typeof window !== 'undefined') {
      window.localStorage.setItem(THEME_STORAGE_KEY, value)
    }
  }

  const toggleTheme = () => {
    setTheme(mode.value === 'dark' ? 'light' : 'dark')
  }

  const initializeTheme = () => {
    if (initialized.value) {
      return
    }

    const storedTheme =
      typeof window !== 'undefined' ? window.localStorage.getItem(THEME_STORAGE_KEY) : null

    mode.value = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : detectSystemTheme()
    applyTheme(mode.value)
    initialized.value = true
  }

  return {
    mode,
    isDark,
    initializeTheme,
    setTheme,
    toggleTheme,
  }
})
