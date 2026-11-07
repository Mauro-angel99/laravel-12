<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import { usePolling } from '@/composables/usePolling'

const data = ref([])
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
const onlyUnread = ref(false)
const markingId = ref(null)

let fetching = false

// silent: aggiornamento in background (polling) senza spinner
const fetchData = async (page = 1, { silent = false } = {}) => {
  if (silent && (fetching || markingId.value)) return
  fetching = true
  if (!silent) loading.value = true
  try {
    const params = { page }
    if (onlyUnread.value) params.unread = 1
    const res = await axios.get('/api/communications/inbox', { params })
    data.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
    window.Alpine?.store('communications')?.set(res.data.unread_count)
  } catch (error) {
    console.error(error)
  } finally {
    fetching = false
    if (!silent) loading.value = false
  }
}

// Nuove comunicazioni assegnate senza ricaricare la pagina
usePolling(() => fetchData(currentPage.value, { silent: true }))

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchData(page)
  }
}

const markRead = async (row) => {
  markingId.value = row.id
  try {
    const res = await axios.post(`/api/communications/${row.id}/read`)
    row.read_at = res.data.read_at
    window.Alpine?.store('communications')?.set(res.data.unread_count)
    if (onlyUnread.value) {
      const goBack = data.value.length === 1 && currentPage.value > 1
      await fetchData(goBack ? currentPage.value - 1 : currentPage.value)
    }
  } catch (error) {
    console.error(error)
  } finally {
    markingId.value = null
  }
}

watch(onlyUnread, () => fetchData(1))

onMounted(() => fetchData())
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Toolbar -->
      <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
        <span class="text-xs text-gray-500">
          <span v-if="loading">Caricamento...</span>
          <span v-else>
            <span class="font-semibold text-gray-700">{{ pagination.total }}</span> comunicazioni
          </span>
        </span>
        <label class="inline-flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
          <input type="checkbox" v-model="onlyUnread"
            class="rounded border-gray-300 text-copam-blue focus:ring-copam-blue" />
          Solo da leggere
        </label>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="px-3 py-10 flex items-center justify-center gap-2 text-gray-400">
        <svg class="animate-spin h-5 w-5 text-copam-blue" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-xs">Caricamento...</span>
      </div>

      <!-- Empty state -->
      <div v-else-if="!data.length" class="px-3 py-16 text-center">
        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <p class="mt-2 text-xs font-medium text-gray-400">
          {{ onlyUnread ? 'Nessuna comunicazione da leggere' : 'Nessuna comunicazione ricevuta' }}
        </p>
      </div>

      <!-- Elenco comunicazioni -->
      <ul v-else class="divide-y divide-gray-100">
        <li v-for="row in data" :key="row.id"
          :class="['px-4 py-3 flex flex-col sm:flex-row sm:items-start gap-3', row.read_at ? 'bg-white' : 'bg-blue-50/60']">
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
              <span v-if="!row.read_at" class="inline-block bg-copam-blue text-white text-[10px] font-bold uppercase px-1.5 py-0.5 rounded">Nuova</span>
              <span v-if="row.opart_padre" class="bg-gray-100 text-gray-700 text-[11px] font-mono px-2 py-0.5 rounded">Padre: {{ row.opart_padre }}</span>
              <span v-if="row.opart" class="bg-gray-100 text-gray-700 text-[11px] font-mono px-2 py-0.5 rounded">Art.: {{ row.opart }}</span>
              <span v-if="row.fllav" class="bg-gray-100 text-gray-700 text-[11px] font-mono px-2 py-0.5 rounded">FLLAV: {{ row.fllav }}</span>
              <span v-if="row.idopr" class="bg-gray-100 text-gray-700 text-[11px] font-mono px-2 py-0.5 rounded">IDOPR: {{ row.idopr }}</span>
            </div>
            <p :class="['text-sm whitespace-pre-line break-words', row.read_at ? 'text-gray-700' : 'text-gray-900 font-medium']">{{ row.message }}</p>
            <p class="mt-1 text-[11px] text-gray-500">
              Da <span class="font-medium text-gray-700">{{ row.user_name || '—' }}</span>
              &middot; {{ row.sent_at }}
              <span v-if="row.edited" class="italic">(modificata)</span>
            </p>
          </div>
          <div class="shrink-0">
            <span v-if="row.read_at" class="inline-flex items-center gap-1 text-[11px] text-green-700">
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Letta il {{ row.read_at }}
            </span>
            <button v-else type="button" @click="markRead(row)" :disabled="markingId === row.id"
              class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
              {{ markingId === row.id ? '...' : 'Segna come letta' }}
            </button>
          </div>
        </li>
      </ul>

      <!-- Paginazione -->
      <div v-if="pagination.last_page > 1" class="border-t border-gray-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
        <p class="text-xs text-gray-500">
          Mostrando <span class="font-medium text-gray-700">{{ pagination.from }}</span> &ndash; <span class="font-medium text-gray-700">{{ pagination.to }}</span> di <span class="font-medium text-gray-700">{{ pagination.total }}</span> comunicazioni
        </p>
        <div class="flex items-center gap-1">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &lsaquo; Prec.
          </button>
          <span class="px-3 py-1 text-xs bg-copam-blue text-white rounded font-medium">{{ currentPage }} / {{ pagination.last_page }}</span>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Succ. &rsaquo;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
