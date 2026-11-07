<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import { usePolling } from '@/composables/usePolling'

const data = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0,
})

const filters = ref({
  message: '',
  opart_padre: '',
  opart: '',
  fllav: '',
  idopr: '',
  user_name: '',
  date_from: '',
  date_to: '',
  unassigned: false,
})

let fetching = false

// silent: aggiornamento in background (polling) senza spinner
const fetchData = async (page = 1, { silent = false } = {}) => {
  if (silent && fetching) return
  fetching = true
  if (!silent) loading.value = true
  try {
    const params = { page }
    Object.entries(filters.value).forEach(([key, val]) => {
      if (val) params[key] = val === true ? 1 : val
    })
    const res = await axios.get('/api/communications/manage', { params })
    data.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
  } finally {
    fetching = false
    if (!silent) loading.value = false
  }
}

// Nuove comunicazioni e letture dei destinatari senza ricaricare la pagina
usePolling(() => fetchData(currentPage.value, { silent: true }))

const hasFilters = () => Object.values(filters.value).some(Boolean)

const clearAllFilters = () => {
  Object.keys(filters.value).forEach((key) => {
    filters.value[key] = typeof filters.value[key] === 'boolean' ? false : ''
  })
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchData(page)
  }
}

let searchTimeout
watch(filters, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchData(1), 300)
}, { deep: true })

onMounted(() => fetchData())

// Assegnazione destinatari
const users = ref([])
const assignModal = ref({ show: false, row: null })
const selectedUserIds = ref([])
const userSearch = ref('')
const assigning = ref(false)
const assignError = ref('')

const fetchUsers = async () => {
  if (users.value.length) return
  const res = await axios.get('/api/users')
  users.value = res.data
}

const filteredUsers = computed(() => {
  const term = userSearch.value.trim().toLowerCase()
  if (!term) return users.value
  return users.value.filter(u => u.name.toLowerCase().includes(term) || u.email?.toLowerCase().includes(term))
})

const openAssign = async (row) => {
  assignModal.value = { show: true, row }
  selectedUserIds.value = row.recipients.map(r => r.id)
  userSearch.value = ''
  assignError.value = ''
  try {
    await fetchUsers()
  } catch (error) {
    console.error(error)
    assignError.value = 'Errore nel caricamento degli utenti'
  }
}

const closeAssign = () => {
  assignModal.value = { show: false, row: null }
}

const toggleAllFiltered = () => {
  const ids = filteredUsers.value.map(u => u.id)
  const allSelected = ids.every(id => selectedUserIds.value.includes(id))
  selectedUserIds.value = allSelected
    ? selectedUserIds.value.filter(id => !ids.includes(id))
    : [...new Set([...selectedUserIds.value, ...ids])]
}

const saveAssign = async () => {
  const row = assignModal.value.row
  if (!row) return
  assigning.value = true
  assignError.value = ''
  try {
    await axios.put(`/api/communications/${row.id}/recipients`, { user_ids: selectedUserIds.value })
    closeAssign()
    await fetchData(currentPage.value)
  } catch (error) {
    assignError.value = error.response?.data?.message || 'Errore durante l\'assegnazione'
  } finally {
    assigning.value = false
  }
}

// Eliminazione comunicazione
const deleting = ref(false)
const confirmDelete = ref({ show: false, row: null })

const askDelete = (row) => {
  confirmDelete.value = { show: true, row }
}

const cancelDelete = () => {
  confirmDelete.value = { show: false, row: null }
}

