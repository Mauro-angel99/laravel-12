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

              <!-- Tabs -->
              <div class="mt-4 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                  <button
                    @click="activeTab = 'dettagli'"
                    :class="[
                      activeTab === 'dettagli'
                        ? 'border-copam-blue text-copam-blue'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                  >
                    Dettagli
                  </button>
                  <button
                    @click="activeTab = 'parametri'"
                    :class="[
                      activeTab === 'parametri'
                        ? 'border-copam-blue text-copam-blue'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                  >
                    Parametri
                  </button>
                </nav>
              </div>

              <!-- Tab Content -->
              <div class="mt-4">
                <!-- Tab Dettagli -->
                <div v-show="activeTab === 'dettagli'" class="space-y-4">
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
                    <p class="text-sm font-medium text-gray-500">OPART</p>
                    <p class="mt-1">{{ assignment?.work_phase?.OPART || 'N/D' }}</p>
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

              <!-- Tab Parametri -->
              <div v-show="activeTab === 'parametri'">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Seleziona Parametro di Lavorazione
                    </label>
                    <select
                      v-model="selectedParameterId"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                    >
                      <option :value="null">-- Seleziona un parametro --</option>
                      <option
                        v-for="parameter in parameters"
                        :key="parameter.id"
                        :value="parameter.id"
                      >
                        {{ parameter.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Form campi dinamici -->
                  <div v-if="selectedParameter && selectedParameter.fields && selectedParameter.fields.length > 0" class="mt-6 space-y-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Compila i campi del parametro</h4>
                    <div
                      v-for="(field, index) in selectedParameter.fields"
                      :key="index"
                    >
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ field }}
                      </label>
                      <input
                        v-model="parameterValues[field]"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                      />
                    </div>
                    
                    <!-- Bottone Salva -->
                    <div class="flex justify-end pt-4">
                      <button
                        @click="saveParameters"
                        :disabled="saving"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-copam-blue text-base font-medium text-white hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed sm:text-sm"
                      >
                        {{ saving ? 'Salvataggio...' : 'Salva Parametri' }}
                      </button>
                    </div>
                  </div>

                  <!-- Messaggio quando non ci sono campi -->
                  <div v-else-if="selectedParameter && (!selectedParameter.fields || selectedParameter.fields.length === 0)" class="mt-6">
                    <p class="text-sm text-gray-500 text-center py-4 bg-gray-50 rounded-md">
                      Questo parametro non ha campi configurati
                    </p>
                  </div>
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

  <!-- Modal Messaggio -->
  <div v-if="messageModal.show" class="fixed inset-0 z-[60] overflow-y-auto">
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
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

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

const activeTab = ref('dettagli');
const parameters = ref([]);
const selectedParameterId = ref(null);
const parameterValues = ref({});
const saving = ref(false);

const messageModal = ref({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

const showMessageModal = (type, title, message) => {
  messageModal.value = { show: true, type, title, message };
};

const closeMessageModal = () => {
  messageModal.value.show = false;
};

const selectedParameter = computed(() => {
  return parameters.value.find(p => p.id === selectedParameterId.value);
});

const fetchParameters = async () => {
  try {
    const res = await axios.get('/api/work-parameters');
    parameters.value = res.data;
    
    // Auto-seleziona il parametro basato su FLLAV se disponibile
    if (props.assignment?.work_phase?.FLLAV) {
      const matchingParam = parameters.value.find(p => p.name === props.assignment.work_phase.FLLAV);
      if (matchingParam) {
        selectedParameterId.value = matchingParam.id;
      }
    }
  } catch (error) {
    console.error('Errore nel caricamento dei parametri:', error);
  }
};

const loadParameterValues = async () => {
  const jobCode = props.assignment?.work_phase?.FLLAV;
  const artCode = props.assignment?.work_phase?.OPART;
  
  if (!jobCode || !artCode) {
    return;
  }
  
  try {
    const res = await axios.get('/api/job-parameter-values', {
      params: { job_code: jobCode, art_code: artCode }
    });
    
    if (res.data.parameter_values) {
      parameterValues.value = res.data.parameter_values;
    }
  } catch (error) {
    console.error('Errore nel caricamento dei valori parametri:', error);
  }
};

watch(() => props.show, async (newVal) => {
  if (newVal) {
    await fetchParameters();
    await loadParameterValues();
  }
});

watch(selectedParameterId, (newVal, oldVal) => {
  // Reset dei valori solo se c'era un valore precedente (cambio manuale)
  // Non resettare se è il primo caricamento (oldVal è null/undefined)
  if (oldVal !== null && oldVal !== undefined) {
    parameterValues.value = {};
  }
});

const close = () => {
  emit('update:show', false)
  activeTab.value = 'dettagli'
  selectedParameterId.value = null
  parameterValues.value = {}
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

const saveParameters = async () => {
  const jobCode = props.assignment?.work_phase?.FLLAV;
  const artCode = props.assignment?.work_phase?.OPART;
  
  if (!props.assignment?.id || !jobCode || !artCode) {
    showMessageModal('error', 'Errore', 'Dati mancanti per il salvataggio');
    return;
  }
  
  saving.value = true;
  try {
    await axios.put(`/api/work-phase-assignments/${props.assignment.id}/parameters`, {
      job_code: jobCode,
      art_code: artCode,
      parameter_values: parameterValues.value
    });
    
    showMessageModal('success', 'Successo', 'Parametri salvati con successo!');
  } catch (error) {
    console.error('Errore nel salvataggio dei parametri:', error);
    showMessageModal('error', 'Errore', 'Errore nel salvataggio dei parametri');
  } finally {
    saving.value = false;
  }
}
</script>
