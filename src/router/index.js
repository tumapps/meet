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
    meta: { auth: true, name: 'Home', isBanner: false },
    component: () => import('@/views/modules/appointment/DashboardPage.vue')
  },
  {
    path: 'book-appointment',
    name: prefix + '.book-appointment',
    meta: { auth: true, name: 'Book Appointment' },
    component: () => import('@/views/modules/appointment/BookAppointment.vue')
  },
  {
    path: 'doctor-visit',
    name: prefix + '.doctor-visit',
    meta: { auth: true, name: 'Doctor Visit' },
    component: () => import('@/views/modules/appointment/DoctorVisit.vue')
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
  } else {
    // Non-protected route, allow access
    next()
  }
})

export default router
