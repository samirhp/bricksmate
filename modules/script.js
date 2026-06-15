/**
 * BricksMate — BEM Generator
 * Depende de bricksmate-utils.js (BricksMate.utils disponible en window).
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // Alias cortos a las utilidades compartidas
    const { getBricksState, CONTENT_BLACKLIST, waitForBricks } = BricksMate.utils;

    const SETTINGS_KEY = 'bbem_settings';
    let userSettings = JSON.parse(localStorage.getItem(SETTINGS_KEY)) || {
        syncLabels:    true,
        showModifiers: false,
        showLabels:    false,
        copyStyles:    true,
        classAction:   'rename'
    };

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    // Espera a Bricks usando el helper centralizado (MutationObserver, sin setInterval infinito)
    waitForBricks(initBemPlugin);

    function initBemPlugin() {
        // Observer de estructura compartido (centralizado en bricksmate-utils):
        // un único MutationObserver para todos los módulos en lugar de uno cada uno.
        BricksMate.utils.onStructureChange(() => {
            const structurePanel = document.getElementById('bricks-structure');
            if (structurePanel) injectBemButtons(structurePanel);
        });
    }

    function injectBemButtons(panel) {
        const items = panel.querySelectorAll('li[data-id]:not(.has-bbem-btn)');
        items.forEach(item => {
            if (item.querySelector('.bbem-trigger-btn')) return;

            const target = item.querySelector('.actions') || item.querySelector('.structure-item-actions');

            // <li> en lugar de <div>: dentro de <ul class="actions"> lo correcto
            // semánticamente es un <li>. El estilo (display:inline-flex) evita la viñeta.
            const btn = document.createElement('li');
            btn.className = 'bbem-trigger-btn';
            btn.textContent = 'BEM';
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const existing = document.querySelector('.bbem-draggable-panel');
                if (existing) existing.remove();
                openBemPanel(item.getAttribute('data-id'));
            });

            if (target) {
                target.insertBefore(btn, target.firstChild || null);
            } else {
                btn.classList.add('bbem-no-actions');
                const titleNode = item.firstElementChild;
                if (titleNode) {
                    titleNode.style.position = 'relative';
                    titleNode.appendChild(btn);
                } else {
                    item.appendChild(btn);
                }
            }

            item.classList.add('has-bbem-btn');
        });
    }

    function findElement(id) {
        const state = getBricksState();
        if (!state) return null;
        return [
            ...(state.header  || []),
            ...(state.content || []),
            ...(state.footer  || [])
        ].find(el => el.id === id);
    }

    function getDescendants(parentId, list = [], depth = 0) {
        const parent = findElement(parentId);
        if (!parent || !parent.children) return list;
        parent.children.forEach(childId => {
            const child = findElement(childId);
            if (child) {
                list.push({ ...child, depth: depth + 1 });
                getDescendants(childId, list, depth + 1);
            }
        });
        return list;
    }

    function slugify(text) {
        return (text || 'element').toString().toLowerCase().trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    function formatLabel(className, blockName) {
        const cleanName = className.replace(blockName + '__', '');
        return cleanName.replace(/-/g, ' ').replace(/(^\w{1})|(\s+\w{1})/g, l => l.toUpperCase());
    }

    function openBemPanel(rootId) {
        const rootEl = findElement(rootId);
        if (!rootEl) return;

        const baseLabel  = rootEl.label || rootEl.name || 'block';
        const blockClass = slugify(baseLabel);
        const state      = getBricksState();

        let showActionSelect = false;
        const elementsToProcess = [rootEl, ...getDescendants(rootId)];

        elementsToProcess.forEach(el => {
            if (!el || !el.settings) return;

            // ¿Hay clases reales asignadas?
            if (el.settings._cssGlobalClasses) {
                try {
                    const classIds = Object.values(el.settings._cssGlobalClasses);
                    for (const cid of classIds) {
                        if (typeof cid === 'string' && cid.trim() !== '') {
                            const realClass = state.globalClasses.find(gc => gc.id === cid);
                            if (realClass?.name?.trim()) {
                                showActionSelect = true;
                                break;
                            }
                        }
                    }
                } catch (e) { /* continuar */ }
            }

            // Nota: el desplegable solo aparece si YA existen clases CSS (arriba).
            // No se muestra solo por tener estilos en el ID (creación desde cero).
        });

        const panel = document.createElement('div');
        panel.className = 'bbem-draggable-panel';

        let rowsHtml = '';
        elementsToProcess.forEach(el => {
            const rawLabel      = el.label || el.name || 'element';
            const safeLabel     = escapeHtml(rawLabel);
            const safeBlockClass = escapeHtml(blockClass);
            const type          = el.id === rootId ? 'BLOCK' : 'ELEMENT';
            const depth         = el.depth || 0;
            const isBlock       = el.id === rootId;
            const elementSlug   = slugify(safeLabel);
            const hideModsClass = userSettings.showModifiers ? '' : 'hide-mods';

            let classWrapperHtml;
            if (isBlock) {
                classWrapperHtml = `<div class="bbem-class-wrapper"><input type="text" class="bbem-class-name inner-input" value="${safeBlockClass}" data-is-block="true"></div>`;
            } else {
                classWrapperHtml = `<div class="bbem-class-wrapper"><span class="bbem-block-prefix">${safeBlockClass}__</span><input type="text" class="bbem-class-name inner-input" value="${elementSlug}" data-is-block="false"></div>`;
            }

            rowsHtml += `
                <div class="bbem-row" data-id="${el.id}">
                    <div class="bbem-indent-wrapper bbem-indent-${Math.min(depth, 3)}">
                        <div class="bbem-label-group">
                            <span class="bbem-original-name">${safeLabel}</span>
                            <span class="bbem-tag">${type}</span>
                        </div>
                        <div class="bbem-input-group ${hideModsClass}">
                            ${classWrapperHtml}
                            <input type="text" class="bbem-input bbem-modifier" placeholder="mod">
                        </div>
                    </div>
                    <div class="bbem-checkbox-col">
                        <input type="checkbox" class="bbem-include-checkbox" checked title="Include">
                    </div>
                </div>`;
        });

        let actionSelectHtml = '';
        if (showActionSelect) {
            const opt = (val, label) =>
                `<option value="${val}" ${userSettings.classAction === val ? 'selected' : ''}>${label}</option>`;
            actionSelectHtml = `
                <select id="bbem-class-action" class="bbem-select">
                    ${opt('rename',  'Rename classes')}
                    ${opt('remove',  'Create new & remove old')}
                    ${opt('delete',  'Create new & delete old')}
                    ${opt('keep',    'Create new & keep old')}
                    ${opt('copy-id', 'Copy ID styles to Class')}
                </select>`;
        }

        const bodyClass    = userSettings.showLabels    ? '' : 'hide-labels';
        const toolbarClass = userSettings.showModifiers ? 'mods-active' : '';
        const modBtnClass  = userSettings.showModifiers ? 'active' : '';
        const baseLabelSafe = escapeHtml(baseLabel);

        panel.innerHTML = `
            <div class="bbem-header" id="bbem-drag-handle">
                <h2>BEM: ${baseLabelSafe}</h2>
                <button class="bbem-close">&times;</button>
            </div>
            <div class="bbem-toolbar ${toolbarClass}">
                <div class="bbem-general-group">
                    ${actionSelectHtml}
                    <label class="bbem-toggle-group">
                        <div class="bbem-switch">
                            <input type="checkbox" id="bbem-toggle-sync" ${userSettings.syncLabels ? 'checked' : ''}>
                            <span class="bbem-slider"></span>
                        </div>
                        <span class="bbem-toggle-label">Sync</span>
                    </label>
                </div>
                <div class="bbem-separator"></div>
                <button class="bbem-text-toggle ${modBtnClass}" id="bbem-toggle-mods-vis">MODIFIER</button>
                <span class="bbem-toggle-label" id="bbem-select-all-btn"
                      style="margin-left:auto;cursor:pointer;color:var(--bbem-accent)">None</span>
            </div>
            <div class="bbem-body ${bodyClass}">${rowsHtml}</div>
            <div class="bbem-footer">
                <div class="bbem-footer-left">
                    <label class="bbem-toggle-group" id="bbem-copy-group">
                        <div class="bbem-switch">
                            <input type="checkbox" id="bbem-toggle-copy" ${userSettings.copyStyles !== false ? 'checked' : ''}>
                            <span class="bbem-slider"></span>
                        </div>
                        <span class="bbem-toggle-label">Copy styles</span>
                    </label>
                </div>
                <div class="bbem-footer-right">
                    <button class="bbem-btn bbem-btn-secondary bbem-close-btn">Cancel</button>
                    <button class="bbem-btn bbem-btn-primary" id="bbem-apply">Apply</button>
                </div>
            </div>`;

        document.body.appendChild(panel);

        // Posicionar el panel junto al panel de estructura
        const structurePanel = document.getElementById('bricks-structure');
        let left = 350, top = 100;
        if (structurePanel) {
            const rect = structurePanel.getBoundingClientRect();
            left = rect.right + 10;
            top  = rect.top + 40;
        }
        if (left + 440 > window.innerWidth) left = window.innerWidth - 440 - 20;
        panel.style.left = left + 'px';
        panel.style.top  = top  + 'px';
        requestAnimationFrame(() => { panel.style.opacity = '1'; });

        setupDraggable(panel);
        setupInteractions(panel);
    }

    function setupDraggable(element) {
        const header = element.querySelector('#bbem-drag-handle');
        if (!header) return;

        let isDragging = false, startX, startY, initialLeft, initialTop;

        header.addEventListener('mousedown', (e) => {
            if (e.target.closest('.bbem-close')) return;
            isDragging = true;
            startX = e.clientX; startY = e.clientY;
            const rect = element.getBoundingClientRect();
            initialLeft = rect.left; initialTop = rect.top;
            element.style.transform = 'none';
            header.style.cursor = 'grabbing';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            element.style.left = `${initialLeft + (e.clientX - startX)}px`;
            element.style.top  = `${initialTop  + (e.clientY - startY)}px`;
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                header.style.cursor = 'grab';
                document.body.style.userSelect = '';
            }
        });
    }

    function setupInteractions(panel) {
        const toolbar = panel.querySelector('.bbem-toolbar');

        const actionSelect = panel.querySelector('#bbem-class-action');
        const copyGroup    = panel.querySelector('#bbem-copy-group');

        // El toggle "Copy styles" solo aplica al crear una clase nueva y soltar la
        // antigua (remove/delete). En rename ya se copia siempre; en keep/copy-id
        // no tiene sentido. Por eso se muestra únicamente en esas dos acciones.
        const updateCopyVisibility = (action) => {
            if (!copyGroup) return;
            const applies = actionSelect && (action === 'remove' || action === 'delete');
            copyGroup.style.display = applies ? '' : 'none';
        };
        updateCopyVisibility(actionSelect ? userSettings.classAction : 'rename');

        if (actionSelect) {
            actionSelect.addEventListener('change', (e) => {
                userSettings.classAction = e.target.value;
                localStorage.setItem(SETTINGS_KEY, JSON.stringify(userSettings));
                updateCopyVisibility(e.target.value);
            });
        }

        const saveSetting = (selector, key) => {
            const el = panel.querySelector(selector);
            if (el) {
                el.addEventListener('change', (e) => {
                    userSettings[key] = e.target.checked;
                    localStorage.setItem(SETTINGS_KEY, JSON.stringify(userSettings));
                });
            }
        };
        saveSetting('#bbem-toggle-sync', 'syncLabels');
        saveSetting('#bbem-toggle-copy', 'copyStyles');

        const modVisBtn = panel.querySelector('#bbem-toggle-mods-vis');
        if (modVisBtn) {
            modVisBtn.addEventListener('click', (e) => {
                e.preventDefault(); e.stopPropagation();
                const isActive = modVisBtn.classList.toggle('active');
                userSettings.showModifiers = isActive;
                localStorage.setItem(SETTINGS_KEY, JSON.stringify(userSettings));
                panel.querySelectorAll('.bbem-input-group').forEach(g =>
                    g.classList.toggle('hide-mods', !isActive)
                );
                toolbar.classList.toggle('mods-active', isActive);
            });
        }

        const blockInput = panel.querySelector('input[data-is-block="true"]');
        if (blockInput) {
            blockInput.addEventListener('input', (e) => {
                const newBlockName = slugify(e.target.value);
                panel.querySelectorAll('.bbem-block-prefix').forEach(prefix => {
                    prefix.textContent = newBlockName ? `${newBlockName}__` : '';
                });
            });
        }

        panel.querySelectorAll('.bbem-class-name').forEach(input => {
            input.addEventListener('input', (e) => {
                const wrapper = e.target.closest('.bbem-class-wrapper');
                if (wrapper) {
                    wrapper.classList.toggle('bbem-input-error', !e.target.value.trim());
                    if (e.target.value.trim()) wrapper.classList.remove('bbem-shake');
                }
            });
        });

        panel.querySelectorAll('.bbem-class-wrapper').forEach(wrapper => {
            wrapper.addEventListener('click', () => {
                wrapper.querySelector('input')?.focus();
            });
        });

        const selectAllBtn = panel.querySelector('#bbem-select-all-btn');
        if (selectAllBtn) {
            let allSelected = true;
            selectAllBtn.addEventListener('click', (e) => {
                e.preventDefault(); e.stopPropagation();
                allSelected = !allSelected;
                panel.querySelectorAll('.bbem-include-checkbox').forEach(cb => {
                    cb.checked = allSelected;
                    const row     = cb.closest('.bbem-row');
                    const wrapper = row.querySelector('.bbem-class-wrapper');
                    const input   = row.querySelector('.bbem-class-name');

                    if (allSelected) {
                        row.classList.remove('disabled');
                        if (input && !input.value.trim() && wrapper) wrapper.classList.add('bbem-input-error');
                    } else {
                        row.classList.add('disabled');
                        if (wrapper) { wrapper.classList.remove('bbem-input-error', 'bbem-shake'); }
                    }
                });
                selectAllBtn.textContent = allSelected ? 'None' : 'All';
            });
        }

        panel.querySelectorAll('.bbem-include-checkbox').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const row     = e.target.closest('.bbem-row');
                const wrapper = row.querySelector('.bbem-class-wrapper');
                const input   = row.querySelector('.bbem-class-name');

                if (e.target.checked) {
                    row.classList.remove('disabled');
                    if (input && !input.value.trim() && wrapper) wrapper.classList.add('bbem-input-error');
                } else {
                    row.classList.add('disabled');
                    if (wrapper) wrapper.classList.remove('bbem-input-error', 'bbem-shake');
                }
            });
        });

        const closePanel = (e) => {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            panel.remove();
        };

        panel.querySelectorAll('.bbem-close, .bbem-close-btn').forEach(btn => {
            btn.addEventListener('click', closePanel);
        });

        const applyBtn = panel.querySelector('#bbem-apply');
        if (applyBtn) {
            applyBtn.addEventListener('click', (e) => {
                e.preventDefault(); e.stopPropagation();
                let isValid = true;

                panel.querySelectorAll('.bbem-row').forEach(row => {
                    if (!row.querySelector('.bbem-include-checkbox').checked) return;
                    const inputEl = row.querySelector('.bbem-class-name');
                    const wrapper = row.querySelector('.bbem-class-wrapper');

                    if (!inputEl.value.trim()) {
                        isValid = false;
                        wrapper.classList.add('bbem-input-error');
                        wrapper.classList.remove('bbem-shake');
                        // Reiniciar animación usando la API de animaciones (evita el reflow forzado)
                        wrapper.getAnimations().forEach(a => { a.cancel(); a.play(); });
                        wrapper.classList.add('bbem-shake');
                    } else {
                        wrapper.classList.remove('bbem-input-error', 'bbem-shake');
                    }
                });

                if (isValid) {
                    applyClasses(panel);
                    closePanel();
                }
            });
        }
    }

    function applyClasses(panel) {
        let count = 0;
        const state           = getBricksState();
        const shouldSyncLabel = panel.querySelector('#bbem-toggle-sync').checked;
        const actionSelect    = panel.querySelector('#bbem-class-action');
        const userAction      = actionSelect ? actionSelect.value : 'rename';

        panel.querySelectorAll('.bbem-row').forEach(row => {
            if (!row.querySelector('.bbem-include-checkbox').checked) return;

            const id          = row.dataset.id;
            const inputEl     = row.querySelector('.bbem-class-name');
            const isBlockInput = inputEl.dataset.isBlock === 'true';
            const rawValue    = slugify(inputEl.value);

            let clsInput = rawValue;
            if (!isBlockInput) {
                const prefixEl = row.querySelector('.bbem-block-prefix');
                const prefix   = prefixEl ? prefixEl.textContent : '';
                clsInput = prefix + rawValue;
            }

            const modInput = row.querySelector('.bbem-modifier').value.trim();
            if (!clsInput) return;

            let cleanMod = modInput;
            if (cleanMod && !cleanMod.startsWith('--')) cleanMod = '--' + cleanMod;
            const isModifierOperation = !!cleanMod;

            const finalClassName = isModifierOperation ? `${clsInput}${cleanMod}` : clsInput;
            const element = findElement(id);

            if (!element) return;

            if (!element.settings) element.settings = {};
            if (!Array.isArray(element.settings._cssGlobalClasses)) element.settings._cssGlobalClasses = [];

            const oldClassIds    = element.settings._cssGlobalClasses.filter(cid => cid?.trim());
            const actionToApply  = isModifierOperation ? 'keep' : userAction;

            let newGlobalClass = state.globalClasses.find(gc => gc.name === finalClassName);
            let isNewClassCreated = false;

            if (!newGlobalClass) {
                newGlobalClass = {
                    id:       Math.random().toString(36).slice(2, 8),
                    name:     finalClassName,
                    settings: {}
                };
                state.globalClasses.push(newGlobalClass);
                isNewClassCreated = true;
            }

            // Migración de estilos del ID a la clase BEM
            if (actionToApply === 'copy-id') {
                Object.keys(element.settings).forEach(key => {
                    if (!CONTENT_BLACKLIST.includes(key)) {
                        newGlobalClass.settings[key] = JSON.parse(JSON.stringify(element.settings[key]));
                        delete element.settings[key];
                    }
                });
            }

            // Clonar estilos de la clase antigua en la nueva.
            //  - rename: siempre (es la esencia de la acción).
            //  - remove / delete: solo si el toggle "Copy styles" está activo.
            const copyStylesOn  = panel.querySelector('#bbem-toggle-copy')?.checked;
            const shouldCloneOld = isNewClassCreated && oldClassIds.length > 0 && (
                actionToApply === 'rename' ||
                ( ( actionToApply === 'remove' || actionToApply === 'delete' ) && copyStylesOn )
            );
            if (shouldCloneOld) {
                const firstOldClassObj = state.globalClasses.find(gc => gc.id === oldClassIds[0]);
                if (firstOldClassObj?.settings) {
                    const oldName = firstOldClassObj.name;
                    // Mutar clave por clave sobre el objeto settings existente (mismo
                    // patrón que 'copy-id', que es el que aplica los estilos de forma fiable).
                    Object.keys(firstOldClassObj.settings).forEach(key => {
                        let val = JSON.parse(JSON.stringify(firstOldClassObj.settings[key]));
                        // El CSS personalizado (_cssCustom) lleva el selector literal de la
                        // clase antigua (p.ej. ".section {…}"); lo reescribimos al nombre de la
                        // clase nueva para que siga aplicando. (%root% se deja intacto.)
                        if (key === '_cssCustom' && typeof val === 'string' && oldName) {
                            const esc = oldName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                            val = val.replace(new RegExp('\\.' + esc + '(?![\\w-])', 'g'), '.' + finalClassName);
                        }
                        newGlobalClass.settings[key] = val;
                    });
                }
            }

            // Borrar clases viejas globalmente si corresponde
            if (actionToApply === 'delete' || actionToApply === 'rename') {
                oldClassIds.forEach(idToRemove => {
                    const idx = state.globalClasses.findIndex(gc => gc.id === idToRemove);
                    if (idx !== -1) state.globalClasses.splice(idx, 1);
                });
            }

            // Asignar clases al elemento
            element.settings._cssGlobalClasses.splice(0);

            if (actionToApply === 'keep') {
                oldClassIds.forEach(oldId => element.settings._cssGlobalClasses.push(oldId));
                if (!element.settings._cssGlobalClasses.includes(newGlobalClass.id)) {
                    element.settings._cssGlobalClasses.push(newGlobalClass.id);
                }
            } else {
                element.settings._cssGlobalClasses.push(newGlobalClass.id);
            }

            count++;

            if (shouldSyncLabel && !isModifierOperation) {
                const blockName = panel.querySelector('input[data-is-block="true"]').value.trim();
                let newLabel = formatLabel(finalClassName, blockName);
                if (!newLabel?.trim()) {
                    newLabel = finalClassName.replace(/-/g, ' ').replace(/(^\w{1})|(\s+\w{1})/g, l => l.toUpperCase());
                }
                element.label = newLabel;

                setTimeout(() => {
                    const selectors = [
                        `li[data-id="${id}"] .structure-item-title span.text`,
                        `li[data-id="${id}"] .structure-item-title span`,
                        `li[data-id="${id}"] .name`
                    ];
                    for (const sel of selectors) {
                        const domEl = document.querySelector(sel);
                        if (domEl) { domEl.innerText = newLabel; break; }
                    }
                }, 50);
            }
        });

        // Forzar refresco de Vue
        if (count > 0 || shouldSyncLabel || userAction !== 'keep') {
            state.globalClasses.push({});
            setTimeout(() => state.globalClasses.pop(), 50);
        }
    }
});
