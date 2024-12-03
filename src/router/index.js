import { createRouter, createWebHistory } from 'vue-router'
import authRoutes from './auth'

// Lazy load components
const Error404 = () => import('@/components/Error404.vue')
const ErrorPage = () => import('@/views/ErrorPage.vue')
const Lockscreen = () => import('@/views/iam-admin/authentication/LockScreen.vue')

// Default routes
export const defaultChildRoutes = (prefix) => [
  {
    path: '',
    name: prefix + '.dashboard', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'Home', isBanner: false },
    component: () => import('@/views/modules/appointment/DashboardPageView.vue')
  },
  {
    path: 'users',
    name: prefix + '.users',
    meta: { requiresAuth: true, name: 'users' },
    component: () => import('@/views/iam-admin/admin/UsersView.vue')
  },
  {
    path: '/settings',
    name: prefix + '.settings', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'settings' },
    component: () => import('@/views/modules/appointment/SettingsView.vue')
  },
  {
    path: '/new-user',
    name: prefix + '.adduser',
    meta: { requiresAuth: true, name: 'Add User', isBanner: true },
    component: () => import('@/views/iam-admin/AddUser.vue')
  },
  {
    path: '/profile',
    name: 'profile',
    meta: { requiresAuth: true, name: 'profile' },
    component: () => import('@/views/iam-admin/ProfileView.vue')
  },

  {
    path: '/appointments',
    name: 'appointments',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/appointment/AppointmentsView.vue')
  },
  {
    path: '/availability',
    name: 'availability',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/appointment/AvailabilityView.vue')
  },

  //venues routes
  {
    path: '/venue-management',
    name: 'venue-management',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/venues/VenueManagement.vue')
  },
  {
    path: '/meetings-approval',
    name: 'meetings-approval',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/venues/MeetingsApproval.vue')
  },
  {
    path: '/events',
    name: 'events',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/venues/EventsView.vue')
  },
  {
    path: '/admin@venues',
    name: 'venues',
    component: () => import('@/views/modules/appointment/VenuesView.vue'),
    meta: {
      requiresAuth: true
    }
  },

  //admin routes
  // {
  //   path: '/users',
  //   name: 'users',
  //   meta: { requiresAuth: true },
  //   component: () => import('@/views/iam-admin/admin/UsersView.vue')
  // },
  {
    path: '/roles',
    name: 'roles',
    meta: { requiresAuth: true },
    component: () => import('@/views/iam-admin/admin/RolesView.vue')
  },
  {
    path: '/permissions',
    name: 'permissions',
    meta: { requiresAuth: true },
    component: () => import('@/views/iam-admin/admin/PermissionsView.vue')
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('@/views/iam-admin/admin/AdminDashboardView.vue'),
    meta: {
      requiresAuth: true
    }
  }
]

const routes = [
  // Default Pages
  {
    path: '',
    name: 'dashboard',
    component: () => import('@/layouts/modules/AppointmentLayout.vue'),
    children: defaultChildRoutes('default'), // Changed from 'default' to 'appointment'
    meta: {
      requiresAuth: true // Add meta field to indicate protected route
    }
  },
  {
    path: '/request-password-reset',
    name: 'request-password-reset',
    component: () => import('@/views/iam-admin/authentication/RequestPasswordReset.vue')
  },

  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/views/iam-admin/authentication/ResetPassword.vue')
  },
  {
    path: '/email-confirmed',
    name: 'email-confirmed',
    component: () => import('@/views/iam-admin/authentication/EmailConfirmed.vue')
  },
  {
    path: '/:catchAll(.*)', // Update the wildcard route
    name: 'Error404',
    component: Error404
  },
  {
    path: '/error/:code',
    name: 'ErrorPage',
    component: ErrorPage,
    props: true
  },
  {
    path: '/lockscreen',
    name: 'locked',
    component: Lockscreen,
    meta: {
      requiresAuth: true // Add meta field to indicate protected route
    }
  },

  ...authRoutes
]

const router = createRouter({
  linkActiveClass: 'active',
  linkExactActiveClass: 'exact-active',
  history: createWebHistory(import.meta.env.BASE_URL),
  base: import.meta.env.BASE_URL,
  routes
})

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('user.token')
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (token) {
      // User is authenticated, proceed to the route
      return next()
    } else {
      // User is not authenticated, redirect to login
      return next('/auth/login')
    }
  }

  // Handle specific routes that shouldn't be accessed by authenticated users
  const unauthenticatedRoutes = ['/auth/login', '/request-password-reset', '/reset-password', '/email-confirmed']

  if (unauthenticatedRoutes.includes(to.path)) {
    if (token) {
      // User is already authenticated, redirect to home/dashboard
      return next('/')
    } else {
      // User is not authenticated, proceed to the route
      return next()
    }
  }

  // Handle special case for lockscreen route
  if (to.path === '/lockscreen') {
    if (token) {
      // Authenticated user, redirect to the previous page or dashboard
      return next(from.fullPath || '/')
    }
    // Unauthenticated user, proceed to lockscreen
    return next()
  }

  // For non-protected routes, allow access
  return next()
})

export default router
