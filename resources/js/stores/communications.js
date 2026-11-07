import axios from 'axios'

const POLL_INTERVAL = 15000

/**
 * Store Alpine per il badge delle comunicazioni non lette nel menu.
 * Il valore iniziale arriva dal server (navigation.blade.php), poi si
 * aggiorna via polling; si ferma quando la scheda non è visibile.
 */
export default {
  unread: 0,
  timer: null,
  started: false,

  start(initial) {
    this.unread = initial
    if (this.started) return
    this.started = true

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.stopTimer()
      } else {
        this.refresh()
        this.startTimer()
      }
    })

    if (!document.hidden) this.startTimer()
  },

  startTimer() {
    this.stopTimer()
    this.timer = setInterval(() => this.refresh(), POLL_INTERVAL)
  },

  stopTimer() {
    if (this.timer) {
      clearInterval(this.timer)
      this.timer = null
    }
  },

  set(count) {
    if (Number.isInteger(count)) this.unread = count
  },

  async refresh() {
    try {
      const res = await axios.get('/api/communications/unread-count')
      this.set(res.data.unread_count)
    } catch {
      // badge invariato in caso di errore
    }
  },
}
