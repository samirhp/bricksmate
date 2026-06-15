/**
 * BricksMate — Expand Children
 *
 * Inyecta un botón en cada elemento del panel de estructura que tenga hijos.
 * Al pulsarlo, expande recursivamente TODO su subárbol (no solo un nivel, como
 * hace Bricks por defecto).
 *
 * Estrategia:
 *  - El árbol "verdad" viene del estado Vue (getBricksState → header/content/footer),
 *    donde cada elemento tiene .id y .children[]. De ahí sacamos qué ids deben
 *    quedar expandidos.
 *  - La expansión se ejecuta sobre el DOM clicando el caret de cada li colapsado.
 *    Como Bricks renderiza los hijos de forma perezosa, se hace en pasadas
 *    sucesivas hasta que no queden padres colapsados dentro del subárbol.
 *
 * Depende de bricksmate-utils.js.
 */
( function () {
  'use strict';

  const { getBricksState, waitForBricks } = BricksMate.utils;

  // Selectores candidatos para el "caret"/toggle de colapso dentro de la fila de
  // un li de estructura. Se prueban en orden; el primero que exista se usa.
  // Si Bricks cambia el marcado, basta con ajustar esta lista.
  const CARET_SELECTORS = [
    '.toggle',
    '.structure-item-toggle',
    '.collapse',
    '.expand',
    '[class*="toggle"]',
    '[class*="collapse"]'
  ];

  const MAX_PASSES = 40; // tope de seguridad para árboles muy profundos

  // ── Estado: utilidades de árbol ─────────────────────────────────────────────
  function findElement( id ) {
    const state = getBricksState();
    if ( ! state ) return null;
    return [
      ...( state.header  || [] ),
      ...( state.content || [] ),
      ...( state.footer  || [] )
    ].find( el => el.id === id ) || null;
  }

  function hasChildren( id ) {
    const el = findElement( id );
    return !! ( el && Array.isArray( el.children ) && el.children.length > 0 );
  }

  // Todos los descendientes (ids) de un elemento, incluido él mismo.
  function collectSubtree( id, acc ) {
    acc = acc || [];
    acc.push( id );
    const el = findElement( id );
    if ( el && Array.isArray( el.children ) ) {
      el.children.forEach( childId => collectSubtree( childId, acc ) );
    }
    return acc;
  }

  // ── DOM: helpers ────────────────────────────────────────────────────────────
  function liFor( id ) {
    return document.querySelector( '#bricks-structure li[data-id="' + CSS.escape( id ) + '"]' );
  }

  // La "fila" propia del li (sin descender a los li hijos), donde vive el caret.
  function rowOf( li ) {
    // El primer hijo directo del li suele ser el contenedor de la fila.
    return li.querySelector( ':scope > *' ) || li;
  }

  function findCaret( li ) {
    const row = rowOf( li );
    for ( const sel of CARET_SELECTORS ) {
      const caret = row.querySelector( ':scope ' + sel ) || row.querySelector( sel );
      if ( caret ) return caret;
    }
    return null;
  }

  // ¿El li está colapsado? Señales (cualquiera vale):
  //  - clase collapsed/closed en el li o su fila
  //  - aria-expanded="false" en el caret
  //  - tiene hijos en el estado pero ninguno está presente/visible en el DOM
  function isCollapsed( id ) {
    const li = liFor( id );
    if ( ! li ) return false;

    const row = rowOf( li );
    if ( li.classList.contains( 'collapsed' ) || li.classList.contains( 'closed' ) ) return true;
    if ( row.classList.contains( 'collapsed' ) || row.classList.contains( 'closed' ) ) return true;

    const caret = findCaret( li );
    if ( caret && caret.getAttribute( 'aria-expanded' ) === 'false' ) return true;

    // Heurística por DOM: si tiene hijos en el estado pero ninguno se ve, está colapsado.
    const el = findElement( id );
    if ( el && Array.isArray( el.children ) && el.children.length ) {
      const anyVisible = el.children.some( childId => {
        const childLi = liFor( childId );
        return childLi && childLi.offsetParent !== null;
      } );
      if ( ! anyVisible ) return true;
    }

    return false;
  }

  // Clica el caret de un elemento (alterna su estado un nivel en Bricks).
  function clickCaret( id ) {
    const li = liFor( id );
    if ( ! li ) return false;
    const caret = findCaret( li );
    if ( ! caret ) return false;
    caret.click();
    return true;
  }

  // Expande recursivamente todo el subárbol de `rootId` en pasadas sucesivas.
  function expandSubtree( rootId ) {
    let pass = 0;

    function doPass() {
      if ( pass++ > MAX_PASSES ) return;

      const subtree   = collectSubtree( rootId );
      const collapsed = subtree.filter( id => hasChildren( id ) && isCollapsed( id ) );

      if ( collapsed.length === 0 ) return; // todo expandido

      collapsed.forEach( clickCaret );

      // Dar tiempo a Bricks a renderizar el nuevo nivel y repetir.
      requestAnimationFrame( () => setTimeout( doPass, 30 ) );
    }

    doPass();
  }

  // Comprime recursivamente todo el subárbol de `rootId`.
  // Se colapsan los padres de más profundos a menos profundos: así sus carets
  // siguen renderizados en el momento de clicarlos (Bricks desmonta los hijos
  // al colapsar al padre).
  function collapseSubtree( rootId ) {
    const parents = collectSubtree( rootId ).filter( hasChildren );
    // collectSubtree es pre-orden (padre antes que hijos); al invertir, los
    // descendientes más profundos quedan primero.
    parents.reverse().forEach( id => {
      if ( ! isCollapsed( id ) ) clickCaret( id );
    } );
  }

  // Alterna: si está colapsado → expandir todo; si no → comprimir todo.
  function toggleSubtree( rootId ) {
    if ( isCollapsed( rootId ) ) {
      expandSubtree( rootId );
    } else {
      collapseSubtree( rootId );
    }
  }

  // ── Inyección del botón en cada li con hijos ────────────────────────────────
  function injectButtons() {
    const structure = document.getElementById( 'bricks-structure' );
    if ( ! structure ) return;

    structure.querySelectorAll( 'li[data-id]' ).forEach( li => {
      const id = li.getAttribute( 'data-id' );
      if ( ! id || ! hasChildren( id ) ) return;
      if ( li.querySelector( ':scope > * .bm-expand-btn, :scope > .bm-expand-btn' ) ) return;

      const target = rowOf( li ).querySelector( '.structure-item-actions' )
                  || rowOf( li ).querySelector( '.actions' )
                  || rowOf( li );
      if ( target.querySelector( '.bm-expand-btn' ) ) return;

      // Replicamos la estructura nativa de Bricks (<li class="action"> con
      // .bricks-svg-wrapper > svg.bricks-svg) para heredar su tamaño y alineado.
      const btn = document.createElement( 'li' );
      btn.className = 'action bm-expand-btn';
      btn.title     = 'Expand / collapse all children';
      btn.setAttribute( 'role', 'button' );
      btn.setAttribute( 'tabindex', '0' );
      btn.setAttribute( 'aria-label', 'Expand or collapse all children' );
      // Flechas diagonales en viewBox 14×14 (igual que los iconos nativos).
      btn.innerHTML = '<span class="bricks-svg-wrapper" data-name="bm-expand">'
        + '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14" '
        + 'class="bricks-svg">'
        + '<polyline points="9 1 13 1 13 5" stroke="currentColor" stroke-width="1" '
        + 'stroke-linecap="round" stroke-linejoin="round"/>'
        + '<line x1="13" y1="1" x2="8" y2="6" stroke="currentColor" stroke-width="1" '
        + 'stroke-linecap="round" stroke-linejoin="round"/>'
        + '<polyline points="5 13 1 13 1 9" stroke="currentColor" stroke-width="1" '
        + 'stroke-linecap="round" stroke-linejoin="round"/>'
        + '<line x1="1" y1="13" x2="6" y2="8" stroke="currentColor" stroke-width="1" '
        + 'stroke-linecap="round" stroke-linejoin="round"/>'
        + '</svg></span>';

      btn.addEventListener( 'click', e => {
        e.preventDefault();
        e.stopPropagation();
        toggleSubtree( id );
      } );

      target.appendChild( btn );
    } );
  }

  // ── Init ────────────────────────────────────────────────────────────────────
  waitForBricks( () => {
    // Observer de estructura compartido (centralizado en bricksmate-utils):
    // un único MutationObserver para todos los módulos.
    BricksMate.utils.onStructureChange( injectButtons );
  } );

} )();
