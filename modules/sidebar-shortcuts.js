/**
 * BricksMate — Sidebar Shortcuts
 * Injects a quick-insert rail into the Bricks structure panel.
 * The element list is configurable from the settings panel and arrives via
 * the localized `BricksMateSidebar.items` (id + label + inline SVG icon).
 * Depends on bricksmate-utils.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const { waitForBricks, getVueGlobal } = BricksMate.utils;

    function getItems() {
        return (typeof BricksMateSidebar !== 'undefined' && Array.isArray(BricksMateSidebar.items))
            ? BricksMateSidebar.items
            : [];
    }

    function injectBar() {
        const structureList = document.getElementById('bricks-structure');
        if (!structureList) return false;

        const wrapper = structureList.parentElement;
        if (!wrapper) return false;

        const items   = getItems();
        const existing = wrapper.querySelector('.bbem-shortcuts-bar');

        // No configured elements → make sure there is no empty rail.
        if (!items.length) {
            if (existing) { existing.remove(); structureList.style.paddingRight = ''; }
            return true;
        }

        // Already injected.
        if (existing) return true;

        wrapper.style.position = 'relative';
        structureList.style.paddingRight = '40px';

        const bar = document.createElement('div');
        bar.className = 'bbem-shortcuts-bar';

        items.forEach(item => {
            const btn = document.createElement('div');
            btn.className = 'bbem-shortcut-btn';
            btn.title = 'Add ' + item.label;
            btn.setAttribute('role', 'button');
            btn.setAttribute('tabindex', '0');
            btn.setAttribute('aria-label', 'Add ' + item.label);
            // Native Bricks look: themify glyph when available, inline SVG as fallback.
            btn.innerHTML = item.ti ? ('<i class="' + item.ti + '"></i>') : (item.svg || '');

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                try {
                    const vueGlobal = getVueGlobal();
                    if (vueGlobal && typeof vueGlobal.$_createElement === 'function') {
                        const element = vueGlobal.$_createElement({ name: item.id });
                        vueGlobal.$_addNewElement({ element }, { shiftKey: e.shiftKey }, true);
                    }
                } catch (err) {
                    // Ignore if the Bricks API changes.
                }
            });

            bar.appendChild(btn);
        });

        wrapper.appendChild(bar);
        return true;
    }

    // Shared structure observer (centralized in bricksmate-utils);
    // injectBar is idempotent (does nothing if the rail already exists).
    waitForBricks(() => {
        BricksMate.utils.onStructureChange(injectBar);
    });
});
