import api from './api'

export const fetchFiles = (params = {}) => api.get('/files', { params })
export const uploadFile = (payload) =>
  api.post('/files', payload, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
export const deleteFile = (id) => api.delete(`/files/${id}`)
export const restoreFile = (id) => api.post(`/files/${id}/restore`)
export const downloadFile = async (file) => {
  const response = await api.get(`/files/${file.file_id}/download`, {
    responseType: 'blob',
  })

  const blobUrl = window.URL.createObjectURL(response.data)
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = file.file_name || 'download'
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(blobUrl)
}
