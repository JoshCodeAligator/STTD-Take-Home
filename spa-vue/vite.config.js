import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  base: '/spa/',                         
  build: {
    outDir: '../smart-ticket-triage/public/spa',
    emptyOutDir: true,
  },
  server: { port: 5173 }
})
