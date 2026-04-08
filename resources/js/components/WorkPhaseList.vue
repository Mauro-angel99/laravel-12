<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import WorkPhaseAssModal from './WorkPhaseAssModal.vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'
import { Italian } from 'flatpickr/dist/l10n/it.js'

const workPhases = ref([])
const search = ref('')
const searchFllav = ref('')
const searchDtras = ref('')
const searchDtric = ref('')
const searchDtnum = ref('')
const searchIdopr = ref('')
const searchOpart = ref('')
const showOnlyWorked = ref(true)
const showOnlyAvailable = ref(true)
const dateFrom = ref('')
const dateTo = ref('')
const dateFromPicker = ref(null)
const dateToPicker = ref(null)
const dateFromInstance = ref(null)
const dateToInstance = ref(null)
const selected = ref([])
const loading = ref(false)
const currentPage = ref(1)
const showModal = ref(false)
const selectedPhase = ref(null)
const users = ref([])
const selectedUser = ref('')
const notes = ref('')
const sortBy = ref('drcon_asc')
const tableContainer = ref(null)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0,
  has_more_pages: false
})

// Stato della modal di messaggio
const messageModal = ref({
  show: false,
  type: 'success', // 'success' o 'error'
  title: '',
  message: ''
})

const showMessageModal = (type, title, message) => {
  messageModal.value = {
    show: true,
    type,
    title,
    message
  }
}

const closeMessageModal = () => {
  messageModal.value.show = false
}

const fetchWorkPhases = async (searchTerm = '', fllav = '', dtras = '', dtric = '', dtnum = '', idopr = '', opart = '', fromDate = '', toDate = '', onlyWorked = false, onlyAvailable = false, page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page
    }
    if (searchTerm) params.search = searchTerm
    if (fllav) params.fllav = fllav
    if (dtras) params.dtras = dtras
    if (dtric) params.dtric = dtric
    if (dtnum) params.dtnum = dtnum
    if (idopr) params.idopr = idopr
    if (opart) params.opart = opart
    if (fromDate) params.date_from = fromDate
    if (toDate) params.date_to = toDate
    if (onlyWorked) params.only_worked = '1'
    if (onlyAvailable) params.only_available = '1'
    if (sortBy.value) params.sort = sortBy.value
    
    const res = await axios.get('/api/work-phases', { params })
    workPhases.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1 // Reset alla prima pagina quando si applicano filtri
  fetchWorkPhases(search.value, searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, searchOpart.value, dateFrom.value, dateTo.value, showOnlyWorked.value, showOnlyAvailable.value, 1)
}

