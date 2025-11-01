<template>
    <div class="bg-white shadow rounded-lg p-3">
      <div class="mb-6">
        <!-- Search Bar -->
        <div class="flex gap-4 items-end">
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
          
          <div class="flex gap-2">
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
          </div>
        </div>

        <!-- Date Filters -->
        <div class="mt-4 flex gap-4 items-end">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Data di consegna</label>
            <div class="flex gap-4">
              <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Da</label>
                <input
                  type="date"
                  v-model="dateFrom"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">A</label>
                <input
                  type="date"
                  v-model="dateTo"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
            </div>
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
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">#</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLASS</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">IDOPR</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLSEQ</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLLAV</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLDES</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLQTA</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLQTB</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLQTD</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">FLCON</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">MATERIALE</th>
              <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">SPESSORE</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">LAV_SUCC</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">LAV_SUCC_ASS</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
            </tr>
          </thead>
          <tbody class="bg-white">
            <tr v-if="loading">
              <td colspan="14" class="px-3 py-2 text-center text-sm text-gray-500">
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
              <td class="px-3 py-2 whitespace-nowrap text-sm text-center border-r border-gray-200">
                <input type="checkbox" v-model="selected" :value="phase.RECORD_ID" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLASS }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.IDOPR }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLSEQ }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLLAV }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLDES }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLQTA }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLQTB }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.FLQTD }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ formatDate(phase.FLCON) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.MATERIALE }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.SPESSORE }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ phase.LAV_SUCC }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ phase.LAV_SUCC_ASS }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                  <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna Work Phase trovata</h3>
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
      
      <div class="mt-4 flex justify-end">
        <button class="bg-green-500 text-white px-4 py-2 rounded" @click="confirmSelected">
          Conferma Selezionati
        </button>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Dettagli Work Phase
                </h3>
                <div class="mt-4 space-y-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLASS</p>
                      <p class="mt-1">{{ selectedPhase?.FLASS }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">IDOPR</p>
                      <p class="mt-1">{{ selectedPhase?.IDOPR }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLSEQ</p>
                      <p class="mt-1">{{ selectedPhase?.FLSEQ }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLLAV</p>
                      <p class="mt-1">{{ selectedPhase?.FLLAV }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLDES</p>
                      <p class="mt-1">{{ selectedPhase?.FLDES }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLQTA</p>
                      <p class="mt-1">{{ selectedPhase?.FLQTA }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLQTB</p>
                      <p class="mt-1">{{ selectedPhase?.FLQTB }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">FLQTD</p>
                      <p class="mt-1">{{ selectedPhase?.FLQTD }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Data Consegna</p>
                      <p class="mt-1">{{ formatDate(selectedPhase?.FLCON) }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">MATERIALE</p>
                      <p class="mt-1">{{ selectedPhase?.MATERIALE }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">SPESSORE</p>
                      <p class="mt-1">{{ selectedPhase?.SPESSORE }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">LAV_SUCC</p>
                      <p class="mt-1">{{ selectedPhase?.LAV_SUCC }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">LAV_SUCC_ASS</p>
                      <p class="mt-1">{{ selectedPhase?.LAV_SUCC_ASS }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
              @click="closeModal"
            >
              Chiudi
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue'
  import axios from 'axios'
  
  const workPhases = ref([])
  const search = ref('')
  const dateFrom = ref('')
  const dateTo = ref('')
  const selected = ref([])
  const loading = ref(false)
  const currentPage = ref(1)
  const showModal = ref(false)
  const selectedPhase = ref(null)
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
  
  // Watcher per la ricerca con debounce
  let searchTimeout
  watch(search, (newSearch) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      applyFilters()
    }, 500) // Debounce di 500ms
  })
  
  // Watcher per le date con debounce
  let dateTimeout
  watch([dateFrom, dateTo], () => {
    clearTimeout(dateTimeout)
    dateTimeout = setTimeout(() => {
      applyFilters()
    }, 300) // Debounce di 300ms per le date
  })
  
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
    selectedPhase.value = null
  }

  const confirmSelected = async () => {
    try {
      const res = await axios.post('/api/work-phases/confirm', { selected: selected.value }, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      })
      alert(res.data.message)
    } catch (error) {
      console.error(error)
    }
  }
  </script>
  