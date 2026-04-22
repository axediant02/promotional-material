import api from './api'

export const fetchAdminRequests = () => api.get('/admin/requests')
export const updateAdminRequestDueDate = (requestId, payload) => api.patch(`/admin/requests/${requestId}`, payload)
