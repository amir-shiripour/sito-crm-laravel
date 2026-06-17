/**
 * Laravel Echo & Pusher Client-side integration bootstrap script.
 * Suitable for Vite builds in clinical ERP setup.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Configuration for Laravel Echo supporting both Pusher.com and self-hosted Socket servers (like Soketi/Reverb)
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

/**
 * Example Usage:
 * Listening to a treatment plan's real-time workflow instance transitions.
 *
 * @param {number} treatmentPlanId
 */
export function listenToTreatmentPlan(treatmentPlanId) {
    console.log(`Subscribing to treatment-plan.${treatmentPlanId}...`);

    window.Echo.private(`treatment-plan.${treatmentPlanId}`)
        .listen('.Modules\\Workflows\\Events\\PatientStageAdvanced', (e) => {
            console.log('Real-time stage advance received:', e);

            // Access data:
            const currentStageNode = e.current_node; // { id, name, type }
            const status = e.status; // e.g. ACTIVE, COMPLETED

            // Trigger UI updates without page reload, e.g.:
            // updatePatientPipelineUI(e.instance_id, currentStageNode, status);
        });
}
