import { ref, Ref } from 'vue'

export interface ModalState {
    show: boolean;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    message: string;
}

export function useModal() {
    const modalState = ref<ModalState>({
        show: false,
        type: 'info',
        title: '',
        message: ''
    })

    const showModal = (
        type: ModalState['type'],
        title: string,
        message: string
    ) => {
        modalState.value = {
            show: true,
            type,
            title,
            message
        }
    }

    const closeModal = () => {
        modalState.value.show = false
    }

    const showSuccess = (message: string, title: string = 'Successo') => {
        showModal('success', title, message)
    }

    const showError = (message: string, title: string = 'Errore') => {
        showModal('error', title, message)
    }

    const showWarning = (message: string, title: string = 'Attenzione') => {
        showModal('warning', title, message)
    }

    const showInfo = (message: string, title: string = 'Informazione') => {
        showModal('info', title, message)
    }

    return {
        modalState,
        showModal,
        closeModal,
        showSuccess,
        showError,
        showWarning,
        showInfo
    }
}
