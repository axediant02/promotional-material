import api from './api'

export const fetchClientWorkspace = () => api.get('/client/dashboard')

export const fetchClientFile = (fileId) => api.get(`/files/${fileId}`)
