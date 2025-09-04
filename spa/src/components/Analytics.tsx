import { useEffect, useState } from 'react'
import { api } from '../api'

type Summary = {
  byCategory: Record<string, number>
  overrides: number
  avgClassificationSeconds: number
  byStatus: Record<string, number>
}

function Card({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div style={{ border: '1px solid #eee', borderRadius: 8, padding: 10 }}>
      <div style={{ fontWeight: 600, marginBottom: 6 }}>{title}</div>
      <div>{children}</div>
    </div>
  )
}

export default function Analytics() {
  const [data, setData] = useState<Summary | null>(null)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    ;(async () => {
      try {
        const s = (await api.analytics()) as Summary
        setData(s)
      } catch (e) {
        const msg = e instanceof Error ? e.message : 'Failed to load analytics'
        setError(msg)
      }
    })()
  }, [])

  return (
    <div style={{ border: '1px solid #ddd', padding: 12, borderRadius: 8 }}>
      <h3>Analytics</h3>
      {error && <div style={{ color: 'crimson' }}>{error}</div>}
      {!data && !error && <div>Loading…</div>}
      {data && (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: 12 }}>
          <Card title="Overrides">{data.overrides}</Card>
          <Card title="Avg Classify Time (s)">{data.avgClassificationSeconds}</Card>
          <Card title="By Status">
            {Object.entries(data.byStatus).map(([k, v]) => (
              <div key={k}>
                {k}: {v}
              </div>
            ))}
          </Card>
          <Card title="By Category">
            {Object.entries(data.byCategory).map(([k, v]) => (
              <div key={k}>
                {k}: {v}
              </div>
            ))}
          </Card>
        </div>
      )}
    </div>
  )
}
