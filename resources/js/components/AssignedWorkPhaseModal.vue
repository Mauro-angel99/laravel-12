<template>
  <!-- Main Modal -->
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
                  Fase di Lavoro
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
                  <button
                    @click="activeTab = 'immagini'"
                    :class="[
                      activeTab === 'immagini'
                        ? 'border-copam-blue text-copam-blue'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                  >
                    Immagini
                  </button>
                  <button
                    @click="activeTab = 'pdf'"
                    :class="[
                      activeTab === 'pdf'
                        ? 'border-copam-blue text-copam-blue'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                  >
                    PDF
                  </button>
                  <button
                    @click="activeTab = 'nc'"
                    :class="[
                      activeTab === 'nc'
                        ? 'border-copam-blue text-copam-blue'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                      'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                  >
                    N.C.
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

              <!-- Tab Parametri -->
              <div v-show="activeTab === 'parametri'">
                <div class="space-y-4">
                  <!-- Campi informativi -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Codice Lavorazione (FLLAV)
                      </label>
                      <input
                        :value="assignment?.work_phase?.FLLAV || 'N/D'"
                        type="text"
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Codice Articolo (OPART)
                      </label>
                      <input
                        :value="assignment?.work_phase?.OPART || 'N/D'"
                        type="text"
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600"
                      />
                    </div>
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
                  <div v-else-if="!selectedParameter || !selectedParameter.fields || selectedParameter.fields.length === 0" class="mt-6">
                    <p class="text-sm text-gray-500 text-center py-4 bg-gray-50 rounded-md">
                      Nessun parametro configurato per questa lavorazione
                    </p>
                  </div>
                </div>
              </div>

              <!-- Tab Immagini -->
              <div v-show="activeTab === 'immagini'" class="space-y-4">
                <!-- Upload Section -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6"
                  @drop.prevent="handleDrop"
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  :class="{ 'border-copam-blue bg-blue-50': isDragging }">
                  
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mt-4">
                      <label class="cursor-pointer">
                        <span class="mt-2 block text-sm font-medium text-gray-900">
                          Trascina le immagini qui o 
                          <span class="text-copam-blue hover:underline">sfoglia</span>
                        </span>
                        <input
                          ref="fileInput"
                          type="file"
                          multiple
                          accept="image/*"
                          @change="handleFileSelect"
                          class="hidden"
                        />
                      </label>
                      <p class="mt-1 text-xs text-gray-500">
                        PNG, JPG, GIF fino a 10MB (max 10 immagini)
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Selected Files Preview -->
                <div v-if="selectedFiles.length > 0" class="mt-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-2">File selezionati ({{ selectedFiles.length }})</h4>
                  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <div v-for="(item, index) in selectedFiles" :key="index" class="relative group">
                      <img :src="item.preview" :alt="item.name" class="w-full h-24 object-cover rounded" />
                      <button
                        @click="removeSelectedFile(index)"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                      <p class="text-xs text-gray-600 mt-1 truncate">{{ item.name }}</p>
                    </div>
                  </div>
                  <button
                    @click="uploadImages"
                    :disabled="uploading"
                    class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-copam-blue hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg v-if="uploading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ uploading ? 'Caricamento...' : 'Carica Immagini' }}
                  </button>
                </div>

                <!-- Existing Images Gallery -->
                <div v-if="loadingImages" class="text-center py-8">
                  <svg class="animate-spin h-8 w-8 text-copam-blue mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Caricamento immagini...</p>
                </div>

                <div v-else-if="images.length > 0" class="mt-6">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Immagini caricate ({{ images.length }})</h4>
                  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <div v-for="image in images" :key="image.id" class="relative group">
                      <img
                        :src="image.url"
                        :alt="image.file_name"
                        @click="viewImage(image)"
                        class="w-full h-24 object-cover rounded cursor-pointer hover:opacity-75 transition-opacity"
                      />
                      <button
                        @click="deleteImage(image.id)"
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                      <p class="text-xs text-gray-600 mt-1 truncate">{{ image.file_name }}</p>
                    </div>
                  </div>
                </div>

                <div v-else-if="!loadingImages" class="mt-6">
                  <div class="text-center py-8 bg-gray-50 rounded-md">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nessuna immagine caricata</p>
                  </div>
                </div>
              </div>

              <!-- Tab PDF -->
              <div v-show="activeTab === 'pdf'" class="space-y-4">
                <!-- Loading PDF -->
                <div v-if="loadingPdf" class="text-center py-8">
                  <svg class="animate-spin h-8 w-8 text-copam-blue mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Caricamento PDF...</p>
                </div>

                <!-- PDF Viewer -->
                <div v-else-if="pdfUrl" class="w-full">
                  <div class="bg-gray-100 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                      <h4 class="text-sm font-medium text-gray-900">{{ assignment?.work_phase?.OPART }}.pdf</h4>
                      <a :href="pdfUrl" target="_blank" class="text-copam-blue hover:underline text-sm">
                        Apri in nuova scheda
                      </a>
                    </div>
                    <iframe
                      :src="pdfUrl"
                      class="w-full border-0 rounded"
                      style="height: 600px;"
                    ></iframe>
                  </div>
                </div>

                <!-- No PDF Available -->
                <div v-else class="text-center py-8">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Nessun PDF disponibile</p>
                  <p class="mt-1 text-xs text-gray-400">Cerca: {{ assignment?.work_phase?.OPART }}.pdf</p>
                </div>
              </div>

              <!-- Tab N.C. -->
              <div v-show="activeTab === 'nc'" class="space-y-4">
                <!-- Loading NC -->
                <div v-if="loadingNC" class="text-center py-8">
                  <svg class="animate-spin h-8 w-8 text-copam-blue mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Caricamento non conformit\u00e0...</p>
                </div>

                <!-- NC Table -->
                <div v-else-if="nonConformities.length > 0" class="overflow-x-auto">
                  <table class="w-full">
                    <thead class="bg-gray-50">
                      <tr class="border-b border-gray-200">
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">NCRIL</th>
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">NCCLA</th>
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-200">NCART</th>
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Descrizione</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white">
                      <tr v-for="(nc, index) in nonConformities" :key="index" class="hover:bg-gray-100 border-b border-gray-200 transition-colors">
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">{{ nc.NCRIL }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">{{ nc.NCCLA }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 border-r border-gray-200">{{ nc.NCART }}</td>
                        <td class="px-3 py-2 text-xs text-gray-900">{{ nc.NCDES }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <p class="mt-2 text-xs text-gray-500">Articolo: {{ assignment?.work_phase?.OPART }}</p>
                </div>

                <!-- No NC -->
                <div v-else class="text-center py-8">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Nessuna non conformit\u00e0</p>
                  <p class="mt-1 text-xs text-gray-400">Articolo: {{ assignment?.work_phase?.OPART }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
        <!-- Modal Footer -->
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

  <!-- Modal Conferma Eliminazione -->
  <div v-if="confirmModal.show" class="fixed inset-0 z-[60] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <div 
        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        @click="confirmModal.show = false"
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
              {{ confirmModal.title }}
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                {{ confirmModal.message }}
              </p>
            </div>
          </div>
        </div>
        
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button 
            type="button"
            @click="confirmModal.onConfirm"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Ok
          </button>
          <button 
            type="button"
            @click="confirmModal.show = false"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Annulla
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

  <!-- Lightbox Modal -->
  <div v-if="lightbox.show" class="fixed inset-0 z-[70] overflow-hidden bg-black bg-opacity-95">
    <div class="flex items-center justify-center h-full">
      <!-- Close Button -->
      <button
        @click="closeLightbox"
        class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10"
      >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>

      <!-- Previous Button -->
      <button
        v-if="images.length > 1"
        @click="previousImage"
        class="absolute left-4 text-white hover:text-gray-300 transition-colors z-10"
      >
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
      </button>

      <!-- Image -->
      <div class="max-w-7xl max-h-full p-4">
        <img
          :src="lightbox.currentImage?.url"
          :alt="lightbox.currentImage?.file_name"
          class="max-w-full max-h-[90vh] object-contain mx-auto"
        />
        <!-- Image Info -->
        <div class="text-center mt-4">
          <p class="text-white text-sm">{{ lightbox.currentImage?.file_name }}</p>
          <p v-if="images.length > 1" class="text-gray-400 text-xs mt-1">
            {{ lightbox.currentIndex + 1 }} / {{ images.length }}
          </p>
        </div>
      </div>

      <!-- Next Button -->
      <button
        v-if="images.length > 1"
        @click="nextImage"
        class="absolute right-4 text-white hover:text-gray-300 transition-colors z-10"
      >
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>

      <!-- Thumbnails (if more than one image) -->
      <div v-if="images.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 max-w-full overflow-x-auto px-4 scrollbar-hide">
        <button
          v-for="(image, index) in images"
          :key="image.id"
          @click="lightbox.currentIndex = index"
          :class="[
            'flex-shrink-0 w-16 h-16 rounded border-2 transition-all',
            index === lightbox.currentIndex 
              ? 'border-copam-blue scale-110' 
              : 'border-gray-600 opacity-60 hover:opacity-100'
          ]"
        >
          <img
            :src="image.url"
            :alt="image.file_name"
            class="w-full h-full object-cover rounded"
          />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
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

// Image upload state
const images = ref([]);
const selectedFiles = ref([]);
const fileInput = ref(null);
const uploading = ref(false);
const loadingImages = ref(false);
const isDragging = ref(false);

// PDF state
const pdfUrl = ref(null);
const loadingPdf = ref(false);

// Non Conformity state
const nonConformities = ref([]);
const loadingNC = ref(false);

// Lightbox state
const lightbox = ref({
  show: false,
  currentIndex: 0,
  currentImage: null
});

const messageModal = ref({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

const confirmModal = ref({
  show: false,
  title: '',
  message: '',
  onConfirm: null
});

const showMessageModal = (type, title, message) => {
  messageModal.value = { show: true, type, title, message };
};

const closeMessageModal = () => {
  messageModal.value.show = false;
};

const showConfirmModal = (title, message, onConfirm) => {
  confirmModal.value = {
    show: true,
    title,
    message,
    onConfirm: () => {
      confirmModal.value.show = false;
      onConfirm();
    }
  };
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
    await fetchImages();
    await checkPdf();
    await fetchNonConformities();
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
  selectedFiles.value = []
  images.value = []
  pdfUrl.value = null
  nonConformities.value = []
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

// PDF management functions
const checkPdf = async () => {
  const artCode = props.assignment?.work_phase?.OPART;
  
  if (!artCode) {
    pdfUrl.value = null;
    return;
  }
  
  loadingPdf.value = true;
  try {
    // Cerca il PDF nella cartella storage/app/public/work_phase_pdfs con nome = OPART.pdf
    const pdfPath = `/storage/work_phase_pdfs/${artCode}.pdf`;
    
    // Verifica se il file esiste facendo una HEAD request
    const response = await fetch(pdfPath, { method: 'HEAD' });
    
    if (response.ok) {
      pdfUrl.value = pdfPath;
    } else {
      pdfUrl.value = null;
    }
  } catch (error) {
    console.error('Errore nel controllo del PDF:', error);
    pdfUrl.value = null;
  } finally {
    loadingPdf.value = false;
  }
};

// Non Conformity management functions
const fetchNonConformities = async () => {
  const idopr = props.assignment?.work_phase?.IDOPR;
  
  if (!idopr) {
    nonConformities.value = [];
    return;
  }
  
  loadingNC.value = true;
  try {
    const res = await axios.get('/api/non-conformities', {
      params: { idopr: idopr }
    });
    nonConformities.value = res.data;
  } catch (error) {
    console.error('Errore nel caricamento delle non conformit\u00e0:', error);
    nonConformities.value = [];
  } finally {
    loadingNC.value = false;
  }
};

// Image management functions
const fetchImages = async () => {
  const jobCode = props.assignment?.work_phase?.FLLAV;
  const artCode = props.assignment?.work_phase?.OPART;
  
  if (!jobCode || !artCode) return;
  
  loadingImages.value = true;
  try {
    const res = await axios.get('/api/work-phase-images', {
      params: { fllav: jobCode, opart: artCode }
    });
    images.value = res.data;
  } catch (error) {
    console.error('Errore nel caricamento delle immagini:', error);
    showMessageModal('error', 'Errore', 'Errore nel caricamento delle immagini');
  } finally {
    loadingImages.value = false;
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const files = Array.from(event.dataTransfer.files);
  
  // Filtra solo le immagini
  const imageFiles = files.filter(file => file.type.startsWith('image/'));
  
  if (imageFiles.length === 0) {
    showMessageModal('error', 'Errore', 'Nessun file immagine trovato');
    return;
  }
  
  if (selectedFiles.value.length + imageFiles.length > 10) {
    showMessageModal('error', 'Errore', 'Puoi caricare massimo 10 immagini alla volta');
    return;
  }
  
  imageFiles.forEach(file => {
    if (file.size > 10 * 1024 * 1024) {
      showMessageModal('error', 'Errore', `Il file ${file.name} supera i 10MB`);
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
      selectedFiles.value.push({
        file: file,
        name: file.name,
        preview: e.target.result
      });
    };
    reader.readAsDataURL(file);
  });
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  
  if (selectedFiles.value.length + files.length > 10) {
    showMessageModal('error', 'Errore', 'Puoi caricare massimo 10 immagini alla volta');
    return;
  }
  
  files.forEach(file => {
    if (file.size > 10 * 1024 * 1024) {
      showMessageModal('error', 'Errore', `Il file ${file.name} supera i 10MB`);
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
      selectedFiles.value.push({
        file: file,
        name: file.name,
        preview: e.target.result
      });
    };
    reader.readAsDataURL(file);
  });
  
  // Reset input
  event.target.value = '';
};

const removeSelectedFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

const uploadImages = async () => {
  const jobCode = props.assignment?.work_phase?.FLLAV;
  const artCode = props.assignment?.work_phase?.OPART;
  
  if (!jobCode || !artCode) {
    showMessageModal('error', 'Errore', 'Codici lavorazione e articolo mancanti');
    return;
  }
  
  if (selectedFiles.value.length === 0) {
    showMessageModal('error', 'Errore', 'Seleziona almeno un\'immagine');
    return;
  }
  
  uploading.value = true;
  try {
    const formData = new FormData();
    formData.append('fllav', jobCode);
    formData.append('opart', artCode);
    
    selectedFiles.value.forEach((item, index) => {
      formData.append(`images[${index}]`, item.file);
    });
    
    const res = await axios.post('/api/work-phase-images', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    showMessageModal('success', 'Successo', res.data.message);
    selectedFiles.value = [];
    await fetchImages();
    
  } catch (error) {
    console.error('Errore upload immagini:', error);
    const errorMsg = error.response?.data?.message || 'Errore durante il caricamento';
    showMessageModal('error', 'Errore', errorMsg);
  } finally {
    uploading.value = false;
  }
};

const deleteImage = async (imageId) => {
  showConfirmModal(
    'Elimina immagine',
    'Sei sicuro di voler eliminare questa immagine? Questa azione non può essere annullata.',
    async () => {
      try {
        await axios.delete(`/api/work-phase-images/${imageId}`);
        showMessageModal('success', 'Successo', 'Immagine eliminata con successo');
        await fetchImages();
      } catch (error) {
        console.error('Errore eliminazione immagine:', error);
        showMessageModal('error', 'Errore', 'Errore durante l\'eliminazione');
      }
    }
  );
};

const viewImage = (image) => {
  const index = images.value.findIndex(img => img.id === image.id);
  lightbox.value = {
    show: true,
    currentIndex: index,
    currentImage: image
  };
};

const closeLightbox = () => {
  lightbox.value.show = false;
};

const nextImage = () => {
  lightbox.value.currentIndex = (lightbox.value.currentIndex + 1) % images.value.length;
  lightbox.value.currentImage = images.value[lightbox.value.currentIndex];
};

const previousImage = () => {
  lightbox.value.currentIndex = (lightbox.value.currentIndex - 1 + images.value.length) % images.value.length;
  lightbox.value.currentImage = images.value[lightbox.value.currentIndex];
};

// Watch per aggiornare l'immagine corrente quando cambia l'indice
watch(() => lightbox.value.currentIndex, (newIndex) => {
  if (lightbox.value.show && images.value[newIndex]) {
    lightbox.value.currentImage = images.value[newIndex];
  }
});

// Gestione tasti freccia per navigare nel carosello
watch(() => lightbox.value.show, (isShown) => {
  if (isShown) {
    document.addEventListener('keydown', handleKeydown);
  } else {
    document.removeEventListener('keydown', handleKeydown);
  }
});

const handleKeydown = (event) => {
  if (!lightbox.value.show) return;
  
  if (event.key === 'ArrowRight') {
    nextImage();
  } else if (event.key === 'ArrowLeft') {
    previousImage();
  } else if (event.key === 'Escape') {
    closeLightbox();
  }
};
</script>