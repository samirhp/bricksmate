/**
 * BricksMate — Export ID Styles to Class
 * Depende de bricksmate-utils.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const { getBricksState, CONTENT_BLACKLIST, waitForBricks } = BricksMate.utils;

    // Los estilos viven en export-id-to-class.css (enrolado desde el PHP del módulo).

    // ── Toast ─────────────────────────────────────────────────────────────────
    function showToast(message, isError) {
        const existing = document.querySelector('.bm-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'bm-toast' + (isError ? ' bm-toast-error' : '');
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('bm-toast-visible'));
        });

        setTimeout(() => {
            toast.classList.remove('bm-toast-visible');
            setTimeout(() => toast.remove(), 250);
        }, 3000);
    }

    // ── Lógica de exportación ─────────────────────────────────────────────────
    function exportIdStylesToClass() {
        const state = getBricksState();
        if (!state || !state.activeElement) {
            showToast('No active element selected.', true);
            return;
        }

        const element  = state.activeElement;
        const settings = element.settings;

        if (!settings) {
            showToast('This element has no settings.', true);
            return;
        }

        const styleKeys = Object.keys(settings).filter(k => !CONTENT_BLACKLIST.includes(k));

        if (styleKeys.length === 0) {
            showToast('No ID styles found to export.', true);
            return;
        }

        const classIds = Array.isArray(settings._cssGlobalClasses)
            ? settings._cssGlobalClasses.filter(id => id && id.trim())
            : [];

        if (classIds.length === 0) {
            showToast('Assign a CSS class to this element first.', true);
            return;
        }

        const targetClassId  = classIds[0];
        const targetClassObj = state.globalClasses.find(c => c.id === targetClassId);

        if (!targetClassObj) {
            showToast('Target class not found.', true);
            return;
        }

        if (!targetClassObj.settings) targetClassObj.settings = {};

        let exportedCount = 0;
        styleKeys.forEach(key => {
            targetClassObj.settings[key] = JSON.parse(JSON.stringify(settings[key]));
            delete settings[key];
            exportedCount++;
        });

        state.globalClasses.push({});
        setTimeout(() => state.globalClasses.pop(), 50);

        showToast(`✓ ${exportedCount} style${exportedCount !== 1 ? 's' : ''} exported to "${targetClassObj.name}".`);
    }

    // ── Icono SVG ─────────────────────────────────────────────────────────────
    const BTN_SVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        xmlns="http://www.w3.org/2000/svg" class="bricks-svg">
        <path d="M3 12v8h18v-8"/>
        <line x1="12" y1="16" x2="12" y2="4"/>
        <polyline points="8 8 12 4 16 8"/>
    </svg>`;

    // ── Encontrar el dropdown correcto ────────────────────────────────────────
    // El dropdown tiene exactamente estos 4 hijos con data-balloon conocidos:
    // "Unlocked", "Copy: Styles", "Reset: Styles", "Rename: Class name"
    // Usamos la combinación lock + rename que es única en todo el DOM.
    function findDropdown() {
        const allDropdowns = document.querySelectorAll('div.dropdown');
        for (const dd of allDropdowns) {
            const hasLock   = dd.querySelector('.bricks-svg-wrapper.lock');
            const hasRename = dd.querySelector('.bricks-svg-wrapper.rename');
            if (hasLock && hasRename) return dd;
        }
        return null;
    }

    // ── Inyección ─────────────────────────────────────────────────────────────
    function injectButton(dropdown) {
        if (dropdown.querySelector('.bm-export-btn')) return;

        const span = document.createElement('span');
        span.className = 'bricks-svg-wrapper bm-export-btn';
        span.setAttribute('data-balloon', 'Export ID → Class');
        span.setAttribute('data-balloon-pos', 'left');
        span.setAttribute('role', 'button');
        span.setAttribute('tabindex', '0');
        span.setAttribute('aria-label', 'Export ID styles to Class');
        span.innerHTML = BTN_SVG;

        span.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            exportIdStylesToClass();
        });

        // Insertar antes del lápiz (rename), último icono nativo
        const renameBtn = dropdown.querySelector('.bricks-svg-wrapper.rename');
        if (renameBtn) {
            dropdown.insertBefore(span, renameBtn);
        } else {
            dropdown.appendChild(span);
        }
    }

    // ── Arranque ──────────────────────────────────────────────────────────────
    waitForBricks(() => {
        // Comprobación inmediata
        const dd = findDropdown();
        if (dd) injectButton(dd);

        // El dropdown se crea/destruye al cambiar elemento o clase activa
        const observer = new MutationObserver(() => {
            const dd = findDropdown();
            if (dd) injectButton(dd);
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
});
