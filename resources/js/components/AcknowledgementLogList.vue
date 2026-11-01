<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import * as XLSX from 'xlsx'

const data = ref([])
const loading = ref(false)
const exportLoading = ref(false)
const currentPage = ref(1)
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0,
})

const filters = ref({
  opart_padre: '',
  opart: '',
  fllav: '',
  idopr: '',
  user_name: '',
  date_from: '',
  date_to: '',
})

const fetchData = async (page = 1) => {
  loading.value = true
  try {
    const params = { page }
    Object.entries(filters.value).forEach(([key, val]) => {
      if (val) params[key] = val
    })
    const res = await axios.get('/api/work-phase-acknowledgements', { params })
    data.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const hasFilters = () => Object.values(filters.value).some(Boolean)

const clearAllFilters = () => {
  Object.keys(filters.value).forEach((key) => { filters.value[key] = '' })
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchData(page)
  }
}

let searchTimeout
watch(filters, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchData(1), 300)
}, { deep: true })

onMounted(() => fetchData())

// Eliminazione presa visione
const deleting = ref(false)
const confirmDelete = ref({ show: false, row: null })

const askDelete = (row) => {
  confirmDelete.value = { show: true, row }
}

const cancelDelete = () => {
  confirmDelete.value = { show: false, row: null }
}

const performDelete = async () => {
  const row = confirmDelete.value.row
  if (!row) return
  deleting.value = true
  try {
    await axios.delete(`/api/work-phase-acknowledgements/${row.id}`)
    confirmDelete.value = { show: false, row: null }
    const goBack = data.value.length === 1 && currentPage.value > 1
    await fetchData(goBack ? currentPage.value - 1 : currentPage.value)
  } catch (error) {
    console.error(error)
  } finally {
    deleting.value = false
  }
}

// Esportazione XLSX
const exportExcel = async () => {
  exportLoading.value = true
  try {
    const params = {}
    Object.entries(filters.value).forEach(([key, val]) => {
      if (val) params[key] = val
    })

    const res = await axios.get('/api/work-phase-acknowledgements/export', { params })
    const records = res.data.data

    if (!records.length) return

    const columns = [
      { key: 'viewed_at',   label: 'Data e ora',     fmt: (r) => r.viewed_at ?? '' },
      { key: 'user_name',   label: 'Operatore',      fmt: (r) => r.user_name ?? '' },
      { key: 'opart_padre', label: 'Articolo padre', fmt: (r) => r.opart_padre ?? '' },
      { key: 'opart',       label: 'Codice articolo', fmt: (r) => r.opart ?? '' },
      { key: 'flass',       label: 'FLASS',          fmt: (r) => r.flass ?? '' },
      { key: 'fllav',       label: 'FLLAV',          fmt: (r) => r.fllav ?? '' },
      { key: 'idopr',       label: 'IDOPR',          fmt: (r) => r.idopr ?? '' },
    ]

    const rows = records.map(r => {
      const obj = {}
      columns.forEach(c => { obj[c.label] = c.fmt(r) })
      return obj
    })

    const ws = XLSX.utils.json_to_sheet(rows, { header: columns.map(c => c.label) })

    const colWidths = columns.map(c => {
      const maxLen = Math.max(
        c.label.length,
        ...records.map(r => String(c.fmt(r) ?? '').length)
      )
      return { wch: Math.min(maxLen + 2, 60) }
    })
    ws['!cols'] = colWidths

    const headerStyle = {
      font: { bold: true, color: { rgb: 'FFFFFF' } },
      fill: { fgColor: { rgb: '1E3A5F' } },
      alignment: { horizontal: 'center', vertical: 'center' },
      border: {
        bottom: { style: 'thin', color: { rgb: 'CCCCCC' } },
        right:  { style: 'thin', color: { rgb: 'CCCCCC' } },
      },
    }
    columns.forEach((_, i) => {
      const cellRef = XLSX.utils.encode_cell({ r: 0, c: i })
      if (ws[cellRef]) ws[cellRef].s = headerStyle
    })

    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Prese Visione')
    XLSX.writeFile(wb, `prese_visione_${new Date().toISOString().slice(0, 10)}.xlsx`)
  } catch (error) {
    console.error(error)
  } finally {
    exportLoading.value = false
  }
}
</script>

