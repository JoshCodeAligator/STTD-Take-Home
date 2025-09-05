<template>
    <section class="tickets">
      <header class="tickets__header">
        <h2 class="tickets__title">Tickets</h2>
        <div class="tickets__actions">
          <input class="input" v-model="q" placeholder="Search subject/body…" />
          <select class="select" v-model="status">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="closed">Closed</option>
          </select>
          <select class="select" v-model="categoryFilter">
            <option value="">All Categories</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
          <button class="btn" @click="showModal = true">New Ticket</button>
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
  
        <div v-for="t in paged" :key="t.id" class="ticket-list__row" :class="{'ticket-list__row--loading': loadingId===t.id}">
          <div class="ticket-list__cell w-20">
            <router-link :to="`/tickets/${t.id}`" class="link">{{ t.subject }}</router-link>
          </div>
          <div class="ticket-list__cell w-30 ellipsis" :title="t.body">{{ t.body }}</div>
          <div class="ticket-list__cell w-10">
            <select class="select select--sm" v-model="t.status" @change="save(t.id, { status: t.status })">
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
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
            <span v-if="t.note" class="badge badge--noted" title="Has note">●</span>
          </div>
          <div class="ticket-list__cell w-10">
            <button class="btn btn--sm" :disabled="loadingId===t.id" @click="runClassify(t.id)">
              <span v-if="loadingId===t.id">Classifying…</span>
              <span v-else>Classify</span>
            </button>
          </div>
        </div>
  
        <div v-if="!paged.length" class="tickets__empty">No tickets match your filters.</div>
      </div>
  
      <footer class="tickets__footer">
        <button class="btn btn--ghost" :disabled="page===1" @click="page--">Prev</button>
        <span class="mono">{{ page }} / {{ totalPages }}</span>
        <button class="btn btn--ghost" :disabled="page===totalPages" @click="page++">Next</button>
      </footer>
  
      <CreateTicketModal v-if="showModal" @close="showModal=false" @create="created"/>
    </section>
  </template>
  
  <script>
  import { listTickets, updateTicket, classify, safe } from '../services/api'
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
        pageSize: 10,
        loadingId: null,
        showModal: false
      }
    },
    computed: {
      filtered() {
        const q = this.q.trim().toLowerCase()
        return this.all.filter(t => {
          const matchQ = !q || (t.subject||'').toLowerCase().includes(q) || (t.body||'').toLowerCase().includes(q)
          const matchS = !this.status || t.status === this.status
          const matchC = !this.categoryFilter || t.category === this.categoryFilter
          return matchQ && matchS && matchC
        })
      },
      totalPages() {
        return Math.max(1, Math.ceil(this.filtered.length / this.pageSize))
      },
      paged() {
        const start = (this.page - 1) * this.pageSize
        return this.filtered.slice(start, start + this.pageSize)
      }
    },
    methods: {
      async load() {
        const fallback = { data: [], meta: {} }
        const res = await safe(() => listTickets({}), fallback)
        this.all = Array.isArray(res) ? res : (res.data || [])
      },
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
        Object.assign(t, payload) // optimistic
        await safe(() => updateTicket(id, payload), Promise.resolve())
      },
      async runClassify(id) {
        this.loadingId = id
        await safe(() => classify(id), Promise.resolve())
        await this.load()
        this.loadingId = null
      },
      async created(newTicket) {
        this.all.unshift(newTicket)
        this.q = ''; this.status=''; this.categoryFilter=''
        this.page = 1
        this.showModal = false
      }
    },
    async mounted() { await this.load() }
  }
  </script>