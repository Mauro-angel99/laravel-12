<template>
    <div>
      <div class="mb-4 space-y-4">
        <!-- Filtri di ricerca -->
        <div class="flex justify-between items-center">
          <input
            type="text"
            v-model="search"
            placeholder="Cerca Fasi di Lavoro..."
            class="border px-3 py-2 rounded w-1/3"
          />
          <button class="bg-blue-500 text-white px-4 py-2 rounded" @click="applyFilters" :disabled="loading">
            {{ loading ? 'Caricamento...' : 'Aggiorna' }}
          </button>
        </div>
        
        <!-- Filtri per data FLCON -->
        <div class="flex items-center space-x-4 bg-gray-50 p-4 rounded">
          <label class="text-sm font-medium text-gray-700">Filtro per Data FLCON:</label>
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-600">Da:</label>
            <input
              type="date"
              v-model="dateFrom"
              class="border px-3 py-1 rounded text-sm"
            />
          </div>
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-600">A:</label>
            <input
              type="date"
              v-model="dateTo"
              class="border px-3 py-1 rounded text-sm"
            />
          </div>
          <button 
            @click="clearDateFilters" 
            class="text-sm text-gray-500 hover:text-gray-700 underline"
          >
            Cancella filtri data
          </button>
        </div>
      </div>
  
      <table class="w-full border-collapse bg-white shadow-sm rounded">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-3 py-2">#</th>
            <th class="border px-3 py-2">FLASS</th>
            <th class="border px-3 py-2">IDOPR</th>
            <th class="border px-3 py-2">FLSEQ</th>
            <th class="border px-3 py-2">FLLAV</th>
            <th class="border px-3 py-2">FLDES</th>
            <th class="border px-3 py-2">FLQTA</th>
            <th class="border px-3 py-2">FLQTB</th>
            <th class="border px-3 py-2">FLQTD</th>
            <th class="border px-3 py-2">FLCON</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="text-center py-4">Caricamento...</td>
          </tr>
          <tr v-else v-for="phase in workPhases" :key="phase.RECORD_ID" class="hover:bg-gray-50">
            <td class="border px-3 py-2 text-center">
              <input type="checkbox" v-model="selected" :value="phase.RECORD_ID" />
            </td>
            <td class="border px-3 py-2">{{ phase.FLASS }}</td>
            <td class="border px-3 py-2">{{ phase.IDOPR }}</td>
            <td class="border px-3 py-2">{{ phase.FLSEQ }}</td>
            <td class="border px-3 py-2">{{ phase.FLLAV }}</td>
            <td class="border px-3 py-2">{{ phase.FLDES }}</td>
            <td class="border px-3 py-2">{{ phase.FLQTA }}</td>
            <td class="border px-3 py-2">{{ phase.FLQTB }}</td>
            <td class="border px-3 py-2">{{ phase.FLQTD }}</td>
            <td class="border px-3 py-2">{{ phase.FLCON }}</td>
          </tr>
          <tr v-if="!loading && !workPhases.length">
            <td colspan="10" class="text-center py-4">Nessuna Work Phase trovata</td>
          </tr>
        </tbody>
      </table>
  
      <div class="mt-4 flex justify-between items-center">
        <div>Selezionati: {{ selected.length }}</div>
        <button class="bg-green-500 text-white px-4 py-2 rounded" @click="confirmSelected">
          Conferma Selezionati
        </button>
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
  
  const fetchWorkPhases = async (searchTerm = '', fromDate = '', toDate = '') => {
    loading.value = true
    try {
      const params = {}
      if (searchTerm) params.search = searchTerm
      if (fromDate) params.date_from = fromDate
      if (toDate) params.date_to = toDate
      
      const res = await axios.get('/api/work-phases', { params })
      workPhases.value = res.data
    } catch (error) {
      console.error(error)
    } finally {
      loading.value = false
    }
  }
  
  const applyFilters = () => {
    fetchWorkPhases(search.value, dateFrom.value, dateTo.value)
  }
  
  const clearDateFilters = () => {
    dateFrom.value = ''
    dateTo.value = ''
    applyFilters()
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
  