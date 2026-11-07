<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  // Fase di lavoro (work_phase dell'assegnazione)
  workPhase: { type: Object, default: null },
})

const emit = defineEmits(['count'])

const communications = ref([])
const loading = ref(false)
const message = ref('')
const sending = ref(false)
const error = ref('')
const editingId = ref(null)
const editMessage = ref('')
const savingEdit = ref(false)
const confirmDeleteId = ref(null)
const deletingId = ref(null)

// Stessa chiave usata per l'inizio lavoro
const workPhaseKey = () => {
  const wp = props.workPhase
  return {
    opart_padre: wp?.OPART_PADRE?.toString().trim() || null,
    opart: wp?.OPART?.toString().trim() || null,
    flass: wp?.FLASS?.toString().trim() || null,
    fllav: wp?.FLLAV?.toString().trim() || null,
    idopr: wp?.IDOPR?.toString().trim() || null,
  }
}

const fetchCommunications = async () => {
  communications.value = []
  message.value = ''
  error.value = ''
  cancelEdit()
  if (!props.workPhase) return
  loading.value = true
  try {
    const res = await axios.get('/api/communications/work-phase', { params: workPhaseKey() })
    communications.value = res.data.data
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Errore nel caricamento delle comunicazioni'
  } finally {
    loading.value = false
  }
}

const send = async () => {
  if (!message.value.trim()) {
    error.value = 'Scrivi il testo della comunicazione'
    return
  }
  sending.value = true
  error.value = ''
  try {
    const res = await axios.post('/api/communications', {
      ...workPhaseKey(),
      message: message.value.trim(),
    })
    communications.value.unshift(res.data.data)
    message.value = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Errore durante l\'invio'
  } finally {
    sending.value = false
  }
}

const startEdit = (communication) => {
  editingId.value = communication.id
  editMessage.value = communication.message
  confirmDeleteId.value = null
  error.value = ''
}

function cancelEdit() {
  editingId.value = null
  editMessage.value = ''
  confirmDeleteId.value = null
}

const saveEdit = async () => {
  if (!editMessage.value.trim()) {
    error.value = 'Il testo non può essere vuoto'
    return
  }
  savingEdit.value = true
  error.value = ''
  try {
    const res = await axios.put(`/api/communications/${editingId.value}`, { message: editMessage.value.trim() })
    const index = communications.value.findIndex(c => c.id === editingId.value)
    if (index !== -1) communications.value[index] = res.data.data
    cancelEdit()
  } catch (err) {
    error.value = err.response?.data?.message || 'Errore durante la modifica'
  } finally {
    savingEdit.value = false
  }
}

const remove = async (communication) => {
  deletingId.value = communication.id
  error.value = ''
  try {
    await axios.delete(`/api/communications/${communication.id}`)
    communications.value = communications.value.filter(c => c.id !== communication.id)
    confirmDeleteId.value = null
  } catch (err) {
    error.value = err.response?.data?.message || 'Errore durante l\'eliminazione'
  } finally {
    deletingId.value = null
  }
}

watch(() => props.workPhase, fetchCommunications, { immediate: true })

// Comunica al genitore il numero di comunicazioni (badge sulla tab)
watch(() => communications.value.length, (count) => emit('count', count))
</script>

<template>
  <div class="space-y-4">
    <!-- Nuova comunicazione -->
    <form @submit.prevent="send" class="space-y-3">
      <div>
        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Comunicazione</label>
        <textarea v-model="message" rows="3" maxlength="2000" placeholder="Scrivi una comunicazione su questa fase di lavoro…"
          class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"></textarea>
      </div>
      <div class="flex justify-end">
        <button type="submit" :disabled="sending || !message.trim()"
          class="inline-flex items-center gap-2 px-4 py-2 bg-copam-blue text-white text-xs font-semibold rounded-lg hover:bg-copam-blue/90 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          {{ sending ? 'Invio...' : 'Invia' }}
        </button>
      </div>
      <p v-if="error" class="text-xs font-medium text-red-600">{{ error }}</p>
    </form>

    <!-- Comunicazioni esistenti -->
    <div class="border-t border-gray-100 pt-4">
      <h4 class="text-xs font-semibold text-gray-700 mb-2">Comunicazioni ({{ communications.length }})</h4>
      <p v-if="loading" class="text-xs text-gray-400">Caricamento...</p>
      <p v-else-if="!communications.length" class="text-xs text-gray-400">Nessuna comunicazione per questa fase di lavoro</p>
      <ul v-else class="space-y-2 max-h-72 overflow-y-auto">
        <li v-for="communication in communications" :key="communication.id"
          :class="[
            'border rounded-lg px-3 py-2',
            editingId === communication.id ? 'border-copam-blue bg-blue-50' : 'border-gray-200 bg-gray-50'
          ]">

          <!-- Modifica -->
          <div v-if="editingId === communication.id" class="space-y-2">
            <textarea v-model="editMessage" rows="3" maxlength="2000"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"></textarea>
            <div class="flex justify-end gap-2">
              <button type="button" @click="cancelEdit"
                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Annulla
              </button>
              <button type="button" @click="saveEdit" :disabled="savingEdit"
                class="px-3 py-1.5 bg-copam-blue text-white text-xs font-semibold rounded-lg hover:bg-copam-blue/90 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                {{ savingEdit ? 'Salvataggio...' : 'Salva modifiche' }}
              </button>
            </div>
          </div>

          <div v-else class="flex items-start gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-xs text-gray-800 whitespace-pre-line break-words">{{ communication.message }}</p>
              <p class="mt-1 text-[11px] text-gray-500">
                <span class="font-medium text-gray-700">{{ communication.user_name ?? '—' }}</span>
                &middot; {{ communication.sent_at }}
                <span v-if="communication.edited" class="italic">(modificata)</span>
              </p>
            </div>

            <template v-if="communication.can_manage">
              <!-- Conferma eliminazione -->
              <div v-if="confirmDeleteId === communication.id" class="flex items-center gap-1 shrink-0">
                <span class="text-[11px] text-gray-600 mr-1">Eliminare?</span>
                <button type="button" @click="remove(communication)" :disabled="deletingId === communication.id"
                  class="px-2 py-1 text-[11px] font-semibold text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-60 transition-colors">
                  {{ deletingId === communication.id ? '...' : 'Sì' }}
                </button>
                <button type="button" @click="confirmDeleteId = null"
                  class="px-2 py-1 text-[11px] font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                  No
                </button>
              </div>

              <!-- Azioni -->
              <div v-else class="flex items-center gap-1 shrink-0">
                <button type="button" @click="startEdit(communication)" title="Modifica"
                  class="p-1 rounded text-gray-500 hover:text-copam-blue hover:bg-white transition-colors">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button type="button" @click="confirmDeleteId = communication.id" title="Elimina"
                  class="p-1 rounded text-gray-500 hover:text-red-600 hover:bg-white transition-colors">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </template>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>
