/**
 * BricksMate — Style Indicator
 * Pinta indicadores de color en el panel de estructura según si el elemento
 * tiene clases globales y/o estilos en el ID.
 * Depende de bricksmate-utils.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const { getBricksState, CONTENT_BLACKLIST, waitForBricks } = BricksMate.utils;

    waitForBricks(initStyleIndicators);

    function initStyleIndicators() {
        const structurePanel = document.getElementById('bricks-structure');
        const bricksPanel    = document.getElementById('bricks-panel');
        if (!structurePanel) return;

        let debounceTimer;
        const debounceUpdate = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(updateIndicators, 250);
        };

        // Observar cambios en el árbol de estructura
        const observer = new MutationObserver(debounceUpdate);
        observer.observe(structurePanel, { childList: true, subtree: true });

        // Reaccionar a cambios en el panel de propiedades
        if (bricksPanel) {
            ['input', 'change', 'click'].forEach(evt =>
                bricksPanel.addEventListener(evt, debounceUpdate)
            );
        }

        updateIndicators();
    }

    function updateIndicators() {
        const state = getBricksState();
        if (!state) return;

        const allElements = [
            ...(state.header  || []),
            ...(state.content || []),
            ...(state.footer  || [])
        ];

        document.querySelectorAll('#bricks-structure li[data-id]').forEach(li => {
            const id = li.getAttribute('data-id');
            const el = allElements.find(e => e.id === id);
            if (!el?.settings) return;

            // ¿Tiene clases globales válidas?
            const hasClasses = Array.isArray(el.settings._cssGlobalClasses) &&
                el.settings._cssGlobalClasses.some(c => typeof c === 'string' && c.trim() !== '');

            // ¿Tiene estilos pegados en el ID?
            const hasIdStyles = Object.keys(el.settings).some(k => !CONTENT_BLACKLIST.includes(k));

            const targetDiv = li.firstElementChild;
            if (!targetDiv) return;

            targetDiv.classList.remove('bbem-style-class', 'bbem-style-id', 'bbem-style-both');

            if (hasClasses && hasIdStyles) {
                targetDiv.classList.add('bbem-style-both');
            } else if (hasClasses) {
                targetDiv.classList.add('bbem-style-class');
            } else if (hasIdStyles) {
                targetDiv.classList.add('bbem-style-id');
            }
        });
    }
});
