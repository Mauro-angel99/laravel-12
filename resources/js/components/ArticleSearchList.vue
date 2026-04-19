<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const data = ref([])
const searchCdart = ref('')
const searchArdes = ref('')
const searchArmat = ref('')
const searchArdmz = ref('')
const searchArprf = ref('')
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

const fetchData = async (page = 1) => {
  loading.value = true
  try {
    const params = { page }
    if (searchCdart.value) params.cdart = searchCdart.value
    if (searchArdes.value) params.ardes = searchArdes.value
    if (searchArmat.value) params.armat = searchArmat.value
    if (searchArdmz.value) params.ardmz = searchArdmz.value
    if (searchArprf.value) params.arprf = searchArprf.value
    const res = await axios.get('/api/article-search', { params })
    data.value = res.data.data
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
  fetchData(1)
}

const hasFilters = () => {
  return searchCdart.value || searchArdes.value || searchArmat.value || searchArdmz.value || searchArprf.value
}

const clearAllFilters = () => {
  searchCdart.value = ''
  searchArdes.value = ''
  searchArmat.value = ''
  searchArdmz.value = ''
  searchArprf.value = ''
  applyFilters()
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchData(page)
  }
}

let searchTimeout
watch([searchCdart, searchArdes, searchArmat, searchArdmz, searchArprf], () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
})

onMounted(() => {
  fetchData()
})
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

      <div class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 items-end">
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Articolo (CDART)</label>
            <input type="text" v-model="searchCdart"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Descrizione (ARDES)</label>
            <input type="text" v-model="searchArdes"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Materiale (ARMAT)</label>
            <input type="text" v-model="searchArmat"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Spessore (ARDMZ)</label>
            <input type="text" v-model="searchArdmz"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Profilo (ARPRF)</label>
            <input type="text" v-model="searchArprf"
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
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">Articolo</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">Descrizione</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">Materiale</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40">Spessore</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider">Profilo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="5" class="px-3 py-10 text-center text-gray-400">
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
            <tr v-else v-for="(row, index) in data" :key="index"
              :class="[
                'transition-colors',
                index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'
              ]">
              <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">{{ row.CDART }}</td>
              <td class="px-3 py-2 text-gray-700 border-r border-gray-100">{{ row.ARDES }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.ARMAT }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.ARDMZ }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700">{{ row.ARPRF }}</td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!loading && !data.length">
              <td colspan="5" class="px-3 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-xs font-medium text-gray-400">Nessun record trovato</p>
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
</template>
