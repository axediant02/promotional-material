import api from './api'

export const createRequest = (payload) => api.post('/requests', payload)
export const fetchRequests = (params = {}) => api.get('/requests', { params })
export const fetchMyRequests = (params = {}) => api.get('/requests', { params })
export const fetchProductionRequests = () => api.get('/production/requests')
export const updateProductionRequestStatus = (requestId, payload) => api.patch(`/production/requests/${requestId}`, payload)
