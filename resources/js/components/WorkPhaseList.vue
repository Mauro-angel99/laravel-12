<template>
    <div>
      <div class="mb-4 flex justify-between items-center">
        <input
          type="text"
          v-model="search"
          placeholder="Cerca Fasi di Lavoro..."
          class="border px-3 py-2 rounded w-1/3"
        />
        <button class="bg-blue-500 text-white px-4 py-2 rounded" @click="fetchWorkPhases(search)" :disabled="loading">
          {{ loading ? 'Caricamento...' : 'Aggiorna' }}
        </button>
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
  const selected = ref([])
  const loading = ref(false)
  
  const fetchWorkPhases = async (searchTerm = '') => {
    loading.value = true
    try {
      const params = searchTerm ? { search: searchTerm } : {}
      const res = await axios.get('/api/work-phases', { params })
      workPhases.value = res.data
    } catch (error) {
      console.error(error)
    } finally {
      loading.value = false
    }
  }
  
  // Watcher per la ricerca con debounce
  let searchTimeout
  watch(search, (newSearch) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      fetchWorkPhases(newSearch)
    }, 500) // Debounce di 500ms
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
  