import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LandingPage from '../features/landing-page/pages/LandingPage.vue'
import LoginPage from '../features/auth/pages/LoginPage.vue'
import RegisterPage from '../features/auth/pages/RegisterPage.vue'
import ClientDashboardPage from '../features/client-dashboard/pages/ClientDashboardPage.vue'
import AgentWorkspacePage from '../features/agent-workspace/pages/AgentWorkspacePage.vue'
import AdminOverviewPage from '../features/admin/pages/AdminOverviewPage.vue'
import ProductionDashboardPage from '../features/production-dashboard/pages/ProductionDashboardPage.vue'
import AgentDashboardPage from '../features/agent-dashboard/pages/AgentDashboardPage.vue'
import AdminDashboardPage from '../features/admin-dashboard/pages/AdminDashboardPage.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingPage, meta: { guestOnly: true } },
  { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: RegisterPage, meta: { guestOnly: true } },
  { path: '/client', name: 'client-dashboard', component: ClientDashboardPage, meta: { requiresAuth: true, role: 'client' } },
  { path: '/agent', name: 'agent-workspace', component: AgentWorkspacePage, meta: { requiresAuth: true, role: 'agent' } },
  { path: '/admin', name: 'admin-overview', component: AdminOverviewPage, meta: { requiresAuth: true, role: 'production' } },
  { path: '/production', name: 'production-dashboard', component: ProductionDashboardPage, meta: { requiresAuth: true, role: 'production' } },
  { path: '/agent-new', name: 'agent-dashboard', component: AgentDashboardPage, meta: { requiresAuth: true, role: 'agent' } },
  { path: '/admin-new', name: 'admin-dashboard', component: AdminDashboardPage, meta: { requiresAuth: true, role: 'admin' } },
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
