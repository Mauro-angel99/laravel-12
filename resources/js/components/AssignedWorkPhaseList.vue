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
          v-if="searchFllav || searchDtras || searchDtric || searchDtnum || searchIdopr || dateFrom || dateTo"
          @click="clearAllFilters"
          class="inline-flex items-center gap-1 text-xs text-blue-100 hover:text-white transition-colors"
        >
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Cancella filtri
        </button>
      </div>

      <div class="p-4 space-y-3">
        <!-- Riga 1 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Codice Lav.</label>
            <input type="text" v-model="searchFllav"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Data da</label>
            <input ref="dateFromPicker" type="text" v-model="dateFrom"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Data a</label>
            <input ref="dateToPicker" type="text" v-model="dateTo"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
        </div>

        <!-- Riga 2 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Rag. Soc.</label>
            <input type="text" v-model="searchDtras"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">N. Ord. Cli.</label>
            <input type="text" v-model="searchDtric"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">N. Ns. Ord.</label>
            <input type="text" v-model="searchDtnum"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
        </div>

        <!-- Riga 3 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Ord. Prod.</label>
            <input type="text" v-model="searchIdopr"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
        </div>
      </div>
    </div>

    <!-- TABELLA -->
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
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLASS</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">IDOPR</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLSEQ</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLLAV</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">FLDES</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">OPART</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">Assegnato a</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider">Data Assegnazione</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="8" class="px-3 py-10 text-center text-gray-400">
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
            <tr v-else v-for="(assignment, index) in assignedWorkPhases" :key="assignment.id"
              @click="openModal(assignment)"
              :class="[
                'cursor-pointer transition-colors',
                index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'
              ]">
              <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">
                {{ assignment.work_phase?.FLASS || 'N/D' }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">
                {{ assignment.work_phase?.IDOPR || 'N/D' }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">
                {{ assignment.work_phase?.FLSEQ || 'N/D' }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap border-r border-gray-100">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-copam-blue">
                  {{ assignment.work_phase?.FLLAV || 'N/D' }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">
                {{ assignment.work_phase?.FLDES || 'N/D' }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">
                {{ assignment.work_phase?.OPART || 'N/D' }}
              </td>
              <td class="px-3 py-2 whitespace-nowrap border-r border-gray-100">
                <span class="inline-flex items-center gap-1 text-gray-700">
                  <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ assignment.assigned_user?.name || 'N/D' }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                {{ formatDate(assignment.created_at) }}
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!loading && !assignedWorkPhases.length">
              <td colspan="8" class="px-3 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-xs font-medium text-gray-400">Nessuna fase di lavoro assegnata</p>
                <p class="mt-1 text-xs text-gray-300">Non hai ancora fasi di lavoro prese in carico.</p>
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

  </div>

  <!-- Modal -->
  <AssignedWorkPhaseModal
    :show="showModal"
    :assignment="selectedAssignment"
    @update:show="closeModal"
  />
</template>
