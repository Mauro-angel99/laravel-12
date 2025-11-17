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
const dateFrom = ref('')
const dateTo = ref('')
const dateFromPicker = ref(null)
const dateToPicker = ref(null)
const selected = ref([])
const loading = ref(false)
const currentPage = ref(1)
const showModal = ref(false)
const selectedPhase = ref(null)
const users = ref([])
const selectedUser = ref('')
const notes = ref('')
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

const fetchWorkPhases = async (searchTerm = '', fllav = '', dtras = '', dtric = '', dtnum = '', idopr = '', fromDate = '', toDate = '', page = 1) => {
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
    if (fromDate) params.date_from = fromDate
    if (toDate) params.date_to = toDate
    
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
  fetchWorkPhases(search.value, searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, dateFrom.value, dateTo.value, 1)
}

const clearAllFilters = () => {
  search.value = ''
  searchFllav.value = ''
  searchDtras.value = ''
  searchDtric.value = ''
  searchDtnum.value = ''
  searchIdopr.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  applyFilters()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchWorkPhases(search.value, searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, dateFrom.value, dateTo.value, page)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
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
  flatpickr(dateFromPicker.value, {
    locale: Italian,
    dateFormat: 'Y-m-d',
    altFormat: 'd/m/Y',
    altInput: true,
    allowInput: true,
    onChange: function(selectedDates, dateStr) {
      dateFrom.value = dateStr;
    }
  });

  // Inizializza il datepicker per la data finale
  flatpickr(dateToPicker.value, {
    locale: Italian,
    dateFormat: 'Y-m-d',
    altFormat: 'd/m/Y',
    altInput: true,
    allowInput: true,
    onChange: function(selectedDates, dateStr) {
      dateTo.value = dateStr;
    }
  });

  // Carica i dati iniziali
  await fetchUsers();
  await fetchWorkPhases();
});

