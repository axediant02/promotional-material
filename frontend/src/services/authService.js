import api, { ensureCsrfCookie } from './api'

export const login = async (payload) => {
  await ensureCsrfCookie()

  return api.post('/auth/login', payload)
}

export const register = async (payload) => {
  await ensureCsrfCookie()

  return api.post('/auth/register', payload)
}

export const currentUser = () => api.get('/auth/currentUser')
export const logout = () => api.post('/auth/logout')
