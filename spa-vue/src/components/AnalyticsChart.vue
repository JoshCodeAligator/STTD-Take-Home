<template>
    <canvas ref="cv" class="chart"></canvas>
  </template>
  
  <script>
  export default {
    name: 'AnalyticsChart',
    props: { data: { type: Array, default: () => [] } },
    data() { return { } },
    methods: {
      draw() {
        const cv = this.$refs.cv
        if (!cv) return
        const w = cv.width = cv.clientWidth || 640
        const h = cv.height = 240
        const ctx = cv.getContext('2d')
        ctx.clearRect(0,0,w,h)
  
        const styles = getComputedStyle(document.documentElement)
        const fg = styles.getPropertyValue('--fg').trim() || '#111'
        ctx.fillStyle = fg
        ctx.font = '12px system-ui, -apple-system, Segoe UI, Roboto, sans-serif'
  
        const max = Math.max(1, ...this.data.map(d => d.value || 0))
        const pad = 24
        const barW = (w - pad*2) / Math.max(1, this.data.length) * 0.6
        const gap = (w - pad*2) / Math.max(1, this.data.length) * 0.4
        let x = pad
  
        this.data.forEach(d => {
          const val = d.value || 0
          const bh = (h - 60) * (val / max)
          ctx.fillRect(x, h - 40 - bh, barW, bh)
          ctx.fillText(String(d.label || ''), x, h - 20, barW + gap)
          ctx.fillText(String(val), x, h - 45 - bh)
          x += barW + gap
        })
      }
    },
    mounted() { this.draw(); window.addEventListener('resize', this.draw) },
    watch: { data: { deep: true, handler() { this.$nextTick(this.draw) } } },
    unmounted() { window.removeEventListener('resize', this.draw) }
  }
  </script>