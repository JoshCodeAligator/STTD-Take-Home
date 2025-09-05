<template>
    <div class="modal" @click.self="$emit('close')">
      <div class="modal__panel">
        <header class="modal__header">
          <h3 class="modal__title">New Ticket</h3>
          <button class="btn btn--ghost" @click="$emit('close')">×</button>
        </header>
  
        <form class="form" @submit.prevent="submit">
          <label class="form__group">
            <span class="form__label">Subject</span>
            <input class="input" v-model.trim="subject" required maxlength="160" />
          </label>
  
          <label class="form__group">
            <span class="form__label">Body</span>
            <textarea class="input input--area" v-model.trim="body" required></textarea>
          </label>
  
          <div class="form__actions">
            <button class="btn" :disabled="submitting">
              <span v-if="submitting">Creating…</span><span v-else>Create</span>
            </button>
            <button class="btn btn--ghost" type="button" @click="$emit('close')">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import { createTicket, safe } from '../services/api'
  
  export default {
    name: 'CreateTicketModal',
    data() {
      return { subject: '', body: '', submitting: false }
    },
    methods: {
      async submit() {
        if (!this.subject || !this.body) return
        this.submitting = true
        const payload = { subject: this.subject, body: this.body, status: 'open' }
        const created = await safe(() => createTicket(payload), payload)
        this.submitting = false
        this.$emit('create', created)
      }
    }
  }
  </script>