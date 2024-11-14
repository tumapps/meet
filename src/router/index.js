import { createRouter, createWebHistory } from 'vue-router'
import authRoutes from './auth'
// import { useMenuStore } from '@/store/menuStore';

// const menuStore = useMenuStore();


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
    component: () => import('@/views/modules/appointment/DashboardPage.vue')
  },
  {
    path: 'users',
    name: prefix + '.users',
    meta: { requiresAuth: true, name: 'users' },
    component: () => import('@/views/modules/appointment/Users.vue')
  },
  {
    path: '/settings',
    name: prefix + '.settings', // Now it will become appointment.dashboard
    meta: { requiresAuth: true, name: 'settings' },
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

  {
    path: '/appointments',
    name: 'appointments',
    meta: { requiresAuth: true, },
    component: () => import('@/views/modules/appointment/Appointments.vue'),
  },
  {
    path: '/availability',
    name: 'availability',
    meta: { requiresAuth: true, },
    component: () => import('@/views/modules/appointment/Availability.vue'),
  },

  //venues routes
  {
    path: '/venue-management',
    name: 'venue-management',
    meta: { requiresAuth: true, },
    component: () => import('@/views/modules/venues/VenueManagement.vue'),
  },
  {
    path: '/meetings-approval',
    name: 'meetings-approval',
    meta: { requiresAuth: true, },
    component: () => import('@/views/modules/venues/MeetingsApproval.vue'),
  },
  {
    path: '/events',
    name: 'events',
    meta: { requiresAuth: true, },
    component: () => import('@/views/modules/venues/EventsView.vue'),
  },
  {
    path: '/admin@venues',
    name: 'venues',
    component: () => import('@/views/modules/appointment/VenuesView.vue'),
    meta: {
      requiresAuth: true,
      customMenus: [
        { route: "venues", label: "Dashboard", icon: "home" },
        { route: "meetings-approval", label: "Meetings Approval", icon: "check-circle" },
        { route: "venue-management", label: "Venue Management", icon: "domain" },
        { route: "events", label: "Events", icon: "calendar" }
      ]
    }
  },

  //admin routes
  {
    path: '/A-users',
    name: 'A-users',
    meta: { requiresAuth: true, },
    component: () => import('@/views/iam-admin/admin/UsersView.vue'),
  },
  {
    path: '/roles',
    name: 'roles',
    meta: { requiresAuth: true, },
    component: () => import('@/views/iam-admin/admin/RolesView.vue'),
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('@/views/iam-admin/admin/AdminDashboardView.vue'),
    meta: {
      requiresAuth: true,
      customMenus: [
        { route: "admin", label: "Dashboard", icon: "home" },
        { route: "roles", label: "Roles", icon: "clipboard" },
        { route: "A-users", label: "Users", icon: "user" },
      ]
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
    path: "/:catchAll(.*)", // Update the wildcard route
    name: "Error404",
    component: Error404,
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

  //check is the router === admin@venues
  //   if (to.path === '/admin@venues') {
  // //set up my own menus here
  // const menus = [
  //   {
  //     route: "default.dashboard",
  //     label: "Dashboard",
  //     icon: "home" // Add the appropriate icon here if not using "home"
  //   },
  //   {
  //     route: "venue-management",
  //     label: "Venue Management",
  //     icon: "building" // Replace with the relevant Font Awesome icon, like "building" for venues
  //   },
  //   {
  //     route: "meetings-approval",
  //     label: "Meetings Approval",
  //     icon: "check-circle" // Replace with the relevant Font Awesome icon, like "check-circle"
  //   },
  //   {
  //     route: "settings",
  //     label: "Settings",
  //     icon: "cog" // Replace with the relevant Font Awesome icon, like "cog" for settings
  //   }


  // ];

  // menuStore.setMenus(menus);

  // console.log(menuStore.menus);



  //   }


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
      next('/'); // Adjust the redirect path as needed
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  } else if (to.path === '/request-password-reset') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next('/'); // Adjust the redirect path as needed
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  } else if (to.path === '/reset-password') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next('/'); // Adjust the redirect path as 
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  } else if (to.path === '/email-confirmed') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // User is already authenticated, redirect back
      next('/'); // Adjust the redirect path as 
    } else {
      // User is not authenticated, proceed to login page
      next();
    }
  } else if (to.path === '/lockscreen') {
    // Redirect authenticated users away from the login page
    const token = localStorage.getItem('user.token');
    if (token) {
      // go back to the previous page if the user is already authenticated
      console.log(from.fullPath);
      next(from.fullPath); // Adjust the redirect path as needed

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
