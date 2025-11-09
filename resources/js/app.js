import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { createApp } from 'vue'
import WorkPhaseList from './components/WorkPhaseList.vue'
import AssignedWorkPhaseList from './components/AssignedWorkPhaseList.vue'

const app = createApp({})

// registra il componente globalmente
app.component('work-phase-list', WorkPhaseList)
app.component('assigned-work-phase-list', AssignedWorkPhaseList)

// monta l'app su un div con id app
app.mount('#app')

