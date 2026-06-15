<?php
/**
 * BricksMate — Módulo Style Indicator
 * Muestra indicadores de color en el panel de estructura de Bricks.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'bricksmate_style_indicator_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_style_indicator_enqueue', 9999 );

function bricksmate_style_indicator_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    // CSS de los indicadores (archivo; consume los tokens de core.css)
    $css = $module_path . 'style-indicator.css';
    if ( file_exists( $css ) ) {
        wp_enqueue_style(
            'bricksmate-style-indicator',
            $module_url . 'style-indicator.css',
            [ 'bricksmate-core' ],
            filemtime( $css )
        );
    }

    // JS del módulo (depende de las utilidades compartidas)
    wp_enqueue_script(
        'bricksmate-style-indicator',
        $module_url . 'style-indicator.js',
        [ 'bricksmate-utils' ],
        file_exists( $module_path . 'style-indicator.js' )
            ? filemtime( $module_path . 'style-indicator.js' )
            : '1.0',
        true
    );
}