// Watcher per la ricerca con debounce
let searchTimeout
watch([search, searchFllav, searchDtras, searchDtric, searchDtnum, searchIdopr], () => {
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
    await fetchWorkPhases(search.value, searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, dateFrom.value, dateTo.value, currentPage.value);

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
  <div class="bg-white shadow rounded-lg p-3">
    <div class="mb-6">
      <!-- Search Bar and Filters -->
      <div class="space-y-4">
        <!-- First row: 3 search fields -->
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-xs mb-1">Codice Lav</label>
            <input 
              type="text"
              v-model="searchFllav"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-xs mb-1">Data da</label>
            <input
              ref="dateFromPicker"
              type="text"
              v-model="dateFrom"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-xs mb-1">Data a</label>
            <input
              ref="dateToPicker"
              type="text"
              v-model="dateTo"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
          
        </div>

        <!-- Second row: 2 search fields -->
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-xs mb-1">Rag. Soc.</label>
            <input 
              type="text"
              v-model="searchDtras"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-xs mb-1">N. Ord. Cli.</label>
            <input 
              type="text"
              v-model="searchDtric"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
          <div>
            <label class="block text-xs mb-1">N. Ns. Ord.</label>
            <input 
              type="text"
              v-model="searchDtnum"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
        </div>

        <!-- Third row: Date range inputs -->
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-xs mb-1">Ord. Prod.</label>
            <input 
              type="text"
              v-model="searchIdopr"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
            />
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex justify-end gap-2">
          <button 
            v-if="searchFllav || searchDtras || searchDtric || searchDtnum || searchIdopr || dateFrom || dateTo"
            @click="clearAllFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cancella
          </button>
          <button 
            @click="applyFilters"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 bg-copam-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-copam-blue-hover focus:bg-copam-blue-hover active:bg-copam-blue-active focus:outline-none focus:ring-2 focus:ring-copam-blue focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            {{ loading ? 'Caricamento...' : 'Cerca' }}
          </button>
        </div>
      </div>

      <!-- Results info -->
      <div v-if="searchFllav || searchDtras || searchDtric || searchDtnum || searchIdopr || dateFrom || dateTo" class="mt-4 text-sm text-gray-600">
        <span v-if="pagination.total > 0">
          Trovati {{ pagination.total }} record{{ pagination.total === 1 ? 'o' : 'i' }}
        </span>
        <span v-else>
          Nessun record trovato
        </span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr class="border-b border-gray-200">
            <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider border-r border-gray-200">#</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">FLASS</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">IDOPR</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">FLSEQ</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">FLLAV</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">FLDES</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">DTRAS</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">DTRIC</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">DTNUM</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">FLCON</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">MATERIALE</th>
            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200">SPESSORE</th>
            <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Azioni</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-if="loading">
            <td colspan="13" class="px-3 py-3 text-center text-sm text-gray-500">
              <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-gray-500" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Caricamento...
              </div>
            </td>
          </tr>
          <tr v-else v-for="phase in workPhases" :key="phase.RECORD_ID" class="hover:bg-gray-50 border-b border-gray-200">
            <td class="px-3 py-3 whitespace-nowrap text-sm text-center border-r border-gray-200">
              <input type="checkbox" v-model="selected" :value="phase.RECORD_ID" class="rounded border-gray-300 text-copam-blue focus:ring-copam-blue" />
            </td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLASS }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.IDOPR }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLSEQ }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLLAV }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLDES }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.DTRAS }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.DTRIC }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.DTNUM }}</td>
            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ formatDate(phase.FLCON) }}</td>
            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.MATERIALE }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.SPESSORE }}</td>
            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
              <button 
                @click="openModal(phase)"
                class="text-copam-blue hover:text-copam-blue-hover transition duration-150 ease-in-out"
                title="Visualizza dettagli"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="!loading && !workPhases.length">
            <td colspan="13" class="px-3 py-2 text-center">
              <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna fase di lavoro trovata</h3>
                <p class="mt-1 text-sm text-gray-500">
                  {{ search ? 'Prova a modificare i termini di ricerca.' : 'Non ci sono fasi di lavoro disponibili.' }}
                </p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Informazioni paginazione -->
    <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
      <div>
        Mostrando {{ pagination.from }} - {{ pagination.to }} di {{ pagination.total }} record
      </div>
      <div>Selezionati: {{ selected.length }}</div>
    </div>
    
    <!-- Controlli paginazione -->
    <div class="mt-2 flex justify-center items-center space-x-2">
      <button 
        @click="goToPage(1)" 
        :disabled="currentPage === 1"
        class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Prima
      </button>
      <button 
        @click="goToPage(currentPage - 1)" 
        :disabled="currentPage === 1"
        class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Precedente
      </button>
      
      <span class="px-3 py-1 text-sm">
        Pagina {{ currentPage }} di {{ pagination.last_page }}
      </span>
      
      <button 
        @click="goToPage(currentPage + 1)" 
        :disabled="currentPage === pagination.last_page"
        class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Successiva
      </button>
      <button 
        @click="goToPage(pagination.last_page)" 
        :disabled="currentPage === pagination.last_page"
        class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Ultima
      </button>
    </div>
    
    <!-- User selection and notes -->
    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Seleziona Utente
        </label>
        <select
          v-model="selectedUser"
          class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-copam-blue focus:border-copam-blue sm:text-sm rounded-md"
        >
          <option value="">Seleziona un utente</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Note
        </label>
        <textarea
          v-model="notes"
          rows="3"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
          placeholder="Inserisci le note..."
        ></textarea>
      </div>
    </div>

    <div class="mt-4 flex justify-end">
      <button class="bg-green-500 text-white px-4 py-2 rounded" @click="confirmSelected">
        Assegna
      </button>
    </div>
  </div>

  <!-- Modal -->
  <WorkPhaseAssModal 
    v-model:show="showModal"
    v-model:modelValue="selected"
    :phase="selectedPhase"
  />

  <!-- Modal Messaggio di Conferma/Errore -->
  <Teleport to="body">
    <div v-if="messageModal.show" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeMessageModal"
        ></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <!-- Icona -->
            <div :class="[
              'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10',
              messageModal.type === 'success' ? 'bg-green-100' : 'bg-red-100'
            ]">
              <svg 
                v-if="messageModal.type === 'success'"
                class="h-6 w-6 text-green-600" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg 
                v-else
                class="h-6 w-6 text-red-600" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            
            <!-- Contenuto -->
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ messageModal.title }}
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  {{ messageModal.message }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Pulsante -->
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button 
              type="button"
              @click="closeMessageModal"
              :class="[
                'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm',
                messageModal.type === 'success' 
                  ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                  : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
              ]"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>