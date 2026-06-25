<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'

const data = ref([])
const selectedIds = ref(new Set())
const searchOpras = ref('')
const searchOpdnr = ref('')
const searchDrconFrom = ref('')
const searchDrconTo = ref('')
const searchDrcorFrom = ref('')
const searchDrcorTo = ref('')
const searchOpart = ref('')
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

const formatDate = (val) => {
  if (!val) return ''
  const d = new Date(val)
  if (isNaN(d.getTime())) return val
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  const yyyy = d.getFullYear()
  return `${dd}/${mm}/${yyyy}`
}

const fetchData = async (page = 1) => {
  selectedIds.value = new Set()
  loading.value = true
  try {
    const params = { page }
    if (searchOpras.value) params.opras = searchOpras.value
    if (searchOpdnr.value) params.opdnr = searchOpdnr.value
    if (searchDrconFrom.value) params.drcon_from = searchDrconFrom.value
    if (searchDrconTo.value) params.drcon_to = searchDrconTo.value
    if (searchDrcorFrom.value) params.drcor_from = searchDrcorFrom.value
    if (searchDrcorTo.value) params.drcor_to = searchDrcorTo.value
    if (searchOpart.value) params.opart = searchOpart.value
    const res = await axios.get('/api/production-orders', { params })
    data.value = res.data.data
    pagination.value = res.data.pagination
    currentPage.value = page
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchData(1)
}

const hasFilters = () => {
  return searchOpras.value || searchOpdnr.value || searchDrconFrom.value || searchDrconTo.value ||
         searchDrcorFrom.value || searchDrcorTo.value || searchOpart.value
}

const clearAllFilters = () => {
  searchOpras.value = ''
  searchOpdnr.value = ''
  searchDrconFrom.value = ''
  searchDrconTo.value = ''
  searchDrcorFrom.value = ''
  searchDrcorTo.value = ''
  searchOpart.value = ''
  applyFilters()
}

const allSelected = computed(() =>
  data.value.length > 0 && data.value.every(row => selectedIds.value.has(row.RECORD_ID))
)
const someSelected = computed(() =>
  data.value.some(row => selectedIds.value.has(row.RECORD_ID)) && !allSelected.value
)
const toggleAll = () => {
  if (allSelected.value) {
    data.value.forEach(row => selectedIds.value.delete(row.RECORD_ID))
  } else {
    data.value.forEach(row => selectedIds.value.add(row.RECORD_ID))
  }
  selectedIds.value = new Set(selectedIds.value)
}
const toggleRow = (id) => {
  const s = new Set(selectedIds.value)
  s.has(id) ? s.delete(id) : s.add(id)
  selectedIds.value = s
}

const printPdf = () => {
  const selected = data.value.filter(row => selectedIds.value.has(row.RECORD_ID))
  if (!selected.length) return

  const columns = [
    { key: 'positions',  label: 'Posizioni',            fmt: (v) => Array.isArray(v) ? v.join(', ') : (v || '—') },
    { key: 'OPASS',      label: 'Assieme' },
    { key: 'RECORD_ID',  label: 'Ord. Prod.' },
    { key: 'OPART',      label: 'Articolo' },
    { key: 'OPDNR',      label: 'Nr. Documento' },
    { key: 'DTRIC',      label: 'Rif. Cliente' },
    { key: 'OPRAS',      label: 'Ragione Sociale' },
    { key: 'OPCMM',      label: 'Commessa' },
    { key: 'OPUMP',      label: 'U. M.' },
    { key: 'OPQTA',      label: 'Q.tà' },
    { key: 'OPQTP',      label: 'Q.tà prod.' },
    { key: 'OPQTD',      label: 'Q.tà da prod.' },
    { key: 'FASI',       label: 'Fasi incomplete' },
    { key: 'ARMAT',      label: 'Materiale art.' },
    { key: 'DRCON',      label: 'Cons. ns. magazzino', fmt: formatDate },
    { key: 'DRCOR',      label: 'Cons. richiesta',     fmt: formatDate },
  ]

  const thead = columns.map(c => `<th>${c.label}</th>`).join('')
  const tbody = selected.map(row =>
    '<tr>' + columns.map(c => {
      const val = row[c.key] ?? ''
      return `<td>${c.fmt ? c.fmt(val) : (val ?? '')}</td>`
    }).join('') + '</tr>'
  ).join('')

  const html = `<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Stato Ordini di Produzione</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 8pt; padding: 12mm; }
    h1 { font-size: 11pt; margin-bottom: 8px; color: #1e3a5f; }
    p.meta { font-size: 7.5pt; color: #666; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e3a5f; color: #fff; padding: 4px 5px; text-align: left; font-size: 7pt; white-space: nowrap; }
    td { padding: 3px 5px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    tr:nth-child(even) td { background: #f8fafc; }
    @media print { @page { size: A4 landscape; margin: 10mm; } }
  </style>
</head>
<body>
  <h1>Stato Ordini di Produzione</h1>
  <p class="meta">Stampato il ${new Date().toLocaleDateString('it-IT')} &mdash; ${selected.length} record selezionati</p>
  <table>
    <thead><tr>${thead}</tr></thead>
    <tbody>${tbody}</tbody>
  </table>
</body>
</html>`

  const w = window.open('', '_blank', 'width=1200,height=800')
  w.document.write(html)
  w.document.close()
  w.focus()
  w.onload = () => { w.print(); w.close() }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchData(page)
  }
}

let searchTimeout
watch([searchOpras, searchOpdnr, searchDrconFrom, searchDrconTo, searchDrcorFrom, searchDrcorTo, searchOpart], () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
})

