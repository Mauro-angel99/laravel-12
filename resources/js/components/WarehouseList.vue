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

const opartFormatSettings = ref({
  opart_total_chars: null,
  opart_remove_before: null,
  opart_remove_after: null,
})

const formatOpart = (value) => {
  if (!value) return value
  const { opart_total_chars, opart_remove_before, opart_remove_after } = opartFormatSettings.value
  if (!opart_total_chars) return value
  const str = value.toString()
  if (str.length <= opart_total_chars) return str
  const removeBefore = opart_remove_before || 0
  const removeAfter = opart_remove_after || 0
  return str.slice(removeBefore, str.length - removeAfter)
}

const searchQuery = ref('')
const filterPending = ref(false)
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
  dimension_x: '',
  dimension_y: '',
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
    dimension_x: '',
    dimension_y: '',
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
  editingStarted.value = position.started
  editingPositionQuantity.value = position.quantity ?? ''
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
    dimension_x: product.dimension_x ?? '',
    dimension_y: product.dimension_y ?? '',
    production_order: product.production_order || '',
    product_description: product.product_description || '',
    notes: product.notes || ''
  }
  // Non chiudere showProductsModal per permettere modali annidate
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingProduct.value = null
}

const saveWarehouse = async () => {
  try {
    const payload = {
      ...formData.value,
      production_order: formatOpart(formData.value.production_order)
    }
    const res = await axios.post('/api/warehouse', payload)
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
    
    // Se la modale prodotti è aperta, ricarica i prodotti della NUOVA posizione
    // (la vecchia potrebbe essere stata eliminata se rimasta vuota)
    if (showProductsModal.value && selectedPosition.value) {
      const newPosition = res.data.data?.position
      const oldPositionId = selectedPosition.value.id
      const newPositionId = newPosition?.id ?? oldPositionId
      const positionChanged = newPositionId !== oldPositionId

      if (positionChanged) {
        // La merce è stata spostata in un'altra posizione:
        // ricarica i prodotti rimasti nella posizione originale
        try {
          const posRes = await axios.get(`/api/warehouse/positions/${oldPositionId}/products`)
          selectedProducts.value = posRes.data.products
          // Se la posizione originale è vuota, chiudi la modal
          if (selectedProducts.value.length === 0) {
            closeProductsModal()
          }
        } catch {
          // Posizione originale eliminata (era l'ultima merce): chiudi la modal
          closeProductsModal()
        }
      } else {
        // La posizione non è cambiata, ricarica normalmente
        try {
          const posRes = await axios.get(`/api/warehouse/positions/${oldPositionId}/products`)
          selectedProducts.value = posRes.data.products
          if (selectedProducts.value.length === 0) {
            closeProductsModal()
          }
        } catch {
          closeProductsModal()
        }
      }
    }
    
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
    
    // Se la modale prodotti è aperta, ricarica solo i prodotti della posizione
    if (showProductsModal.value && selectedPosition.value) {
      try {
        const posRes = await axios.get(`/api/warehouse/positions/${selectedPosition.value.id}/products`)
        selectedProducts.value = posRes.data.products
        // Se la posizione è rimasta vuota, chiudi la modale
        if (selectedProducts.value.length === 0) {
          closeProductsModal()
        }
      } catch {
        // La posizione è stata eliminata (era l'ultimo prodotto): chiudi la modale
        closeProductsModal()
      }
    }
    
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

const editingStarted = ref(false)
const editingPositionQuantity = ref('')

const toggleStarted = async () => {
  try {
    await axios.put(`/api/warehouse/positions/${selectedPosition.value.id}`, {
      warehouse_position: selectedPosition.value.warehouse_position,
      started: !selectedPosition.value.started
    })
    selectedPosition.value.started = !selectedPosition.value.started
    editingStarted.value = selectedPosition.value.started
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante l\'aggiornamento'
    showMessageModal('error', 'Errore', errorMessage)
  }
}

const updatePositionName = async () => {
  if (!editingPositionName.value) {
    isEditingPosition.value = false
    return
  }

  const nameUnchanged = editingPositionName.value === selectedPosition.value.warehouse_position
  const quantityUnchanged = (editingPositionQuantity.value === '' ? null : parseFloat(editingPositionQuantity.value)) === (selectedPosition.value.quantity ?? null)

  if (nameUnchanged && quantityUnchanged) {
    isEditingPosition.value = false
    return
  }

  try {
    const payload = {
      warehouse_position: editingPositionName.value,
      quantity: editingPositionQuantity.value !== '' ? editingPositionQuantity.value : null,
    }
    const res = await axios.put(`/api/warehouse/positions/${selectedPosition.value.id}`, payload)
    isEditingPosition.value = false
    closeProductsModal()
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
  try {
    const res = await axios.get('/api/file-path-settings')
    opartFormatSettings.value = {
      opart_total_chars: res.data?.opart_total_chars ?? null,
      opart_remove_before: res.data?.opart_remove_before ?? null,
      opart_remove_after: res.data?.opart_remove_after ?? null,
    }
  } catch (e) {
    console.error('Errore caricamento impostazioni formattazione', e)
  }
})
</script>

<template>
  <div class="space-y-4">

    <!-- Pannello filtri -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="bg-copam-blue px-4 py-3 flex items-center gap-2">
        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span class="text-sm font-semibold text-white">Magazzino</span>
      </div>
      <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-lg hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue transition-colors w-full sm:w-auto justify-center"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Aggiungi Merce
        </button>

        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input
            v-model="filterPending"
            @change="fetchPositions(1)"
            type="checkbox"
            class="sr-only peer"
          />
          <div class="relative w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-copam-blue"></div>
          <span class="text-sm text-gray-700 font-medium">In Attesa</span>
        </label>

        <div class="sm:ml-auto sm:max-w-md w-full">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Cerca posizione, codice merce, ord. prod..."
              class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Tabella posizioni -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">Posizione</th>
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">Codici Merce</th>
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">Ord. Prod.</th>
              <th class="px-4 py-2.5 text-center font-semibold uppercase tracking-wider text-xs">Iniziato</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="4" class="px-4 py-10 text-center text-gray-400">
                <svg class="animate-spin h-6 w-6 text-copam-blue mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs">Caricamento...</span>
              </td>
            </tr>
            <tr
              v-else
              v-for="(position, index) in positions"
              :key="position.id"
              @click="openProductsModal(position)"
              :class="[
                'cursor-pointer transition-colors',
                index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'
              ]"
            >
              <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">{{ position.warehouse_position }}</td>
              <td class="px-4 py-2.5 text-gray-600 border-r border-gray-100">
                <span class="text-xs">{{ (position.warehouses || []).map(w => w.product_code).filter(Boolean).join(' | ') || '&mdash;' }}</span>
              </td>
              <td class="px-4 py-2.5 text-gray-600 border-r border-gray-100">
                <span class="text-xs">{{ (position.warehouses || []).map(w => w.production_order).filter(Boolean).join(' | ') || '&mdash;' }}</span>
              </td>
              <td class="px-4 py-2.5 whitespace-nowrap text-center">
                <span v-if="position.started" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Sì</span>
                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">No</span>
              </td>
            </tr>
            <tr v-if="!loading && !positions.length">
              <td colspan="4" class="px-4 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">
                  {{ filterPending ? 'Nessuna posizione in attesa' : 'Nessuna posizione in magazzino' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ filterPending ? 'Non ci sono posizioni registrate in attesa.' : 'Non ci sono posizioni registrate nel magazzino.' }}
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginazione integrata -->
      <div class="border-t border-gray-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
        <p class="text-xs text-gray-500">
          Mostrando <span class="font-medium text-gray-700">{{ pagination.from }}</span> &ndash; <span class="font-medium text-gray-700">{{ pagination.to }}</span> di <span class="font-medium text-gray-700">{{ pagination.total }}</span> posizioni
        </p>
        <div class="flex items-center gap-1">
          <button
            @click="goToPage(1)"
            :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >&laquo;</button>
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >&lsaquo; Prec.</button>
          <span class="px-3 py-1 text-xs bg-copam-blue text-white rounded font-medium">{{ currentPage }} / {{ pagination.last_page }}</span>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >Succ. &rsaquo;</button>
          <button
            @click="goToPage(pagination.last_page)"
            :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
          >&raquo;</button>
        </div>
      </div>
    </div>

    <!-- Modal Creazione Merce -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeCreateModal"></div>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <!-- Header -->
          <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Aggiungi Nuova Merce</h3>
            <button @click="closeCreateModal" class="text-white/70 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <!-- Body -->
          <form @submit.prevent="saveWarehouse" class="px-6 py-5 space-y-4">
            <div class="flex items-center gap-3">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input v-model="formData.pending" type="checkbox" class="sr-only peer"/>
                <div class="relative w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-copam-blue"></div>
                <span class="text-sm font-medium text-gray-700">In Attesa</span>
              </label>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Codice Attesa</label>
              <input v-model="formData.pending_code" type="text" :disabled="!formData.pending"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue disabled:bg-gray-100 disabled:cursor-not-allowed"/>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                Posizione {{ !formData.pending ? '*' : '' }}
              </label>
              <input v-model="formData.warehouse_position" type="text" :required="!formData.pending" :disabled="formData.pending"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue disabled:bg-gray-100 disabled:cursor-not-allowed"/>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Codice Merce</label>
              <input v-model="formData.product_code" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Ord. Prod.</label>
              <input v-model="formData.production_order" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
              <button type="button" @click="closeCreateModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Annulla
              </button>
              <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue">
                Salva
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Lista Prodotti in Posizione -->
    <div v-if="showProductsModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeProductsModal"></div>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
          <!-- Header -->
          <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <h3 class="text-base font-semibold text-white">Posizione</h3>
              <span class="bg-white/20 text-white text-xs font-mono px-2 py-0.5 rounded">{{ selectedPosition?.warehouse_position }}</span>
            </div>
            <button @click="closeProductsModal" class="text-white/70 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <!-- Body -->
          <div class="px-6 py-5 space-y-5">
            <!-- Toggle Iniziato -->
            <div class="bg-gray-50 rounded-xl border border-gray-200 px-4 py-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Iniziato</p>
                </div>
                <button
                  @click="toggleStarted"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-copam-blue focus:ring-offset-2',
                    selectedPosition?.started ? 'bg-copam-blue' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      selectedPosition?.started ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  />
                </button>
              </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
              <table class="w-full text-sm">
                <thead class="bg-copam-blue text-white">
                  <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider border-r border-blue-400/40">Codice Merce</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider border-r border-blue-400/40">Dim. X</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider border-r border-blue-400/40">Dim. Y</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Ord. Prod.</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-if="selectedProducts.length === 0">
                    <td colspan="4" class="px-4 py-8 text-center text-xs text-gray-400">Nessuna merce in questa posizione</td>
                  </tr>
                  <tr
                    v-for="(product, index) in selectedProducts"
                    :key="product.id"
                    @click="openEditModal(product)"
                    :class="[
                      'cursor-pointer transition-colors',
                      index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'
                    ]"
                  >
                    <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">{{ product.product_code || '&mdash;' }}</td>
                    <td class="px-4 py-2.5 whitespace-nowrap text-gray-600 border-r border-gray-100">{{ product.dimension_x ?? '&mdash;' }}</td>
                    <td class="px-4 py-2.5 whitespace-nowrap text-gray-600 border-r border-gray-100">{{ product.dimension_y ?? '&mdash;' }}</td>
                    <td class="px-4 py-2.5 whitespace-nowrap text-gray-600">{{ product.production_order || '&mdash;' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Modifica posizione -->
            <div class="bg-gray-50 rounded-xl border border-gray-200 px-4 py-4">
              <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Rinomina Posizione</p>
                  <input
                    v-model="editingPositionName"
                    type="text"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                    placeholder="Inserisci nuovo nome posizione"
                  />
                </div>
                <div class="w-full sm:w-36">
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Quantità</p>
                  <input
                    v-model="editingPositionQuantity"
                    type="number"
                    min="0"
                    step="0.001"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                    placeholder="0.000"
                  />
                </div>
                <div class="flex items-end">
                  <button
                    @click="updatePositionName"
                    :disabled="!editingPositionName"
                    class="px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-copam-blue"
                  >
                    Aggiorna
                  </button>
                </div>
              </div>
            </div>

            <div class="flex justify-end border-t border-gray-100 pt-4">
              <button @click="closeProductsModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Chiudi
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Modifica Merce -->
    <div v-if="showEditModal" class="fixed inset-0 z-[60] overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeEditModal"></div>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <!-- Header -->
          <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Modifica Merce</h3>
            <button @click="closeEditModal" class="text-white/70 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <!-- Body -->
          <form @submit.prevent="updateWarehouse" class="px-6 py-5 space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Posizione *</label>
              <input v-model="formData.warehouse_position" type="text" required
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Codice Merce</label>
              <input v-model="formData.product_code" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Dim. X</label>
                <input v-model="formData.dimension_x" type="number" min="0" step="0.001"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                  placeholder="0.000"/>
              </div>
              <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Dim. Y</label>
                <input v-model="formData.dimension_y" type="number" min="0" step="0.001"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                  placeholder="0.000"/>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Ord. Prod.</label>
              <input v-model="formData.production_order" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-2 border-t border-gray-100">
              <button type="button" @click="openDeleteModal"
                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                Elimina
              </button>
              <button type="submit"
                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue">
                Salva
              </button>
              <button type="button" @click="closeEditModal"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Chiudi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Conferma Eliminazione -->
  <Teleport to="body">
    <div v-if="showDeleteModal" class="fixed inset-0 z-[70] overflow-y-auto">
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
