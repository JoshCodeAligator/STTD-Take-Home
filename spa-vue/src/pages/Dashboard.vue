<template>
    <section class="dashboard">
      <h2 class="dashboard__title">Dashboard</h2>
  
      <div class="stats">
        <div class="stat">
          <div class="stat__label">Open</div>
          <div class="stat__value mono">{{ counts.status.open || 0 }}</div>
        </div>
        <div class="stat">
          <div class="stat__label">In Progress</div>
          <div class="stat__value mono">{{ counts.status.in_progress || 0 }}</div>
        </div>
        <div class="stat">
          <div class="stat__label">Closed</div>
          <div class="stat__value mono">{{ counts.status.closed || 0 }}</div>
        </div>
      </div>
  
      <h3 class="dashboard__subtitle">By Category</h3>
      <div class="stats stats--grid">
        <div v-for="c in categories" :key="c" class="stat">
          <div class="stat__label">{{ c }}</div>
          <div class="stat__value mono">{{ counts.category[c] || 0 }}</div>
        </div>
      </div>
  
      <h3 class="dashboard__subtitle">Category Chart</h3>
      <AnalyticsChart :data="chartData"/>
    </section>
  </template>
  
  <script>
  import { getStats, safe } from '../services/api'
  import AnalyticsChart from '../components/AnalyticsChart.vue'
  
  export default {
    name: 'Dashboard',
    components: { AnalyticsChart },
    data() {
      return {
        counts: { status: {}, category: {} },
        categories: ['Billing','Bug','Access','Feature Request','Outage','Other']
      }
    },
    computed: {
      chartData() {
        return this.categories.map(c => ({ label: c, value: this.counts.category[c] || 0 }))
      }
    },
    methods: {
      async load() {
        const fallback = { status: {}, category: {} }
        this.counts = await safe(() => getStats(), fallback)
      }
    },
    async mounted() { await this.load() }
  }
  </script>