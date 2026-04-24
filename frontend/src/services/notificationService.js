import api from './api'

export const fetchNotifications = () => api.get('/notifications')
export const markNotificationAsRead = (notificationId) => api.patch(`/notifications/${notificationId}`)
export const markAllNotificationsAsRead = () => api.post('/notifications/read-all')
