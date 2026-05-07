import api from './api'

export const fetchActiveChatThread = () => api.get('/chat/thread')
export const fetchChatThreads = () => api.get('/chat/threads')
export const fetchChatThread = (threadId) => api.get(`/chat/threads/${threadId}`)
export const sendChatMessage = (threadId, payload) => api.post(`/chat/threads/${threadId}/messages`, payload)
export const markChatThreadAsRead = (threadId) => api.post(`/chat/threads/${threadId}/read`)
