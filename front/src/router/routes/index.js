import MainLayout from '@/layouts/MainLayout.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Home from '@/pages/Home/index.vue'

export const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      { path: '', name: 'home', component: Home },
      {
        path: 'horses',
        name: 'horses',
        component: () => import('@/pages/Horses/index.vue'),
      },
      {
        path: 'horses/:id',
        name: 'horse-details',
        component: () => import('@/pages/Horses/HorseDetails.vue'),
        props: true,
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('@/pages/About/index.vue'),
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('@/pages/Contact/index.vue'),
      },
    ],
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/Auth/Login.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requireAuth: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('@/pages/Admin/index.vue'),
      },
      {
        path: 'horses',
        name: 'admin-horses',
        component: () => import('@/pages/Admin/Horses/index.vue'),
      },
      {
        path: 'horses/create',
        name: 'admin-horse-create',
        component: () => import('@/pages/Admin/Horses/HorseForm.vue'),
      },
      {
        path: 'horses/:id/edit',
        name: 'admin-horse-edit',
        component: () => import('@/pages/Admin/Horses/HorseForm.vue'),
        props: true,
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: () => import('@/pages/Admin/Settings/index.vue'),
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/pages/NotFound/index.vue'),
  },
]
