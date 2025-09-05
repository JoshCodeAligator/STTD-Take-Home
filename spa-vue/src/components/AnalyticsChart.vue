<template>
  <canvas ref="cv" class="chart" aria-label="Category Chart" role="img"></canvas>
</template>

<script>
export default {
  name: 'AnalyticsChart',
  props: { data: { type: Array, default: () => [] } },
  methods: {
    getVars() {
      const root = document.querySelector('.app') || document.documentElement
      const cs = getComputedStyle(root)
      const read = (n) => cs.getPropertyValue(n).trim()
      return {
        bg: read('--bg') || '#ffffff',
        fg: read('--fg') || '#0f172a',
        accent: read('--accent') || '#2563eb',
        success: read('--success') || '#16a34a',
        warn: read('--warn') || '#f59e0b',
        danger: read('--danger') || '#dc2626',
        border: read('--border') || '#e5e7eb',
      }
    },
    rgba(hex, a = 1) {
      const h = hex.replace('#','')
      const v = parseInt(h.length===3 ? h.split('').map(x=>x+x).join('') : h, 16)
      const r = (v >> 16) & 255, g = (v >> 8) & 255, b = v & 255
      return `rgba(${r}, ${g}, ${b}, ${a})`
    },
    draw() {
      const cv = this.$refs.cv
      if (!cv) return

      // DPR-aware crisp canvas
      const dpr = window.devicePixelRatio || 1
      const cssW = cv.clientWidth || 640
      const cssH = 260
      cv.width = Math.floor(cssW * dpr)
      cv.height = Math.floor(cssH * dpr)
      cv.style.width = cssW + 'px'
      cv.style.height = cssH + 'px'
      const ctx = cv.getContext('2d')
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0)

      const t = this.getVars()
      const labels = this.data.map(d => String(d.label || ''))
      const values = this.data.map(d => Number(d.value || 0))
      const max = Math.max(1, ...values)

      const padX = 24
      const padTop = 16
      const padBottom = 38
      const w = cssW
      const h = cssH

      // background (transparent to let parent card color show)
      ctx.clearRect(0,0,w,h)

      // grid line
      ctx.strokeStyle = this.rgba(t.border, 0.8)
      ctx.lineWidth = 1
      ctx.beginPath()
      ctx.moveTo(padX, h - padBottom + 0.5)
      ctx.lineTo(w - padX, h - padBottom + 0.5)
      ctx.stroke()

      // palette from theme tokens
      const bases = [t.accent, t.success, t.warn, t.danger, t.fg]
      const fill = (i) => this.rgba(bases[i % bases.length], 0.28)
      const stroke = (i) => this.rgba(bases[i % bases.length], 0.9)

      // typography
      ctx.fillStyle = t.fg
      ctx.font = '12px system-ui, -apple-system, Segoe UI, Roboto, sans-serif'

      // bars
      const n = Math.max(1, values.length)
      const slot = (w - padX*2) / n
      const barW = Math.max(8, slot * 0.6)
      const gap = slot - barW
      let x = padX

      values.forEach((val, i) => {
        const barH = Math.round((h - padTop - padBottom) * (val / max))
        const y = h - padBottom - barH
        ctx.fillStyle = fill(i)
        ctx.strokeStyle = stroke(i)
        ctx.lineWidth = 1
        ctx.fillRect(x, y, barW, barH)
        ctx.strokeRect(x + 0.5, y + 0.5, barW - 1, barH - 1)

        // value label
        ctx.fillStyle = t.fg
        const vText = String(val)
        ctx.fillText(vText, x, y - 6)

        // x label (truncate if too long)
        const lab = labels[i]
        const maxChars = Math.max(4, Math.floor(barW / 6))
        const short = lab.length > maxChars ? lab.slice(0, maxChars - 1) + '…' : lab
        ctx.fillText(short, x, h - padBottom + 16, barW + gap)

        x += barW + gap
      })
    },
    onTheme() { this.draw() },
  },
  mounted() {
    this.draw()
    window.addEventListener('resize', this.draw)
    window.addEventListener('theme-changed', this.onTheme)
  },
  watch: { data: { deep: true, handler() { this.$nextTick(this.draw) } } },
  beforeUnmount() {
    window.removeEventListener('resize', this.draw)
    window.removeEventListener('theme-changed', this.onTheme)
  }
}
</script>

<style scoped>
.chart { width: 100%; height: 260px; display: block; }
</style>