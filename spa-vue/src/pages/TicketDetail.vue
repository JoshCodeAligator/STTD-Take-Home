<template>
  <section class="ticket-detail" v-if="ticket">
    <header class="ticket-detail__header">
      <h2 class="ticket-detail__title">Ticket #{{ ticket.id }}</h2>
      <router-link to="/tickets" class="link">← Back to list</router-link>
    </header>

    <div class="card">
      <div class="card__row">
        <div class="card__label">Subject</div>
        <div class="card__value">{{ ticket.subject }}</div>
      </div>
      <div class="card__row">
        <div class="card__label">Body</div>
        <div class="card__value prewrap">{{ ticket.body }}</div>
      </div>

      <div class="card__grid">
        <div class="card__field">
          <label class="card__label">Status</label>
          <select class="select" v-model="ticket.status" @change="save({ status: ticket.status })">
            <option value="new">New</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <div class="card__field">
          <label class="card__label">Category</label>
          <select
            class="select"
            v-model="ticket.category"
            @change="save({ category: ticket.category })"
          >
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>

        <div class="card__field">
          <label class="card__label">Confidence</label>
          <div class="mono">{{ formatConf(ticket.confidence) }}</div>
        </div>
      </div>

      <div class="card__row">
        <div class="card__label">Explanation</div>
        <div class="card__value muted prewrap">{{ ticket.explanation || '—' }}</div>
      </div>

      <div class="card__row">
        <div class="card__label">Internal Note</div>
        <textarea
          class="input input--area"
          v-model="noteDraft"
          @blur="save({ note: noteDraft })"
          placeholder="Add a private note…"
        ></textarea>
      </div>

      <div class="card__actions">
        <button class="btn" :disabled="loading" @click="reclassify">
          <span v-if="loading">Classifying…</span><span v-else>Run Classification</span>
        </button>
      </div>
    </div>
  </section>

  <div v-else class="tickets__empty">Loading…</div>
</template>

<script>
import { getTicket, updateTicket, classify, safe } from '../services/api'

export default {
  name: 'TicketDetail',
  data() {
    return {
      ticket: null,
      categories: ['Billing', 'Bug', 'Access', 'Feature Request', 'Outage', 'Other'],
      noteDraft: '',
      loading: false,
    }
  },
  methods: {
    formatConf(v) {
      if (v === null || v === undefined) return '—'
      const n = Number(v)
      return isNaN(n) ? '—' : n.toFixed(2)
    },
    async load() {
      const id = this.$route.params.id
      const fallback = {}
      const data = await safe(() => getTicket(id), fallback)
      this.ticket = data
      this.noteDraft = data?.note || ''
    },
    async save(payload) {
      if (!this.ticket) return
      Object.assign(this.ticket, payload)
      await safe(() => updateTicket(this.ticket.id, payload), Promise.resolve())
    },
    async reclassify() {
      if (!this.ticket) return
      this.loading = true
      await safe(() => classify(this.ticket.id), Promise.resolve())
      await this.load()
      this.loading = false
    },
  },
  async mounted() {
    await this.load()
  },
}
</script>
