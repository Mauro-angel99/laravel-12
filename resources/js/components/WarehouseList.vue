<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const positions = ref([])
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

const searchQuery = ref('')
const filterPending = ref(true)
const showCreateModal = ref(false)
const showProductsModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedPosition = ref(null)
const selectedProducts = ref([])
const editingProduct = ref(null)
const editingPositionName = ref('')
const isEditingPosition = ref(false)

const formData = ref({
  warehouse_position: '',
  product_code: '',
  production_order: '',
  product_description: '',
  notes: '',
  pending: true,
  pending_code: ''
})

// Stato della modal di messaggio
const messageModal = ref({
  show: false,
  type: 'success',
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

const fetchPositions = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page: page,
      search: searchQuery.value,
      pending: filterPending.value ? 1 : 0
    }
    
    const res = await axios.get('/api/warehouse', { params })
    positions.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
    showMessageModal('error', 'Errore', 'Errore nel caricamento delle posizioni')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  fetchPositions(1)
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchPositions(page)
  }
}

const openCreateModal = () => {
  formData.value = {
    warehouse_position: '',
    product_code: '',
    production_order: '',
    product_description: '',
    notes: '',
    pending: true,
    pending_code: ''
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
}

const openProductsModal = async (position) => {
  selectedPosition.value = position
  editingPositionName.value = position.warehouse_position
  isEditingPosition.value = false
  try {
    const res = await axios.get(`/api/warehouse/positions/${position.id}/products`)
    selectedProducts.value = res.data.products
    showProductsModal.value = true
  } catch (error) {
    console.error(error)
    showMessageModal('error', 'Errore', 'Errore nel caricamento dei prodotti')
  }
}

const closeProductsModal = () => {
  showProductsModal.value = false
  selectedPosition.value = null
  selectedProducts.value = []
  editingPositionName.value = ''
  isEditingPosition.value = false
}

const openEditModal = (product) => {
  editingProduct.value = product
  formData.value = {
    warehouse_position: product.position?.warehouse_position || '',
    product_code: product.product_code || '',
    production_order: product.production_order || '',
    product_description: product.product_description || '',
    notes: product.notes || ''
  }
  showProductsModal.value = false
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingProduct.value = null
}

const saveWarehouse = async () => {
  try {
    const res = await axios.post('/api/warehouse', formData.value)
    closeCreateModal()
    showMessageModal('success', 'Successo', res.data.message || 'Elemento aggiunto con successo')
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante il salvataggio'
    showMessageModal('error', 'Errore', errorMessage)
  }
}

const updateWarehouse = async () => {
  try {
    const res = await axios.put(`/api/warehouse/${editingProduct.value.id}`, formData.value)
    closeEditModal()
    showMessageModal('success', 'Successo', res.data.message || 'Elemento aggiornato con successo')
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante l\'aggiornamento'
    showMessageModal('error', 'Errore', errorMessage)
  }
}

const deleteWarehouse = async () => {
  showDeleteModal.value = false

  try {
    const res = await axios.delete(`/api/warehouse/${editingProduct.value.id}`)
    closeEditModal()
    showMessageModal('success', 'Successo', res.data.message || 'Elemento eliminato con successo')
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante l\'eliminazione'
    showMessageModal('error', 'Errore', errorMessage)
  }
}

const openDeleteModal = () => {
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
}

const updatePositionName = async () => {
  if (!editingPositionName.value || editingPositionName.value === selectedPosition.value.warehouse_position) {
    isEditingPosition.value = false
    return
  }

  try {
    const res = await axios.put(`/api/warehouse/positions/${selectedPosition.value.id}`, {
      warehouse_position: editingPositionName.value
    })
    isEditingPosition.value = false
    selectedPosition.value.warehouse_position = editingPositionName.value
    showMessageModal('success', 'Successo', res.data.message || 'Posizione aggiornata con successo')
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante l\'aggiornamento della posizione'
    showMessageModal('error', 'Errore', errorMessage)
    editingPositionName.value = selectedPosition.value.warehouse_position
    isEditingPosition.value = false
  }
}

onMounted(async () => {
  await fetchPositions()
})
</script>

<template>
  <div class="bg-white shadow rounded-lg p-3">
    <!-- Header con bottone Aggiungi e barra di ricerca -->
    <div class="mb-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        <button 
          @click="openCreateModal"
          class="px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue w-full sm:w-auto"
        >
          Aggiungi Merce
        </button>
        
        <label class="flex items-center cursor-pointer">
          <input
            v-model="filterPending"
            @change="fetchPositions(1)"
            type="checkbox"
            class="sr-only peer"
          />
          <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-copam-blue"></div>
          <span class="ms-3 text-sm font-medium text-gray-700">In Attesa</span>
        </label>
      </div>
      
      <div class="flex-1 w-full sm:max-w-md">
        <input 
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Cerca per posizione, per Codice merce, per Ord. Prod..."
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
        >
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr class="border-b border-gray-200">
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">
              Posizione
            </th>
            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">N° Merci</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr v-if="loading">
            <td colspan="2" class="px-3 py-2 text-center text-xs text-gray-500">
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
            v-for="position in positions" 
            :key="position.id" 
            @click="openProductsModal(position)"
            class="hover:bg-gray-100 border-b border-gray-200 cursor-pointer transition-colors"
          >
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
              {{ position.warehouse_position }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
              {{ position.warehouses_count }}
            </td>
          </tr>
          <tr v-if="!loading && !positions.length">
            <td colspan="2" class="px-3 py-2 text-center">
              <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-xs font-medium text-gray-900">
                  {{ filterPending ? 'Nessuna posizione in attesa' : 'Nessuna posizione in magazzino' }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">
                  {{ filterPending ? 'Non ci sono posizioni registrate in attesa.' : 'Non ci sono posizioni registrate nel magazzino.' }}
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

    <!-- Modal Creazione Merce -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeCreateModal"
        ></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 max-w-md w-full">
          <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Aggiungi Nuova Merce</h3>
            
            <form @submit.prevent="saveWarehouse" class="space-y-4">
              <div class="flex items-center gap-3 pt-2">
                <label class="flex items-center cursor-pointer">
                  <input
                    v-model="formData.pending"
                    type="checkbox"
                    class="sr-only peer"
                  />
                  <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-copam-blue"></div>
                  <span class="ms-3 text-sm font-medium text-gray-700">In Attesa</span>
                </label>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codice Attesa</label>
                <input 
                  v-model="formData.pending_code"
                  type="text"
                  :disabled="!formData.pending"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Posizione {{ !formData.pending ? '*' : '' }}
                </label>
                <input 
                  v-model="formData.warehouse_position"
                  type="text"
                  :required="!formData.pending"
                  :disabled="formData.pending"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codice Merce</label>
                <input 
                  v-model="formData.product_code"
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ord. Prod.</label>
                <input 
                  v-model="formData.production_order"
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                >
              </div>

              <div class="flex justify-end space-x-3 pt-4">
                <button 
                  type="button"
                  @click="closeCreateModal"
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

    <!-- Modal Lista Prodotti in Posizione -->
    <div v-if="showProductsModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeProductsModal"
        ></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
          <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
              Merci in Posizione: {{ selectedPosition?.warehouse_position }}
            </h3>
            
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr class="border-b border-gray-200">
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">Codice</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Ord. Prod.</th>
                  </tr>
                </thead>
                <tbody class="bg-white">
                  <tr v-if="selectedProducts.length === 0">
                    <td colspan="2" class="px-3 py-2 text-center text-xs text-gray-500">
                      Nessuna merce in questa posizione
                    </td>
                  </tr>
                  <tr
                    v-for="product in selectedProducts"
                    :key="product.id"
                    class="hover:bg-gray-100 border-b border-gray-200 cursor-pointer transition-colors"
                    @click="openEditModal(product)"
                  >
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">
                      {{ product.product_code || '-' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                      {{ product.production_order || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-6 space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modifica Posizione</label>
                <div class="flex gap-2">
                  <input
                    v-model="editingPositionName"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue text-sm"
                    placeholder="Inserisci nuovo nome posizione"
                  />
                  <button
                    @click="updatePositionName"
                    :disabled="!editingPositionName || editingPositionName === selectedPosition?.warehouse_position"
                    class="px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Aggiorna
                  </button>
                </div>
              </div>
              
              <div class="flex justify-end">
                <button 
                  @click="closeProductsModal"
                  class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 focus:outline-none"
                >
                  Chiudi
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Modifica Merce -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeEditModal"
        ></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 max-w-md w-full">
          <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Modifica Merce</h3>
            
            <form @submit.prevent="updateWarehouse" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Posizione *</label>
                <input 
                  v-model="formData.warehouse_position"
                  type="text" 
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codice Merce</label>
                <input 
                  v-model="formData.product_code"
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ord. Prod.</label>
                <input 
                  v-model="formData.production_order"
                  type="text" 
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
                  @click="openDeleteModal"
                  class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                >
                  Elimina
                </button>
                <button 
                  type="button"
                  @click="closeEditModal"
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

  <!-- Modal Conferma Eliminazione -->
  <Teleport to="body">
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeDeleteModal"
        ></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Conferma Eliminazione
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Sei sicuro di voler eliminare questo elemento? Questa azione non può essere annullata.
                </p>
              </div>
            </div>
          </div>
          
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
            <button 
              type="button"
              @click="deleteWarehouse"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Elimina
            </button>
            <button 
              type="button"
              @click="closeDeleteModal"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue sm:mt-0 sm:w-auto sm:text-sm"
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
        <div 
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          @click="closeMessageModal"
        ></div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
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
