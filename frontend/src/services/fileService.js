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
