import { ref } from 'vue'
import flatpickr from 'flatpickr'
import { Italian } from 'flatpickr/dist/l10n/it.js'
import type { Instance as FlatpickrInstance } from 'flatpickr/dist/types/instance'

export interface DatePickerOptions {
    dateFormat?: string;
    enableTime?: boolean;
    time_24hr?: boolean;
    minDate?: string | Date;
    maxDate?: string | Date;
    mode?: 'single' | 'multiple' | 'range';
    onChange?: (selectedDates: Date[], dateStr: string) => void;
}

export function useDatePicker() {
    const instances = ref<Map<string, FlatpickrInstance>>(new Map())

    const initDatePicker = (
        element: HTMLInputElement | null,
        key: string,
        options: DatePickerOptions = {}
    ): FlatpickrInstance | null => {
        if (!element) {
            console.warn(`Element for key "${key}" is null`)
            return null
        }

        // Destroy existing instance if present
        if (instances.value.has(key)) {
            instances.value.get(key)?.destroy()
        }

        const instance = flatpickr(element, {
            dateFormat: options.dateFormat || 'Y-m-d',
            enableTime: options.enableTime || false,
            time_24hr: options.time_24hr !== undefined ? options.time_24hr : true,
            minDate: options.minDate,
            maxDate: options.maxDate,
            mode: options.mode || 'single',
            locale: Italian,
            onChange: options.onChange
        })

        instances.value.set(key, instance)
        return instance
    }

    const destroyDatePicker = (key: string): void => {
        const instance = instances.value.get(key)
        if (instance) {
            instance.destroy()
            instances.value.delete(key)
        }
    }

    const destroyAllDatePickers = (): void => {
        instances.value.forEach(instance => instance.destroy())
        instances.value.clear()
    }

    const getDatePicker = (key: string): FlatpickrInstance | undefined => {
        return instances.value.get(key)
    }

    const setDate = (key: string, date: string | Date | null): void => {
        const instance = instances.value.get(key)
        if (instance && date) {
            instance.setDate(date)
        } else if (instance && date === null) {
            instance.clear()
        }
    }

    return {
        initDatePicker,
        destroyDatePicker,
        destroyAllDatePickers,
        getDatePicker,
        setDate
    }
}
