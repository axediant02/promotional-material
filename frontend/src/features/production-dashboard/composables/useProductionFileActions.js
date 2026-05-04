import { computed, ref } from 'vue'
import { downloadFile, restoreFile, updateFile, uploadFile } from '../../../services/fileService'
import { formatDateLabel, formatShortId } from '../utils/productionDashboardHelpers'

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
    uploadingFileId.value = folderId
    error.value = ''

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder_id', folderId)

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

  const handleReplaceFile = async (file, folderId, fileId) => {
    updatingFileId.value = fileId
    error.value = ''

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder_id', folderId)
      formData.append('_method', 'PATCH')

      const response = await updateFile(fileId, formData)
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
    downloadingFileId.value = file.file_id
    error.value = ''

    try {
      await downloadFile(file)
    } catch (err) {
      error.value = err?.response?.data?.message ?? 'Unable to download the file.'
    } finally {
      downloadingFileId.value = ''
    }
  }

  const restoreRecycleFile = async (fileId) => {
    restoringFileId.value = fileId
    error.value = ''

    try {
      const response = await restoreFile(fileId)
      const restoredFile = response.data.data.file

      recycleBinFiles.value = recycleBinFiles.value.filter((file) => (file.file_id ?? file.id) !== fileId)
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
    handleReplaceFile,
    handleDownloadFile,
    restoreRecycleFile,
  }
}
