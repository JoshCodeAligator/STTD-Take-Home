import { useState } from 'react'
import { api } from '../api'
import type { Ticket } from '../types'

type Props = { onCreated: (t: Ticket) => void }

export default function SubmitTicketForm({ onCreated }: Props) {
  const [subject, setSubject] = useState<string>('')
  const [description, setDescription] = useState<string>('')
  const [email, setEmail] = useState<string>('')
  const [classify, setClassify] = useState<boolean>(true)
  const [loading, setLoading] = useState<boolean>(false)
  const [error, setError] = useState<string | null>(null)

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setLoading(true)
    setError(null)
    try {
      const created = await api.createTicket({
        subject,
        description,
        requester_email: email,
        status: 'new',
        classify,
      }) as Ticket
      onCreated(created)
      setSubject('')
      setDescription('')
      setEmail('')
      setClassify(true)
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'Failed to submit ticket'
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  return (
    <form onSubmit={submit} style={{ display: 'grid', gap: 8, border: '1px solid #ddd', padding: 12, borderRadius: 8 }}>
      <h3>Submit Support Ticket</h3>
      <input required placeholder="Subject" value={subject} onChange={(e) => setSubject(e.target.value)} />
      <textarea required placeholder="Description" value={description} onChange={(e) => setDescription(e.target.value)} rows={4} />
      <input required type="email" placeholder="Requester email" value={email} onChange={(e) => setEmail(e.target.value)} />
      <label style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
        <input type="checkbox" checked={classify} onChange={(e) => setClassify(e.target.checked)} />
        Classify on submit
      </label>
      <button disabled={loading}>{loading ? 'Submitting…' : 'Submit Ticket'}</button>
      {error && <div style={{ color: 'crimson' }}>{error}</div>}
    </form>
  )
}
