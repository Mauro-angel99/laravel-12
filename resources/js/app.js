import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { createApp } from 'vue'
import WorkPhaseList from './components/WorkPhaseList.vue'

const app = createApp({})

// registra il componente globalmente
app.component('work-phase-list', WorkPhaseList)

// monta l’app su un div con id app
app.mount('#app')

