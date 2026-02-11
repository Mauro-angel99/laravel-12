<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useModal } from '@/composables/useModal';
import { useWorkParameters, type WorkParameter } from '@/composables/useWorkParameters';

const { parameters, loading, fetchParameters, createParameter, updateParameter, deleteParameter } = useWorkParameters();
const { modalState, showSuccess, showError, closeModal } = useModal();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedParameter = ref<WorkParameter | null>(null);
const formData = ref<{ name: string; fields: string[] }>({
    name: '',
    fields: []
});
const newFieldName = ref('');

interface FilePathSettings {
    pdf_path: string;
}

const filePathSettings = ref<FilePathSettings>({
    pdf_path: ''
});
const savingFilePaths = ref(false);

const fetchFilePathSettings = async (): Promise<void> => {
    try {
        const res = await axios.get<FilePathSettings>('/api/file-path-settings');
        filePathSettings.value = {
            pdf_path: res.data?.pdf_path || ''
        };
    } catch (error) {
        console.error(error);
        showError('Errore nel caricamento dei percorsi file');
    }
};

const openCreateModal = (): void => {
    formData.value = { name: '', fields: [] };
    newFieldName.value = '';
    showCreateModal.value = true;
};

const closeCreateModal = (): void => {
    showCreateModal.value = false;
};

const openEditModal = (parameter: WorkParameter): void => {
    selectedParameter.value = parameter;
    formData.value = { 
        name: parameter.name,
        fields: parameter.fields ? [...parameter.fields] : []
    };
    newFieldName.value = '';
    showEditModal.value = true;
};

const closeEditModal = (): void => {
    showEditModal.value = false;
    selectedParameter.value = null;
};

const addField = (): void => {
    if (newFieldName.value.trim()) {
        formData.value.fields.push(newFieldName.value.trim());
        newFieldName.value = '';
    }
};

const removeField = (index: number): void => {
    formData.value.fields.splice(index, 1);
};

const openDeleteModal = (parameter: WorkParameter): void => {
    selectedParameter.value = parameter;
    showDeleteModal.value = true;
};

const closeDeleteModal = (): void => {
    showDeleteModal.value = false;
    selectedParameter.value = null;
};

const saveParameter = async (): Promise<void> => {
    try {
        await createParameter(formData.value);
        closeCreateModal();
        showSuccess('Parametro creato con successo');
    } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Errore durante il salvataggio';
        showError(errorMessage);
    }
};

const updateParameterHandler = async (): Promise<void> => {
    if (!selectedParameter.value) return;
    
    try {
        await updateParameter(selectedParameter.value.id, formData.value);
        closeEditModal();
        showSuccess('Parametro aggiornato con successo');
    } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Errore durante l\'aggiornamento';
        showError(errorMessage);
    }
};

const deleteParameterHandler = async (): Promise<void> => {
    if (!selectedParameter.value) return;
    
    try {
        await deleteParameter(selectedParameter.value.id);
        closeDeleteModal();
        showSuccess('Parametro eliminato con successo');
    } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Errore durante l\'eliminazione';
        showError(errorMessage);
    }
};

const saveFilePathSettings = async (): Promise<void> => {
    savingFilePaths.value = true;
    try {
        const res = await axios.put('/api/file-path-settings', filePathSettings.value);
        showSuccess(res.data.message || 'Percorsi aggiornati');
    } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Errore durante il salvataggio dei percorsi';
        showError(errorMessage);
    } finally {
        savingFilePaths.value = false;
    }
};

onMounted(() => {
    fetchParameters();
    fetchFilePathSettings();
});
</script>

