import { ref, Ref } from 'vue'
import axios from 'axios'

export interface WorkParameter {
    id: number;
    name: string;
    fields: string[];
    created_at?: string;
    updated_at?: string;
}

export function useWorkParameters() {
    const parameters = ref<WorkParameter[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    const fetchParameters = async (): Promise<void> => {
        loading.value = true
        error.value = null

        try {
            const response = await axios.get<WorkParameter[]>('/api/work-parameters')
            parameters.value = response.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Errore nel caricamento dei parametri'
            console.error('Parameters fetch error:', e)
        } finally {
            loading.value = false
        }
    }

    const createParameter = async (data: { name: string; fields: string[] }): Promise<WorkParameter> => {
        const response = await axios.post<{ data: WorkParameter }>('/api/work-parameters', data)
        await fetchParameters() // Ricarica la lista
        return response.data.data
    }

    const updateParameter = async (id: number, data: { name: string; fields: string[] }): Promise<WorkParameter> => {
        const response = await axios.put<{ data: WorkParameter }>(`/api/work-parameters/${id}`, data)
        await fetchParameters() // Ricarica la lista
        return response.data.data
    }

    const deleteParameter = async (id: number): Promise<void> => {
        await axios.delete(`/api/work-parameters/${id}`)
        await fetchParameters() // Ricarica la lista
    }

    const clearError = () => {
        error.value = null
    }

    return {
        parameters,
        loading,
        error,
        fetchParameters,
        createParameter,
        updateParameter,
        deleteParameter,
        clearError
    }
}
