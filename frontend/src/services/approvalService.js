import api from './api'

export const createAgent = (payload) => api.post('/admin/agents', payload)