<template>
  <div class="space-y-4">

    <!-- FILTRI -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="bg-copam-blue px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="h-4 w-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
          </svg>
          <span class="text-sm font-semibold text-white">Filtri di ricerca</span>
        </div>
        <button
          v-if="hasFilters()"
          @click="clearAllFilters"
          class="inline-flex items-center gap-1 text-xs text-blue-100 hover:text-white transition-colors"
        >
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Cancella filtri
        </button>
      </div>

      <div class="p-4 grid grid-cols-1 sm:grid-cols-7 gap-3">
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Articolo padre</label>
          <input type="text" v-model="filters.opart_padre" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Codice articolo</label>
          <input type="text" v-model="filters.opart" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">FLLAV</label>
          <input type="text" v-model="filters.fllav" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">IDOPR</label>
          <input type="text" v-model="filters.idopr" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Operatore</label>
          <input type="text" v-model="filters.user_name" placeholder="Ricerca parziale…"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Dal</label>
          <input type="date" v-model="filters.date_from"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
        <div>
          <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Al</label>
          <input type="date" v-model="filters.date_to"
            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
        </div>
      </div>
    </div>

    <!-- TABELLA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Toolbar -->
      <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
        <span class="text-xs text-gray-500">
          <span v-if="loading">Caricamento...</span>
          <span v-else>
            <span class="font-semibold text-gray-700">{{ pagination.total }}</span> record totali
          </span>
        </span>
        <button
          @click="exportExcel"
          :disabled="exportLoading || pagination.total === 0"
          class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold shadow transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <svg v-if="exportLoading" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
          </svg>
          {{ exportLoading ? 'Esportazione...' : 'Esporta XLSX' }}
          <span v-if="!exportLoading && pagination.total > 0" class="bg-emerald-900 text-white text-xs rounded-full px-1.5 py-0.5">{{ pagination.total }}</span>
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Data e ora</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Operatore</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Articolo padre</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Codice articolo</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">FLASS</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">FLLAV</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider whitespace-nowrap border-r border-blue-400/40">IDOPR</th>
              <th class="px-3 py-2.5 text-center font-semibold uppercase tracking-wider whitespace-nowrap">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="8" class="px-3 py-10 text-center text-gray-400">
                <div class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5 text-copam-blue" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span class="text-xs">Caricamento...</span>
                </div>
              </td>
            </tr>

            <!-- Righe dati -->
            <tr v-else v-for="(row, index) in data" :key="row.id"
              :class="index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'">
              <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">{{ row.viewed_at }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.user_name || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.opart_padre || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.opart || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.flass || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.fllav || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.idopr || '—' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-center">
                <button
                  @click="askDelete(row)"
                  title="Elimina presa visione"
                  class="inline-flex items-center justify-center rounded-md p-1.5 text-red-600 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!loading && !data.length">
              <td colspan="8" class="px-3 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-xs font-medium text-gray-400">Nessuna presa visione registrata</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginazione -->
      <div class="border-t border-gray-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/50">
        <p class="text-xs text-gray-500">
          Mostrando <span class="font-medium text-gray-700">{{ pagination.from }}</span> &ndash; <span class="font-medium text-gray-700">{{ pagination.to }}</span> di <span class="font-medium text-gray-700">{{ pagination.total }}</span> record
        </p>
        <div class="flex items-center gap-1">
          <button @click="goToPage(1)" :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &laquo;
          </button>
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &lsaquo; Prec.
          </button>
          <span class="px-3 py-1 text-xs bg-copam-blue text-white rounded font-medium">{{ currentPage }} / {{ pagination.last_page }}</span>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Succ. &rsaquo;
          </button>
          <button @click="goToPage(pagination.last_page)" :disabled="currentPage === pagination.last_page"
            class="px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            &raquo;
          </button>
        </div>
      </div>
    </div>

    <!-- Modale conferma eliminazione -->
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0" enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100" leave-to-class="opacity-0"
    >
      <div v-if="confirmDelete.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="cancelDelete"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
          <div class="px-6 pt-6 pb-4 flex items-start gap-4">
            <div class="flex-shrink-0 flex items-center justify-center h-11 w-11 rounded-full bg-red-100">
              <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-semibold text-gray-900">Elimina presa visione</h3>
              <p class="mt-1 text-sm text-gray-500">
                Sei sicuro di voler eliminare questa presa visione
                <span v-if="confirmDelete.row?.viewed_at" class="font-medium text-gray-700">del {{ confirmDelete.row.viewed_at }}</span>?
                L'operazione non è reversibile.
              </p>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
            <button
              @click="cancelDelete"
              :disabled="deleting"
              class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-300 disabled:opacity-50"
            >
              Annulla
            </button>
            <button
              @click="performDelete"
              :disabled="deleting"
              class="inline-flex items-center gap-2 justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="deleting" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              {{ deleting ? 'Eliminazione...' : 'Elimina' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>
