// --- Alpine (mantém como estava) ---
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// --- Tabler (já vem com Bootstrap JS incluído) ---
import '@tabler/core/dist/js/tabler.min.js';
import './layout.js';
import './modules/workflow/steps.js';
import './modules/returnProcess/return-process-ui.js';
import './modules/returnProcess/return-process-flow.js';
