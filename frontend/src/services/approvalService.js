import api from './api'

export const fetchPendingClients = () => api.get('/admin/pending-clients')
export const updatePendingClient = (id, payload) => api.patch(`/admin/pending-clients/${id}`, payload)
export const createAgent = (payload) => api.post('/admin/agents', payload)
