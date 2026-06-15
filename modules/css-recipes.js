/**
 * BricksMate — CSS Recipes
 *
 * Expande @recipe-name; en los paneles CSS del editor de Bricks.
 * Mecanismo de expansión:
 *  - keydown (no keyup): previene que el ";" se escriba cuando hay expansión
 *  - CodeMirror v4/v5: usa la API nativa .CodeMirror.doc
 *  - Solo procesa el texto antes del cursor, no el contenido completo
 */

( function () {
  'use strict';

  // ── Recetas ────────────────────────────────────────────────────────────────
  function getRecipes() {
    return ( typeof BricksMateRecipes !== 'undefined' ) ? BricksMateRecipes : {};
  }

  // Aplica expansiones al texto dado.
  // El ";" ya fue interceptado antes de llegar aquí, así que buscamos @nombre al final.
  function applyExpansions( text ) {
    const recipes = getRecipes();
    let result    = text;

    for ( const [ name, recipe ] of Object.entries( recipes ) ) {
      if ( ! recipe.css ) continue;
      // Escapar guiones en el nombre para uso en regex
      const escaped = name.replace( /[-]/g, '\\-' );
      const re      = new RegExp( '@' + escaped + '$' );
      result        = result.replace( re, recipe.css );
    }

    return result;
  }

  // ── CodeMirror v4/v5 (Bricks usa esta versión) ────────────────────────────
  function handleCM( e ) {
    const cmEl = document.querySelector( '.CodeMirror' );
    if ( ! cmEl || ! cmEl.contains( e.target ) ) return false;

    const cm = cmEl.CodeMirror;
    if ( ! cm ) return false;

    const doc      = cm.doc;
    const cursor   = doc.getCursor();
    const lines    = doc.getValue().split( '\n' );
    const line     = lines[ cursor.line ] || '';

    // Texto de la línea actual antes del cursor (sin el ";" que se está tecleando)
    const before   = line.substring( 0, cursor.ch );
    const expanded = applyExpansions( before );

    if ( expanded === before ) return false;

    e.preventDefault();

    // Reemplazar la línea actual con el texto expandido
    lines[ cursor.line ] = expanded + line.substring( cursor.ch );

    const scroll        = cm.getScrollInfo();
    doc.setValue( lines.join( '\n' ) );

    // Colocar el cursor al final del texto expandido
    const expandedLines = expanded.split( '\n' );
    const newLine       = cursor.line + expandedLines.length - 1;
    const newCh         = expandedLines[ expandedLines.length - 1 ].length;
    doc.setCursor( newLine, newCh );
    cm.scrollTo( scroll.left, scroll.top );

    return true;
  }

  // ── Textarea (fallback) ────────────────────────────────────────────────────
  function handleTextarea( e ) {
    const target = e.target;
    if ( target.tagName !== 'TEXTAREA' ) return false;

    // No procesar si hay texto seleccionado
    if ( target.selectionStart !== target.selectionEnd ) return false;

    const cursorPos = target.selectionStart;
    const before    = target.value.substring( 0, cursorPos );
    const expanded  = applyExpansions( before );

    if ( expanded === before ) return false;

    e.preventDefault();

    const newValue = expanded + target.value.substring( cursorPos );
    // Setter nativo + dispatch centralizado en utils (sin setCursor: aquí el
    // cursor va al final del texto EXPANDIDO, no al final del valor completo).
    BricksMate.utils.setInputValue( target, newValue );
    target.selectionStart = expanded.length;
    target.selectionEnd   = expanded.length;

    return true;
  }

  // ── Listener único en document ─────────────────────────────────────────────
  document.addEventListener( 'keydown', function ( e ) {
    if ( e.key !== ';' ) return;
    if ( handleCM( e ) ) return;
    handleTextarea( e );
  } );

} )();
