import './bootstrap';
import '../css/app.css';

import Alpine from 'alpinejs';
import communicationsStore from './stores/communications';

window.Alpine = Alpine;

Alpine.store('communications', communicationsStore);

Alpine.start();

import { createApp } from 'vue'
import WorkPhaseList from './components/WorkPhaseList.vue'
import AssignedWorkPhaseList from './components/AssignedWorkPhaseList.vue'
import WarehouseList from './components/WarehouseList.vue'
import BillOfMaterialsList from './components/BillOfMaterialsList.vue'
import ArticleSearchList from './components/ArticleSearchList.vue'
import WorkParametersList from './pages/settings/General.vue'
import SheetsList from './components/SheetsList.vue'
import ProductionOrdersList from './components/ProductionOrdersList.vue'
import AcknowledgementLogList from './components/AcknowledgementLogList.vue'
import CommunicationManageList from './components/CommunicationManageList.vue'
import CommunicationInboxList from './components/CommunicationInboxList.vue'

const app = createApp({})

// registra il componente globalmente
app.component('work-phase-list', WorkPhaseList)
app.component('assigned-work-phase-list', AssignedWorkPhaseList)
app.component('warehouse-list', WarehouseList)
app.component('distinta-base-list', BillOfMaterialsList)
app.component('article-search-list', ArticleSearchList)
app.component('work-parameters-list', WorkParametersList)
app.component('sheets-list', SheetsList)
app.component('production-orders-list', ProductionOrdersList)
app.component('acknowledgement-log-list', AcknowledgementLogList)
app.component('communication-manage-list', CommunicationManageList)
app.component('communication-inbox-list', CommunicationInboxList)

// monta l'app su un div con id app
app.mount('#app')

