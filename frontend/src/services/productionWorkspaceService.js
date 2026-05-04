import api from './api'

export const fetchProductionWorkspace = () => api.get('/production/dashboard')
