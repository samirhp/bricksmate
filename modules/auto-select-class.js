/**
 * BricksMate — Auto-Select Class
 * Cuando el usuario selecciona un elemento en Bricks, activa automáticamente
 * su primera clase global válida en el panel de clases.
 *
 * Estrategia de detección:
 * - Se usa un setInterval moderado (150ms) en lugar de los 50ms originales,
 *   lo que reduce el trabajo un 66% manteniendo una respuesta fluida.
 * - El intervalo se pausa automáticamente cuando la ventana pierde el foco
 *   (usuario en otra pestaña) y se reanuda al volver.
 * - La detección de cambio compara el ID del elemento activo guardado en una
 *   variable local, no en el propio estado reactivo de Vue (que ya ha mutado
 *   para cuando se ejecuta el callback).
 *
 * Nota sobre el MutationObserver: NO es viable aquí porque state.activeElement
 * es una propiedad reactiva de Vue. Cuando cambia la selección, Vue muta el
 * objeto en memoria pero no necesariamente actualiza el DOM en el mismo tick,
 * y cuando lo hace, el objeto ya refleja el nuevo elemento — haciendo imposible
 * detectar el cambio comparando en el callback del observer.
 *
 * Depende de bricksmate-utils.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const { getBricksState, getVueGlobal, waitForBricks } = BricksMate.utils;

    waitForBricks(initAutoSelectClass);

    function initAutoSelectClass() {
        let lastElementId = null;
        let intervalId    = null;

        function trySelectClass() {
            const state     = getBricksState();
            const vueGlobal = getVueGlobal();
            if (!state || !vueGlobal || !state.activeElement) return;

            const elementObj = state.activeElement;

            // Solo actuar cuando el elemento activo ha cambiado
            if (lastElementId === elementObj.id) return;
            lastElementId = elementObj.id;

            const classes = elementObj.settings?._cssGlobalClasses;
            if (!Array.isArray(classes) || classes.length === 0) return;

            // Buscar la primera clase válida y no bloqueada
            const firstClassId = classes.find(classId => {
                if (!classId) return false;
                const globalClass = state.globalClasses.find(c => c.id === classId);
                if (!globalClass) return false;
                const isLocked = typeof vueGlobal.$_isLocked === 'function'
                    ? vueGlobal.$_isLocked(classId)
                    : false;
                return !isLocked;
            });

            if (!firstClassId) return;

            const classObj = state.globalClasses.find(el => el.id === firstClassId);
            if (!classObj) return;

            state.messageOrigin = 'main';
            state.activeClass   = classObj;
        }

        function startPolling() {
            if (intervalId) return;
            intervalId = setInterval(trySelectClass, 150);
        }

        function stopPolling() {
            if (!intervalId) return;
            clearInterval(intervalId);
            intervalId = null;
        }

        // Pausar cuando el usuario cambia de pestaña, reanudar al volver
        document.addEventListener('visibilitychange', () => {
            document.hidden ? stopPolling() : startPolling();
        });

        startPolling();
    }
});
