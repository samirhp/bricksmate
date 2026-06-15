<?php
/**
 * BricksMate — Módulo BEM Generator
 * Carga los assets del generador BEM únicamente dentro del editor de Bricks.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'bricksmate_bem_enqueue_assets', 9999 );

function bricksmate_bem_enqueue_assets() {
    if ( ! function_exists( 'bricks_is_builder_main' ) || ! bricks_is_builder_main() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    // 1. Utilidades compartidas (dependencia base de todos los módulos)
    $utils_file = $module_path . 'bricksmate-utils.js';
    if ( file_exists( $utils_file ) ) {
        wp_enqueue_script(
            'bricksmate-utils',
            $module_url . 'bricksmate-utils.js',
            [],
            filemtime( $utils_file ),
            true
        );
    }

    // 2. Tokens del sistema de diseño (--bm-*): dependencia de los estilos BEM,
    //    porque style.css consume estas variables vía los tokens legacy --bbem-*.
    $core_css = $module_path . 'core.css';
    if ( file_exists( $core_css ) && ! wp_style_is( 'bricksmate-core', 'enqueued' ) ) {
        wp_enqueue_style(
            'bricksmate-core',
            $module_url . 'core.css',
            [],
            filemtime( $core_css )
        );
    }

    // 3. CSS del BEM Generator (min si existe, normal si no)
    $css_file = file_exists( $module_path . 'style.min.css' ) ? 'style.min.css' : 'style.css';
    if ( file_exists( $module_path . $css_file ) ) {
        wp_enqueue_style(
            'bricksmate-bem-styles',
            $module_url . $css_file,
            [ 'bricksmate-core' ],
            filemtime( $module_path . $css_file )
        );
    }

    // 3. JS del BEM Generator (min si existe, normal si no)
    $js_file = file_exists( $module_path . 'script.min.js' ) ? 'script.min.js' : 'script.js';
    if ( file_exists( $module_path . $js_file ) ) {
        wp_enqueue_script(
            'bricksmate-bem-script',
            $module_url . $js_file,
            [ 'bricksmate-utils' ],  // depende de las utilidades
            filemtime( $module_path . $js_file ),
            true
        );
    }
}
