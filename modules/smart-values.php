<?php
/**
 * BricksMate — Módulo Smart Values
 * Convierte valores al pulsar Enter en cualquier campo del editor de Bricks:
 *   --space-m            → var(--space-m)
 *   var(--space-m) * 2   → calc(var(--space-m) * 2)
 *   100% - 20px          → calc(100% - 20px)
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts',    'bricksmate_smart_values_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_smart_values_enqueue', 9999 );

function bricksmate_smart_values_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );
    $js          = $module_path . 'smart-values.js';

    wp_enqueue_script(
        'bricksmate-smart-values',
        $module_url . 'smart-values.js',
        [ 'bricksmate-utils' ],
        file_exists( $js ) ? filemtime( $js ) : '1.0',
        true
    );
}