onMounted(() => {
  fetchData()
})
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

      <div class="p-4 space-y-3">
        <!-- Riga 1: testi -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Ragione Sociale Cliente</label>
            <input type="text" v-model="searchOpras" placeholder="Ricerca parziale…"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Nr. Ordine di Vendita</label>
            <input type="text" v-model="searchOpdnr" placeholder="Numero esatto…"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
          <div>
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-700 mb-1">Codice Articolo</label>
            <input type="text" v-model="searchOpart" placeholder="Codice esatto…"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
          </div>
        </div>
        <!-- Riga 2: range date -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="border border-gray-200 rounded-lg p-3">
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-600 mb-2">Cons. Ns. Magazzino</label>
            <div class="flex items-center gap-2">
              <div class="flex-1">
                <label class="block text-[10px] text-gray-500 mb-1">Dal</label>
                <input type="date" v-model="searchDrconFrom"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
              </div>
              <span class="text-gray-400 text-xs mt-4">→</span>
              <div class="flex-1">
                <label class="block text-[10px] text-gray-500 mb-1">Al</label>
                <input type="date" v-model="searchDrconTo"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
              </div>
            </div>
          </div>
          <div class="border border-gray-200 rounded-lg p-3">
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-600 mb-2">Cons. Richiesta dal Cliente</label>
            <div class="flex items-center gap-2">
              <div class="flex-1">
                <label class="block text-[10px] text-gray-500 mb-1">Dal</label>
                <input type="date" v-model="searchDrcorFrom"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
              </div>
              <span class="text-gray-400 text-xs mt-4">→</span>
              <div class="flex-1">
                <label class="block text-[10px] text-gray-500 mb-1">Al</label>
                <input type="date" v-model="searchDrcorTo"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-copam-blue focus:border-copam-blue" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TABELLA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

      <!-- Info risultati -->
      <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between bg-gray-50">
        <span class="text-xs text-gray-500">
          <span v-if="loading">Caricamento...</span>
          <span v-else>
            Mostrando <span class="font-semibold text-gray-700">{{ pagination.from }} - {{ pagination.to }}</span>
            di <span class="font-semibold text-gray-700">{{ pagination.total }}</span> record
          </span>
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="bg-copam-blue text-white">
              <th class="px-3 py-2.5 border-r border-blue-400/40 w-8"></th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Posizioni</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Assieme</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Ord. Prod.</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Articolo</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Nr. Documento</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Rif. Cliente</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Ragione Sociale</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Commessa</th>
              <th class="px-3 py-2.5 text-center font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">U. M.</th>
              <th class="px-3 py-2.5 text-right font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Q.tà</th>
              <th class="px-3 py-2.5 text-right font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Q.tà prod.</th>
              <th class="px-3 py-2.5 text-right font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Q.tà da prod.</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Fasi incomplete</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Materiale art.</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider border-r border-blue-400/40 whitespace-nowrap">Cons. ns. magazzino</th>
              <th class="px-3 py-2.5 text-left font-semibold uppercase tracking-wider whitespace-nowrap">Cons. richiesta</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="17" class="px-3 py-10 text-center text-gray-400">
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
            <tr v-else v-for="(row, index) in data" :key="index"
              :class="[
                'transition-colors',
                index % 2 === 0 ? 'bg-white hover:bg-blue-50' : 'bg-gray-50/60 hover:bg-blue-50'
              ]">
              <td class="px-3 py-2 text-center border-r border-gray-100 w-8">
                <input
                  type="checkbox"
                  :checked="selectedIds.has(row.RECORD_ID)"
                  @change="toggleRow(row.RECORD_ID)"
                  class="rounded border-gray-300 text-copam-blue cursor-pointer"
                />
              </td>
              <td class="px-3 py-2 border-r border-gray-100">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="pos in row.positions" :key="pos"
                    class="inline-block px-1.5 py-0.5 rounded bg-copam-blue/10 text-copam-blue font-semibold text-[10px] whitespace-nowrap"
                  >{{ pos }}</span>
                  <span v-if="!row.positions?.length" class="text-gray-300">&mdash;</span>
                </div>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-800 border-r border-gray-100">{{ row.OPASS }}</td>
              <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-800 border-r border-gray-100">{{ row.RECORD_ID }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.OPART }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.OPDNR }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.DTRIC }}</td>
              <td class="px-3 py-2 text-gray-700 border-r border-gray-100">{{ row.OPRAS }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.OPCMM }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-center text-gray-700 border-r border-gray-100">{{ row.OPUMP }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 border-r border-gray-100">{{ row.OPQTA }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 border-r border-gray-100">{{ row.OPQTP }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700 border-r border-gray-100">{{ row.OPQTD }}</td>
              <td class="px-3 py-2 text-gray-700 border-r border-gray-100">{{ row.FASI }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ row.ARMAT }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700 border-r border-gray-100">{{ formatDate(row.DRCON) }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-gray-700">{{ formatDate(row.DRCOR) }}</td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!loading && !data.length">
              <td colspan="17" class="px-3 py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-xs font-medium text-gray-400">Nessun record trovato</p>
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

    <!-- BOTTONE STAMPA -->
    <div class="flex justify-end">
      <button
        @click="printPdf"
        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        :disabled="selectedIds.size === 0"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Stampa
        <span v-if="selectedIds.size > 0" class="bg-green-800 text-white text-xs rounded-full px-1.5 py-0.5">{{ selectedIds.size }}</span>
      </button>
    </div>

  </div>
</template>
