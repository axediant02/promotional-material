import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/', name: 'landing', component: () => import('../features/landing-page/pages/LandingPage.vue'), meta: { guestOnly: true } },
  { path: '/login', name: 'login', component: () => import('../features/auth/pages/LoginPage.vue'), meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: () => import('../features/auth/pages/RegisterPage.vue'), meta: { guestOnly: true } },
  { path: '/client', name: 'client-dashboard', component: () => import('../features/client-dashboard/pages/ClientDashboardPage.vue'), meta: { requiresAuth: true, role: 'client' } },
  { path: '/agent', name: 'agent-dashboard', component: () => import('../features/agent-dashboard/pages/AgentDashboardPage.vue'), meta: { requiresAuth: true, role: 'agent' } },
  { path: '/agent-new', redirect: { name: 'agent-dashboard' } },
  { path: '/admin', name: 'admin-dashboard', component: () => import('../features/admin-dashboard/pages/AdminDashboardPage.vue'), meta: { requiresAuth: true, role: 'admin' } },
  { path: '/admin-new', redirect: { name: 'admin-dashboard' } },
  {
    path: '/production',
    name: 'production-dashboard',
    component: () => import('../features/production-dashboard/pages/ProductionDashboardPage.vue'),
    meta: { requiresAuth: true, role: 'production' },
    children: [
      { path: '', redirect: { name: 'production-folder-index' } },
      { path: 'folders', name: 'production-folder-index', component: () => import('../features/production-dashboard/pages/ProductionFolderIndexPage.vue') },
      { path: 'folders/:folderId', name: 'production-folder-detail', component: () => import('../features/production-dashboard/pages/ProductionFolderFilesPage.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  if (!authStore.isReady && authStore.token) {
    await authStore.bootstrap()
  }

  if (to.meta.requiresAuth && !authStore.user) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && authStore.user) {
    return authStore.defaultRoute
  }

  if (to.meta.role && authStore.user?.role !== to.meta.role) {
    return authStore.defaultRoute
  }

  return true
})

export default router
