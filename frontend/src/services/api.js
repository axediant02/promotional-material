import axios from 'axios'

const apiBaseUrl = import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api'
const backendOrigin = apiBaseUrl.replace(/\/api\/?$/, '')

const api = axios.create({
  baseURL: apiBaseUrl,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
})

export const ensureCsrfCookie = () =>
  axios.get(`${backendOrigin}/sanctum/csrf-cookie`, {
    withCredentials: true,
    headers: {
      Accept: 'application/json',
    },
  })

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('pm_token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export default api
