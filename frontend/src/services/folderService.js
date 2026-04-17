import api from './api'

export const fetchFolders = (params = {}) => api.get('/folders', { params })
export const createFolder = (payload) => api.post('/folders', payload)
