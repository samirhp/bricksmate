/**
 * BricksMate — Smart Values
 *
 * Al pulsar Enter dentro de un input de control de Bricks, convierte el valor:
 *   --space-m            → var(--space-m)        (custom property suelta → var)
 *   var(--space-m) * 2   → calc(var(--space-m) * 2)
 *   100% - 20px          → calc(100% - 20px)     (expresión matemática → calc)
 *
 * Mecanismo: igual que css-recipes.js — se intercepta la tecla, se reescribe el
 * valor con el setter nativo y se disparan los eventos input/change para que el
 * v-model de Vue (Bricks) registre el cambio.
 */
( function () {
  'use strict';

  const { setInputValue } = BricksMate.utils;

  // ── Lógica de conversión ────────────────────────────────────────────────────

  // ¿El valor es una expresión matemática de nivel superior?
  //  - "*" y "/" son inequívocos como operadores.
  //  - "+" y "-" solo cuentan si van rodeados de espacios, para no confundir
  //    con números negativos ("-20px") ni con nombres de variable ("--space-m").
  function isMathExpression( v ) {
    return /[*/]/.test( v ) || /\s[+-]\s/.test( v );
  }

  // Funciones CSS que ya envuelven su propia expresión: no las metemos en calc().
  // OJO: "var(" no entra aquí a propósito, porque "var(--x) * 2" SÍ debe ir a calc.
  function isWrappedFunction( v ) {
    return /^(calc|clamp|min|max)\s*\(/i.test( v );
  }

  // Custom property suelta: "--algo" (sin var, sin operadores)
  function isBareCustomProperty( v ) {
    return /^--[A-Za-z0-9_-]+$/.test( v );
  }

  // Devuelve el valor convertido, o el mismo valor si no hay nada que hacer.
  function convertValue( raw ) {
    const v = raw.trim();
    if ( ! v ) return raw;

    // 1) Custom property suelta → var()
    if ( isBareCustomProperty( v ) ) {
      return 'var(' + v + ')';
    }

    // 2) Expresión matemática (y no ya envuelta en calc/clamp/min/max) → calc()
    if ( isMathExpression( v ) && ! isWrappedFunction( v ) ) {
      return 'calc(' + v + ')';
    }

    return raw;
  }

  // ── ¿Es un input de control válido de Bricks? ───────────────────────────────
  function isEligibleInput( el ) {
    if ( ! el || el.tagName !== 'INPUT' ) return false;

    // Solo campos de texto/número/sin tipo (los de valores CSS)
    const type = ( el.getAttribute( 'type' ) || 'text' ).toLowerCase();
    if ( [ 'checkbox', 'radio', 'range', 'color', 'file', 'submit', 'button' ].includes( type ) ) {
      return false;
    }

    // Debe estar dentro del panel de controles de Bricks
    if ( ! el.closest( '#bricks-panel' ) ) return false;

    // Excluir nuestros propios inputs del plugin (panel de ajustes, BEM, etc.)
    if ( /^(bm-|bbem-)/.test( el.id ) ) return false;
    if ( el.closest( '#bm-settings-panel, .bbem-panel' ) ) return false;

    return true;
  }

  // ── Listener único en document ──────────────────────────────────────────────
  document.addEventListener( 'keydown', function ( e ) {
    if ( e.key !== 'Enter' ) return;

    const el = e.target;
    if ( ! isEligibleInput( el ) ) return;

    const converted = convertValue( el.value );
    if ( converted === el.value ) return;

    // Evitamos que el Enter "confirme/cierre" antes de reescribir el valor.
    e.preventDefault();
    e.stopPropagation();

    setInputValue( el, converted, { setCursor: true } );
  }, true ); // capture: nos adelantamos a los handlers de Bricks

} )();
