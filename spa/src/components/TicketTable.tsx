import type { Ticket, Paginated } from '../types'

type Filters = { q?: string; status?: 'new' | 'open' | 'closed' | undefined; category?: string | undefined }

type Props = {
  page: Paginated<Ticket> | null
  onPage: (n: number) => void
  onOpen: (t: Ticket) => void
  filters: Filters
  setFilters: (f: Filters) => void
}

const thStyle: React.CSSProperties = { textAlign: 'left', borderBottom: '1px solid #eee', padding: '8px 6px' }
const tdStyle: React.CSSProperties = { borderBottom: '1px solid #f2f2f2', padding: '8px 6px' }

export default function TicketTable({ page, onPage, onOpen, filters, setFilters }: Props) {
  const data: Ticket[] = page?.data ?? []
  const canPrev = !!page && page.current_page > 1
  const canNext = !!page && page.current_page < page.last_page

  return (
    <div style={{ border: '1px solid #ddd', padding: 12, borderRadius: 8 }}>
      <h3>Tickets</h3>

      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 8 }}>
        <input
          placeholder="Search…"
          value={filters.q ?? ''}
          onChange={(e) => setFilters({ ...filters, q: e.target.value || undefined })}
        />
        <select
          value={filters.status ?? ''}
          onChange={(e) =>
            setFilters({ ...filters, status: (e.target.value as 'new' | 'open' | 'closed' | '') || undefined })
          }
        >
          <option value="">All Statuses</option>
          <option value="new">new</option>
          <option value="open">open</option>
          <option value="closed">closed</option>
        </select>
        <input
          placeholder="Category (effective)"
          value={filters.category ?? ''}
          onChange={(e) => setFilters({ ...filters, category: e.target.value || undefined })}
        />
      </div>

      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            <th style={thStyle}>Subject</th>
            <th style={thStyle}>Requester</th>
            <th style={thStyle}>Effective Category</th>
            <th style={thStyle}>Status</th>
            <th style={thStyle}>Classify</th>
            <th style={thStyle}>Notes</th>
          </tr>
        </thead>
        <tbody>
          {data.map((t: Ticket) => (
            <tr key={t.id} style={{ cursor: 'pointer' }} onClick={() => onOpen(t)}>
              <td style={tdStyle}>{t.subject}</td>
              <td style={tdStyle}>{t.requester_email}</td>
              <td style={tdStyle}>{t.effective_category}</td>
              <td style={tdStyle}>{t.status}</td>
              <td style={tdStyle}>{t.classification_status}</td>
              <td style={tdStyle}>{t.notes_count ?? 0}</td>
            </tr>
          ))}
          {data.length === 0 && (
            <tr>
              <td colSpan={6} style={{ padding: 12, textAlign: 'center', color: '#666' }}>
                No tickets
              </td>
            </tr>
          )}
        </tbody>
      </table>

      <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 8 }}>
        <span>
          Page {page?.current_page ?? 0} / {page?.last_page ?? 0}
        </span>
        <div style={{ display: 'flex', gap: 6 }}>
          <button onClick={() => canPrev && onPage((page!.current_page - 1) as number)} disabled={!canPrev}>
            Prev
          </button>
          <button onClick={() => canNext && onPage((page!.current_page + 1) as number)} disabled={!canNext}>
            Next
          </button>
        </div>
      </div>
    </div>
  )
}
