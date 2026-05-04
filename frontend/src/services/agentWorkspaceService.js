import api from './api'

export const fetchAgentWorkspace = () => api.get('/agent/dashboard')
