<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const items = ref([])
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

const searchQuery = ref('')

// --- Modal aggiungi merce ---
const showAddModal = ref(false)
const isSaving = ref(false)
const addForm = ref({ product_code: '', position: '', format: '' })

// --- Messaggio ---
const messageModal = ref({ show: false, type: 'success', title: '', message: '' })

const showMessageModal = (type, title, message) => {
  messageModal.value = { show: true, type, title, message }
}
const closeMessageModal = () => {
  messageModal.value.show = false
}

// --- Fetch ---
const fetchItems = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/sheets', {
      params: { page, search: searchQuery.value },
    })
    items.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch {
    showMessageModal('error', 'Errore', 'Errore nel caricamento delle lamiere.')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => fetchItems(1)

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) fetchItems(page)
}

// --- Aggiungi merce ---
const openAddModal = () => {
  addForm.value = { product_code: '', position: '', format: '' }
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
}

const saveItem = async () => {
  isSaving.value = true
  try {
    const res = await axios.post('/api/sheets', addForm.value)
    closeAddModal()
    showMessageModal('success', 'Successo', res.data.message || 'Merce aggiunta con successo.')
    await fetchItems(currentPage.value)
  } catch (error) {
    const msg = error.response?.data?.message || 'Errore durante il salvataggio.'
    showMessageModal('error', 'Errore', msg)
  } finally {
    isSaving.value = false
  }
}

onMounted(() => fetchItems())
</script>

<template>
  <div class="space-y-3">

    <!-- Header card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <!-- Titolo barra -->
      <div class="bg-copam-blue px-4 py-2.5 flex items-center gap-2">
        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        <span class="text-sm font-semibold text-white">Lamiere</span>
      </div>

      <!-- Toolbar: bottone + ricerca -->
      <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
        <button
          @click="openAddModal"
          class="inline-flex items-center gap-2 px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-copam-blue transition-colors w-full sm:w-auto justify-center"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Aggiungi Merce
        </button>

        <div class="sm:ml-auto sm:max-w-md w-full">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
              fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Cerca per codice merce..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-transparent bg-gray-50"
            />
          </div>
        </div>
      </div>

      <!-- Tabella -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">
                Codice Merce
              </th>
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs border-r border-blue-400/40">
                Posizione
              </th>
              <th class="px-4 py-2.5 text-left font-semibold uppercase tracking-wider text-xs">
                Formato
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="3" class="px-4 py-10 text-center text-gray-400">
                <svg class="animate-spin h-6 w-6 text-copam-blue mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Caricamento...
              </td>
            </tr>
            <!-- Nessun risultato -->
            <tr v-else-if="!items.length">
              <td colspan="3" class="px-4 py-16 text-center text-gray-400 text-sm">
                Nessuna merce trovata.
              </td>
            </tr>
            <!-- Righe dati -->
            <tr
              v-else
              v-for="(item, index) in items"
              :key="item.id"
              :class="[
                index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50',
              ]"
            >
              <td class="px-4 py-2.5 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">
                {{ item.product_code || '&mdash;' }}
              </td>
              <td class="px-4 py-2.5 text-gray-600 border-r border-gray-100">
                {{ item.position || '&mdash;' }}
              </td>
              <td class="px-4 py-2.5 text-gray-600">
                {{ item.format || '&mdash;' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginazione -->
      <div class="border-t border-gray-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
        <p class="text-xs text-gray-500">
          Mostrando
          <span class="font-medium text-gray-700">{{ pagination.from }}</span>
          &ndash;
          <span class="font-medium text-gray-700">{{ pagination.to }}</span>
          di
          <span class="font-medium text-gray-700">{{ pagination.total }}</span>
          merci
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
          <span class="px-3 py-1 text-xs bg-copam-blue text-white rounded font-medium">
            {{ currentPage }} / {{ pagination.last_page }}
          </span>
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

    <!-- Modal Aggiungi Merce -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeAddModal"></div>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 relative z-10">

          <!-- Header modal -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Aggiungi Merce</h3>
            <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Campi form -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Codice Merce</label>
              <input
                v-model="addForm.product_code"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-copam-blue"
                placeholder="es. ABC123"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Posizione <span class="text-red-500">*</span></label>
              <input
                v-model="addForm.position"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-copam-blue"
                placeholder="es. A1"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Formato</label>
              <input
                v-model="addForm.format"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-copam-blue"
                placeholder="es. 3000x1500x3"
              />
            </div>
          </div>

          <!-- Azioni -->
          <div class="mt-6 flex justify-end gap-3">
            <button
              @click="closeAddModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Annulla
            </button>
            <button
              @click="saveItem"
              :disabled="isSaving || !addForm.position"
              class="px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="isSaving">Salvataggio...</span>
              <span v-else>Salva</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal messaggio -->
    <div v-if="messageModal.show" class="fixed inset-0 z-[70] overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-50" @click="closeMessageModal"></div>
        <div class="inline-block bg-white rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform sm:my-8 sm:max-w-sm sm:w-full sm:p-6 relative z-10">
          <div class="sm:flex sm:items-start">
            <div :class="[
              'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10',
              messageModal.type === 'success' ? 'bg-green-100' : 'bg-red-100'
            ]">
              <svg v-if="messageModal.type === 'success'" class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <svg v-else class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-base font-semibold text-gray-900">{{ messageModal.title }}</h3>
              <p class="mt-1 text-sm text-gray-500">{{ messageModal.message }}</p>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button
              @click="closeMessageModal"
              class="px-4 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-blue-700 transition-colors"
            >OK</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
