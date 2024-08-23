const authChildRoutes = (prefix) => [
  {
    path: 'login',
    name: prefix + '.login',
    meta: { auth: false, name: 'Login' },
    component: () => import('@/views/iam-admin/authentication/SignIn.vue')
  }
]
export default [
  {
    path: '/auth',
    name: 'auth',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: authChildRoutes('auth')
  }
]
