import { onMounted, onUnmounted } from 'vue'

/**
 * Esegue callback ogni intervalMs finché il componente è montato.
 * Si ferma quando la scheda del browser non è visibile e,
 * al ritorno sulla scheda, aggiorna subito e riparte.
 */
export function usePolling(callback: () => unknown, intervalMs = 15000) {
    let timer: ReturnType<typeof setInterval> | null = null

    const start = () => {
        stop()
        timer = setInterval(callback, intervalMs)
    }

    const stop = () => {
        if (timer) {
            clearInterval(timer)
            timer = null
        }
    }

    const onVisibilityChange = () => {
        if (document.hidden) {
            stop()
        } else {
            callback()
            start()
        }
    }

    onMounted(() => {
        if (!document.hidden) start()
        document.addEventListener('visibilitychange', onVisibilityChange)
    })

    onUnmounted(() => {
        stop()
        document.removeEventListener('visibilitychange', onVisibilityChange)
    })
}
