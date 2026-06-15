<?php
/**
 * BricksMate — Módulo Export ID Styles to Class
 * Añade una opción al menú contextual del elemento activo en Bricks Builder
 * para exportar todos los estilos aplicados a nivel de ID a la primera clase
 * CSS global que el usuario haya asignado manualmente al elemento.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts',    'bricksmate_export_id_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_export_id_enqueue', 9999 );

function bricksmate_export_id_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    // CSS del módulo (archivo; consume los tokens de core.css)
    $css = $module_path . 'export-id-to-class.css';
    if ( file_exists( $css ) ) {
        wp_enqueue_style(
            'bricksmate-export-id-to-class',
            $module_url . 'export-id-to-class.css',
            [ 'bricksmate-core' ],
            filemtime( $css )
        );
    }

    wp_enqueue_script(
        'bricksmate-export-id-to-class',
        $module_url . 'export-id-to-class.js',
        [ 'bricksmate-utils' ],
        file_exists( $module_path . 'export-id-to-class.js' )
            ? filemtime( $module_path . 'export-id-to-class.js' )
            : '1.0',
        true
    );
}