const performDelete = async () => {
  const row = confirmDelete.value.row
  if (!row) return
  deleting.value = true
  try {
    await axios.delete(`/api/communications/${row.id}`)
    confirmDelete.value = { show: false, row: null }
    const goBack = data.value.length === 1 && currentPage.value > 1
    await fetchData(goBack ? currentPage.value - 1 : currentPage.value)
  } catch (error) {
    console.error(error)
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="space-y-4">

    <!-- FILTRI -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="bg-copam-blue px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
          </svg>
          <span class="text-sm font-semibold text-white">Filtri di ricerca</span>
        </div>
        <button
          v-if="hasFilters()"
          @click="clearAllFilters"
          class="inline-flex items-center gap-1 text-xs text-blue-100 hover:text-white transition-colors"
        >
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Cancella filtri
        </button>
      </div>

      <div class="p-4 grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        <div class="sm:col-span-2 lg:col-span-1">
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Testo</label>
          <input type="text" v-model="filters.message" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Articolo padre</label>
          <input type="text" v-model="filters.opart_padre" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Codice articolo</label>
          <input type="text" v-model="filters.opart" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">FLLAV</label>
          <input type="text" v-model="filters.fllav" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">IDOPR</label>
          <input type="text" v-model="filters.idopr" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Mittente</label>
          <input type="text" v-model="filters.user_name" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Dal</label>
          <input type="date" v-model="filters.date_from"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Al</label>
          <input type="date" v-model="filters.date_to"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
      </div>
      <div class="px-4 pb-4">
        <label class="inline-flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
          <input type="checkbox" v-model="filters.unassigned"
            class="rounded border-gray-300 text-copam-blue focus:ring-copam-blue" />
          Solo comunicazioni non ancora assegnate
        </label>
      </div>
    </div>

    <!-- TABELLA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Toolbar -->
      <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
        <span class="text-xs text-gray-500">
          <span v-if="loading">Caricamento...</span>
          <span v-else>
            <span class="font-semibold text-gray-700">{{ pagination.total }}</span> comunicazioni totali
          </span>
        </span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Data e ora</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Mittente</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Fase di lavoro</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">Comunicazione</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Destinatari</th>
              <th class="px-3 py-2.5 text-center font-semibold uppercase tracking-wider whitespace-nowrap">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="6" class="px-3 py-10 text-center text-gray-400">
                <div class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5 text-copam-blue" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span class="text-xs">Caricamento...</span>
                </div>
              </td>
            </tr>

            <!-- Righe dati -->
            <tr v-else v-for="(row, index) in data" :key="row.id"
              :class="index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'">
              <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100 align-top">
                {{ row.sent_at }}
                <span v-if="row.edited" class="block text-[10px] italic text-gray-400">modificata</span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100 align-top">{{ row.user_name || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100 align-top">
                <div><span class="text-gray-400">Padre:</span> {{ row.opart_padre || '—' }}</div>
                <div><span class="text-gray-400">Art.:</span> {{ row.opart || '—' }}</div>
                <div><span class="text-gray-400">FLLAV:</span> {{ row.fllav || '—' }} &middot; <span class="text-gray-400">IDOPR:</span> {{ row.idopr || '—' }}</div>
              </td>
              <td class="px-3 py-2 text-gray-800 border-r border-gray-100 align-top min-w-[16rem]">
                <p class="whitespace-pre-line break-words">{{ row.message }}</p>
              </td>
              <td class="px-3 py-2 text-gray-700 border-r border-gray-100 align-top">
                <span v-if="!row.recipients.length" class="inline-block bg-amber-100 text-amber-700 text-[11px] font-semibold px-2 py-0.5 rounded">Non assegnata</span>
                <div v-else class="flex flex-wrap gap-1 max-w-xs">
                  <span v-for="recipient in row.recipients" :key="recipient.id"
                    :title="recipient.read_at ? `Letta il ${recipient.read_at}` : 'Non ancora letta'"
                    :class="[
                      'inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded',
                      recipient.read_at ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-copam-blue'
                    ]">
                    <svg v-if="recipient.read_at" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ recipient.name }}
                  </span>
                </div>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-center align-top">
                <div class="inline-flex items-center gap-1">
                  <button
                    @click="openAssign(row)"
                    title="Assegna destinatari"
                    class="inline-flex items-center gap-1 rounded-md px-2 py-1.5 text-copam-blue hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-copam-blue transition-colors font-semibold"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Assegna
                  </button>
                  <button
                    @click="askDelete(row)"
                    title="Elimina comunicazione"
                    class="inline-flex items-center justify-center rounded-md p-1.5 text-red-600 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition-colors"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!loading && !data.length">
              <td colspan="6" class="px-3 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <p class="mt-2 text-xs font-medium text-gray-400">Nessuna comunicazione</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginazione -->
      <div class="border-t border-gray-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
        <p class="text-xs text-gray-500">
          Mostrando <span class="font-medium text-gray-700">{{ pagination.from }}</span> &ndash; <span class="font-medium text-gray-700">{{ pagination.to }}</span> di <span class="font-medium text-gray-700">{{ pagination.total }}</span> comunicazioni
        </p>
        <div class="flex items-center gap-1">
          <button @click="goToPage(1)" :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &laquo;
          </button>
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &lsaquo; Prec.
          </button>
          <span class="px-3 py-1 text-xs bg-copam-blue text-white rounded font-medium">{{ currentPage }} / {{ pagination.last_page }}</span>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Succ. &rsaquo;
          </button>
          <button @click="goToPage(pagination.last_page)" :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &raquo;
          </button>
        </div>
      </div>
    </div>

    <!-- Modale assegnazione destinatari -->
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0" enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100" leave-to-class="opacity-0"
    >
      <div v-if="assignModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="closeAssign"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
          <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Assegna comunicazione</h3>
            <button @click="closeAssign" class="text-white/70 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-6 py-4 space-y-3">
            <div class="border border-gray-200 bg-gray-50 rounded-lg px-3 py-2">
              <p class="text-xs text-gray-800 whitespace-pre-line break-words max-h-24 overflow-y-auto">{{ assignModal.row?.message }}</p>
              <p class="mt-1 text-[11px] text-gray-500">{{ assignModal.row?.user_name }} &middot; {{ assignModal.row?.sent_at }}</p>
            </div>

            <div class="flex items-center gap-2">
              <input type="text" v-model="userSearch" placeholder="Cerca utente…"
                class="flex-1 px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
              <button type="button" @click="toggleAllFiltered"
                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                Seleziona/Deseleziona tutti
              </button>
            </div>

            <ul class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-72 overflow-y-auto">
              <li v-for="user in filteredUsers" :key="user.id">
                <label class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-blue-50">
                  <input type="checkbox" :value="user.id" v-model="selectedUserIds"
                    class="rounded border-gray-300 text-copam-blue focus:ring-copam-blue" />
                  <span class="text-xs text-gray-800 font-medium">{{ user.name }}</span>
                  <span class="text-[11px] text-gray-400 truncate">{{ user.email }}</span>
                </label>
              </li>
              <li v-if="!filteredUsers.length" class="px-3 py-4 text-center text-xs text-gray-400">Nessun utente trovato</li>
            </ul>
            <p class="text-[11px] text-gray-500">{{ selectedUserIds.length }} destinatari selezionati</p>
            <p v-if="assignError" class="text-xs font-medium text-red-600">{{ assignError }}</p>
          </div>
          <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
            <button
              @click="closeAssign"
              :disabled="assigning"
              class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300 disabled:opacity-50"
            >
              Annulla
            </button>
            <button
              @click="saveAssign"
              :disabled="assigning"
              class="inline-flex items-center gap-2 justify-center rounded-md border border-transparent bg-copam-blue px-4 py-2 text-sm font-medium text-white hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ assigning ? 'Salvataggio...' : 'Salva assegnazione' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modale conferma eliminazione -->
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0" enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100" leave-to-class="opacity-0"
    >
      <div v-if="confirmDelete.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="cancelDelete"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
          <div class="px-6 pt-6 pb-4 flex items-start gap-4">
            <div class="flex-shrink-0 flex items-center justify-center h-11 w-11 rounded-full bg-red-100">
              <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900">Elimina comunicazione</h3>
              <p class="mt-1 text-sm text-gray-500">
                Sei sicuro di voler eliminare questa comunicazione
                <span v-if="confirmDelete.row?.sent_at" class="font-medium text-gray-700">del {{ confirmDelete.row.sent_at }}</span>?
                Verrà rimossa anche ai destinatari. L'operazione non è reversibile.
              </p>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
            <button
              @click="cancelDelete"
              :disabled="deleting"
              class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300 disabled:opacity-50"
            >
              Annulla
            </button>
            <button
              @click="performDelete"
              :disabled="deleting"
              class="inline-flex items-center gap-2 justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ deleting ? 'Eliminazione...' : 'Elimina' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>
