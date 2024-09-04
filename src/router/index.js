import { createRouter, createWebHistory } from 'vue-router'
import authRoutes from './auth'

// Lazy load components
const NotFound = () => import('@/components/NotFound.vue')
const ErrorPage = () => import('@/views/ErrorPage.vue')
const Lockscreen = () => import('@/views/iam-admin/authentication/LockScreen.vue')

// Default routes
export const defaultChildRoutes = (prefix) => [
  {
    path: '',
    name: prefix + '.dashboard', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'Home', isBanner: false },
    component: () => import('@/views/modules/appointment/DashboardPage.vue')
  },
  {
    path: 'book-appointment',
    name: prefix + '.book-appointment',
    meta: { requiresAuth: true, name: 'Book Appointment' },
    component: () => import('@/views/modules/appointment/BookAppointment.vue')
  },
  {
    path: '/settings',
    name: prefix + '.settings', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'settings'},
    component: () => import('@/views/modules/appointment/Settings.vue')
  },
  {
    path: '/new-user',
    name: prefix + '.adduser',
    meta: { requiresAuth: true, name: 'Add User', isBanner: true },
    component: () => import('@/views/iam-admin/AddUser.vue'),
  },
  {
    path: '/profile',
    name: 'profile',
    meta: { requiresAuth: true, name: 'profile' },
    component: () => import('@/views/iam-admin/Profile.vue'),
  },
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
    path: "/:catchAll(.*)", // Update the wildcard route
    name: "Error404",
    component: NotFound,
  },
  {
    path: '/error/:code',
    name: 'ErrorPage',
    component: ErrorPage,
    props: true
  },
  {
    path: '/LockScreen',
    name: 'locked',
    component: Lockscreen,
    meta: {
      requiresAuth: true // Add meta field to indicate protected route
    }
  },
  {
    path: '/appointment/:id',
    name: 'appointment',
    meta: { requiresAuth: true, },
    component: () => import('@/views/modules/appointment/Appointment.vue'),
  },
  ...authRoutes,
]

const router = createRouter({
  linkActiveClass: 'active',
  linkExactActiveClass: 'exact-active',
  history: createWebHistory(import.meta.env.BASE_URL),
  base: import.meta.env.BASE_URL,
  routes
})

router.beforeEach(async (to, from, next) => {
  
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('user.token')
    if (token) {
      // User is authenticated, proceed to the route
      next()
    } else {
      // User is not authenticated, redirect to login
      next('/auth/login')
    }
  } else if (to.path === '/auth/login') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect to dashboard or home
      next(''); // Adjust the redirect path as needed
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  } else if (to.path === '/request-password-reset') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next(''); // Adjust the redirect path as needed
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  }else if (to.path === '/reset-password') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next(''); // Adjust the redirect path as 
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  } else if (to.path === '/email-confirmed') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next(''); // Adjust the redirect path as 
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  }
  else if (to.path === '/LockScreen') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next(''); // Adjust the redirect path as 
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  }
  else {
    // Non-protected route, allow access
    next();
  }
})

export default router
