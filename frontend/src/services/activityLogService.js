import api from './api'

export const fetchActivityLogs = () => api.get('/admin/activity-logs')
export const fetchRecycleBin = () => api.get('/recycle-bin')
