<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const assignedWorkPhases = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0,
  has_more_pages: false
})

const fetchAssignedWorkPhases = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page
    }
    
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

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchAssignedWorkPhases(page)
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

// Carica i dati iniziali
onMounted(async () => {
  await fetchAssignedWorkPhases();
});

</script>

<template>
  <div class="bg-white shadow rounded-lg p-3">
    <div class="mb-6">
      <h3 class="text-lg font-medium text-gray-900">Le tue Fasi di Lavoro Assegnate</h3>
      <p class="mt-1 text-sm text-gray-500">Visualizza tutte le fasi di lavoro che ti sono state assegnate.</p>
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
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Assegnato a</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Data Assegnazione</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Note</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-if="loading">
            <td colspan="8" class="px-3 py-2 text-center text-xs text-gray-500">
              <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-gray-500" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Caricamento...
              </div>
            </td>
          </tr>
          <tr v-else v-for="assignment in assignedWorkPhases" :key="assignment.id" class="hover:bg-gray-50 border-b border-gray-200">
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
            <td colspan="8" class="px-3 py-2 text-center">
              <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-xs font-medium text-gray-900">Nessuna fase di lavoro assegnata</h3>
                <p class="mt-1 text-xs text-gray-500">
                  Non hai ancora fasi di lavoro assegnate.
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
        class="px-3 py-1 text-xs border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
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
        class="px-3 py-1 text-xs border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Ultima
      </button>
    </div>
  </div>
</template>
