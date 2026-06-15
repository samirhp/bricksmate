<?php
/**
 * BricksMate — Módulo Auto-Select Class
 * Selecciona automáticamente la primera clase global del elemento activo.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts',    'bricksmate_auto_select_class_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_auto_select_class_enqueue', 9999 );

function bricksmate_auto_select_class_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    wp_enqueue_script(
        'bricksmate-auto-select-class',
        $module_url . 'auto-select-class.js',
        [ 'bricksmate-utils' ],
        file_exists( $module_path . 'auto-select-class.js' )
            ? filemtime( $module_path . 'auto-select-class.js' )
            : '1.0',
        true
    );
}
