/**
 * BricksMate — HTML Tags Manager
 * Selector visual de etiqueta HTML integrado en el panel de estructura de Bricks.
 * Depende de bricksmate-utils.js.
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const { getBricksState, waitForBricks } = BricksMate.utils;

    const availableTags = [
        'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav',
        'p', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a',
        'figure', 'ul', 'ol', 'li', 'button'
    ];

    let activeDropdown = null;

    // Cerrar el dropdown al hacer clic fuera
    document.addEventListener('mousedown', (e) => {
        if (activeDropdown &&
            !activeDropdown.contains(e.target) &&
            !e.target.closest('.bm-tag-btn')) {
            closeDropdown();
        }
    });

    // Cerrar el dropdown al hacer scroll en la estructura
    document.body.addEventListener('scroll', (e) => {
        if (e.target.id === 'bricks-structure' && activeDropdown) closeDropdown();
    }, true);

    waitForBricks(() => {
        // Observer de estructura compartido (centralizado en bricksmate-utils).
        // injectTagBadges ya hace no-op si aún no hay elementos en la estructura.
        BricksMate.utils.onStructureChange(injectTagBadges);

        // Re-inyectar cuando el usuario modifica propiedades
        const bricksPanel = document.getElementById('bricks-panel');
        if (bricksPanel) {
            bricksPanel.addEventListener('input', () => setTimeout(injectTagBadges, 300));
        }
    });

    function getDefaultTag(elName) {
        if (!elName) return 'div';
        const name = elName.toLowerCase();
        if (name.includes('heading')) return 'h3';
        if (name.includes('section')) return 'section';
        return 'div';
    }

    function injectTagBadges() {
        const structureItems = document.querySelectorAll('#bricks-structure li[data-id]');
        if (structureItems.length === 0) return;

        const state = getBricksState();
        if (!state) return;

        const allElements = [
            ...(state.header  || []),
            ...(state.content || []),
            ...(state.footer  || [])
        ];

        structureItems.forEach(li => {
            const titleContainer = li.querySelector('.title');
            if (!titleContainer) return;

            const icon = titleContainer.querySelector('.icon');
            if (!icon) return;

            const iconTitle = icon.getAttribute('title');
            if (!iconTitle) return;

            const elName = iconTitle.toLowerCase();
            const allowedElements = ['section', 'container', 'block', 'div', 'heading', 'text'];
            if (!allowedElements.some(a => elName.includes(a))) return;

            const id = li.getAttribute('data-id');

            // Obtener etiqueta actual
            let currentTag = getDefaultTag(elName);
            const el = allElements.find(e => e.id === id);
            if (el?.settings) {
                if (el.settings.tag)       currentTag = el.settings.tag;
                if (el.settings.customTag) currentTag = el.settings.customTag;
            }

            // Estilos del contenedor de título para alineación
            titleContainer.style.display    = 'flex';
            titleContainer.style.alignItems = 'center';

            // Si el botón ya existe, solo actualizar texto/color si cambió
            const wrapper = li.querySelector('.bm-tag-btn-wrapper');
            if (wrapper) {
                const btn = wrapper.querySelector('.bm-tag-btn');
                if (btn && btn.textContent !== currentTag) {
                    btn.textContent = currentTag;
                    btn.setAttribute('data-tag', currentTag);
                }
                return;
            }

            // Crear el botón por primera vez
            const newWrapper = document.createElement('div');
            newWrapper.className = 'bm-tag-btn-wrapper';

            const btn = document.createElement('button');
            btn.className = 'bm-tag-btn';
            btn.textContent = currentTag;
            btn.setAttribute('data-tag', currentTag);

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openDropdown(btn, id);
            });

            newWrapper.appendChild(btn);
            icon.after(newWrapper);
        });
    }

    function openDropdown(btnElement, elementId) {
        closeDropdown();

        const dropdown = document.createElement('div');
        dropdown.className = 'bm-tag-dropdown';

        availableTags.forEach(tag => {
            const item = document.createElement('div');
            item.className = 'bm-tag-dropdown-item';
            item.textContent = tag === 'a' ? 'a [Link]' : tag;

            item.addEventListener('click', (e) => {
                e.stopPropagation();
                changeElementTag(elementId, tag, btnElement);
                closeDropdown();
            });
            dropdown.appendChild(item);
        });

        document.body.appendChild(dropdown);
        activeDropdown = dropdown;

        const rect   = btnElement.getBoundingClientRect();
        let topPos   = rect.bottom + 4;

        // Abrir hacia arriba si se sale de la pantalla por abajo
        if (topPos + 280 > window.innerHeight) {
            topPos = rect.top - 280 - 4;
        }

        dropdown.style.top  = topPos + 'px';
        dropdown.style.left = rect.left + 'px';
    }

    function closeDropdown() {
        if (activeDropdown) {
            activeDropdown.remove();
            activeDropdown = null;
        }
    }

    function changeElementTag(elementId, newTag, btnElement) {
        btnElement.textContent = newTag;
        btnElement.setAttribute('data-tag', newTag);

        const state = getBricksState();
        if (!state) return;

        const allElements = [
            ...(state.header  || []),
            ...(state.content || []),
            ...(state.footer  || [])
        ];
        const el = allElements.find(e => e.id === elementId);
        if (!el) return;

        if (!el.settings) el.settings = {};
        el.settings.tag = newTag;

        // Forzar re-render de Vue
        state.globalClasses.push({});
        setTimeout(() => state.globalClasses.pop(), 50);
    }
});
