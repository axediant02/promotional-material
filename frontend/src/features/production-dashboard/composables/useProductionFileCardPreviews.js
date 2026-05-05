import { onBeforeUnmount, ref, watch } from 'vue'
import { fetchFilePreview } from '../../../services/fileService'
import { getFileId } from '../utils/productionDashboardHelpers'

const isPreviewableMedia = (file) =>
  file?.category === 'image' || file?.category === 'video' || file?.category === 'pdf'

const MAX_CACHED_PREVIEWS = 24

const revokePreviewUrl = (url) => {
  if (url && typeof window !== 'undefined') {
    window.URL.revokeObjectURL(url)
  }
}

export const useProductionFileCardPreviews = (files) => {
  const previewUrls = ref({})
  const previewCache = new Map()
  let previewToken = 0

  const touchPreviewCache = (fileId, previewUrl) => {
    const existing = previewCache.get(fileId)
    if (existing?.url && existing.url !== previewUrl) {
      revokePreviewUrl(existing.url)
    }

    if (previewCache.has(fileId)) {
      previewCache.delete(fileId)
    }

    previewCache.set(fileId, {
      url: previewUrl,
      touchedAt: Date.now(),
    })

    while (previewCache.size > MAX_CACHED_PREVIEWS) {
      const oldestEntry = previewCache.entries().next().value
      if (!oldestEntry) {
        break
      }

      const [oldestFileId, oldestPreview] = oldestEntry
      previewCache.delete(oldestFileId)
      revokePreviewUrl(oldestPreview.url)
    }
  }

  watch(
    files,
    async (nextFiles) => {
      if (typeof window === 'undefined') {
        previewUrls.value = {}
        return
      }

      const previewableFiles = (nextFiles ?? []).filter(isPreviewableMedia)
      if (!previewableFiles.length) {
        previewUrls.value = {}
        return
      }

      const token = ++previewToken
      const nextPreviewUrls = {}
      const missingFiles = []

      for (const file of previewableFiles) {
        const fileId = getFileId(file)

        if (!fileId) {
          continue
        }

        const cachedPreview = previewCache.get(fileId)
        if (cachedPreview) {
          touchPreviewCache(fileId, cachedPreview.url)
          nextPreviewUrls[fileId] = cachedPreview.url
          continue
        }

        missingFiles.push(fileId)
      }

      previewUrls.value = { ...nextPreviewUrls }

      if (!missingFiles.length) {
        return
      }

      const results = await Promise.allSettled(
        missingFiles.map(async (fileId) => {
          const response = await fetchFilePreview(fileId)
          return [fileId, window.URL.createObjectURL(response.data)]
        }),
      )

      if (token !== previewToken) {
        return
      }

      for (const result of results) {
        if (result.status === 'fulfilled' && result.value) {
          const [fileId, previewUrl] = result.value
          touchPreviewCache(fileId, previewUrl)
          nextPreviewUrls[fileId] = previewUrl
        }
      }

      previewUrls.value = nextPreviewUrls
    },
    { immediate: true },
  )

  onBeforeUnmount(() => {
    for (const { url } of previewCache.values()) {
      revokePreviewUrl(url)
    }
    previewCache.clear()
  })

  return {
    previewUrls,
  }
}
