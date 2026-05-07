import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const APP_TITLE = 'Promotional Materials Portal'

const routes = [
  { path: '/', name: 'landing', component: () => import('../features/landing-page/pages/LandingPage.vue'), meta: { guestOnly: true, title: `Home | ${APP_TITLE}` } },
  { path: '/login', name: 'login', component: () => import('../features/auth/pages/LoginPage.vue'), meta: { guestOnly: true, title: `Login | ${APP_TITLE}` } },
  { path: '/register', name: 'register', component: () => import('../features/auth/pages/RegisterPage.vue'), meta: { guestOnly: true, title: `Register | ${APP_TITLE}` } },
  { path: '/client', name: 'client-dashboard', component: () => import('../features/client-dashboard/pages/ClientDashboardPage.vue'), meta: { requiresAuth: true, role: 'client', title: `Client Dashboard | ${APP_TITLE}` } },
  { path: '/agent', name: 'agent-dashboard', component: () => import('../features/agent-dashboard/pages/AgentDashboardPage.vue'), meta: { requiresAuth: true, role: 'agent', title: `Agent Dashboard | ${APP_TITLE}` } },
  { path: '/admin', name: 'admin-dashboard', component: () => import('../features/admin-dashboard/pages/AdminDashboardPage.vue'), meta: { requiresAuth: true, role: 'admin', title: `Admin Dashboard | ${APP_TITLE}` } },
  {
    path: '/production',
    name: 'production-dashboard',
    component: () => import('../features/production-dashboard/pages/ProductionDashboardPage.vue'),
    meta: { requiresAuth: true, role: 'production', title: `Production Dashboard | ${APP_TITLE}` },
    children: [
      { path: '', redirect: { name: 'production-folder-index' } },
      {
        path: 'folders',
        name: 'production-folder-index',
        component: () => import('../features/production-dashboard/pages/ProductionFolderIndexPage.vue'),
        meta: { title: `Production Folders | ${APP_TITLE}` },
      },
      {
        path: 'folders/:folderId',
        name: 'production-folder-detail',
        component: () => import('../features/production-dashboard/pages/ProductionFolderFilesPage.vue'),
        meta: { title: `Folder Workspace | ${APP_TITLE}` },
      },
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

router.afterEach((to) => {
  const matchedWithTitle = [...to.matched].reverse().find((record) => record.meta?.title)
  document.title = matchedWithTitle?.meta?.title ?? APP_TITLE
})

export default router
