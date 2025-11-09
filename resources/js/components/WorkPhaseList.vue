<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import WorkPhaseAssModal from './WorkPhaseAssModal.vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'
import { Italian } from 'flatpickr/dist/l10n/it.js'

const workPhases = ref([])
const search = ref('')
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

const fetchWorkPhases = async (searchTerm = '', fromDate = '', toDate = '', page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page
    }
    if (searchTerm) params.search = searchTerm
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
  fetchWorkPhases(search.value, dateFrom.value, dateTo.value, 1)
}

const clearAllFilters = () => {
  search.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  applyFilters()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchWorkPhases(search.value, dateFrom.value, dateTo.value, page)
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
watch(search, (newSearch) => {
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
    alert('Seleziona almeno una fase di lavoro');
    return;
  }
  if (!selectedUser.value) {
    alert('Seleziona un utente a cui assegnare le fasi');
    return;
  }

  try {
    loading.value = true;
    const res = await axios.post('/api/work-phases/assign', {
      selected: selected.value,
      assigned_to: selectedUser.value,
      notes: notes.value
    });
    alert(res.data.message);

    // Resetta selezioni
    selected.value = [];
    selectedUser.value = '';
    notes.value = '';

    // Ricarica la lista se vuoi
    await fetchWorkPhases(search.value, dateFrom.value, dateTo.value, currentPage.value);

  } catch (error) {
    console.error(error);
    alert('Errore durante l\'assegnazione delle fasi');
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
        <div class="flex gap-4 items-end">
          <!-- Search input -->
          <div class="flex-1">
            <div class="relative">
              <input 
                type="text"
                v-model="search"
                placeholder="Cerca Fasi di Lavoro..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Date range inputs -->
          <div class="flex gap-4 flex-1">
            <div class="flex-1">
              <label class="block text-xs text-gray-500 mb-1">Data da</label>
              <input
                ref="dateFromPicker"
                type="text"
                v-model="dateFrom"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Seleziona data iniziale..."
              />
            </div>
            <div class="flex-1">
              <label class="block text-xs text-gray-500 mb-1">Data a</label>
              <input
                ref="dateToPicker"
                type="text"
                v-model="dateTo"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Seleziona data finale..."
              />
            </div>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex justify-end gap-2">
          <button 
            v-if="search || dateFrom || dateTo"
            @click="clearAllFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cancella
          </button>
          <button 
            @click="applyFilters"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            {{ loading ? 'Caricamento...' : 'Cerca' }}
          </button>
        </div>
      </div>

      <!-- Results info -->
      <div v-if="search || dateFrom || dateTo" class="mt-4 text-sm text-gray-600">
        <span v-if="pagination.total > 0">
          Trovati {{ pagination.total }} record{{ pagination.total === 1 ? 'o' : 'i' }}
          <template v-if="search"> per "{{ search }}"</template>
        </span>
        <span v-else>
          Nessun record trovato
          <template v-if="search"> per "{{ search }}"</template>
        </span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr class="border-b border-gray-200">
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">#</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLASS</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">IDOPR</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLSEQ</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLLAV</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLDES</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLCON</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">MATERIALE</th>
            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">SPESSORE</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-if="loading">
            <td colspan="14" class="px-3 py-3 text-center text-sm text-gray-500">
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
              <input type="checkbox" v-model="selected" :value="phase.RECORD_ID" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            </td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLASS }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.IDOPR }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLSEQ }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLLAV }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLDES }}</td>
            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ formatDate(phase.FLCON) }}</td>
            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.MATERIALE }}</td>
            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.SPESSORE }}</td>
            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
              <button 
                @click="openModal(phase)"
                class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 px-3 py-1 rounded-md text-sm font-medium transition duration-150 ease-in-out"
              >
                Dettagli
              </button>
            </td>
          </tr>
          <tr v-if="!loading && !workPhases.length">
            <td colspan="14" class="px-3 py-2 text-center">
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
          class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
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
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          placeholder="Inserisci le note..."
        ></textarea>
      </div>
    </div>

    <div class="mt-4 flex justify-end">
      <button class="bg-green-500 text-white px-4 py-2 rounded" @click="confirmSelected">
        Conferma Selezionati
      </button>
    </div>
  </div>

  <!-- Modal -->
  <WorkPhaseAssModal 
    v-model:show="showModal"
    v-model:modelValue="selected"
    :phase="selectedPhase"
  />
</template>