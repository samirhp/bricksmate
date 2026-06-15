<?php
/**
 * BricksMate — Módulo HTML Tags Manager
 * Añade un selector de etiqueta HTML interactivo en el panel de estructura.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts',    'bricksmate_html_tags_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_html_tags_enqueue', 9999 );

function bricksmate_html_tags_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    // CSS del módulo (archivo; consume los tokens de core.css)
    $css = $module_path . 'html-tags.css';
    if ( file_exists( $css ) ) {
        wp_enqueue_style(
            'bricksmate-html-tags',
            $module_url . 'html-tags.css',
            [ 'bricksmate-core' ],
            filemtime( $css )
        );
    }

    // JS del módulo (depende de las utilidades compartidas)
    wp_enqueue_script(
        'bricksmate-html-tags',
        $module_url . 'html-tags.js',
        [ 'bricksmate-utils' ],
        file_exists( $module_path . 'html-tags.js' )
            ? filemtime( $module_path . 'html-tags.js' )
            : '1.0',
        true
    );
}
