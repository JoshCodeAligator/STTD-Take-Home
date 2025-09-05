// Axios Client for Our Laravel API.
import axios from 'axios'
export const api = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  timeout: 15000
})

export const listTickets  = (params = {})             => api.get('/tickets', { params }).then(r => r.data)
export const createTicket = (payload)                 => api.post('/tickets', payload).then(r => r.data)
export const getTicket    = (id)                      => api.get(`/tickets/${id}`).then(r => r.data)
export const updateTicket = (id, payload)             => api.patch(`/tickets/${id}`, payload).then(r => r.data)
export const classify     = (id)                      => api.post(`/tickets/${id}/classify`).then(r => r.data)
export const getStats     = ()                        => api.get('/stats').then(r => r.data)

// I developed this helper function so that our UI display still works despite having a faulty backend
export function safe(call, fallback) {
  return call().catch((err) => {
    console.error('[API error]', err?.response?.data || err?.message || err)
    return fallback
  })
}