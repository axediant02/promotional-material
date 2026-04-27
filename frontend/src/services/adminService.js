import api from './api'

export const fetchAdminAssignments = () => api.get('/admin/assignments')
export const saveAdminAssignment = (payload) => api.post('/admin/assignments', payload)
export const removeAdminAssignment = (assignmentId) => api.delete(`/admin/assignments/${assignmentId}`)
export const fetchAdminRequests = () => api.get('/admin/requests')
export const fetchAdminUsers = () => api.get('/admin/users')
export const createAdminAgent = (payload) => api.post('/admin/agents', payload)
export const updateAdminRequestDueDate = (requestId, payload) => api.patch(`/admin/requests/${requestId}`, payload)
export const updateAdminUserRole = (userId, payload) => api.patch(`/admin/users/${userId}`, payload)
export const fetchAdminActivityLogs = () => api.get('/admin/activity-logs')
