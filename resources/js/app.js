import './bootstrap';
import '../css/app.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { createApp } from 'vue'
import WorkPhaseList from './components/WorkPhaseList.vue'
import AssignedWorkPhaseList from './components/AssignedWorkPhaseList.vue'
import WarehouseList from './components/WarehouseList.vue'
import WorkParametersList from './pages/settings/General.vue'

const app = createApp({})

// registra il componente globalmente
app.component('work-phase-list', WorkPhaseList)
app.component('assigned-work-phase-list', AssignedWorkPhaseList)
app.component('warehouse-list', WarehouseList)
app.component('work-parameters-list', WorkParametersList)

// monta l'app su un div con id app
app.mount('#app')

