import api from './api'

export const fetchClientWorkspace = () => api.get('/client/dashboard')
