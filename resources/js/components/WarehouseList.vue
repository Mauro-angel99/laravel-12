<script setup>
import { ref, onMounted, nextTick, computed, watch } from 'vue'
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

const heatFormatSettings = ref({
  heat_search: null,
  heat_replace: null,
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

const formatHeat = (value) => {
  if (!value) return value
  const { heat_search, heat_replace } = heatFormatSettings.value
  if (!heat_search) return value
  return value.replaceAll(heat_search, heat_replace || '')
}

const searchQuery = ref('')
const filterPending = ref(false)
const showCreateModal = ref(false)
const showAddPositionModal = ref(false)
const newPositionForm = ref({ name: '', quantity: null, pending: true, productionOrders: [''] })
const productionOrderRefs = ref([])
const showProductsModal = ref(false)
const showAddMerceInPositionModal = ref(false)
const addMerceInPositionForm = ref({ heat: '', product_code: '', production_order: '', product_description: '', notes: '', format: '', pending: false, pending_code: '' })
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedPosition = ref(null)
const selectedProducts = ref([])
const editingProduct = ref(null)
const editingPositionName = ref('')
const editingPositionQuantity = ref(null)
const isEditingPosition = ref(false)
const isSaving = ref(false)

const formData = ref({
  warehouse_position: '',
  heat: '',
  product_code: '',
  production_order: '',
  product_description: '',
  notes: '',
  format: '',
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
    heat: '',
    product_code: '',
    production_order: '',
    product_description: '',
    notes: '',
    format: '',
    pending: true,
    pending_code: ''
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
}

const onProductionOrderKeydown = async (index, event) => {
  if (event.key !== 'Enter') return
  event.preventDefault()
  const val = newPositionForm.value.productionOrders[index]
  if (!val || !val.trim()) return
  newPositionForm.value.productionOrders.push('')
  await nextTick()
  productionOrderRefs.value[index + 1]?.focus()
}

const removeProductionOrder = (index) => {
  newPositionForm.value.productionOrders.splice(index, 1)
}

const duplicateProductionOrders = computed(() => {
  const orders = selectedProducts.value
    .map(p => p.production_order)
    .filter(o => o && o.trim())
  const counts = {}
  orders.forEach(o => { counts[o] = (counts[o] || 0) + 1 })
  return new Set(Object.keys(counts).filter(o => counts[o] > 1))
})

const positionOrderCount = computed(() =>
  newPositionForm.value.productionOrders.filter(o => o && o.trim()).length
)

const isDuplicateOrder = (index) => {
  const val = newPositionForm.value.productionOrders[index]
  if (!val || !val.trim()) return false
  const formatted = formatOpart(val.trim())
  const allFormatted = newPositionForm.value.productionOrders.map((o, i) =>
    i !== index && o && o.trim() ? formatOpart(o.trim()) : null
  )
  return allFormatted.includes(formatted)
}

const savePosition = async () => {
  // Blocca se ci sono ordini di produzione duplicati (sui codici formattati)
  const hasDuplicates = newPositionForm.value.productionOrders.some((_, i) => isDuplicateOrder(i))
  if (hasDuplicates) {
    showMessageModal('error', 'Errore', 'Sono presenti ordini di produzione duplicati. Correggili prima di salvare.')
    return
  }
  isSaving.value = true
  try {
    const res = await axios.post('/api/warehouse/positions', {
      warehouse_position: newPositionForm.value.name,
      quantity: positionOrderCount.value,
      pending: newPositionForm.value.pending,
    })
    if (!res.data.success) return

    const positionName = res.data.data.warehouse_position
    const orders = newPositionForm.value.productionOrders.filter(o => o && o.trim())

    for (const order of orders) {
      await axios.post('/api/warehouse', {
        warehouse_position: positionName,
        production_order: formatOpart(order.trim()),
        pending: newPositionForm.value.pending,
      })
    }

    showAddPositionModal.value = false
    newPositionForm.value = { name: '', quantity: null, pending: true, productionOrders: [''] }
    fetchPositions(currentPage.value)
    const detail = orders.length > 0 ? ` con ${orders.length} ordine/i di produzione` : ''
    showMessageModal('success', 'Posizione creata', res.data.message + detail)
  } catch (err) {
    const msg = err.response?.data?.message || 'Errore durante la creazione della posizione'
    showMessageModal('error', 'Errore', msg)
  } finally {
    isSaving.value = false
  }
}

const openProductsModal = async (position) => {
  selectedPosition.value = position
  editingPositionName.value = position.warehouse_position
  editingPositionQuantity.value = position.quantity ?? null
  editingStarted.value = position.started
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
  editingPositionQuantity.value = null
  isEditingPosition.value = false
}

const openEditModal = (product) => {
  editingProduct.value = product
  formData.value = {
    warehouse_position: product.position?.warehouse_position || '',
    product_code: product.product_code || '',
    production_order: product.production_order || '',
    product_description: product.product_description || '',
    notes: product.notes || '',
    heat: product.heat || '',
    format: product.format || '',
  }
  // Non chiudere showProductsModal per permettere modali annidate
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingProduct.value = null
}

const saveWarehouse = async () => {
  isSaving.value = true
  try {
    const payload = {
      ...formData.value,
      heat: formatHeat(formData.value.heat),
      production_order: formatOpart(formData.value.production_order)
    }
    const res = await axios.post('/api/warehouse', payload)
    closeCreateModal()
    showMessageModal('success', 'Successo', res.data.message || 'Elemento aggiunto con successo')
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante il salvataggio'
    showMessageModal('error', 'Errore', errorMessage)
  } finally {
    isSaving.value = false
  }
}

const saveAddMerceInPosition = async () => {
  isSaving.value = true
  try {
    const payload = {
      ...addMerceInPositionForm.value,
      warehouse_position: selectedPosition.value.warehouse_position,
      heat: formatHeat(addMerceInPositionForm.value.heat),
      production_order: formatOpart(addMerceInPositionForm.value.production_order),
    }
    const res = await axios.post('/api/warehouse', payload)
    showAddMerceInPositionModal.value = false
    addMerceInPositionForm.value = { heat: '', product_code: '', production_order: '', product_description: '', notes: '', format: '', pending: false, pending_code: '' }
    showMessageModal('success', 'Successo', res.data.message || 'Merce aggiunta con successo')
    // Ricarica i prodotti della posizione corrente
    const posRes = await axios.get(`/api/warehouse/positions/${selectedPosition.value.id}/products`)
    selectedProducts.value = posRes.data.products
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante il salvataggio'
    showMessageModal('error', 'Errore', errorMessage)
  } finally {
    isSaving.value = false
  }
}

const updateWarehouse = async () => {
  isSaving.value = true
  try {
    const payload = {
      ...formData.value,
      heat: formatHeat(formData.value.heat),
    }
    const res = await axios.put(`/api/warehouse/${editingProduct.value.id}`, payload)
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
  } finally {
    isSaving.value = false
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
  if (!editingPositionName.value || editingPositionName.value === selectedPosition.value.warehouse_position) {
    isEditingPosition.value = false
    return
  }

  isSaving.value = true
  try {
    const res = await axios.put(`/api/warehouse/positions/${selectedPosition.value.id}`, {
      warehouse_position: editingPositionName.value,
      quantity: selectedProducts.value.length
    })
    isEditingPosition.value = false
    closeProductsModal()
    showMessageModal('success', 'Successo', res.data.message || 'Posizione aggiornata con successo')
    await fetchPositions(currentPage.value)
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Errore durante l\'aggiornamento della posizione'
    showMessageModal('error', 'Errore', errorMessage)
    editingPositionName.value = selectedPosition.value.warehouse_position
    isEditingPosition.value = false
  } finally {
    isSaving.value = false
  }
}

let heatLookupTimer = null
const isLoadingHeat = ref(false)

const lookupHeatData = async (heatValue, targetForm) => {
  if (!heatValue) return
  const formatted = formatHeat(heatValue)
  if (!formatted) return
  isLoadingHeat.value = true
  try {
    const res = await axios.get('/api/warehouse/heat-lookup', { params: { cddet: formatted } })
    if (res.data.cdart !== null && res.data.cdart !== undefined) {
      targetForm.product_code = res.data.cdart ?? ''
    }
    if (res.data.cdfmt !== null && res.data.cdfmt !== undefined) {
      targetForm.format = res.data.cdfmt ?? ''
    }
  } catch (e) {
    // fallback silenzioso
  } finally {
    isLoadingHeat.value = false
  }
}

watch(() => formData.value.heat, (newVal) => {
  clearTimeout(heatLookupTimer)
  heatLookupTimer = setTimeout(() => lookupHeatData(newVal, formData.value), 400)
})

watch(() => addMerceInPositionForm.value.heat, (newVal) => {
  clearTimeout(heatLookupTimer)
  heatLookupTimer = setTimeout(() => lookupHeatData(newVal, addMerceInPositionForm.value), 400)
})

onMounted(async () => {
  await fetchPositions()
  try {
    const res = await axios.get('/api/file-path-settings')
    opartFormatSettings.value = {
      opart_total_chars: res.data?.opart_total_chars ?? null,
      opart_remove_before: res.data?.opart_remove_before ?? null,
      opart_remove_after: res.data?.opart_remove_after ?? null,
    }
    heatFormatSettings.value = {
      heat_search: res.data?.heat_search ?? null,
      heat_replace: res.data?.heat_replace ?? null,
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
          @click="showAddPositionModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors w-full sm:w-auto justify-center"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Aggiungi Posizione
        </button>
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
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">Ord. Prod.</th>
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">Codici Merce</th>
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
                <span class="text-xs">{{ (position.warehouses || []).map(w => w.production_order).filter(Boolean).join(' | ') || '&mdash;' }}</span>
              </td>
              <td class="px-4 py-2.5 text-gray-600 border-r border-gray-100">
                <span class="text-xs">{{ (position.warehouses || []).map(w => w.product_code).filter(Boolean).join(' | ') || '&mdash;' }}</span>
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
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
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Colata</label>
              <div class="relative">
                <input v-model="formData.heat" type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue pr-8"/>
                <svg v-if="isLoadingHeat" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-copam-blue animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
              </div>
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

            <div class="flex gap-3">
              <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Formato</label>
                <input v-model="formData.format" type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
              <button type="button" @click="closeCreateModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Annulla
              </button>
              <button type="submit" :disabled="isSaving"
                class="px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed">
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
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
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Ord. Prod.</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-if="selectedProducts.length === 0">
                    <td colspan="2" class="px-4 py-8 text-center text-xs text-gray-400">Nessuna merce in questa posizione</td>
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
                    <td class="px-4 py-2.5 whitespace-nowrap text-gray-600">
                      <span class="inline-flex items-center gap-1.5">
                        {{ product.production_order || '&mdash;' }}
                        <span
                          v-if="product.production_order && duplicateProductionOrders.has(product.production_order)"
                          class="relative group flex-shrink-0"
                        >
                          <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                          </svg>
                          <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-48 rounded bg-gray-900 text-white text-xs px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity z-10 text-center whitespace-normal">
                            Ordine di produzione duplicato in questa posizione
                          </span>
                        </span>
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Aggiungi merce nella posizione -->
            <div class="flex justify-start">
              <button
                @click="showAddMerceInPositionModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-lg hover:bg-copam-blue/90 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Aggiungi Merce
              </button>
            </div>

            <!-- Modifica posizione -->
            <div class="bg-gray-50 rounded-xl border border-gray-200 px-4 py-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Modifica Posizione</p>
              <div class="flex flex-col gap-2">
                <div class="flex flex-col sm:flex-row gap-2">
                  <input
                    v-model="editingPositionName"
                    type="text"
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                    placeholder="Inserisci nuovo nome posizione"
                  />
                  <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Contatore</label>
                    <input
                      :value="selectedProducts.length"
                      type="number"
                      disabled
                      class="w-28 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-transparent cursor-not-allowed text-copam-blue font-semibold"
                    />
                  </div>
                  <button
                    @click="updatePositionName"
                    :disabled="!editingPositionName || editingPositionName === selectedPosition?.warehouse_position || isSaving"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-copam-blue"
                  >
                    <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    {{ isSaving ? 'Aggiornamento...' : 'Aggiorna' }}
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
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
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Colata</label>
              <div class="relative">
                <input v-model="formData.heat" type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue pr-8"/>
                <svg v-if="isLoadingHeat" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-copam-blue animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
              </div>
            </div>
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
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Ord. Prod.</label>
              <input v-model="formData.production_order" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>

            <div class="flex gap-3">
              <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Formato</label>
                <input v-model="formData.format" type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-2 border-t border-gray-100">
              <button type="button" @click="openDeleteModal"
                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                Elimina
              </button>
              <button type="submit" :disabled="isSaving"
                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed">
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

  <!-- Modal Aggiungi Merce in Posizione -->
  <Teleport to="body">
    <div v-if="showAddMerceInPositionModal" class="fixed inset-0 z-[70] overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <h3 class="text-base font-semibold text-white">Aggiungi Merce</h3>
              <span class="bg-white/20 text-white text-xs font-mono px-2 py-0.5 rounded">{{ selectedPosition?.warehouse_position }}</span>
            </div>
            <button @click="showAddMerceInPositionModal = false" class="text-white/70 hover:text-white transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <form @submit.prevent="saveAddMerceInPosition" class="px-6 py-5 space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Colata</label>
              <div class="relative">
                <input v-model="addMerceInPositionForm.heat" type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue pr-8"/>
                <svg v-if="isLoadingHeat" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-copam-blue animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Codice Merce</label>
              <input v-model="addMerceInPositionForm.product_code" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Ord. Prod.</label>
              <input v-model="addMerceInPositionForm.production_order" type="text"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Formato</label>
                <input v-model="addMerceInPositionForm.format" type="text"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"/>
              </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
              <button type="button" @click="showAddMerceInPositionModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Annulla
              </button>
              <button type="submit" :disabled="isSaving"
                class="px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed">
                Salva
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Modal Aggiungi Posizione -->
  <Teleport to="body">
    <div v-if="showAddPositionModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md xl:max-w-4xl mx-4 overflow-hidden">
        <!-- Header -->
        <div class="bg-copam-blue px-6 xl:px-12 py-4 xl:py-7 flex items-center justify-between">
          <h3 class="text-base xl:text-3xl font-semibold text-white">Aggiungi Posizione</h3>
          <button @click="showAddPositionModal = false" class="text-white/70 hover:text-white transition-colors">
            <svg class="w-5 h-5 xl:w-10 xl:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 xl:p-12 space-y-4 xl:space-y-8">
          <div class="flex gap-3 xl:gap-6">
            <div class="flex-1">
              <label class="block text-sm xl:text-2xl font-medium text-gray-700 mb-1 xl:mb-3">Posizione</label>
              <input
                v-model="newPositionForm.name"
                type="text"
                class="w-full px-3 xl:px-6 py-2 xl:py-5 border border-gray-300 rounded-lg xl:rounded-xl text-sm xl:text-3xl focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-transparent"
              />
            </div>
            <div class="w-28 xl:w-52">
              <label class="block text-sm xl:text-2xl font-medium text-gray-700 mb-1 xl:mb-3">Contatore</label>
              <input
                :value="positionOrderCount"
                type="number"
                disabled
                class="w-full px-3 xl:px-6 py-2 xl:py-5 border border-gray-300 rounded-lg xl:rounded-xl text-sm xl:text-3xl bg-transparent cursor-not-allowed text-copam-blue font-semibold"
              />
            </div>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm xl:text-2xl font-medium text-gray-700">In Attesa</span>
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="newPositionForm.pending" type="checkbox" class="sr-only peer" />
              <div class="relative w-10 xl:w-20 h-5 xl:h-10 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full xl:peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] xl:after:top-[4px] after:start-[2px] xl:after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 xl:after:h-8 after:w-4 xl:after:w-8 after:transition-all peer-checked:bg-copam-blue"></div>
            </label>
          </div>
          <div>
            <label class="block text-sm xl:text-2xl font-medium text-gray-700 mb-2 xl:mb-4">Ordini di Produzione</label>
            <div class="space-y-2 xl:space-y-4 max-h-48 xl:max-h-96 overflow-y-auto p-1 -m-1">
              <div
                v-for="(order, index) in newPositionForm.productionOrders"
                :key="index"
                class="flex items-center gap-2 xl:gap-4"
              >
                <input
                  :ref="el => productionOrderRefs[index] = el"
                  v-model="newPositionForm.productionOrders[index]"
                  type="text"
                  placeholder="Scansiona o digita ord. prod."
                  @keydown="onProductionOrderKeydown(index, $event)"
                  :class="[
                    'flex-1 px-3 xl:px-6 py-2 xl:py-5 rounded-lg xl:rounded-xl text-sm xl:text-3xl focus:outline-none focus:ring-2 focus:border-transparent',
                    isDuplicateOrder(index)
                      ? 'border-2 border-red-500 focus:ring-red-300'
                      : 'border border-gray-300 focus:ring-copam-blue'
                  ]"
                />
                <button
                  v-if="newPositionForm.productionOrders.length > 1"
                  @click="removeProductionOrder(index)"
                  class="text-gray-400 hover:text-red-500 transition-colors flex-shrink-0"
                  title="Rimuovi"
                >
                  <svg class="w-4 h-4 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="flex justify-end mt-2 xl:mt-4">
              <button
                @click="newPositionForm.productionOrders.push('')"
                class="inline-flex items-center justify-center w-6 h-6 xl:w-12 xl:h-12 rounded-full bg-copam-blue text-white hover:bg-copam-blue/90 transition-colors"
                title="Aggiungi ordine"
              >
                <svg class="w-3.5 h-3.5 xl:w-7 xl:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="flex justify-end gap-2 xl:gap-4 pt-2 xl:pt-4 border-t border-gray-100">
            <button
              type="button"
              @click="showAddPositionModal = false"
              class="px-4 xl:px-8 py-2 xl:py-4 text-sm xl:text-2xl font-medium text-gray-700 bg-white border border-gray-300 rounded-lg xl:rounded-xl hover:bg-gray-50 transition-colors"
            >
              Annulla
            </button>
            <button
              type="button"
              @click="savePosition"
              :disabled="isSaving"
              class="px-4 xl:px-8 py-2 xl:py-4 text-sm xl:text-2xl font-medium text-white bg-copam-blue rounded-lg xl:rounded-xl hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Salva
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
