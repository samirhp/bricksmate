<?php
/**
 * BricksMate — Módulo Expand Children
 * Añade un botón en cada elemento del panel de estructura que tenga hijos para
 * expandir recursivamente todo su subárbol de una sola vez.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts',    'bricksmate_expand_children_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_expand_children_enqueue', 9999 );

function bricksmate_expand_children_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );
    $js          = $module_path . 'expand-children.js';

    wp_enqueue_script(
        'bricksmate-expand-children',
        $module_url . 'expand-children.js',
        [ 'bricksmate-utils' ],
        file_exists( $js ) ? filemtime( $js ) : '1.0',
        true
    );
}