<template>
  <div class="bg-white shadow rounded-lg p-3">
    <!-- Header con bottone Aggiungi -->
    <div class="mb-3 flex justify-between items-center">
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        Parametri di Lavorazione
      </h3>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue"
      >
        Aggiungi Lavorazione
      </button>
    </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Nome Parametro</th>
                                <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-if="loading">
                                <td colspan="2" class="px-3 py-2 text-center text-xs text-gray-500">
                                    <div class="flex items-center justify-center">
                                        <svg class="animate-spin h-5 w-5 mr-3 text-gray-500" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Caricamento...
                                    </div>
                                </td>
                            </tr>
                            <tr v-else-if="parameters.length === 0">
                                <td colspan="2" class="px-3 py-2 text-center">
                                    <div class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <h3 class="mt-2 text-xs font-medium text-gray-900">
                                            Nessun parametro creato
                                        </h3>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Inizia creando un nuova Lavorazione.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="parameter in parameters"
                                :key="parameter.id"
                                class="hover:bg-gray-100 border-b border-gray-200 transition-colors"
                            >
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ parameter.name }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-xs">
                                    <button
                                        @click="openEditModal(parameter)"
                                        class="text-white hover:text-white bg-copam-blue hover:bg-copam-blue-hover px-3 py-1 rounded-md text-sm font-medium transition duration-150 ease-in-out"
                                    >
                                        Modifica
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
    </div>

    <div class="bg-white shadow rounded-lg p-3 mt-6">
        <div class="mb-3 flex justify-between items-center">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Percorsi
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="border-b border-gray-200">
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Tipo</th>
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Percorso</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <tr class="border-b border-gray-200">
                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">PDF</td>
                        <td class="px-3 py-2">
                            <input
                                v-model="filePathSettings.pdf_path"
                                type="text"
                                placeholder="Es. \\SERVER\\Documenti\\PDF"
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-2 text-xs text-gray-500">
            Inserisci il percorso della cartella condivisa sul server locale.
        </p>

        <div class="mt-4 flex justify-end">
            <button
                @click="saveFilePathSettings"
                :disabled="savingFilePaths"
                class="px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90 focus:outline-none focus:ring-2 focus:ring-copam-blue disabled:opacity-60 disabled:cursor-not-allowed"
            >
                {{ savingFilePaths ? 'Salvataggio...' : 'Salva Percorsi' }}
            </button>
        </div>
    </div>

    <!-- Modal Crea Parametro -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCreateModal"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    Nuova Lavorazione
                </h3>
                
                <form @submit.prevent="saveParameter" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input
                            v-model="formData.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                        />
                    </div>

                    <!-- Sezione Campi Dinamici -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campi del Parametro</label>
                        
                        <!-- Lista campi esistenti -->
                        <div v-if="formData.fields.length > 0" class="space-y-2 mb-3">
                            <div
                                v-for="(field, index) in formData.fields"
                                :key="index"
                                class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md"
                            >
                                <span class="text-sm text-gray-700">{{ field }}</span>
                                <button
                                    type="button"
                                    @click="removeField(index)"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Aggiungi nuovo campo -->
                        <div class="flex gap-2">
                            <input
                                v-model="newFieldName"
                                type="text"
                                placeholder="Nome del campo"
                                @keypress.enter.prevent="addField"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                            />
                            <button
                                type="button"
                                @click="addField"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300"
                        >
                            Annulla
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                        >
                            Salva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modifica Parametro -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeEditModal"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    Modifica Parametro
                </h3>
                
                <form @submit.prevent="updateParameterHandler" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input
                            v-model="formData.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                        />
                    </div>

                    <!-- Sezione Campi Dinamici -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Campi del Parametro</label>
                        
                        <!-- Lista campi esistenti -->
                        <div v-if="formData.fields.length > 0" class="space-y-2 mb-3">
                            <div
                                v-for="(field, index) in formData.fields"
                                :key="index"
                                class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-md"
                            >
                                <span class="text-sm text-gray-700">{{ field }}</span>
                                <button
                                    type="button"
                                    @click="removeField(index)"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Aggiungi nuovo campo -->
                        <div class="flex gap-2">
                            <input
                                v-model="newFieldName"
                                type="text"
                                placeholder="Nome del campo"
                                @keypress.enter.prevent="addField"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue"
                            />
                            <button
                                type="button"
                                @click="addField"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-2 pt-4">
                        <button
                            type="submit"
                            class="w-full px-4 py-2 bg-copam-blue text-white text-sm font-medium rounded-md hover:bg-copam-blue/90"
                        >
                            Salva modifiche
                        </button>
                        <button
                            type="button"
                            @click="openDeleteModal(selectedParameter)"
                            class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700"
                        >
                            Elimina
                        </button>
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="w-full px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300"
                        >
                            Chiudi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Conferma Eliminazione -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeDeleteModal"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Conferma Eliminazione
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Sei sicuro di voler eliminare questo parametro? Questa azione non può essere annullata.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button
                        @click="deleteParameterHandler"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Elimina
                    </button>
                    <button
                        @click="closeDeleteModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Annulla
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Messaggio -->
    <div v-if="modalState.show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div>
                    <div :class="[
                        'mx-auto flex items-center justify-center h-12 w-12 rounded-full',
                        modalState.type === 'success' ? 'bg-green-100' : 'bg-red-100'
                    ]">
                        <svg v-if="modalState.type === 'success'" class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg v-else class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ modalState.title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ modalState.message }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <button
                        @click="closeModal"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-copam-blue text-base font-medium text-white hover:bg-copam-blue/90 focus:outline-none"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
