<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  cdart: { type: String, default: '' },
  // Sola lettura: niente form né modifica/eliminazione (gli alert si creano dalla pagina Articoli)
  readonly: { type: Boolean, default: false },
})

const emit = defineEmits(['count'])

// Utente loggato (meta tag nel layout): firma di default
const currentUserId = Number(document.querySelector('meta[name="user-id"]')?.content) || null

const alerts = ref([])
const alertsLoading = ref(false)
const users = ref([])
const alertForm = ref({ note: '', signed_by: currentUserId })
const alertSaving = ref(false)
const alertError = ref('')
const editingAlertId = ref(null)
const confirmDeleteId = ref(null)
const deletingAlertId = ref(null)

const fetchUsers = async () => {
  if (users.value.length) return
  const res = await axios.get('/api/users')
  users.value = res.data
}

const fetchAlerts = async () => {
  alertsLoading.value = true
  try {
    const res = await axios.get('/api/article-alerts', { params: { cdart: props.cdart } })
    alerts.value = res.data.data
  } finally {
    alertsLoading.value = false
  }
}

const resetAlertForm = () => {
  alertForm.value = { note: '', signed_by: currentUserId }
  alertError.value = ''
  editingAlertId.value = null
  confirmDeleteId.value = null
}

const load = async () => {
  alerts.value = []
  resetAlertForm()
  if (!props.cdart?.trim()) return
  try {
    await Promise.all([props.readonly ? null : fetchUsers(), fetchAlerts()])
  } catch (error) {
    console.error(error)
    const status = error.response?.status
    const detail = error.response?.data?.message || error.message
    alertError.value = `Errore nel caricamento degli alert (${error.config?.url ?? ''} ${status ?? ''}): ${detail}`
  }
}

const startEditAlert = (alert) => {
  editingAlertId.value = alert.id
  confirmDeleteId.value = null
  alertForm.value = { note: alert.note, signed_by: alert.signed_by }
  alertError.value = ''
}

const saveAlert = async () => {
  if (!alertForm.value.note.trim() || !alertForm.value.signed_by) {
    alertError.value = 'Compila nota e firma'
    return
  }
  alertSaving.value = true
  alertError.value = ''
  const payload = {
    note: alertForm.value.note.trim(),
    signed_by: alertForm.value.signed_by,
  }
  try {
    if (editingAlertId.value) {
      const res = await axios.put(`/api/article-alerts/${editingAlertId.value}`, payload)
      const index = alerts.value.findIndex(a => a.id === editingAlertId.value)
      if (index !== -1) alerts.value[index] = res.data.data
    } else {
      const res = await axios.post('/api/article-alerts', { ...payload, cdart: props.cdart })
      alerts.value.unshift(res.data.data)
    }
    resetAlertForm()
  } catch (error) {
    alertError.value = error.response?.data?.message || 'Errore durante il salvataggio'
  } finally {
    alertSaving.value = false
  }
}

const deleteAlert = async (alert) => {
  deletingAlertId.value = alert.id
  alertError.value = ''
  try {
    await axios.delete(`/api/article-alerts/${alert.id}`)
    alerts.value = alerts.value.filter(a => a.id !== alert.id)
    if (editingAlertId.value === alert.id) resetAlertForm()
    confirmDeleteId.value = null
  } catch (error) {
    alertError.value = error.response?.data?.message || 'Errore durante l\'eliminazione'
  } finally {
    deletingAlertId.value = null
  }
}

const formatDateTime = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString('it-IT', { dateStyle: 'short', timeStyle: 'short' })
}

watch(() => props.cdart, load, { immediate: true })

// Comunica al genitore il numero di alert (es. per colorare l'icona in tabella)
watch(() => alerts.value.length, (count) => {
  if (!alertsLoading.value) emit('count', count)
})
</script>

<template>
  <div class="space-y-4">
    <p v-if="!cdart?.trim()" class="text-xs text-gray-400">Codice articolo non disponibile</p>

    <template v-else>
      <!-- Nuovo alert / modifica alert -->
      <form v-if="!readonly" @submit.prevent="saveAlert" class="space-y-3">
        <p v-if="editingAlertId" class="text-xs font-semibold text-amber-700">Modifica alert</p>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Nota</label>
          <textarea v-model="alertForm.note" rows="3" maxlength="2000"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"></textarea>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
          <div class="flex-1">
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Firma</label>
            <select v-model="alertForm.signed_by"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue">
              <option :value="null" disabled>Seleziona utente</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
          <button v-if="editingAlertId" type="button" @click="resetAlertForm"
            class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            Annulla
          </button>
          <button type="submit" :disabled="alertSaving"
            class="px-4 py-2 bg-copam-blue text-white text-xs font-semibold rounded-lg hover:bg-copam-blue/90 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
            {{ alertSaving ? 'Salvataggio...' : (editingAlertId ? 'Salva modifiche' : 'Aggiungi alert') }}
          </button>
        </div>
        <p v-if="alertError" class="text-xs font-medium text-red-600">{{ alertError }}</p>
      </form>

      <!-- Alert esistenti -->
      <p v-if="readonly && alertError" class="text-xs font-medium text-red-600">{{ alertError }}</p>
      <div :class="readonly ? '' : 'border-t border-gray-100 pt-4'">
        <h4 class="text-xs font-semibold text-gray-700 mb-2">Alert ({{ alerts.length }})</h4>
        <p v-if="alertsLoading" class="text-xs text-gray-400">Caricamento...</p>
        <p v-else-if="!alerts.length" class="text-xs text-gray-400">Nessun alert per questo articolo</p>
        <ul v-else class="space-y-2 max-h-72 overflow-y-auto">
          <li v-for="alert in alerts" :key="alert.id"
            :class="[
              'border rounded-lg px-3 py-2 flex items-start gap-3',
              editingAlertId === alert.id ? 'border-copam-blue bg-blue-50' : 'border-amber-200 bg-amber-50'
            ]">
            <div class="flex-1 min-w-0">
              <p class="text-xs text-gray-800 whitespace-pre-line break-words">{{ alert.note }}</p>
              <p class="mt-1 text-[11px] text-gray-500">
                Firma: <span class="font-medium text-gray-700">{{ alert.signer?.name ?? '—' }}</span>
                &middot; {{ formatDateTime(alert.created_at) }}
              </p>
            </div>

            <!-- Conferma eliminazione -->
            <div v-if="readonly"></div>
            <div v-else-if="confirmDeleteId === alert.id" class="flex items-center gap-1 shrink-0">
              <span class="text-[11px] text-gray-600 mr-1">Eliminare?</span>
              <button type="button" @click="deleteAlert(alert)" :disabled="deletingAlertId === alert.id"
                class="px-2 py-1 text-[11px] font-semibold text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-60 transition-colors">
                {{ deletingAlertId === alert.id ? '...' : 'Sì' }}
              </button>
              <button type="button" @click="confirmDeleteId = null"
                class="px-2 py-1 text-[11px] font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                No
              </button>
            </div>

            <!-- Azioni -->
            <div v-else class="flex items-center gap-1 shrink-0">
              <button type="button" @click="startEditAlert(alert)" title="Modifica"
                class="p-1 rounded text-gray-500 hover:text-copam-blue hover:bg-white transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button type="button" @click="confirmDeleteId = alert.id" title="Elimina"
                class="p-1 rounded text-gray-500 hover:text-red-600 hover:bg-white transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>
