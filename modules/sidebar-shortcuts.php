<?php
/**
 * BricksMate — Módulo Sidebar Shortcuts
 * Inyecta una barra lateral de accesos rápidos en el panel de estructura.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts',    'bricksmate_sidebar_shortcuts_enqueue', 9999 );
add_action( 'admin_enqueue_scripts', 'bricksmate_sidebar_shortcuts_enqueue', 9999 );

function bricksmate_sidebar_shortcuts_enqueue() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ );
    $module_path = plugin_dir_path( __FILE__ );

    // CSS de la barra lateral (archivo; consume los tokens de core.css)
    $css = $module_path . 'sidebar-shortcuts.css';
    if ( file_exists( $css ) ) {
        wp_enqueue_style(
            'bricksmate-sidebar-shortcuts',
            $module_url . 'sidebar-shortcuts.css',
            [ 'bricksmate-core' ],
            filemtime( $css )
        );
    }

    // JS del módulo (depende de las utilidades compartidas)
    wp_enqueue_script(
        'bricksmate-sidebar-shortcuts',
        $module_url . 'sidebar-shortcuts.js',
        [ 'bricksmate-utils' ],
        file_exists( $module_path . 'sidebar-shortcuts.js' )
            ? filemtime( $module_path . 'sidebar-shortcuts.js' )
            : '1.0',
        true
    );

    // Riel configurable: la lista de elementos viene de los ajustes del plugin
    // (configurable desde el panel). Cada item lleva su nombre de elemento Bricks,
    // su etiqueta y su icono SVG inline (catálogo único en bricksmate.php).
    $items = [];
    if ( function_exists( 'bricksmate_sidebar_active' ) && function_exists( 'bricksmate_sidebar_catalog' ) ) {
        $catalog = bricksmate_sidebar_catalog();
        foreach ( bricksmate_sidebar_active() as $name ) {
            if ( isset( $catalog[ $name ] ) ) {
                $items[] = [
                    'id'    => $name,
                    'label' => $catalog[ $name ][0],
                    'svg'   => $catalog[ $name ][1],
                    'ti'    => isset( $catalog[ $name ][2] ) ? $catalog[ $name ][2] : '',
                ];
            }
        }
    }
    wp_localize_script( 'bricksmate-sidebar-shortcuts', 'BricksMateSidebar', [ 'items' => $items ] );
}
