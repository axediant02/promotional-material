import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { fetchFilePreview } from '../../../services/fileService'

const isImageFile = (file) => {
  if (!file) {
    return false
  }

  if (typeof file.type === 'string' && file.type.startsWith('image/')) {
    return true
  }

  return file.category === 'image'
}

const revokeObjectUrl = (url) => {
  if (url && typeof window !== 'undefined') {
    window.URL.revokeObjectURL(url)
  }
}

export const useProductionFolderUpload = ({ workspace, selectedFolder }) => {
  const showUploadPanel = ref(false)
  const selectedFiles = ref([])
  const selectedFileToReplace = ref(null)
  const uploadError = ref('')
  const fileInputRef = ref(null)
  const isDragging = ref(false)
  const selectedReplacementPreviewUrl = ref('')
  const currentFilePreviewUrl = ref('')
  let previewToken = 0

  const isReplacingFile = computed(() => Boolean(selectedFileToReplace.value))
  const selectedReplacementFile = computed(() => selectedFiles.value[0] ?? null)
  const canSubmitUpload = computed(() =>
    Boolean(selectedFiles.value.length)
      && workspace.uploadingFileId.value !== selectedFolder.value?.id
      && (!isReplacingFile.value || workspace.updatingFileId.value !== selectedFileToReplace.value?.file_id),
  )
  const activePreviewUrl = computed(() => selectedReplacementPreviewUrl.value || currentFilePreviewUrl.value)
  const activePreviewLabel = computed(() =>
    selectedReplacementFile.value ? 'New file preview' : 'Current file preview'
  )
  const activePreviewEmptyLabel = computed(() =>
    selectedReplacementFile.value
      ? 'This file type does not support an image preview.'
      : selectedFileToReplace.value
        ? 'No image preview is available for this file type.'
        : 'Pick a file to start a replacement preview.'
  )

  const clearCurrentFilePreview = () => {
    revokeObjectUrl(currentFilePreviewUrl.value)
    currentFilePreviewUrl.value = ''
  }

  const clearSelectedReplacementPreview = () => {
    if (selectedReplacementPreviewUrl.value) {
      revokeObjectUrl(selectedReplacementPreviewUrl.value)
      selectedReplacementPreviewUrl.value = ''
    }
  }

  watch(
    selectedReplacementFile,
    async (file) => {
      clearSelectedReplacementPreview()

      if (file && isImageFile(file) && typeof window !== 'undefined') {
        selectedReplacementPreviewUrl.value = window.URL.createObjectURL(file)
      }
    },
    { immediate: true },
  )

  watch(
    selectedFileToReplace,
    async (file) => {
      clearCurrentFilePreview()

      if (!file || file.category !== 'image') {
        return
      }

      const token = ++previewToken

      try {
        const response = await fetchFilePreview(file.file_id)
        if (token !== previewToken || typeof window === 'undefined') {
          return
        }

        currentFilePreviewUrl.value = window.URL.createObjectURL(response.data)
      } catch {
        currentFilePreviewUrl.value = ''
      }
    },
    { immediate: true },
  )

  onBeforeUnmount(() => {
    clearCurrentFilePreview()
    clearSelectedReplacementPreview()
  })

  const openUploadPanel = (file = null) => {
    selectedFileToReplace.value = file
    selectedFiles.value = []
    uploadError.value = ''
    showUploadPanel.value = true
  }

  const setSelectedFiles = (files) => {
    selectedFiles.value = isReplacingFile.value ? [files[0]] : [...selectedFiles.value, ...files]
    uploadError.value = ''

    if (isReplacingFile.value && files.length > 1) {
      uploadError.value = 'Choose a single replacement file.'
    }
  }

  const handleFileSelect = (event) => {
    const files = Array.from(event.target.files ?? [])

    if (files.length) {
      setSelectedFiles(files)
    }

    if (fileInputRef.value) {
      fileInputRef.value.value = ''
    }
  }

  const handleFileDrop = (event) => {
    isDragging.value = false
    const files = Array.from(event.dataTransfer.files ?? [])

    if (files.length) {
      setSelectedFiles(files)
    }
  }

  const removeSelectedFile = (index) => {
    selectedFiles.value = selectedFiles.value.filter((_, i) => i !== index)
  }

  const submitUpload = async () => {
    if (!selectedFolder.value?.id || !selectedFiles.value.length) {
      return
    }

    uploadError.value = ''

    try {
      if (selectedFileToReplace.value) {
        await workspace.handleReplaceFile(
          selectedFiles.value[0],
          selectedFolder.value.id,
          selectedFileToReplace.value.file_id,
        )
      } else {
        for (const file of selectedFiles.value) {
          await workspace.handleUploadFile(file, selectedFolder.value.id)
        }
      }

      selectedFiles.value = []
      selectedFileToReplace.value = null
      showUploadPanel.value = false
    } catch (err) {
      uploadError.value = err?.response?.data?.message ?? 'Unable to complete the file upload.'
    }
  }

  const closeUploadPanel = () => {
    selectedFiles.value = []
    selectedFileToReplace.value = null
    uploadError.value = ''
    showUploadPanel.value = false
    clearSelectedReplacementPreview()
  }

  return {
    showUploadPanel,
    selectedFiles,
    selectedFileToReplace,
    uploadError,
    fileInputRef,
    isDragging,
    isReplacingFile,
    selectedReplacementFile,
    canSubmitUpload,
    activePreviewUrl,
    activePreviewLabel,
    activePreviewEmptyLabel,
    openUploadPanel,
    handleFileSelect,
    handleFileDrop,
    removeSelectedFile,
    submitUpload,
    closeUploadPanel,
  }
}
