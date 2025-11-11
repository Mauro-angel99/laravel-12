<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="close"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
              <div class="flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Dettagli Work Phase
                </h3>
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium text-gray-500">Selezionato</label>
                  <input 
                    type="checkbox" 
                    :checked="modelValue.includes(phase?.RECORD_ID)"
                    @change="toggleSelection(phase?.RECORD_ID)"
                    class="rounded border-gray-300 text-copam-blue focus:ring-copam-blue"
                  />
                </div>
              </div>
              <div class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLASS</p>
                    <p class="mt-1">{{ phase?.FLASS }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">IDOPR</p>
                    <p class="mt-1">{{ phase?.IDOPR }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLSEQ</p>
                    <p class="mt-1">{{ phase?.FLSEQ }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLLAV</p>
                    <p class="mt-1">{{ phase?.FLLAV }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLDES</p>
                    <p class="mt-1">{{ phase?.FLDES }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLQTA</p>
                    <p class="mt-1">{{ phase?.FLQTA }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLQTB</p>
                    <p class="mt-1">{{ phase?.FLQTB }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLQTD</p>
                    <p class="mt-1">{{ phase?.FLQTD }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Data Consegna</p>
                    <p class="mt-1">{{ formatDate(phase?.FLCON) }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">MATERIALE</p>
                    <p class="mt-1">{{ phase?.MATERIALE }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">SPESSORE</p>
                    <p class="mt-1">{{ phase?.SPESSORE }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">LAV_SUCC</p>
                    <p class="mt-1">{{ phase?.LAV_SUCC }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">LAV_SUCC_ASS</p>
                    <p class="mt-1">{{ phase?.LAV_SUCC_ASS }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button 
            type="button" 
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue sm:mt-0 sm:w-auto sm:text-sm"
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
