import { onBeforeUnmount, ref, watch } from 'vue'
import { fetchFilePreview } from '../../../services/fileService'
import { getFileId } from '../utils/productionDashboardHelpers'

const isPreviewableMedia = (file) => file?.category === 'image' || file?.category === 'video'

const revokePreviewUrls = (previewUrls) => {
  for (const url of Object.values(previewUrls)) {
    if (url && typeof window !== 'undefined') {
      window.URL.revokeObjectURL(url)
    }
  }
}

export const useProductionFileCardPreviews = (files) => {
  const previewUrls = ref({})
  let previewToken = 0

  watch(
    files,
    async (nextFiles) => {
      const previousUrls = previewUrls.value
      previewUrls.value = {}
      revokePreviewUrls(previousUrls)

      if (typeof window === 'undefined') {
        return
      }

      const previewableFiles = (nextFiles ?? []).filter(isPreviewableMedia)
      if (!previewableFiles.length) {
        return
      }

      const token = ++previewToken

      const results = await Promise.allSettled(
        previewableFiles.map(async (file) => {
          const fileId = getFileId(file)

          if (!fileId) {
            return null
          }

          const response = await fetchFilePreview(fileId)
          return [fileId, window.URL.createObjectURL(response.data)]
        }),
      )

      if (token !== previewToken) {
        return
      }

      const nextPreviewUrls = {}
      for (const result of results) {
        if (result.status === 'fulfilled' && result.value) {
          const [fileId, previewUrl] = result.value
          nextPreviewUrls[fileId] = previewUrl
        }
      }

      previewUrls.value = nextPreviewUrls
    },
    { immediate: true },
  )

  onBeforeUnmount(() => {
    revokePreviewUrls(previewUrls.value)
  })

  return {
    previewUrls,
  }
}
