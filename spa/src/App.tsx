import { useEffect, useState } from 'react'
import SubmitTicketForm from './components/SubmitTicketForm'
import TicketTable from './components/TicketTable'
import TicketDetail from './components/TicketDetail'
import Analytics from './components/Analytics'
import { api } from './api'
import type { Ticket, Paginated } from './types'

type Filters = { q?: string; status?: 'new' | 'open' | 'closed' | undefined; category?: string | undefined }

export default function App() {
  const [page, setPage] = useState<Paginated<Ticket> | null>(null)
  const [filters, setFilters] = useState<Filters>({})
  const [selected, setSelected] = useState<Ticket | null>(null)
  const [currentPage, setCurrentPage] = useState<number>(1)

  async function load(p: number = 1) {
    const res = (await api.listTickets({ ...filters, page: p })) as Paginated<Ticket>
    setPage(res)
    setCurrentPage(p)
  }

  useEffect(() => {
    load(1)
  }, [filters.q, filters.status, filters.category])

  return (
    <div style={{ maxWidth: 1100, margin: '24px auto', padding: '0 16px', display: 'grid', gap: 16 }}>
      <h2>Smart Ticket Triage</h2>

      <SubmitTicketForm onCreated={() => load(1)} />

      <TicketTable
        page={page}
        onPage={(n) => load(n)}
        onOpen={(t) => setSelected(t)}
        filters={filters}
        setFilters={setFilters}
      />

      <Analytics />

      <TicketDetail
        ticketId={selected?.id ?? null}
        onClose={() => {
          setSelected(null)
          load(currentPage)
        }}
      />
    </div>
  )
}
