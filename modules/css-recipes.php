<?php
/**
 * BricksMate — Módulo CSS Recipes
 *
 * Expande atajos @recipe-name; en los paneles CSS del editor de Bricks.
 *
 * ┌─ CÓMO AÑADIR NUEVAS RECETAS ──────────────────────────────────────────────
 * │
 * │  Opción A — Editar el archivo JSON (recomendado para uso propio):
 * │    modules/recipes.json
 * │    Añade una entrada con este formato:
 * │    "nombre-del-atajo": {
 * │        "label":       "Nombre legible",
 * │        "description": "Descripción breve",
 * │        "css":         "propiedad: valor;\notro: valor;"
 * │    }
 * │
 * │  Opción B — Filtro PHP (para plugins/temas hijos que quieran extender):
 * │    add_filter( 'bricksmate_css_recipes', function( $recipes ) {
 * │        $recipes['mi-receta'] = [
 * │            'label'       => 'Mi Receta',
 * │            'description' => 'Descripción',
 * │            'css'         => 'display: flex;\nalign-items: center;',
 * │        ];
 * │        return $recipes;
 * │    } );
 * │
 * └───────────────────────────────────────────────────────────────────────────
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts',    'bricksmate_css_recipes_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_css_recipes_enqueue', 9999 );

function bricksmate_css_recipes_enqueue() {

    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) return;

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    // ── 1. Cargar recetas desde JSON ─────────────────────────────────────────
    $json_path = $module_path . 'recipes.json';
    $recipes   = [];

    if ( file_exists( $json_path ) ) {
        $raw     = file_get_contents( $json_path );
        $decoded = json_decode( $raw, true );
        if ( is_array( $decoded ) ) {
            $recipes = $decoded;
        }
    }

    // ── 2. Filtro para añadir/modificar recetas desde PHP ────────────────────
    $recipes = apply_filters( 'bricksmate_css_recipes', $recipes );

    // ── 3. Enrolar el JS del módulo ──────────────────────────────────────────
    wp_enqueue_script(
        'bricksmate-css-recipes',
        $module_url . 'css-recipes.js',
        [ 'bricksmate-utils' ],
        file_exists( $module_path . 'css-recipes.js' )
            ? filemtime( $module_path . 'css-recipes.js' )
            : '1.0',
        true
    );

    // ── 4. Pasar recetas directamente al script que las necesita ─────────────
    //       wp_localize_script garantiza que BricksMateRecipes esté disponible
    //       justo antes de que css-recipes.js se ejecute, sin depender de timings.
    wp_localize_script( 'bricksmate-css-recipes', 'BricksMateRecipes', $recipes );
}
