<template>
  <!-- Main Modal -->
  <div v-if="show && item" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="close"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <h3 class="text-base font-semibold text-white" id="modal-title">Distinta Base</h3>
            <span v-if="item?.DBART" class="bg-white/20 text-white text-xs font-mono px-2 py-0.5 rounded">{{ item.DBART }}</span>
            <span v-if="item?.DLLAV" class="bg-white/20 text-white text-xs font-mono px-2 py-0.5 rounded">{{ item.DLLAV }}</span>
          </div>
          <button @click="close" class="text-white/70 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="px-6 pt-4 pb-4">
          <div class="w-full">
            <!-- Tabs -->
            <div class="border-b border-gray-200 overflow-x-auto overflow-y-hidden">
              <nav class="-mb-px flex space-x-6 min-w-max" aria-label="Tabs">
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
              <div v-show="activeTab === 'dettagli'" class="space-y-3">

                <!-- Sezione: Identificazione -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                  <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Identificazione</h4>
                  </div>
                  <div class="grid grid-cols-2 sm:grid-cols-3 gap-px bg-gray-100">
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DLACT</p>
                      <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DLACT || '&mdash;' }}</p>
                    </div>
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DLSEQ</p>
                      <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DLSEQ || '&mdash;' }}</p>
                    </div>
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DBART</p>
                      <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DBART || '&mdash;' }}</p>
                    </div>
                  </div>
                </div>

                <!-- Sezione: Descrizione & Lavorazione -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                  <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Descrizione &amp; Lavorazione</h4>
                  </div>
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-xs text-gray-700 uppercase tracking-wide">LVDES</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.LVDES || '&mdash;' }}</p>
                  </div>
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-xs text-gray-700 uppercase tracking-wide">DLNOT2</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DLNOT2 || '&mdash;' }}</p>
                  </div>
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-gray-100">
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DLLAV</p>
                      <span class="mt-1 inline-block bg-blue-100 text-copam-blue text-sm font-semibold px-3 py-0.5 rounded-full">{{ item?.DLLAV || '&mdash;' }}</span>
                    </div>
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DLTAP</p>
                      <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DLTAP || '&mdash;' }}</p>
                    </div>
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DLTMP</p>
                      <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DLTMP || '&mdash;' }}</p>
                    </div>
                    <div class="bg-white px-4 py-3">
                      <p class="text-xs text-gray-700 uppercase tracking-wide">DLTUP</p>
                      <p class="mt-0.5 text-sm font-medium text-gray-800">{{ item?.DLTUP || '&mdash;' }}</p>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Tab Parametri -->
              <div v-show="activeTab === 'parametri'">
                <div class="space-y-4">
                  <!-- Campi informativi -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Codice Lavorazione (DLLAV)
                      </label>
                      <input
                        :value="item?.DLLAV || 'N/D'"
                        type="text"
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Codice Articolo (DBART)
                      </label>
                      <input
                        :value="item?.DBART || 'N/D'"
                        type="text"
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600"
                      />
                    </div>
                  </div>

                  <!-- Form campi dinamici -->
                  <div v-if="selectedParameter && selectedParameter.fields && selectedParameter.fields.length > 0" class="mt-6 space-y-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Compila i campi del parametro</h4>
                    <div class="grid grid-cols-2 gap-4">
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
                  <div v-else-if="!selectedParameter || !selectedParameter.fields || selectedParameter.fields.length === 0" class="mt-6">
                    <p class="text-sm text-gray-500 text-center py-4 bg-gray-50 rounded-md">
                      Nessun parametro configurato per questa lavorazione
                    </p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-3 flex justify-end">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue transition-colors"
            @click="close"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
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
import { ref, watch, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  item: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:show'])

const activeTab = ref('dettagli')
const parameters = ref([])
const selectedParameterId = ref(null)
const parameterValues = ref({})
const saving = ref(false)

const messageModal = ref({
  show: false,
  type: 'success',
  title: '',
  message: ''
})

const showMessageModal = (type, title, message) => {
  messageModal.value = { show: true, type, title, message }
}

const closeMessageModal = () => {
  messageModal.value.show = false
}

const selectedParameter = computed(() => {
  return parameters.value.find(p => p.id === selectedParameterId.value)
})

const fetchParameters = async () => {
  try {
    const res = await axios.get('/api/work-parameters')
    parameters.value = res.data

    // Auto-seleziona il parametro basato su DLLAV
    if (props.item?.DLLAV) {
      const matchingParam = parameters.value.find(p => p.name === props.item.DLLAV)
      if (matchingParam) {
        selectedParameterId.value = matchingParam.id
      }
    }
  } catch (error) {
    console.error('Errore nel caricamento dei parametri:', error)
  }
}

const loadParameterValues = async () => {
  const dllav = props.item?.DLLAV
  const dbart = props.item?.DBART

  if (!dllav || !dbart) return

  try {
    const res = await axios.get('/api/bom-parameter-values', {
      params: { dllav, dbart }
    })

    if (res.data.parameter_values) {
      parameterValues.value = res.data.parameter_values
    }
  } catch (error) {
    console.error('Errore nel caricamento dei valori parametri:', error)
  }
}

watch(() => props.show, async (newVal) => {
  if (newVal) {
    await fetchParameters()
    await loadParameterValues()
  }
})

watch(selectedParameterId, (newVal, oldVal) => {
  if (oldVal !== null && oldVal !== undefined) {
    parameterValues.value = {}
  }
})

const close = () => {
  emit('update:show', false)
  activeTab.value = 'dettagli'
  selectedParameterId.value = null
  parameterValues.value = {}
}

const saveParameters = async () => {
  const dllav = props.item?.DLLAV
  const dbart = props.item?.DBART

  if (!dllav || !dbart) {
    showMessageModal('error', 'Errore', 'Dati mancanti per il salvataggio')
    return
  }

  saving.value = true
  try {
    await axios.put('/api/bom-parameter-values', {
      dllav,
      dbart,
      parameter_values: parameterValues.value
    })

    showMessageModal('success', 'Successo', 'Parametri salvati con successo!')
  } catch (error) {
    console.error('Errore nel salvataggio dei parametri:', error)
    showMessageModal('error', 'Errore', 'Errore nel salvataggio dei parametri')
  } finally {
    saving.value = false
  }
}
</script>
