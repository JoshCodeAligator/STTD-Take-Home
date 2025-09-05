<template>
    <section class="tickets">
      <header class="tickets__header">
        <h2 class="tickets__title">Tickets</h2>
        <div class="tickets__actions">
          <input class="input" v-model="q" placeholder="Search subject/body…" @keyup.enter="goFirstPageAndFetch" />
          <select class="select" v-model="status">
            <option value="">All Statuses</option>
            <option value="new">New</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>
          <select class="select" v-model="categoryFilter">
            <option value="">All Categories</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
          <button class="btn" @click="showModal = true">New Ticket</button>
          <button class="btn" :disabled="busyAll || !tickets.length" @click="classifyVisible">{{ busyAll ? 'Classifying visible…' : 'Classify Visible' }}</button>
        </div>
      </header>
  
      <div class="ticket-list">
        <div class="ticket-list__header">
          <div class="ticket-list__cell w-20">Subject</div>
          <div class="ticket-list__cell w-30">Body</div>
          <div class="ticket-list__cell w-10">Status</div>
          <div class="ticket-list__cell w-15">Category</div>
          <div class="ticket-list__cell w-5">Conf.</div>
          <div class="ticket-list__cell w-15">Explanation</div>
          <div class="ticket-list__cell w-5">Note</div>
          <div class="ticket-list__cell w-10">Actions</div>
        </div>
  
        <div v-for="t in tickets" :key="t.id" class="ticket-list__row" :class="{'ticket-list__row--loading': loadingId===t.id}">
          <div class="ticket-list__cell w-20">
            <router-link :to="`/tickets/${t.id}`" class="link">{{ t.subject }}</router-link>
          </div>
          <div class="ticket-list__cell w-30 ellipsis" :title="t.body">{{ t.body }}</div>
          <div class="ticket-list__cell w-10">
            <select class="select select--sm" v-model="t.status" @change="save(t.id, { status: t.status })">
              <option value="new">New</option>
              <option value="open">Open</option>
              <option value="closed">Closed</option>
            </select>
          </div>
          <div class="ticket-list__cell w-15">
            <select class="select select--sm" v-model="t.category" @change="save(t.id, { category: t.category })">
              <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
          <div class="ticket-list__cell w-5 mono">{{ formatConf(t.confidence) }}</div>
          <div class="ticket-list__cell w-15">
            <span class="muted" :title="t.explanation || '—'">{{ short(t.explanation) }}</span>
          </div>
          <div class="ticket-list__cell w-5">
            <span v-if="t.notes_count && t.notes_count > 0" class="badge badge--noted" title="Has note">●</span>
          </div>
          <div class="ticket-list__cell w-10">
            <button class="btn btn--sm" :disabled="loadingId===t.id" @click="runClassify(t.id)">
              <span v-if="loadingId===t.id">Classifying…</span>
              <span v-else>Classify</span>
            </button>
          </div>
        </div>
  
        <div v-if="!tickets.length" class="tickets__empty">No tickets match your filters.</div>
      </div>
  
      <footer class="tickets__footer" v-if="meta.total > 0">
        <button class="btn btn--ghost" :disabled="meta.current_page<=1" @click="prevPage">Prev</button>
        <span class="mono">{{ meta.current_page }} / {{ totalPages }} • Total {{ meta.total }}</span>
        <button class="btn btn--ghost" :disabled="meta.current_page>=meta.last_page" @click="nextPage">Next</button>
      </footer>
  
      <CreateTicketModal v-if="showModal" @close="showModal=false" @create="created"/>
    </section>
  </template>
  
  <script>
  import { watch } from 'vue'
  import { listTickets, updateTicket, classify, getTicket, safe } from '../services/api'
  import CreateTicketModal from '../components/CreateTicketModal.vue'

  export default {
    name: 'Tickets',
    components: { CreateTicketModal },
    data() {
      return {
        all: [],
        q: '',
        status: '',
        categoryFilter: '',
        categories: ['Billing','Bug','Access','Feature Request','Outage','Other'],
        page: 1,
        perPage: 10,
        meta: { current_page: 1, last_page: 1, total: 0, per_page: 10 },
        loadingId: null,
        busyAll: false,
        watching: new Set(),
        showModal: false
      }
    },
    watch: {
      q() { this.debouncedRefetch() },
      status() { this.debouncedRefetch() },
      categoryFilter() { this.debouncedRefetch() },
      perPage() { this.debouncedRefetch() }
    },
    created() {
      this._debounceTimer = null
    },
    beforeUnmount() {
      if (this._debounceTimer) clearTimeout(this._debounceTimer)
    },
    computed: {
      tickets() {
        return this.all; // already paginated by backend
      },
      totalPages() {
        return Math.max(1, this.meta.last_page || 1)
      }
    },
    methods: {
      async fetchTickets() {
        const params = {
          q: this.q || '',
          status: this.status || '',
          category: this.categoryFilter || '',
          page: this.page,
          per_page: this.perPage
        }
        const fallback = { data: [], current_page:1, last_page:1, total:0, per_page:this.perPage }
        const res = await safe(() => listTickets(params), fallback)
        // Laravel paginator shape
        this.all = res.data || []
        this.meta.current_page = res.current_page || 1
        this.meta.last_page    = res.last_page || 1
        this.meta.total        = res.total || (Array.isArray(res.data) ? res.data.length : 0)
        this.meta.per_page     = res.per_page || this.perPage
      },
      goFirstPageAndFetch() { this.page = 1; this.fetchTickets() },
      prevPage()  { if (this.meta.current_page > 1) { this.page = this.meta.current_page - 1; this.fetchTickets() } },
      nextPage()  { if (this.meta.current_page < this.meta.last_page) { this.page = this.meta.current_page + 1; this.fetchTickets() } },
      goFirstPage(){ if (this.meta.current_page !== 1) { this.page = 1; this.fetchTickets() } },
      goLastPage() { if (this.meta.current_page !== this.meta.last_page) { this.page = this.meta.last_page; this.fetchTickets() } },

      short(s) {
        if (!s) return '—'
        return s.length > 60 ? s.slice(0, 57) + '…' : s
      },
      formatConf(v) {
        if (v === null || v === undefined) return '—'
        const n = Number(v)
        return isNaN(n) ? '—' : n.toFixed(2)
      },
      async save(id, payload) {
        const t = this.all.find(x => x.id === id)
        if (!t) return
        const body = payload.category ? { override_category: payload.category } : payload
        Object.assign(t, payload) // optimistic update
        await safe(() => updateTicket(id, body), Promise.resolve())
        if (payload.category) {
          await this.fetchTickets()
          window.dispatchEvent(new CustomEvent('refresh-stats'))
        }
      },
      async waitForClassification(id, { timeoutMs = 20000, intervalMs = 1000 } = {}) {
        const start = Date.now()
        this.watching.add(id)
        try {
          while (Date.now() - start < timeoutMs) {
            // fetch the latest ticket
            const t = await safe(() => getTicket(id), null)
            if (t) {
              // merge into current list so UI updates immediately
              const idx = this.all.findIndex(x => x.id === id)
              if (idx !== -1) {
                this.$set
                  ? this.$set(this.all, idx, { ...this.all[idx], ...t })
                  : (this.all.splice(idx, 1, { ...this.all[idx], ...t }))
              }
              // consider complete when status is done or confidence present
              if (t.classification_status === 'done' || (t.confidence !== null && t.confidence !== undefined)) {
                // notify dashboard as soon as one finishes
                window.dispatchEvent(new CustomEvent('refresh-stats'))
                return t
              }
            }
            await new Promise(r => setTimeout(r, intervalMs))
          }
          return null
        } finally {
          this.watching.delete(id)
        }
      },
      async runClassify(id) {
        this.loadingId = id
        await safe(() => classify(id), Promise.resolve())
        // start polling this single row so user sees updates without manual refresh
        await this.waitForClassification(id)
        this.loadingId = null
      },
      async classifyVisible() {
        if (!this.tickets.length) return
        this.busyAll = true
        try {
          // 1) enqueue in visible order
          for (const row of this.tickets) {
            await safe(() => classify(row.id), Promise.resolve())
          }
          // 2) poll them in parallel so they update as soon as jobs finish
          await Promise.race([
            Promise.all(this.tickets.map(row => this.waitForClassification(row.id))),
            // safety timeout to avoid hanging UI forever
            new Promise(r => setTimeout(r, 25000))
          ])
        } finally {
          this.busyAll = false
        }
        // final sweep to sync pagination/meta
        await this.fetchTickets()
        window.dispatchEvent(new CustomEvent('refresh-stats'))
      },
      debouncedRefetch() {
        if (this._debounceTimer) clearTimeout(this._debounceTimer)
        this._debounceTimer = setTimeout(() => { this.page = 1; this.fetchTickets() }, 300)
      },
      async created(newTicket) {
        // reload from server to include notes_count & correct page
        this.page = 1
        await this.fetchTickets()
        this.q = ''; this.status=''; this.categoryFilter=''
        this.showModal = false
      }
    },
    async mounted() { await this.fetchTickets() }
  }
  </script>