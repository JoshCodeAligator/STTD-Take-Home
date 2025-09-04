const base = ''

async function http<T>(url: string, init?: RequestInit): Promise<T> {
  const res = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...init
  })
  if (!res.ok) throw new Error(await res.text())
  return res.json()
}

export const api = {
  listTickets: (params: { q?: string; status?: string; category?: string; page?: number } = {}) => {
    const u = new URL('/api/tickets', window.location.origin)
    u.pathname = '/api/tickets'
    Object.entries(params).forEach(([k, v]) => v ? u.searchParams.set(k, String(v)) : null)
    return http(u.pathname + (u.search || ''))
  },
  createTicket: (payload: any) => http('/api/tickets', { method: 'POST', body: JSON.stringify(payload) }),
  showTicket: (id: number) => http(`/api/tickets/${id}`),
  updateTicket: (id: number, payload: any) => http(`/api/tickets/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
  classify: (id: number) => http(`/api/tickets/${id}/classify`, { method: 'POST' }),
  addNote: (id: number, payload: { body: string; author?: string }) =>
    http(`/api/tickets/${id}/notes`, { method: 'POST', body: JSON.stringify(payload) }),
  updateNote: (noteId: number, payload: { body: string }) =>
    http(`/api/notes/${noteId}`, { method: 'PUT', body: JSON.stringify(payload) }),
  deleteNote: (noteId: number) => http(`/api/notes/${noteId}`, { method: 'DELETE' }),
  analytics: () => http('/api/analytics/summary')
}
