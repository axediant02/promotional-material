import { computed, ref } from 'vue'
import { downloadFile, restoreFile, updateFile, uploadFile } from '../../../services/fileService'
import { formatDateLabel, formatShortId, getFileId, getFolderId } from '../utils/productionDashboardHelpers'

export const useProductionFileActions = ({ files, recycleBinFiles, error, currentUser }) => {
  const uploadingFileId = ref('')
  const updatingFileId = ref('')
  const downloadingFileId = ref('')
  const restoringFileId = ref('')

  const currentUserName = computed(() => currentUser.value?.name ?? 'You')

  const enrichFile = (file) => ({
    ...file,
    shortId: formatShortId(file.file_id, 'FILE'),
    uploaderName: currentUserName.value,
    updatedLabel: formatDateLabel(file.updated_at),
    folderName: file.folder?.folder_name ?? 'Workspace',
  })

  const handleUploadFile = async (file, folderId) => {
    const resolvedFolderId = getFolderId({ folder_id: folderId }) ?? folderId

    if (!resolvedFolderId) {
      error.value = 'Unable to identify the destination folder.'
      return
    }

    uploadingFileId.value = resolvedFolderId
    error.value = ''

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder_id', resolvedFolderId)

      const response = await uploadFile(formData)
      const newFile = response.data.data.file

      files.value = [enrichFile(newFile), ...files.value]
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to upload the file.'
      throw err
    } finally {
      uploadingFileId.value = ''
    }
  }

  const handleEditFile = async (file, folderId, fileId) => {
    const resolvedFileId = getFileId({ file_id: fileId }) ?? fileId
    const resolvedFolderId = getFolderId({ folder_id: folderId }) ?? folderId

    if (!resolvedFileId) {
      error.value = 'Unable to identify the file to replace.'
      return
    }

    if (!resolvedFolderId) {
      error.value = 'Unable to identify the destination folder.'
      return
    }

    updatingFileId.value = resolvedFileId
    error.value = ''

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder_id', resolvedFolderId)
      formData.append('_method', 'PATCH')

      const response = await updateFile(resolvedFileId, formData)
      const updatedFile = response.data.data.file
      const enrichedFile = enrichFile(updatedFile)

      files.value = [enrichedFile, ...files.value.filter((item) => item.file_id !== enrichedFile.file_id)]
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to replace the file.'
      throw err
    } finally {
      updatingFileId.value = ''
    }
  }

  const handleDownloadFile = async (file) => {
    const fileId = getFileId(file)

    if (!fileId) {
      error.value = 'Unable to identify the file to download.'
      return
    }

    downloadingFileId.value = fileId
    error.value = ''

    try {
      await downloadFile({ ...file, file_id: fileId })
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to download the file.'
    } finally {
      downloadingFileId.value = ''
    }
  }

  const restoreRecycleFile = async (fileId) => {
    const resolvedFileId = getFileId({ file_id: fileId }) ?? fileId

    if (!resolvedFileId) {
      error.value = 'Unable to identify the file to restore.'
      return
    }

    restoringFileId.value = resolvedFileId
    error.value = ''

    try {
      const response = await restoreFile(resolvedFileId)
      const restoredFile = response.data.data.file

      recycleBinFiles.value = recycleBinFiles.value.filter((file) => getFileId(file) !== resolvedFileId)
      files.value = [restoredFile, ...files.value]
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to restore the file.'
    } finally {
      restoringFileId.value = ''
    }
  }

  return {
    uploadingFileId,
    updatingFileId,
    downloadingFileId,
    restoringFileId,
    handleUploadFile,
    handleEditFile,
    handleDownloadFile,
    restoreRecycleFile,
  }
}
