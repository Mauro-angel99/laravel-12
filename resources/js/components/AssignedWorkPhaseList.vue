<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import AssignedWorkPhaseModal from './AssignedWorkPhaseModal.vue'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'
import { Italian } from 'flatpickr/dist/l10n/it.js'

const assignedWorkPhases = ref([])
const searchFllav = ref('')
const searchDtras = ref('')
const searchDtric = ref('')
const searchDtnum = ref('')
const searchIdopr = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const dateFromPicker = ref(null)
const dateToPicker = ref(null)
const dateFromInstance = ref(null)
const dateToInstance = ref(null)
const loading = ref(false)
const currentPage = ref(1)
const showModal = ref(false)
const selectedAssignment = ref(null)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0,
  has_more_pages: false
})

const fetchAssignedWorkPhases = async (fllav = '', dtras = '', dtric = '', dtnum = '', idopr = '', fromDate = '', toDate = '', page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page
    }
    if (fllav) params.fllav = fllav
    if (dtras) params.dtras = dtras
    if (dtric) params.dtric = dtric
    if (dtnum) params.dtnum = dtnum
    if (idopr) params.idopr = idopr
    if (fromDate) params.date_from = fromDate
    if (toDate) params.date_to = toDate
    
    const res = await axios.get('/api/assigned-work-phases', { params })
    assignedWorkPhases.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchAssignedWorkPhases(searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, dateFrom.value, dateTo.value, 1)
}

const clearAllFilters = () => {
  searchFllav.value = ''
  searchDtras.value = ''
  searchDtric.value = ''
  searchDtnum.value = ''
  searchIdopr.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  if (dateFromInstance.value) dateFromInstance.value.clear()
  if (dateToInstance.value) dateToInstance.value.clear()
  applyFilters()
}

// Watchers per i campi di ricerca con debounce
let searchTimeout
watch([searchFllav, searchDtras, searchDtric, searchDtnum, searchIdopr], () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
})

// Watcher per le date con debounce
let dateTimeout
watch([dateFrom, dateTo], () => {
  clearTimeout(dateTimeout)
  dateTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
})

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchAssignedWorkPhases(searchFllav.value, searchDtras.value, searchDtric.value, searchDtnum.value, searchIdopr.value, dateFrom.value, dateTo.value, page)
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

