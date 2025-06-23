import { createRouter, createWebHistory } from 'vue-router'
import authRoutes from './auth'

// Create an Axios instance

// Lazy load components
const Error404 = () => import('@/components/Error404.vue')
const ErrorPage = () => import('@/views/ErrorPage.vue')
const Lockscreen = () => import('@/views/iam-admin/authentication/LockScreen.vue')
const home = () => import('@/views/modules/appointment/DashboardPageView.vue')
const booking = () => import('@/views/modules/appointment/newBookMeetingView.vue')
const RegistrarDashView = () => import('@/views/modules/venues/RegistrarDashView.vue')
const settings = () => import('@/views/iam-admin/admin/SettingsView.vue')

// Default routes
export const defaultChildRoutes = (prefix) => [
  {
    path: '', // Empty path makes this the default child
    redirect: '/auth/login' // Redirect to the home child route
  },
  {
    path: '/home',
    name: 'home', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'Home', isBanner: false },
    component: home
  },
  {
    path: 'users',
    name: prefix + '.users',
    meta: { requiresAuth: true, name: 'users' },
    component: () => import('@/views/iam-admin/admin/UsersView.vue')
  },
  {
    path: '/admin-settings',
    name: 'settings', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'settings' },
    component: settings
  },
  {
    path: '/meeting-types',
    name: 'meetingTypes',
    component: () => import('@/views/modules/appointment/MeeetingTypesView.vue')
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
  {
    path: '/booking',
    name: 'booking',
    meta: { requiresAuth: true },
    component: booking
  },
  {
    path: '/new-booking',
    name: 'newbooking',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/appointment/newBookMeeting.vue')
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
    path: '/all-events',
    name: 'all-events',
    meta: { requiresAuth: true },
    component: () => import('@/views/modules/venues/EventsView.vue')
  },
  {
    path: '/events-calendar',
    name: 'eventscalendar',
    component: RegistrarDashView,
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*', // Update the wildcard route
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
    path: '/roles',
    name: 'roles',
    meta: { requiresAuth: true },
    component: () => import('@/views/iam-admin/admin/RolesView.vue')
  },
  {
    path: '/permissions',
    meta: { requiresAuth: true },
    name: 'permissions',
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
    path: '/',
    redirect: '/auth/login' // Redirect '/' to a valid route
  },
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
    path: '/lockscreen',
    name: 'locked',
    component: Lockscreen,
    meta: {
      requiresAuth: true // Add meta field to indicate protected route
    }
  },
  {
    path: '/confirm/:meetingId/:attendeeId', // Dynamic parameter `meetingId`
    name: 'AttendanceConfirmation',
    component: () => import('@/views/modules/appointment/AttendanceConfirmationView.vue')
  },

  ...authRoutes
]

const router = createRouter({
  linkActiveClass: 'active',
  linkExactActiveClass: 'exact-active',
  history: createWebHistory(import.meta.env.BASE_URL),
  // base: import.meta.env.BASE_URL,
  routes
})

// router.beforeEach((to, from, next) => {
//   const token = localStorage.getItem('user.token')
//   const user = localStorage.getItem('user.username')
//   // const fallbackRoute = localStorage.getItem('menus') ? JSON.parse(localStorage.getItem('menus'))[0].route : '/'
//   const menus = JSON.parse(localStorage.getItem('menus') || '[]')
//   const fallbackRoute = menus.length && menus[0].route ? menus[0].route : '/'

//   console.log('Fallback Route:', fallbackRoute)
//   console.log('to path:', to.path)

//   // 1. Special handling for the `/lockscreen` route
//   if (to.path === '/lockscreen') {
//     if (token) {
//       console.log('Token fu kind hd:', token)
//       // Redirect authenticated users away from lockscreen
//       if (to.path !== fallbackRoute) {
//         return next(fallbackRoute)
//       } else {
//         return next()
//       }
//     }
//     console.log('kung fu hustle:', token)
//     // Allow unauthenticated users to proceed
//     return next()
//   }

//   // 2. Handle authenticated routes
//   if (to.meta.requiresAuth) {
//     if (token) {
//       // User is authenticated, proceed
//       return next()
//     } else if (!token && user) {
//       // Session locked, redirect to lockscreen
//       return next('/lockscreen')
//     } else {
//       // Not authenticated, redirect to login
//       return next('/auth/login')
//     }
//   }

//   // 3. Handle unauthenticated routes
//   const unauthenticatedRoutes = ['/auth/login', '/request-password-reset', '/reset-password', '/email-confirmed', '/lockscreen']

//   if (unauthenticatedRoutes.includes(to.path)) {
//     if (token && user) {
//       // Authenticated user, redirect to previous route
//       return next(`${fallbackRoute}`)
//     } else if (!token && user) {
//       // Session locked, redirect to lockscreen
//       return next('/lockscreen')
//     }
//     // Allow unauthenticated users to proceed
//     return next()
//   }

//   // 4. Default case: allow access to other routes
//   return next()
// })
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('user.token')
  const user = localStorage.getItem('user.username')

  // Safely get fallback route
  let fallbackRoute = '/'
  try {
    const menus = localStorage.getItem('menus')
    if (menus) {
      const parsedMenus = JSON.parse(menus)
      if (parsedMenus.length > 0 && parsedMenus[0].route) {
        fallbackRoute = parsedMenus[0].route
      }
    }
  } catch (e) {
    console.error('Error parsing menus:', e)
  }

  // Prevent fallback to lockscreen
  if (fallbackRoute === '/lockscreen') {
    fallbackRoute = '/'
  }

  // 1. Handle lockscreen route separately
  if (to.path === '/lockscreen') {
    if (token) {
      // Avoid redirecting to current route
      return next(fallbackRoute !== '/lockscreen' ? fallbackRoute : '/')
    }
    return next() // Allow access to lockscreen
  }

  // 2. Protected routes
  if (to.meta.requiresAuth) {
    if (token) {
      return next() // Allow access
    }
    if (!token && user) {
      // Only redirect to lockscreen if not already going there
      return to.path !== '/lockscreen' ? next('/lockscreen') : next()
    }
    return next('/auth/login') // Redirect to login
  }

  // 3. Public routes
  const publicRoutes = ['/auth/login', '/request-password-reset', '/reset-password', '/email-confirmed']

  if (publicRoutes.includes(to.path)) {
    if (token && user) {
      // Avoid redirect loop to same route
      return to.path !== fallbackRoute ? next(fallbackRoute) : next()
    }
    if (!token && user) {
      // Only redirect to lockscreen if not already going there
      return to.path !== '/lockscreen' ? next('/lockscreen') : next()
    }
    return next() // Allow public access
  }

  // 4. All other routes
  next()
})
export default router
