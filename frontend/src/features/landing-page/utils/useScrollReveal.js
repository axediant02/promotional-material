import { onBeforeUnmount, onMounted, ref } from 'vue'

export function useScrollReveal(options = {}) {
  const element = ref(null)
  const isVisible = ref(false)
  let observer

  onMounted(() => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
      isVisible.value = true
      return
    }

    observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry?.isIntersecting) {
          return
        }

        isVisible.value = true
        observer?.unobserve(entry.target)
      },
      {
        threshold: 0.18,
        rootMargin: '0px 0px -10% 0px',
        ...options,
      },
    )

    if (element.value) {
      observer.observe(element.value)
    }
  })

  onBeforeUnmount(() => {
    observer?.disconnect()
  })

  return {
    element,
    isVisible,
  }
}
