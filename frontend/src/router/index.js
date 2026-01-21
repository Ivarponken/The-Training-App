import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import StatsView from '../views/StatsView.vue'
import ListView from '@/views/ListView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/stats',
      name: 'stats',
      component: StatsView,
    },
    {
      path: '/lista',
      name: 'lista',
      component: ListView,
    },
  ],
})

export default router
