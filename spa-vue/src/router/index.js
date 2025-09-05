import { createRouter, createWebHistory } from 'vue-router'
import Tickets from '../pages/Tickets.vue'
import TicketDetail from '../pages/TicketDetail.vue'
import Dashboard from '../pages/Dashboard.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/tickets' },
    { path: '/tickets', component: Tickets },
    { path: '/tickets/:id', component: TicketDetail },
    { path: '/dashboard', component: Dashboard },
    { path: '/:pathMatch(.*)*', redirect: '/tickets' }
  ]
})

export default router