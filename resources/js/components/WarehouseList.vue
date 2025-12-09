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
const showDetailModal = ref(false)
const showConfirmModal = ref(false)
const confirmModalData = ref({
  message: '',
  onConfirm: null
})
const selectedWarehouse = ref(null)
const formData = ref({
  product_code: '',
  production_order: '',
  warehouse_area: '',
  warehouse_position: ''
})

// Stato della modal di messaggio
const messageModal = ref({
  show: false,
  type: 'success', // 'success' o 'error'
  title: '',
  message: ''
})

const showMessageModal = (type, title, message) => {
  messageModal.value = {
    show: true,
    type,
    title,
    message
  }
}

const closeMessageModal = () => {
  messageModal.value.show = false
}

const showConfirmation = (message, onConfirm) => {
  confirmModalData.value = {
    message,
    onConfirm
  }
  showConfirmModal.value = true
}

const closeConfirmModal = () => {
  showConfirmModal.value = false
  confirmModalData.value = {
    message: '',
    onConfirm: null
  }
}

const handleConfirm = () => {
  if (confirmModalData.value.onConfirm) {
    confirmModalData.value.onConfirm()
  }
  closeConfirmModal()
}

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

const openDetailModal = (warehouse) => {
  selectedWarehouse.value = { ...warehouse }
  showDetailModal.value = true
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedWarehouse.value = null
}

const saveWarehouse = async (forceSave = false) => {
  try {
    const data = { ...formData.value }
    if (forceSave) {
      data.force_save = true
    }
    
    const res = await axios.post('/api/warehouse', data)
    closeModal()
    showMessageModal('success', 'Successo', res.data.message || 'Elemento aggiunto con successo')
    await fetchWarehouses(currentPage.value)
  } catch (error) {
    // Controlla se la posizione è occupata
    if (error.response?.status === 409 && error.response?.data?.position_occupied) {
      const message = error.response.data.message + '\n\nVuoi comunque aggiungere l\'elemento in questa posizione?'
      showConfirmation(message, () => {
        saveWarehouse(true)
      })
      return
    }
    
    const errorMessage = error.response?.data?.message || 'Errore durante il salvataggio'
    showMessageModal('error', 'Errore', errorMessage)
  }
}

const updateWarehouse = async (forceSave = false) => {
  try {
    const data = { ...selectedWarehouse.value }
    if (forceSave) {
      data.force_save = true
    }
    
    const res = await axios.put(`/api/warehouse/${selectedWarehouse.value.id}`, data)
    closeDetailModal()
    showMessageModal('success', 'Successo', res.data.message || 'Elemento aggiornato con successo')
    await fetchWarehouses(currentPage.value)
  } catch (error) {
    // Controlla se la posizione è occupata
    if (error.response?.status === 409 && error.response?.data?.position_occupied) {
      const message = error.response.data.message + '\n\nVuoi comunque modificare l\'elemento con questa posizione?'
      showConfirmation(message, () => {
        updateWarehouse(true)
      })
      return
    }
    
    const errorMessage = error.response?.data?.message || 'Errore durante l\'aggiornamento'
    showMessageModal('error', 'Errore', errorMessage)
  }
}

const deleteWarehouse = async () => {
  showConfirmation('Sei sicuro di voler eliminare questo elemento?', async () => {
    try {
      const res = await axios.delete(`/api/warehouse/${selectedWarehouse.value.id}`)
      closeDetailModal()
      showMessageModal('success', 'Successo', res.data.message || 'Elemento eliminato con successo')
      await fetchWarehouses(currentPage.value)
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Errore durante l\'eliminazione'
      showMessageModal('error', 'Errore', errorMessage)
    }
  })
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
          <tr 
            v-else 
            v-for="warehouse in warehouses" 
            :key="warehouse.id" 
            @click="openDetailModal(warehouse)"
            class="hover:bg-gray-100 border-b border-gray-200 cursor-pointer transition-colors"
          >
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ warehouse.product_code }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ warehouse.production_order || '-' }}
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
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 max-w-md w-full">
        <div class="mt-3">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Aggiungi elemento magazzino</h3>
          
          <form @submit.prevent="saveWarehouse(false)" class="space-y-4">
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

    <!-- Modal Dettaglio/Modifica/Elimina -->
    <div v-if="showDetailModal && selectedWarehouse" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeDetailModal"
        ></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 max-w-md w-full">
        <div class="mt-3">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Dettaglio elemento</h3>
          
          <form @submit.prevent="updateWarehouse(false)" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Merce</label>
              <input 
                v-model="selectedWarehouse.product_code"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ordine di produzione</label>
              <input 
                v-model="selectedWarehouse.production_order"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Area</label>
              <input 
                v-model="selectedWarehouse.warehouse_area"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Posizione</label>
              <input 
                v-model="selectedWarehouse.warehouse_position"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
              >
            </div>

            <div class="flex flex-col space-y-2 pt-4">
              <button 
                type="submit"
                class="w-full px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue"
              >
                Salva modifiche
              </button>
              <button 
                type="button"
                @click="deleteWarehouse"
                class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
              >
                Elimina
              </button>
              <button 
                type="button"
                @click="closeDetailModal"
                class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 focus:outline-none"
              >
                Chiudi
              </button>
            </div>
          </form>
        </div>
      </div>
      </div>
    </div>
  </div>

  <!-- Modal Conferma -->
  <Teleport to="body">
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeConfirmModal"
        ></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <!-- Icona Warning -->
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            
            <!-- Contenuto -->
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Conferma operazione
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500 whitespace-pre-line">
                  {{ confirmModalData.message }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Pulsanti -->
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
            <button 
              type="button"
              @click="handleConfirm"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-copam-blue text-white text-sm font-medium hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue sm:w-auto"
            >
              Conferma
            </button>
            <button 
              type="button"
              @click="closeConfirmModal"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue sm:mt-0 sm:w-auto"
            >
              Annulla
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Modal Messaggio di Conferma/Errore -->
  <Teleport to="body">
    <div v-if="messageModal.show" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeMessageModal"
        ></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <!-- Icona -->
            <div :class="[
              'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10',
              messageModal.type === 'success' ? 'bg-green-100' : 'bg-red-100'
            ]">
              <svg 
                v-if="messageModal.type === 'success'"
                class="h-6 w-6 text-green-600" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg 
                v-else
                class="h-6 w-6 text-red-600" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            
            <!-- Contenuto -->
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ messageModal.title }}
              </h3>
              <div class="mt-2">
                <p class="text-xs text-gray-500">
                  {{ messageModal.message }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Pulsante -->
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button 
              type="button"
              @click="closeMessageModal"
              :class="[
                'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-xs',
                messageModal.type === 'success' 
                  ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                  : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
              ]"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
