import { createRouter, createWebHistory } from 'vue-router'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import UserManagement from '../pages/UserManagement.vue'
import CashbookEntry from '../pages/cashbook/CashbookEntry.vue'
import AccountManagement from '../pages/accounts/AccountManagement.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { 
      requiresAuth: true,
      permission: 'view_dashboard'
    }
  },
  {
    path: '/users',
    name: 'UserManagement',
    component: UserManagement,
    meta: { 
      requiresAuth: true,
      permission: 'manage_users'
    }
  },
  {
    path: '/accounts',
    name: 'AccountManagement',
    component: AccountManagement,
    meta: { 
      requiresAuth: true,
      permission: 'manage_accounts'
    }
  },
  {
    path: '/cashbook',
    name: 'CashbookEntry',
    component: CashbookEntry,
    meta: { 
      requiresAuth: true,
      permission: 'create_transactions'
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token')
  const isAuthenticated = !!token

  // Handle guest-only routes (like login)
  if (to.meta.requiresGuest && isAuthenticated) {
    return next('/dashboard')
  }

  // Handle protected routes
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  // Check permissions for authenticated routes
  if (to.meta.requiresAuth && to.meta.permission) {
    try {
      const response = await fetch('/api/user', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })

      if (!response.ok) {
        localStorage.removeItem('token')
        return next('/login')
      }

      const user = await response.json()
      
      // Check if user has required permission
      const hasPermission = user.role?.permissions?.some(
        permission => permission.name === to.meta.permission
      )

      if (!hasPermission) {
        // Redirect to dashboard with error message
        return next({
          path: '/dashboard',
          query: { error: 'insufficient_permissions' }
        })
      }
    } catch (error) {
      console.error('Auth check failed:', error)
      localStorage.removeItem('token')
      return next('/login')
    }
  }

  next()
})

export default router
