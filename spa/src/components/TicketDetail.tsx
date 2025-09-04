import { useEffect, useState } from 'react'
import { api } from '../api'
import type { Ticket, Note } from '../types'

type Props = { ticketId: number | null; onClose: () => void }

type TicketWithNotes = Ticket & { notes?: Note[] }

const drawerStyle: React.CSSProperties = {
  position: 'fixed',
  right: 16,
  top: 16,
  bottom: 16,
  width: 420,
  background: '#fff',
  border: '1px solid #ddd',
  borderRadius: 8,
  padding: 12,
  overflow: 'auto',
  boxShadow: '0 8px 24px rgba(0,0,0,0.12)',
}

export default function TicketDetail({ ticketId, onClose }: Props) {
  const [ticket, setTicket] = useState<TicketWithNotes | null>(null)
  const [notes, setNotes] = useState<Note[]>([])
  const [override, setOverride] = useState<string>('')
  const [newNote, setNewNote] = useState<string>('')
  const [loading, setLoading] = useState<boolean>(false)

  useEffect(() => {
    if (!ticketId) return
    ;(async () => {
      const t = (await api.showTicket(ticketId)) as TicketWithNotes
      setTicket(t)
      setNotes(t.notes ?? [])
      setOverride(t.override_category ?? '')
    })()
  }, [ticketId])

  if (!ticketId) return null

  async function saveOverride() {
    if (!ticket) return
    setLoading(true)
    const updated = (await api.updateTicket(ticket.id, {
      override_category: override || null,
    })) as TicketWithNotes
    setTicket(updated)
    setLoading(false)
  }

  async function reclassify() {
    if (!ticket) return
    setLoading(true)
    await api.classify(ticket.id)
    setTimeout(async () => {
      const refreshed = (await api.showTicket(ticket.id)) as TicketWithNotes
      setTicket(refreshed)
      setLoading(false)
    }, 800)
  }

  async function addNote() {
    if (!ticket) return
    const body = newNote.trim()
    if (!body) return
    const created = (await api.addNote(ticket.id, { body })) as Note
    setNotes((prev) => [created, ...prev])
    setNewNote('')
  }

  return (
    <div style={drawerStyle}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <h3>Ticket #{ticket?.id}</h3>
        <button onClick={onClose}>Close</button>
      </div>

      <div>
        <div>
          <b>Subject:</b> {ticket?.subject}
        </div>
        <div>
          <b>Requester:</b> {ticket?.requester_email}
        </div>
        <div>
          <b>Status:</b> {ticket?.status}
        </div>
        <div>
          <b>AI Category:</b> {ticket?.ai_category} ({ticket?.ai_confidence ?? '-'})
        </div>
        <div>
          <b>Effective Category:</b> {ticket?.effective_category}
        </div>
        <div>
          <b>Classification Status:</b> {ticket?.classification_status}
        </div>
      </div>

      <div style={{ marginTop: 12 }}>
        <label>Override Category</label>
        <div style={{ display: 'flex', gap: 6 }}>
          <input placeholder="Override…" value={override} onChange={(e) => setOverride(e.target.value)} />
          <button disabled={loading} onClick={saveOverride}>
            Save
          </button>
          <button disabled={loading} onClick={reclassify}>
            Re-classify
          </button>
        </div>
      </div>

      <div style={{ marginTop: 12 }}>
        <h4>Internal Notes</h4>
        <div style={{ display: 'flex', gap: 6 }}>
          <input
            placeholder="Add a note…"
            value={newNote}
            onChange={(e) => setNewNote(e.target.value)}
            style={{ flex: 1 }}
          />
          <button onClick={addNote}>Add</button>
        </div>
        <ul style={{ marginTop: 8, paddingLeft: 16 }}>
          {notes.map((n) => (
            <li key={n.id} style={{ marginBottom: 6 }}>
              <div>
                <b>{n.author}</b> — <small>{new Date(n.created_at).toLocaleString()}</small>
              </div>
              <div>{n.body}</div>
            </li>
          ))}
          {notes.length === 0 && <li style={{ color: '#666' }}>No notes yet</li>}
        </ul>
      </div>
    </div>
  )
}
