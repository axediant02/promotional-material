import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { fetchFilePreview } from '../../../services/fileService'
import { getFileId, getFolderId } from '../utils/productionDashboardHelpers'

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
  const uploadMode = ref('upload')
  const selectedFileToEdit = ref(null)
  const selectedFileToEditId = ref('')
  const uploadError = ref('')
  const fileInputRef = ref(null)
  const isDragging = ref(false)
  const isSubmittingUpload = ref(false)
  const selectedReplacementPreviewUrl = ref('')
  const currentFilePreviewUrl = ref('')
  let previewToken = 0

  const isEditingFile = computed(() => uploadMode.value === 'edit' && Boolean(selectedFileToEdit.value))
  const selectedReplacementFile = computed(() => selectedFiles.value[0] ?? null)
  const canSubmitUpload = computed(() =>
    Boolean(selectedFiles.value.length)
      && Boolean(getFolderId(selectedFolder.value))
      && !isSubmittingUpload.value,
  )
  const activePreviewUrl = computed(() => selectedReplacementPreviewUrl.value || currentFilePreviewUrl.value)
  const activePreviewLabel = computed(() =>
    selectedReplacementFile.value
      ? isEditingFile.value
        ? 'Edited file preview'
        : 'New file preview'
      : isEditingFile.value
        ? 'Current file preview'
        : 'New file preview'
  )
  const activePreviewEmptyLabel = computed(() =>
    selectedReplacementFile.value
      ? 'This file type does not support an image preview.'
      : isEditingFile.value
        ? 'No image preview is available for this file type.'
        : 'Pick a file to preview.'
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
    selectedFileToEdit,
    async (file) => {
      clearCurrentFilePreview()

      if (!file || file.category !== 'image') {
        return
      }

      const token = ++previewToken

      try {
        const fileId = getFileId(file)
        if (!fileId) {
          return
        }

        const response = await fetchFilePreview(fileId)
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

  const resetUploadSelection = () => {
    selectedFiles.value = []
    uploadError.value = ''
    isDragging.value = false
  }

  const openNewUploadPanel = () => {
    uploadMode.value = 'upload'
    selectedFileToEdit.value = null
    selectedFileToEditId.value = ''
    resetUploadSelection()
    showUploadPanel.value = true
  }

  const openEditFilePanel = (file = null) => {
    uploadMode.value = 'edit'
    selectedFileToEdit.value = file
    selectedFileToEditId.value = getFileId(file) ?? ''
    resetUploadSelection()
    showUploadPanel.value = true
  }

  const setSelectedFiles = (files) => {
    selectedFiles.value = isEditingFile.value ? [files[0]] : [...selectedFiles.value, ...files]
    uploadError.value = ''

    if (isEditingFile.value && files.length > 1) {
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
    const folderId = getFolderId(selectedFolder.value)
    const replacementFileId = selectedFileToEditId.value

    if (!folderId || !selectedFiles.value.length || isSubmittingUpload.value) {
      return
    }

    uploadError.value = ''
    isSubmittingUpload.value = true

    try {
      if (isEditingFile.value) {
        if (!replacementFileId) {
          uploadError.value = 'Unable to identify the file to replace.'
          return
        }

        await workspace.handleEditFile(
          selectedFiles.value[0],
          folderId,
          replacementFileId,
        )
      } else {
        for (const file of selectedFiles.value) {
          await workspace.handleUploadFile(file, folderId)
        }
      }

      selectedFiles.value = []
      selectedFileToEdit.value = null
      selectedFileToEditId.value = ''
      showUploadPanel.value = false
    } catch (err) {
      uploadError.value = err?.response?.data?.message ?? 'Unable to complete the file upload.'
    } finally {
      isSubmittingUpload.value = false
    }
  }

  const closeUploadPanel = () => {
    selectedFileToEdit.value = null
    selectedFileToEditId.value = ''
    uploadMode.value = 'upload'
    resetUploadSelection()
    isSubmittingUpload.value = false
    showUploadPanel.value = false
    clearSelectedReplacementPreview()
  }

  return {
    showUploadPanel,
    selectedFiles,
    selectedFileToEdit,
    selectedFileToEditId,
    uploadError,
    fileInputRef,
    isDragging,
    isSubmittingUpload,
    isEditingFile,
    selectedReplacementFile,
    canSubmitUpload,
    activePreviewUrl,
    activePreviewLabel,
    activePreviewEmptyLabel,
    openNewUploadPanel,
    openEditFilePanel,
    handleFileSelect,
    handleFileDrop,
    removeSelectedFile,
    submitUpload,
    closeUploadPanel,
  }
}