const clearAllFilters = () => {
  search.value = ''
  searchFllav.value = ''
  searchDtras.value = ''
  searchDtric.value = ''
  searchDtnum.value = ''
  searchIdopr.value = ''
  searchOpart.value = ''
  showOnlyWorked.value = false
  showOnlyAvailable.value = false
  dateFrom.value = ''
  dateTo.value = ''
  
  // Reset visuale dei datepicker
  if (dateFromInstance.value) {
    dateFromInstance.value.clear()
  }
  if (dateToInstance.value) {
    dateToInstance.value.clear()
  }
  
  applyFilters()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchWorkPhases(search.value, searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, searchOpart.value, dateFrom.value, dateTo.value, showOnlyWorked.value, showOnlyAvailable.value, page)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  // Supporta sia ISO "YYYY-MM-DD..." sia altri formati
  const parts = String(dateString).substring(0, 10).split('-');
  if (parts.length === 3 && parts[0].length === 4) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return '';
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

const openModal = (phase) => {
  selectedPhase.value = phase
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// Funzione per caricare gli utenti
const fetchUsers = async () => {
  try {
    const res = await axios.get('/api/users')
    users.value = res.data
  } catch (error) {
    console.error('Errore nel caricamento degli utenti:', error)
  }
}

// Setup dei datepicker e caricamento dati iniziali
onMounted(async () => {
  // Inizializza il datepicker per la data iniziale
  dateFromInstance.value = flatpickr(dateFromPicker.value, {
    locale: Italian,
    dateFormat: 'Y-m-d',
    altFormat: 'd/m/Y',
    altInput: true,
    allowInput: true,
    onChange: function(selectedDates, dateStr) {
      dateFrom.value = dateStr;
    },
    onClose: function(selectedDates, dateStr) {
      dateFrom.value = dateStr;
    }
  });

  // Inizializza il datepicker per la data finale
  dateToInstance.value = flatpickr(dateToPicker.value, {
    locale: Italian,
    dateFormat: 'Y-m-d',
    altFormat: 'd/m/Y',
    altInput: true,
    allowInput: true,
    onChange: function(selectedDates, dateStr) {
      dateTo.value = dateStr;
    },
    onClose: function(selectedDates, dateStr) {
      dateTo.value = dateStr;
    }
  });

  // Carica i dati iniziali
  await fetchUsers();
  await fetchWorkPhases('', '', '', '', '', '', '', '', '', showOnlyWorked.value, showOnlyAvailable.value, 1);
});

// Watcher per la ricerca con debounce
let searchTimeout
watch([search, searchFllav, searchDtras, searchDtric, searchDtnum, searchIdopr, searchOpart], () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
})

// Watcher per le date con debounce
let dateTimeout
watch([dateFrom, dateTo], () => {
  clearTimeout(dateTimeout)
  dateTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
})

// Watcher per lo switch "Solo Lavorati" (senza debounce)
watch(showOnlyWorked, () => {
  applyFilters()
})

// Watcher per lo switch "Solo Disponibili" (senza debounce)
watch(showOnlyAvailable, () => {
  applyFilters()
})

// Watcher per l'ordinamento
watch(sortBy, () => {
  applyFilters()
})

const confirmSelected = async () => {
  if (!selected.value.length) {
    showMessageModal('error', 'Errore', 'Seleziona almeno una fase di lavoro');
    return;
  }
  if (!selectedUser.value) {
    showMessageModal('error', 'Errore', 'Seleziona un utente a cui assegnare le fasi');
    return;
  }

  try {
    loading.value = true;
    const res = await axios.post('/api/work-phases/assign', {
      selected: selected.value,
      assigned_to: selectedUser.value,
      notes: notes.value
    });
    showMessageModal('success', 'Successo', res.data.message);

    // Resetta selezioni
    selected.value = [];
    selectedUser.value = '';
    notes.value = '';

    // Ricarica la lista se vuoi
    await fetchWorkPhases(search.value, searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, searchOpart.value, dateFrom.value, dateTo.value, showOnlyWorked.value, showOnlyAvailable.value, currentPage.value);

  } catch (error) {
    console.error(error);
    const errorMessage = error.response?.data?.message || 'Errore durante l\'assegnazione delle fasi';
    showMessageModal('error', 'Errore', errorMessage);
  } finally {
    loading.value = false;
  }
}

</script>

<template>
  <div class="space-y-4">

    <!-- ── FILTRI ────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Header filtri -->
      <div class="bg-copam-blue px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
          </svg>
          <span class="text-sm font-semibold text-white">Filtri di ricerca</span>
        </div>
        <button
          v-if="searchFllav || searchDtras || searchDtric || searchDtnum || searchIdopr || searchOpart || showOnlyWorked || showOnlyAvailable || dateFrom || dateTo"
          @click="clearAllFilters"
          class="inline-flex items-center gap-1 text-xs text-blue-100 hover:text-white transition-colors"
        >
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Cancella filtri
        </button>
      </div>

      <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Codice Lav.</label>
            <input type="text" v-model="searchFllav"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Data da</label>
            <input ref="dateFromPicker" type="text" v-model="dateFrom"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Data a</label>
            <input ref="dateToPicker" type="text" v-model="dateTo"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Rag. Soc.</label>
            <input type="text" v-model="searchDtras"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">N. Ord. Cli.</label>
            <input type="text" v-model="searchDtric"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">N. Ns. Ord.</label>
            <input type="text" v-model="searchDtnum"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Ord. Prod.</label>
            <input type="text" v-model="searchIdopr"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Codice Articolo</label>
            <input type="text" v-model="searchOpart"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div class="flex items-center gap-6 pb-0.5">
            <label class="inline-flex items-center cursor-pointer gap-2">
              <div class="relative">
                <input type="checkbox" v-model="showOnlyWorked" class="sr-only peer">
                <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-copam-blue peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all after:border after:border-gray-300"></div>
              </div>
              <span class="text-xs font-medium text-gray-700">Escludi lavorati</span>
            </label>
            <label class="inline-flex items-center cursor-pointer gap-2">
              <div class="relative">
                <input type="checkbox" v-model="showOnlyAvailable" class="sr-only peer">
                <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-copam-blue peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all after:border after:border-gray-300"></div>
              </div>
              <span class="text-xs font-medium text-gray-700">Solo disponibili</span>
            </label>
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Ordinamento</label>
            <select v-model="sortBy"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue">
              <option value="drcon_asc">DRCON crescente</option>
              <option value="drcon_desc">DRCON decrescente</option>
              <option value="dtras_asc">DTRAS crescente</option>
              <option value="flnot_asc">FLNOT crescente</option>
              <option value="armat_asc">ARMAT crescente</option>
              <option value="ardmz_asc">ARDMZ crescente</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TABELLA ─────────────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

      <!-- Info risultati -->
      <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between bg-gray-50">
        <span class="text-xs text-gray-500">
          <span v-if="loading">Caricamento...</span>
          <span v-else>
            Mostrando <span class="font-semibold text-gray-700">{{ pagination.from }} - {{ pagination.to }}</span>
            di <span class="font-semibold text-gray-700">{{ pagination.total }}</span> record
          </span>
        </span>
        <span v-if="selected.length" class="text-xs font-semibold text-copam-blue">
          {{ selected.length }} selezionat{{ selected.length === 1 ? 'o' : 'i' }}
        </span>
      </div>

      <div ref="tableContainer" class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 w-12">#</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">DRCMM</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">DRCON</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLASS</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">IDOPR</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLSEQ</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLLAV</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">OPART</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLDES</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">DTRAS</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">DTRIC</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">DTNUM</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider">FLCON</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="13" class="px-3 py-10 text-center text-gray-400">
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
            <tr v-else v-for="(phase, index) in workPhases" :key="phase.RECORD_ID"
              @click="openModal(phase)"
              :class="[
                'cursor-pointer transition-colors',
                index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'
              ]">
              <td class="px-3 py-2 text-left border-r border-gray-100" @click.stop>
                <div class="flex items-center justify-start gap-1.5">
                  <input type="checkbox" v-model="selected" :value="phase.RECORD_ID"
                    class="rounded border-gray-300 text-copam-blue focus:ring-copam-blue" />
                  <div v-if="phase.is_assigned" class="relative group inline-flex">
                    <svg class="h-4 w-4 text-green-500 cursor-default" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <div class="absolute bottom-full -left-4 mb-1 hidden group-hover:block z-50 pointer-events-none">
                      <div class="bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap shadow-lg">
                        {{ phase.assigned_user_name || 'Utente sconosciuto' }}
                      </div>
                      <div class="w-2 h-2 bg-gray-800 rotate-45 ml-[22px] -mt-1"></div>
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">{{ phase.DRCMM }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ formatDate(phase.DRCON) }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.FLASS }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.IDOPR }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.FLSEQ }}</td>
              <td class="px-3 py-2 whitespace-nowrap border-r border-gray-100">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-copam-blue">{{ phase.FLLAV }}</span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.OPART || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.FLDES }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.DTRAS }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.DTRIC }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ phase.DTNUM }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700">{{ formatDate(phase.FLCON) }}</td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!loading && !workPhases.length">
              <td colspan="13" class="px-3 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-xs font-medium text-gray-400">Nessuna fase di lavoro trovata</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginazione -->
      <div class="border-t border-gray-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
        <p class="text-xs text-gray-500">
          Mostrando <span class="font-medium text-gray-700">{{ pagination.from }}</span> &ndash; <span class="font-medium text-gray-700">{{ pagination.to }}</span> di <span class="font-medium text-gray-700">{{ pagination.total }}</span> record
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

    <!-- ── ASSEGNAZIONE ───────────────────────────────────────── -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="bg-copam-blue px-5 py-3">
        <span class="text-sm font-semibold text-white">Assegnazione fasi</span>
        <span v-if="selected.length" class="ml-2 text-xs text-blue-200">({{ selected.length }} selezionat{{ selected.length === 1 ? 'a' : 'e' }})</span>
      </div>
      <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Utente</label>
          <select v-model="selectedUser"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue">
            <option value="" disabled>-- Seleziona utente --</option>
            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Note</label>
          <textarea v-model="notes" rows="2"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue resize-none"
            placeholder="Inserisci le note..."></textarea>
        </div>
      </div>
      <div class="border-t border-gray-100 px-4 py-3 flex justify-end bg-gray-50">
        <button @click="confirmSelected"
          class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Assegna
        </button>
      </div>
    </div>

  </div>

  <!-- Modal dettaglio fase -->
  <WorkPhaseAssModal
    v-model:show="showModal"
    v-model:modelValue="selected"
    :phase="selectedPhase"
  />

  <!-- Modal conferma/errore -->
  <Teleport to="body">
    <div v-if="messageModal.show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div class="fixed inset-0 bg-gray-900 bg-opacity-50" @click="closeMessageModal"></div>
      <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div :class="['px-5 py-4', messageModal.type === 'success' ? 'bg-green-600' : 'bg-red-600']">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 bg-white/20 rounded-full p-1.5">
              <svg v-if="messageModal.type === 'success'" class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-white">{{ messageModal.title }}</h3>
          </div>
        </div>
        <div class="px-5 py-4">
          <p class="text-xs text-gray-600">{{ messageModal.message }}</p>
        </div>
        <div class="border-t border-gray-100 px-5 py-3 flex justify-end bg-gray-50">
          <button @click="closeMessageModal"
            :class="['px-4 py-1.5 rounded-lg text-xs font-semibold text-white transition-colors', messageModal.type === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700']">
            OK
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
