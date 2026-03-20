<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" aria-hidden="true" @click="close"></div>

      <!-- Modal panel -->
      <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">

        <!-- Header -->
        <div class="bg-copam-blue px-6 py-4 flex justify-between items-center">
          <div>
            <h3 class="text-base font-semibold text-white" id="modal-title">Dettagli Fase di Lavoro</h3>
            <p class="text-xs text-blue-200 mt-0.5">ID: {{ phase?.RECORD_ID }}</p>
          </div>
          <button
            type="button"
            class="text-blue-200 hover:text-white transition-colors"
            @click="close"
            aria-label="Chiudi"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-5">

          <!-- Sezione Identificazione -->
          <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-700 mb-2">Identificazione</p>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Commessa</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.FLASS || '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Ord. Prod.</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.IDOPR || '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Sequenza</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.FLSEQ || '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Lavorazione</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.FLLAV || '—' }}</p>
              </div>
            </div>
          </div>

          <!-- Sezione Descrizione & Quantità -->
          <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-700 mb-2">Descrizione & Quantità</p>
            <div class="grid grid-cols-3 gap-3">
              <div class="col-span-3 bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Descrizione</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.FLDES || '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Q.tà Totale</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ phase?.FLQTA ?? '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Q.tà Prodotta</p>
                <p class="text-lg font-bold text-green-600 mt-0.5">{{ phase?.FLQTB ?? '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Q.tà Residua</p>
                <p class="text-lg font-bold text-orange-500 mt-0.5">{{ phase?.FLQTD ?? '—' }}</p>
              </div>
            </div>
          </div>

          <!-- Sezione Materiale & Date -->
          <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-700 mb-2">Materiale & Date</p>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Materiale</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.MATERIALE || '—' }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Spessore</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ phase?.SPESSORE || '—' }}</p>
              </div>
              <div class="col-span-2 bg-gray-50 rounded-lg px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-700">Data Consegna</p>
                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ formatDate(phase?.FLCON) || '—' }}</p>
              </div>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="border-t border-gray-100 px-6 py-3 flex justify-between items-center bg-gray-50">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="modelValue.includes(phase?.RECORD_ID)"
              @change="toggleSelection(phase?.RECORD_ID)"
              class="w-4 h-4 rounded border-gray-300 text-copam-blue focus:ring-copam-blue cursor-pointer"
            />
            <span class="text-sm font-medium text-gray-700">Seleziona</span>
          </label>
          <button
            type="button"
            class="px-5 py-2 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition-colors shadow-sm"
            @click="close"
          >
            Chiudi
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  phase: {
    type: Object,
    required: true
  },
  modelValue: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:show', 'update:modelValue'])

const close = () => {
  emit('update:show', false)
}

const toggleSelection = (recordId) => {
  const newSelection = [...props.modelValue]
  const index = newSelection.indexOf(recordId)
  
  if (index === -1) {
    newSelection.push(recordId)
  } else {
    newSelection.splice(index, 1)
  }
  
  emit('update:modelValue', newSelection)
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}
</script>
