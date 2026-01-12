import '../css/app.css';

import { initializeTheme } from './composables/useAppearance';
// @ts-expect-error: alpinejs has no bundled types
import Alpine from 'alpinejs';

// This will set light / dark mode on page load...
initializeTheme();

// Initialize Alpine so the Blade navbar dropdowns work
// @ts-expect-error: augmenting Window for Alpine
window.Alpine = Alpine;
Alpine.start();
