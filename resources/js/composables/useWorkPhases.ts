import { ref, Ref } from 'vue'
import { router } from '@inertiajs/vue3'

export interface WorkPhase {
    RECORD_ID: number;
    FLASS: string;
    IDOPR: string;
    FLSEQ: string;
    FLLAV: string;
    FLDES: string;
    FLQTA: number;
    FLQTB: number;
    FLQTD: number;
    FLCON: string;
    DTNUM: string;
    TEMPO: number;
    DTRAS: string;
    DRDES: string;
    DTRIC: string;
    is_assigned?: boolean;
}

export interface WorkPhaseFilters {
    search?: string;
    searchFllav?: string;
    searchDtras?: string;
    searchDtric?: string;
    searchDtnum?: string;
    searchIdopr?: string;
    showOnlyWorked?: boolean;
    showOnlyAvailable?: boolean;
    dateFrom?: string;
    dateTo?: string;
    sortBy?: string;
}

export interface Pagination {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
    has_more_pages: boolean;
}

export function useWorkPhases() {
    const workPhases = ref<WorkPhase[]>([])
    const pagination = ref<Pagination | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)

    const fetchWorkPhases = async (
        filters: WorkPhaseFilters = {},
        page: number = 1
    ): Promise<void> => {
        loading.value = true
        error.value = null

        try {
            router.get(
                '/api/work-phases/list',
                { ...filters, page },
                {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['workPhases', 'pagination'],
                    onSuccess: (responsePage: any) => {
                        if (responsePage.props.workPhases) {
                            workPhases.value = responsePage.props.workPhases as WorkPhase[]
                        }
                        if (responsePage.props.pagination) {
                            pagination.value = responsePage.props.pagination as Pagination
                        }
                    },
                    onError: (errors: any) => {
                        error.value = 'Errore nel caricamento delle fasi di lavoro'
                        console.error('Work phases fetch error:', errors)
                    },
                    onFinish: () => {
                        loading.value = false
                    }
                }
            )
        } catch (e) {
            error.value = 'Errore imprevisto nel caricamento'
            console.error('Work phases unexpected error:', e)
            loading.value = false
        }
    }

    const clearError = () => {
        error.value = null
    }

    return {
        workPhases,
        pagination,
        loading,
        error,
        fetchWorkPhases,
        clearError
    }
}
