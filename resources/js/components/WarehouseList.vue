<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const warehouses = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0
})

const showModal = ref(false)
const formData = ref({
  product_code: '',
  production_order: '',
  warehouse_area: '',
  warehouse_position: ''
})

const searchQuery = ref('')

const fetchWarehouses = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page,
      search: searchQuery.value
    }
    
    const res = await axios.get('/api/warehouse', { params })
    warehouses.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  fetchWarehouses(1)
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchWarehouses(page)
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

const openModal = () => {
  showModal.value = true
  formData.value = {
    product_code: '',
    production_order: '',
    warehouse_area: '',
    warehouse_position: ''
  }
}

const closeModal = () => {
  showModal.value = false
}

const saveWarehouse = async () => {
  try {
    await axios.post('/api/warehouse', formData.value)
    closeModal()
    await fetchWarehouses(currentPage.value)
  } catch (error) {
    console.error(error)
    alert('Errore durante il salvataggio')
  }
}

// Carica i dati iniziali
onMounted(async () => {
  await fetchWarehouses();
});

</script>

<template>
  <div class="bg-white shadow rounded-lg p-3">
    <!-- Header con bottone Aggiungi e barra di ricerca -->
    <div class="mb-3 flex justify-between items-center gap-4">
      <button 
        @click="openModal"
        class="px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue"
      >
        Aggiungi
      </button>
      
      <div class="flex-1 max-w-md">
        <input 
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Cerca per merce, ordine, area o posizione..."
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
        >
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr class="border-b border-gray-200">
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Merce</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Ord. Prod.</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Area</th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Posizione</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-if="loading">
            <td colspan="4" class="px-3 py-2 text-center text-xs text-gray-500">
              <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-gray-500" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Caricamento...
              </div>
            </td>
          </tr>
          <tr v-else v-for="warehouse in warehouses" :key="warehouse.id" class="hover:bg-gray-50 border-b border-gray-200">
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ warehouse.product_code }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ warehouse.production_order }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ warehouse.warehouse_area }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
              {{ warehouse.warehouse_position }}
            </td>
          </tr>
          <tr v-if="!loading && !warehouses.length">
            <td colspan="4" class="px-3 py-2 text-center">
              <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-xs font-medium text-gray-900">Nessun elemento in magazzino</h3>
                <p class="mt-1 text-xs text-gray-500">
                  Non ci sono elementi registrati nel magazzino.
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
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Aggiungi elemento magazzino</h3>
          
          <form @submit.prevent="saveWarehouse" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Merce</label>
              <input 
                v-model="formData.product_code"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ordine di produzione</label>
              <input 
                v-model="formData.production_order"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Area</label>
              <input 
                v-model="formData.warehouse_area"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Posizione</label>
              <input 
                v-model="formData.warehouse_position"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button 
                type="button"
                @click="closeModal"
                class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 focus:outline-none"
              >
                Annulla
              </button>
              <button 
                type="submit"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                Salva
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
