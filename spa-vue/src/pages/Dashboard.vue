<template>
    <section class="dashboard">
      <h2 class="dashboard__title">Dashboard</h2>
  
      <div class="stats">
        <div class="stat">
          <div class="stat__label">Open</div>
          <div class="stat__value mono">{{ counts.status.open || 0 }}</div>
        </div>
        <div class="stat">
          <div class="stat__label">New</div>
          <div class="stat__value mono">{{ counts.status.new || 0 }}</div>
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
      <AnalyticsChart :key="themeVersion" :data="chartData" />
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
        categories: ['Billing','Bug','Access','Feature Request','Outage','Other'],
        themeVersion: 0,
        _timer: null
      }
    },
    computed: {
      chartData() {
        return this.categories.map(c => ({ label: c, value: this.counts.category[c] || 0 }))
      }
    },
    methods: {
      async load() {
        const fallback = { byStatus: {}, byCategory: {}, overrides: 0, avgClassificationSeconds: 0 }
        const s = await safe(() => getStats(), fallback)
        // Map API -> local
        this.counts = {
          status: s.byStatus || {},
          category: s.byCategory || {}
        }
      },
      startAutoRefresh() {
        this._timer = setInterval(() => this.load(), 5000)
        window.addEventListener('refresh-stats', this.load)
      },
      stopAutoRefresh() {
        if (this._timer) clearInterval(this._timer)
        window.removeEventListener('refresh-stats', this.load)
      },
      onThemeChanged() {
        this.themeVersion++
      },
    },
    async mounted() {
      await this.load()
      window.addEventListener('theme-changed', this.onThemeChanged)
      this.startAutoRefresh()
    },
    beforeUnmount() {
      this.stopAutoRefresh()
      window.removeEventListener('theme-changed', this.onThemeChanged)
    }
  }
  </script>