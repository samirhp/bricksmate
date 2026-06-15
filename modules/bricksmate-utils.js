/**
 * BricksMate — Utilidades compartidas
 * Cargado antes que cualquier otro script del plugin.
 */
(function (global) {
  'use strict';

  /**
   * Accede al estado global de Bricks Builder (Vue 3).
   * Centralizado aquí para que un cambio de API solo requiera
   * modificar este único punto.
   *
   * @returns {object|null}
   */
  function getBricksState() {
    try {
      const app = document.querySelector('[data-v-app]');
      return app?.__vue_app__?.config?.globalProperties?.$_state || null;
    } catch (e) {
      return null;
    }
  }

  /**
   * Accede a las propiedades globales de Vue de Bricks.
   *
   * @returns {object|null}
   */
  function getVueGlobal() {
    try {
      const app = document.querySelector('[data-v-app]');
      return app?.__vue_app__?.config?.globalProperties || null;
    } catch (e) {
      return null;
    }
  }

  /**
   * Propiedades de Bricks que son estructura o contenido, NO estilos CSS.
   * Todo lo que NO esté en esta lista se considera un estilo y puede migrarse.
   * Lista centralizada: modificar aquí actualiza todos los módulos.
   */
  const CONTENT_BLACKLIST = [
    '_cssGlobalClasses', '_cssClasses', '_cssId', '_name', '_attributes', '_interactions',
    'text', 'title', 'subtitle', 'image', 'icon', 'video', 'url', 'link', 'query', 'tag', 'type',
    'content', 'items', 'formFields', 'code', 'html', 'shortcode', 'svgCode', 'svgContent',
    'autoplay', 'loop', 'controls', 'placeholder', 'size', 'variant', 'colorScheme',
    'isCustom', 'postType', 'taxonomy', 'terms', 'author', 'useDynamicData', 'dynamicData',
    'popup', 'conditions', 'loopQuery', 'accordion', 'tabs', 'slider', 'gallery', 'iconLibrary',
    'divider', 'label', 'description', 'heading', 'dir', 'lazyLoad', 'customTag'
  ];

  /**
   * Espera a que la app Vue de Bricks esté lista y ejecuta el callback.
   * Usa MutationObserver + un sondeo inicial corto en lugar de setInterval infinito.
   *
   * @param {Function} callback  Se llama cuando Vue está disponible.
   * @param {number}   timeout   Milisegundos máximos de espera (default 15 000).
   */
  function waitForBricks(callback, timeout) {
    timeout = timeout || 15000;
    const deadline = Date.now() + timeout;

    // Comprobación inmediata
    if (document.querySelector('[data-v-app]')?.__vue_app__) {
      callback();
      return;
    }

    // Observamos el body hasta que aparezca el elemento Vue
    const observer = new MutationObserver(function () {
      if (document.querySelector('[data-v-app]')?.__vue_app__) {
        observer.disconnect();
        callback();
      } else if (Date.now() > deadline) {
        observer.disconnect();
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  /**
   * Escribe un valor en un <input> o <textarea> usando el setter NATIVO del
   * prototipo y disparando input/change, para que el v-model de Vue (Bricks)
   * detecte el cambio. Centraliza el patrón usado por css-recipes y smart-values.
   *
   * @param {HTMLInputElement|HTMLTextAreaElement} el
   * @param {string}  value
   * @param {object} [opts]            Opciones.
   * @param {boolean} [opts.setCursor] Si true, coloca el cursor al final.
   */
  function setInputValue(el, value, opts) {
    opts = opts || {};
    var proto = (el.tagName === 'TEXTAREA')
      ? HTMLTextAreaElement.prototype
      : HTMLInputElement.prototype;
    var setter = Object.getOwnPropertyDescriptor(proto, 'value').set;
    setter.call(el, value);
    el.dispatchEvent(new Event('input',  { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
    if (opts.setCursor && typeof el.setSelectionRange === 'function') {
      try { el.setSelectionRange(value.length, value.length); } catch (e) { /* noop */ }
    }
  }

  /**
   * Observa el panel de estructura de Bricks y ejecuta el callback (debounced)
   * cada vez que cambia. Mantiene UN ÚNICO MutationObserver compartido entre
   * todos los módulos en lugar de uno por módulo. Llama al callback una vez de
   * inmediato y luego en cada mutación relevante.
   *
   * @param {Function} callback
   * @param {number}   [debounceMs]  Default 80ms.
   */
  var _structureCallbacks = [];
  var _structureObserver  = null;
  var _structureTimer      = null;

  function onStructureChange(callback, debounceMs) {
    debounceMs = debounceMs || 80;
    _structureCallbacks.push(callback);

    // Disparo inicial
    try { callback(); } catch (e) { /* aislar fallos entre módulos */ }

    if (_structureObserver) return; // ya está montado

    var fire = function () {
      clearTimeout(_structureTimer);
      _structureTimer = setTimeout(function () {
        _structureCallbacks.forEach(function (cb) {
          try { cb(); } catch (e) { /* aislar fallos entre módulos */ }
        });
      }, debounceMs);
    };

    var root = document.getElementById('bricks-panel') || document.body;
    _structureObserver = new MutationObserver(fire);
    _structureObserver.observe(root, { childList: true, subtree: true });
  }

  // Exportar al namespace global del plugin
  global.BricksMate = global.BricksMate || {};
  global.BricksMate.utils = {
    getBricksState:    getBricksState,
    getVueGlobal:      getVueGlobal,
    CONTENT_BLACKLIST: CONTENT_BLACKLIST,
    waitForBricks:     waitForBricks,
    setInputValue:     setInputValue,
    onStructureChange: onStructureChange
  };

})(window);
