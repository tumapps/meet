const childRoutes = (prefix) => [
  {
    path: '',
    name: prefix + '.dashboard',
    meta: { auth: true, name: 'Appointment' },
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
export default [
  {
    path: '/appointment',
    name: 'appointment',
    component: () => import('@/layouts/modules/AppointmentLayout.vue'),
    children: childRoutes('appointment')
  }
]
