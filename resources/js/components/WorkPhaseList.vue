<template>
    <div>
      <div class="mb-4 flex justify-between items-center">
        <input
          type="text"
          v-model="search"
          placeholder="Cerca Work Phase..."
          class="border px-3 py-2 rounded w-1/3"
        />
        <button class="bg-blue-500 text-white px-4 py-2 rounded" @click="fetchWorkPhases">
          Aggiorna
        </button>
      </div>
  
      <table class="w-full border-collapse bg-white shadow-sm rounded">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-3 py-2">#</th>
            <th class="border px-3 py-2">FLASS</th>
            <th class="border px-3 py-2">FLDES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="phase in filteredPhases" :key="phase.RECORD_ID" class="hover:bg-gray-50">
            <td class="border px-3 py-2 text-center">
              <input type="checkbox" v-model="selected" :value="phase.RECORD_ID" />
            </td>
            <td class="border px-3 py-2">{{ phase.FLASS }}</td>
            <td class="border px-3 py-2">{{ phase.FLDES }}</td>
          </tr>
          <tr v-if="!filteredPhases.length">
            <td colspan="3" class="text-center">Nessuna Work Phase trovata</td>
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
  import { ref, computed, onMounted } from 'vue'
  import axios from 'axios'
  
  const workPhases = ref([])
  const search = ref('')
  const selected = ref([])
  
  const fetchWorkPhases = async () => {
    try {
      const res = await axios.get('/api/work-phases')
      workPhases.value = res.data
    } catch (error) {
      console.error(error)
    }
  }
  
  const filteredPhases = computed(() =>
    workPhases.value.filter(phase =>
      phase.FLDES.toLowerCase().includes(search.value.toLowerCase()) ||
      phase.FLASS.toLowerCase().includes(search.value.toLowerCase())
    )
  )
  
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
  
  onMounted(fetchWorkPhases)
  </script>
  