const openModal = (assignment) => {
  selectedAssignment.value = assignment
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// Carica i dati iniziali
onMounted(async () => {
  await fetchAssignedWorkPhases();
  
  // Inizializza flatpickr per dateFrom
  if (dateFromPicker.value) {
    dateFromInstance.value = flatpickr(dateFromPicker.value, {
      dateFormat: 'd/m/Y',
      locale: Italian,
      onChange: (selectedDates, dateStr) => {
        dateFrom.value = dateStr
      }
    })
  }
  
  // Inizializza flatpickr per dateTo
  if (dateToPicker.value) {
    dateToInstance.value = flatpickr(dateToPicker.value, {
      dateFormat: 'd/m/Y',
      locale: Italian,
      onChange: (selectedDates, dateStr) => {
        dateTo.value = dateStr
      }
    })
  }
});

</script>

<template>
  <div class="bg-white shadow rounded-lg p-3">
    <div class="mb-6">
      <!-- Search Bar and Filters -->
      <div class="space-y-4">
        <!-- First row: 3 search fields -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold mb-1">Codice Lav</label>
            <input 
              type="text"
              v-model="searchFllav"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
          <div>
            <label class="block text-xs font-bold mb-1">Data da</label>
            <input
              ref="dateFromPicker"
              type="text"
              v-model="dateFrom"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
          <div>
            <label class="block text-xs font-bold mb-1">Data a</label>
            <input
              ref="dateToPicker"
              type="text"
              v-model="dateTo"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
        </div>

        <!-- Second row: 3 search fields -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold mb-1">Rag. Soc.</label>
            <input 
              type="text"
              v-model="searchDtras"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
          <div>
            <label class="block text-xs font-bold mb-1">N. Ord. Cli.</label>
            <input 
              type="text"
              v-model="searchDtric"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
          <div>
            <label class="block text-xs font-bold mb-1">N. Ns. Ord.</label>
            <input 
              type="text"
              v-model="searchDtnum"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
        </div>

        <!-- Third row: Order production -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold mb-1">Ord. Prod.</label>
            <input 
              type="text"
              v-model="searchIdopr"
              placeholder=""
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-xs"
            />
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex justify-end gap-2">
          <button 
            v-if="searchFllav || searchDtras || searchDtric || searchDtnum || searchIdopr || dateFrom || dateTo"
            @click="clearAllFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 font-semibold text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cancella filtri
          </button>
        </div>
      </div>

      <!-- Results info -->
      <div v-if="searchFllav || searchDtras || searchDtric || searchDtnum || searchIdopr || dateFrom || dateTo" class="mt-4 text-xs text-gray-600">
        <span v-if="pagination.total > 0">
          Trovati {{ pagination.total }} record{{ pagination.total === 1 ? 'o' : 'i' }}
        </span>
        <span v-else>
          Nessun risultato trovato
        </span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr class="border-b border-gray-200">
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">FLASS</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">IDOPR</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">FLSEQ</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">FLLAV</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">FLDES</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">OPART</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Assegnato a</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Data Assegnazione</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Note</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-if="loading">
            <td colspan="9" class="px-3 py-2 text-center text-xs text-gray-500">
              <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-gray-500" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Caricamento...
              </div>
            </td>
          </tr>
          <tr v-else v-for="assignment in assignedWorkPhases" :key="assignment.id" @click="openModal(assignment)" class="hover:bg-gray-100 border-b border-gray-200 cursor-pointer transition-colors">
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.work_phase?.FLASS || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.work_phase?.IDOPR || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.work_phase?.FLSEQ || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.work_phase?.FLLAV || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.work_phase?.FLDES || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.work_phase?.OPART || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ assignment.assigned_user?.name || 'N/D' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ formatDate(assignment.created_at) }}
            </td>
            <td class="px-3 py-2 text-xs text-gray-900">
              {{ assignment.notes || '-' }}
            </td>
          </tr>
          <tr v-if="!loading && !assignedWorkPhases.length">
            <td colspan="9" class="px-3 py-2 text-center">
              <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-xs font-medium text-gray-900">Nessuna fase di lavoro assegnata</h3>
                <p class="mt-1 text-xs text-gray-500">
                  Non hai ancora Fasi di lavoro prese in carico.
                </p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Informazioni paginazione -->
    <div class="mt-4 flex justify-between items-center text-xs text-gray-600">
      <div>
        Mostrando {{ pagination.from }} - {{ pagination.to }} di {{ pagination.total }} record
      </div>
    </div>
    
    <!-- Controlli paginazione -->
    <div class="mt-2 flex justify-center items-center space-x-2">
      <button 
        @click="goToPage(1)" 
        :disabled="currentPage === 1"
        class="hidden md:inline-flex px-3 py-1 text-xs border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Prima
      </button>
      <button 
        @click="goToPage(currentPage - 1)" 
        :disabled="currentPage === 1"
        class="px-3 py-1 text-xs border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Precedente
      </button>
      
      <span class="px-3 py-1 text-xs">
        Pagina {{ currentPage }} di {{ pagination.last_page }}
      </span>
      
      <button 
        @click="goToPage(currentPage + 1)" 
        :disabled="currentPage === pagination.last_page"
        class="px-3 py-1 text-xs border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Successiva
      </button>
      <button 
        @click="goToPage(pagination.last_page)" 
        :disabled="currentPage === pagination.last_page"
        class="hidden md:inline-flex px-3 py-1 text-xs border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Ultima
      </button>
    </div>
  </div>

  <!-- Modal -->
  <AssignedWorkPhaseModal 
    :show="showModal" 
    :assignment="selectedAssignment" 
    @update:show="closeModal"
  />
</template>
