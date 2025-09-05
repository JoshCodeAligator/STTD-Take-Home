<template>
  <div :class="['app', themeClass]">
    <header class="app__header">
      <h1 class="app__title">Smart Ticket Triage</h1>
      <nav class="app__nav">
        <router-link to="/tickets" class="app__link">Tickets</router-link>
        <router-link to="/dashboard" class="app__link">Dashboard</router-link>
      </nav>
      <button class="btn" @click="toggleTheme">{{ themeLabel }}</button>
    </header>

    <main class="app__main">
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return { theme: localStorage.getItem('theme') || 'light' }
  },
  computed: {
    themeClass() { return `theme--${this.theme}` },
    themeLabel() { return this.theme === 'light' ? 'Dark' : 'Light' }
  },
  methods: {
    toggleTheme() {
      this.theme = this.theme === 'light' ? 'dark' : 'light'
      localStorage.setItem('theme', this.theme)
    }
  }
}
</script>