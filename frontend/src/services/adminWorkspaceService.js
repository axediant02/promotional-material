import api from './api'

export const fetchAdminWorkspace = () => api.get('/admin/dashboard')
