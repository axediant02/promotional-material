import api from './api'

export const fetchProductionWorkspace = () => api.get('/production/dashboard')

export const fetchProductionFolder = (folderId) => api.get(`/folders/${folderId}`)
