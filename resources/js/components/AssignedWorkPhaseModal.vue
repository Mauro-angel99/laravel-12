<template>
  <div v-if="show && assignment" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                  Dettagli Fase di Lavoro
                </h3>
              </div>
              <div class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLASS</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLASS || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">IDOPR</p>
                    <p class="mt-1">{{ assignment?.work_phase?.IDOPR || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLSEQ</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLSEQ || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLLAV</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLLAV || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLDES</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLDES || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLQTA</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLQTA || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLQTB</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLQTB || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">FLQTD</p>
                    <p class="mt-1">{{ assignment?.work_phase?.FLQTD || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Data Consegna</p>
                    <p class="mt-1">{{ formatDate(assignment?.work_phase?.FLCON) }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">MATERIALE</p>
                    <p class="mt-1">{{ assignment?.work_phase?.MATERIALE || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">SPESSORE</p>
                    <p class="mt-1">{{ assignment?.work_phase?.SPESSORE || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">LAV_SUCC</p>
                    <p class="mt-1">{{ assignment?.work_phase?.LAV_SUCC || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">LAV_SUCC_ASS</p>
                    <p class="mt-1">{{ assignment?.work_phase?.LAV_SUCC_ASS || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Assegnato a</p>
                    <p class="mt-1">{{ assignment?.assigned_user?.name || 'N/D' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Data Assegnazione</p>
                    <p class="mt-1">{{ formatDate(assignment?.created_at) }}</p>
                  </div>
                  <div class="col-span-2">
                    <p class="text-sm font-medium text-gray-500">Note</p>
                    <p class="mt-1">{{ assignment?.notes || '-' }}</p>
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
const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  assignment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:show'])

const close = () => {
  emit('update:show', false)
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
