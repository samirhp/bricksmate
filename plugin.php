<?php
/**
 * Plugin Name: BricksMate
 * Description: Your personal toolkit for Bricks Builder.
 * Version: 2.0.2
 * Author: Samir Haddad
 * Author URI: https://samirh.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( "ABSPATH" ) ) {
    exit;
}

// Plugin Update Checker — auto-updates from the GitHub repo (branch main).
require plugin_dir_path( __FILE__ ) . "plugin-update-checker/plugin-update-checker.php";
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$bricksmateUpdateChecker = PucFactory::buildUpdateChecker(
    "https://github.com/samirhp/bricksmate",
    __FILE__,
    "bricksmate"
);
// Release mode: updates come from published GitHub Releases and download the
// attached bricksmate.zip asset (clean folder structure, no manual download).
$bricksmateUpdateChecker->getVcsApi()->enableReleaseAssets( '/bricksmate\.zip$/i' );


// ─────────────────────────────────────────────────────────────────────────────
// 1. SETTINGS: read active modules
// ─────────────────────────────────────────────────────────────────────────────
$bricksmate_settings = get_option( 'bricksmate_active_modules', [
    'bem_generator'      => true,
    'style_indicator'    => true,
    'sidebar_shortcuts'  => true,
    'html_tags'          => true,
    'auto_select_class'  => true,
    'export_id_to_class' => true,
    'css_recipes'        => true,
    'expand_children'    => true,
    'smart_values'       => true,
] );

// ─────────────────────────────────────────────────────────────────────────────
// 2. MODULES: conditional loading
// ─────────────────────────────────────────────────────────────────────────────
$modules = [
    'bem_generator'      => 'modules/bem-generator.php',
    'style_indicator'    => 'modules/style-indicator.php',
    'sidebar_shortcuts'  => 'modules/sidebar-shortcuts.php',
    'html_tags'          => 'modules/html-tags.php',
    'auto_select_class'  => 'modules/auto-select-class.php',
    'export_id_to_class' => 'modules/export-id-to-class.php',
    'css_recipes'        => 'modules/css-recipes.php',
    'expand_children'    => 'modules/expand-children.php',
    'smart_values'       => 'modules/smart-values.php',
];

foreach ( $modules as $key => $file ) {
    $path = plugin_dir_path( __FILE__ ) . $file;
    if ( ! empty( $bricksmate_settings[ $key ] ) && file_exists( $path ) ) {
        require_once $path;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. HELPERS: shared SVG + Sidebar Shortcuts element catalog
//    (top-level functions are hoisted, so modules loaded above can call these)
// ─────────────────────────────────────────────────────────────────────────────
function bricksmate_svg( $paths ) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" '
         . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

/**
 * Catalog of elements that can live in the Sidebar Shortcuts rail.
 * key = Bricks element `name`; value = [ label, inline SVG icon, themify class ].
 * The themify class (when present) is used by the RAIL for the native Bricks look;
 * the inline SVG is the fallback (and what the settings panel customizer shows).
 */
function bricksmate_sidebar_catalog() {
    return [
        'section'    => [ 'Section',    bricksmate_svg( '<rect x="3" y="5" width="18" height="14" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/>' ),                       'bbem-ti-section' ],
        'container'  => [ 'Container',  bricksmate_svg( '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="8" y="8" width="8" height="8" rx="1"/>' ),               'bbem-ti-container' ],
        'block'      => [ 'Block',      bricksmate_svg( '<rect x="4" y="7" width="16" height="10" rx="2"/>' ),                                                              'bbem-ti-block' ],
        'div'        => [ 'Div',        bricksmate_svg( '<rect x="4" y="4" width="16" height="16" rx="2" stroke-dasharray="3 3"/>' ),                                       'bbem-ti-div' ],
        'heading'    => [ 'Heading',    bricksmate_svg( '<path d="M6 4v16M18 4v16M6 12h12"/>' ),                                                                            'bbem-ti-heading' ],
        'text-basic' => [ 'Basic Text', bricksmate_svg( '<line x1="5" y1="7" x2="19" y2="7"/><line x1="5" y1="12" x2="19" y2="12"/><line x1="5" y1="17" x2="13" y2="17"/>' ), 'bbem-ti-text-basic' ],
        'text'       => [ 'Rich Text',  bricksmate_svg( '<path d="M7 5h8a3 3 0 0 1 0 6H7z"/><path d="M7 5v14"/><line x1="13" y1="11" x2="13" y2="19"/>' ),                  'ti-align-left' ],
        'button'     => [ 'Button',     bricksmate_svg( '<rect x="3" y="8" width="18" height="8" rx="4"/>' ),                                                              'bbem-ti-button' ],
        'icon'       => [ 'Icon',       bricksmate_svg( '<polygon points="12 3 14.6 9 21 9.6 16 13.7 17.6 20 12 16.5 6.4 20 8 13.7 3 9.6 9.4 9"/>' ),                       'bbem-ti-icon' ],
        'image'      => [ 'Image',      bricksmate_svg( '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5-5L5 20"/>' ), 'bbem-ti-image' ],
        'text-link'  => [ 'Text Link',  bricksmate_svg( '<path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/>' ),  '' ],
        'video'      => [ 'Video',      bricksmate_svg( '<rect x="3" y="5" width="18" height="14" rx="2"/><polygon points="10 9 16 12 10 15"/>' ),                          '' ],
        'divider'    => [ 'Divider',    bricksmate_svg( '<line x1="3" y1="12" x2="21" y2="12"/>' ),                                                                         '' ],
        'code'       => [ 'Code',       bricksmate_svg( '<polyline points="16 8 20 12 16 16"/><polyline points="8 8 4 12 8 16"/>' ),                                        '' ],
        'icon-box'   => [ 'Icon Box',   bricksmate_svg( '<rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="12" cy="9.5" r="2"/><line x1="8" y1="15" x2="16" y2="15"/>' ), '' ],
    ];
}

/** Default rail (the original nine), used until the user customizes it. */
function bricksmate_sidebar_default() {
    return [ 'section', 'container', 'block', 'div', 'heading', 'text-basic', 'button', 'icon', 'image' ];
}

/** Current rail: stored option, filtered to valid catalog keys, with default fallback. */
function bricksmate_sidebar_active() {
    $catalog = bricksmate_sidebar_catalog();
    $stored  = get_option( 'bricksmate_sidebar_elements', null );
    if ( ! is_array( $stored ) ) {
        return bricksmate_sidebar_default();
    }
    $active = array_values( array_filter( $stored, function ( $name ) use ( $catalog ) {
        return isset( $catalog[ $name ] );
    } ) );
    return $active; // may legitimately be empty if the user removed everything
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. MAIN SCRIPT: enqueue shared assets + pass the nonce safely
// ─────────────────────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts',    'bricksmate_enqueue_main', 9998 );
add_action( 'admin_enqueue_scripts', 'bricksmate_enqueue_main', 9998 );

function bricksmate_enqueue_main() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }

    $module_url  = plugin_dir_url( __FILE__ ) . 'modules/';
    $module_path = plugin_dir_path( __FILE__ ) . 'modules/';
    $utils_path  = $module_path . 'bricksmate-utils.js';

    // Design-system tokens (--bm-*): ALWAYS, before any other style, so every
    // module can consume the variables even if the BEM module (style.css) is off.
    $core_css = $module_path . 'core.css';
    if ( file_exists( $core_css ) && ! wp_style_is( 'bricksmate-core', 'enqueued' ) ) {
        wp_enqueue_style( 'bricksmate-core', $module_url . 'core.css', [], filemtime( $core_css ) );
    }

    // Shared utilities (if bem-generator.php didn't enqueue them already).
    if ( file_exists( $utils_path ) && ! wp_script_is( 'bricksmate-utils', 'enqueued' ) ) {
        wp_enqueue_script( 'bricksmate-utils', $module_url . 'bricksmate-utils.js', [], filemtime( $utils_path ), true );
    }

    // Nonce via wp_localize_script (cache-safe).
    wp_localize_script( 'bricksmate-utils', 'BricksMateConfig', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'bm_ajax_nonce' ),
    ] );
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. SETTINGS PANEL (master–detail) UI
// ─────────────────────────────────────────────────────────────────────────────
add_action( 'wp_footer',    'bricksmate_builder_ui_and_modal', 9999 );
add_action( 'admin_footer', 'bricksmate_builder_ui_and_modal', 9999 );

function bricksmate_builder_ui_and_modal() {
    if ( ! function_exists( 'bricks_is_builder' ) || ! bricks_is_builder() ) {
        return;
    }
    global $bricksmate_settings;

    // Cross-promo: URL of the BricksMate DS app.
    $bm_ds_app_url = 'https://ds.samirh.com';

    // Module metadata (single source of truth for the panel).
    $i = function ( $p ) { return bricksmate_svg( $p ); };
    $modules = [
        [
            'key' => 'bem_generator', 'label' => 'BEM Generator',
            'icon' => $i( '<path d="M8 4c-2 0-3 1-3 3v2c0 1-1 2-2 2 1 0 2 1 2 2v2c0 2 1 3 3 3"/><path d="M16 4c2 0 3 1 3 3v2c0 1 1 2 2 2-1 0-2 1-2 2v2c0 2-1 3-3 3"/>' ),
            'desc' => 'Generate and rename CSS classes with BEM naming (block__element--modifier) in bulk, right from the structure panel.',
            'example' => '<div class="bm-tree"><div class="bm-tree-row"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="7" width="16" height="10" rx="2"/></svg></span><span class="bm-tree-in">card</span><span class="bm-tree-role">block</span></div><div class="bm-tree-row" style="margin-left:16px;"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16M18 4v16M6 12h12"/></svg></span><span class="bm-tree-in">card</span><span class="bm-tree-el">__title</span><span class="bm-tree-role">element</span></div><div class="bm-tree-row" style="margin-left:16px;"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 16l-5-5L5 20"/></svg></span><span class="bm-tree-in">card</span><span class="bm-tree-el">__media</span><span class="bm-tree-role">element</span></div><div class="bm-tree-row" style="margin-left:32px;"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="8" rx="4"/></svg></span><span class="bm-tree-in">card</span><span class="bm-tree-el">__cta</span><span class="bm-tree-mod">--active</span><span class="bm-tree-role">modifier</span></div></div>',
        ],
        [
            'key' => 'style_indicator', 'label' => 'Style Indicators',
            'icon' => $i( '<rect x="4" y="4" width="6" height="16" rx="1"/><rect x="14" y="4" width="6" height="16" rx="1"/>' ),
            'desc' => 'Paints a colored bar on each element in the structure tree depending on how its styles are applied.',
            'example' => '__indicators__',
        ],
        [
            'key' => 'sidebar_shortcuts', 'label' => 'Sidebar Shortcuts',
            'icon' => $i( '<rect x="3" y="4" width="18" height="16" rx="2"/><line x1="15" y1="4" x2="15" y2="20"/>' ),
            'desc' => 'A side rail with shortcuts to insert elements in one click. Customize which elements appear below.',
            'example' => '__sidebar__',
        ],
        [
            'key' => 'html_tags', 'label' => 'HTML Tags',
            'icon' => $i( '<polyline points="16 8 20 12 16 16"/><polyline points="8 8 4 12 8 16"/>' ),
            'desc' => "Change an element's semantic HTML tag (section, article, ul, nav…) directly from the structure panel.",
            'example' => '<div class="bm-tree"><div class="bm-tree-row"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span><span class="bm-tree-badge">SECTION</span><span class="bm-tree-name">Section</span></div><div class="bm-tree-row" style="margin-left:16px;"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="8" y="8" width="8" height="8" rx="1"/></svg></span><span class="bm-tree-badge">DIV</span><span class="bm-tree-name">Container</span></div><div class="bm-tree-row" style="margin-left:32px;"><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16M18 4v16M6 12h12"/></svg></span><span class="bm-tree-badge">H3</span><span class="bm-tree-name">Heading</span></div></div>',
        ],
        [
            'key' => 'auto_select_class', 'label' => 'Auto-Select Class',
            'icon' => $i( '<path d="M4 4l7 16 2-6 6-2z"/>' ),
            'desc' => "When you create or rename a class, it's selected automatically so you can keep styling without extra clicks.",
            'example' => '<div class="bm-ex"><div style="display:flex;align-items:center;gap:8px;border:1px solid var(--bm-color-border);border-radius:6px;padding:7px 9px;background:var(--bm-color-bg);"><span style="font-size:11px;font-weight:600;padding:4px 9px;border-radius:5px;background:var(--bm-color-accent);color:#fff;">.card</span><span style="margin-left:auto;display:flex;gap:10px;color:#5f5f5f;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l7 16 2-6 6-2z"/></svg></span></div><span style="align-self:flex-start;font-size:11px;font-weight:600;padding:3px 8px;border-radius:5px;background:var(--bm-color-accent-soft);color:#a99cf8;">.card</span></div>',
        ],
        [
            'key' => 'export_id_to_class', 'label' => 'Export ID Styles to Class',
            'icon' => $i( '<path d="M12 3v11"/><polyline points="8 7 12 3 16 7"/><path d="M5 14v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/>' ),
            'desc' => "Moves the styles applied to the element's ID over to its first global CSS class, in a single click.",
            'example' => '<div class="bm-ex"><div class="bm-ex-row"><div class="bm-ex-col"><span class="bm-ex-cap">Before</span><span class="bm-ex-in">#id { padding }</span></div><span class="bm-ex-arrow">→</span><div class="bm-ex-col"><span class="bm-ex-cap">After</span><span class="bm-ex-out">.card { padding }</span></div></div></div>',
        ],
        [
            'key' => 'css_recipes', 'label' => 'CSS Recipes',
            'icon' => $i( '<line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/>' ),
            'desc' => 'Type a shortcut like @clickable-parent; in the CSS panel and it expands into a full CSS block. Reusable recipes.',
            'example' => '__recipes__',
        ],
        [
            'key' => 'expand_children', 'label' => 'Expand Children',
            'icon' => $i( '<rect x="9" y="3" width="6" height="5" rx="1"/><rect x="3" y="16" width="6" height="5" rx="1"/><rect x="15" y="16" width="6" height="5" rx="1"/><path d="M12 8v4M6 16v-2h12v2"/>' ),
            'desc' => 'Adds a button on every element with children to expand or collapse its whole subtree at once.',
            'example' => '<div class="bm-tree"><div class="bm-tree-row"><span class="bm-tree-chev">▾</span><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span><span class="bm-tree-name">Section</span><span class="bm-tree-expand"><svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 1 13 1 13 5"/><line x1="13" y1="1" x2="8" y2="6"/><polyline points="5 13 1 13 1 9"/><line x1="1" y1="13" x2="6" y2="8"/></svg></span></div><div class="bm-tree-row" style="margin-left:16px;"><span class="bm-tree-chev">▾</span><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="8" y="8" width="8" height="8" rx="1"/></svg></span><span class="bm-tree-name">Container</span></div><div class="bm-tree-row" style="margin-left:32px;"><span class="bm-tree-chev"></span><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v16M18 4v16M6 12h12"/></svg></span><span class="bm-tree-name">Heading</span></div><div class="bm-tree-row" style="margin-left:32px;"><span class="bm-tree-chev"></span><span class="bm-tree-i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="8" rx="4"/></svg></span><span class="bm-tree-name">Button</span></div></div>',
        ],
        [
            'key' => 'smart_values', 'label' => 'Smart Values',
            'icon' => $i( '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M18 15l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>' ),
            'desc' => 'Press Enter and --space-m becomes var(--space-m); an operation like var(--x) * 2 is wrapped in calc(...).',
            'example' => '<div class="bm-ex"><div class="bm-ex-row"><div class="bm-ex-col"><span class="bm-ex-cap">Type</span><span class="bm-ex-in">--space-m</span></div><span class="bm-ex-arrow">→</span><div class="bm-ex-col"><span class="bm-ex-cap">Result</span><span class="bm-ex-out">var(--space-m)</span></div></div><div class="bm-ex-row"><div class="bm-ex-col"><span class="bm-ex-cap">Type</span><span class="bm-ex-in">--x * 2</span></div><span class="bm-ex-arrow">→</span><div class="bm-ex-col"><span class="bm-ex-cap">Result</span><span class="bm-ex-out">calc(--x * 2)</span></div></div></div>',
        ],
    ];

    // Mark enabled state from saved settings.
    foreach ( $modules as &$m ) {
        $m['enabled'] = ! empty( $bricksmate_settings[ $m['key'] ] );
    }
    unset( $m );

    // Recipes list (read from the single source recipes.json).
    $recipes      = [];
    $recipes_file = plugin_dir_path( __FILE__ ) . 'modules/recipes.json';
    if ( file_exists( $recipes_file ) ) {
        $raw = json_decode( file_get_contents( $recipes_file ), true );
        if ( is_array( $raw ) ) {
            foreach ( $raw as $name => $r ) {
                $recipes[] = [
                    'name'        => $name,
                    'description' => isset( $r['description'] ) ? $r['description'] : ( isset( $r['label'] ) ? $r['label'] : '' ),
                ];
            }
        }
    }

    // Sidebar Shortcuts catalog + current rail.
    $catalog = bricksmate_sidebar_catalog();
    $sidebar_catalog = [];
    foreach ( $catalog as $cname => $c ) {
        $sidebar_catalog[ $cname ] = [ 'label' => $c[0], 'svg' => $c[1] ];
    }

    $panel_data = [
        'modules'        => $modules,
        'recipes'        => $recipes,
        'sidebarActive'  => bricksmate_sidebar_active(),
        'sidebarCatalog' => $sidebar_catalog,
    ];
    ?>
    <style>
        #bm-settings-panel { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:640px; max-width:94vw; background:var(--bm-color-bg); border:1px solid var(--bm-color-border); border-radius:var(--bm-radius-md); box-shadow:var(--bm-shadow-panel); z-index:999999; font-family:var(--bm-font-ui); color:var(--bm-color-text-soft); overflow:hidden; }
        #bm-settings-panel.bm-active { display:flex; flex-direction:column; }
        .bm-panel-header { display:flex; justify-content:space-between; align-items:center; background:var(--bm-color-bg-elevated); padding:11px 15px; border-bottom:1px solid var(--bm-color-border); cursor:grab; }
        .bm-panel-header:active { cursor:grabbing; }
        .bm-panel-title { margin:0; font-size:13px; font-weight:600; color:var(--bm-color-text); letter-spacing:.2px; display:flex; align-items:center; gap:8px; }
        .bm-panel-title .bm-logo { width:18px; height:18px; display:block; flex-shrink:0; }
        .bm-close-btn { cursor:pointer; color:var(--bm-color-text-muted); font-size:20px; line-height:1; transition:var(--bm-transition-normal); display:flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:var(--bm-radius-sm); }
        .bm-close-btn:hover { background:var(--bm-color-border); color:#fff; }
        .bm-panel-body { display:flex; min-height:360px; }
        .bm-list { width:228px; flex-shrink:0; border-right:1px solid var(--bm-color-border-subtle); padding:8px; display:flex; flex-direction:column; gap:2px; overflow-y:auto; max-height:62vh; }
        .bm-detail { flex:1; min-width:0; padding:18px 20px; overflow-y:auto; max-height:62vh; }
        .bm-item { display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:6px; cursor:pointer; border:1px solid transparent; transition:var(--bm-transition-fast); }
        .bm-item:hover { background:var(--bm-color-bg-elevated); }
        .bm-item.bm-sel { background:var(--bm-color-accent-soft); border-color:var(--bm-color-accent); }
        .bm-item .bm-ico, .bm-detail .bm-ico { width:18px; height:18px; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--bm-color-text-muted); }
        .bm-item.bm-sel .bm-ico, .bm-detail .bm-ico { color:var(--bm-color-accent); }
        .bm-item .bm-ico svg, .bm-detail .bm-ico svg { width:18px; height:18px; display:block; }
        .bm-item .bm-name { flex:1; font-size:12px; color:var(--bm-color-text-soft); }
        .bm-item.bm-sel .bm-name { color:var(--bm-color-text); }
        .bm-switch { position:relative; display:inline-block; width:26px; height:14px; flex-shrink:0; }
        .bm-switch input { opacity:0; width:0; height:0; }
        .bm-slider { position:absolute; cursor:pointer; inset:0; background:var(--bm-color-bg); border:1px solid var(--bm-color-border); transition:var(--bm-transition-normal); border-radius:14px; }
        .bm-slider:before { position:absolute; content:""; height:10px; width:10px; left:2px; top:50%; transform:translateY(-50%); background:var(--bm-color-text-muted); transition:var(--bm-transition-normal); border-radius:50%; }
        input:checked + .bm-slider { background:var(--bm-color-accent-soft); border-color:var(--bm-color-accent); }
        input:checked + .bm-slider:before { transform:translate(10px,-50%); background:var(--bm-color-accent); }
        .bm-detail-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px; }
        .bm-detail-title { display:flex; align-items:center; gap:9px; font-size:15px; font-weight:600; color:var(--bm-color-text); }
        .bm-detail-desc { font-size:12.5px; color:var(--bm-color-text-soft); line-height:1.6; margin:0 0 16px; }
        .bm-detail-label { font-size:10px; text-transform:uppercase; letter-spacing:.5px; color:var(--bm-color-text-muted); margin-bottom:8px; }
        .bm-example { background:var(--bm-color-bg-elevated); border:1px solid var(--bm-color-border-subtle); border-radius:6px; padding:14px; }
        .bm-legend { display:flex; gap:18px; margin-top:12px; padding-top:12px; border-top:1px solid var(--bm-color-border-subtle); flex-wrap:wrap; }
        .bm-legend span { display:flex; align-items:center; gap:7px; font-size:11px; color:var(--bm-color-text-soft); }
        .bm-sw { width:11px; height:11px; border-radius:3px; flex-shrink:0; }
        .bm-recipe-list { max-height:150px; overflow-y:auto; display:flex; flex-direction:column; gap:1px; }
        .bm-recipe { display:flex; gap:10px; align-items:baseline; padding:5px 0; border-bottom:1px solid var(--bm-color-border-subtle); }
        .bm-recipe code { font-family:var(--bm-font-mono); font-size:11px; color:var(--bm-color-accent); min-width:140px; }
        .bm-recipe span { font-size:11px; color:var(--bm-color-text-muted); }
        .bm-pill { display:inline-flex; align-items:center; gap:6px; font-size:11px; padding:5px 9px; border-radius:6px; cursor:pointer; transition:var(--bm-transition-fast); }
        .bm-pill .bm-pico { width:14px; height:14px; display:flex; } .bm-pill .bm-pico svg { width:14px; height:14px; }
        .bm-pill-on { background:var(--bm-color-accent-soft); border:1px solid var(--bm-color-accent); color:var(--bm-color-accent); }
        .bm-pill-off { background:var(--bm-color-bg); border:1px dashed var(--bm-color-border); color:var(--bm-color-text-muted); }
        .bm-pill-off:hover { border-color:var(--bm-color-accent); color:var(--bm-color-accent); }
        .bm-promo { display:flex; align-items:center; gap:10px; margin:12px; padding:10px 12px; border:1px solid var(--bm-color-accent-soft); background:#16132b; border-radius:7px; text-decoration:none; transition:var(--bm-transition-normal); }
        .bm-promo:hover { border-color:var(--bm-color-accent); }
        .bm-promo .bm-promo-logo { width:22px; height:22px; display:block; flex-shrink:0; }
        .bm-promo .bm-promo-txt { display:flex; flex-direction:column; line-height:1.3; }
        .bm-promo .bm-promo-txt b { font-size:12px; font-weight:600; color:var(--bm-color-text); }
        .bm-promo .bm-promo-txt span { font-size:11px; color:#9a8fd6; }
        .bm-panel-footer { display:flex; justify-content:space-between; align-items:center; background:var(--bm-color-bg-elevated); padding:10px 15px; border-top:1px solid var(--bm-color-border); }
        .bm-count { font-size:11px; color:var(--bm-color-text-muted); }
        .bm-count b { color:var(--bm-color-accent); font-weight:600; }
        .bm-save-btn { background:var(--bm-color-accent); color:#fff; border:none; padding:8px 16px; border-radius:var(--bm-radius-sm); cursor:pointer; font-size:12px; font-weight:600; letter-spacing:.3px; transition:var(--bm-transition-normal); }
        .bm-save-btn:hover { background:var(--bm-color-accent-hover); }
        .bm-topbar-icon { cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0 12px; color:var(--bm-color-text-muted); transition:var(--bm-transition-normal); }
        .bm-topbar-icon:hover { color:var(--bm-color-text); }
        .bm-topbar-icon svg { display:block; }
        /* design polish */
        .bm-list, .bm-detail { scrollbar-width: thin; scrollbar-color: #3a3a3a transparent; }
        .bm-list::-webkit-scrollbar, .bm-detail::-webkit-scrollbar { width:6px; }
        .bm-list::-webkit-scrollbar-thumb, .bm-detail::-webkit-scrollbar-thumb { background:#3a3a3a; border-radius:6px; }
        .bm-list::-webkit-scrollbar-track, .bm-detail::-webkit-scrollbar-track { background:transparent; }
        .bm-item.bm-sel { background:rgba(117,92,245,.10); border-color:transparent; }
        .bm-item.bm-sel .bm-name { font-weight:500; }
        .bm-detail-head { padding-bottom:12px; margin-bottom:14px; border-bottom:1px solid var(--bm-color-border-subtle); }
        .bm-detail-desc { max-width:52ch; line-height:1.65; }
        .bm-detail-label { letter-spacing:.7px; }
        .bm-detail-label::before { content:""; display:inline-block; width:5px; height:5px; border-radius:50%; background:var(--bm-color-accent); margin-right:7px; vertical-align:middle; }
        .bm-count { font-size:10px; font-weight:600; color:var(--bm-color-accent); background:var(--bm-color-accent-soft); padding:3px 9px; border-radius:20px; }
        /* unified example grammar */
        .bm-ex { font-family:var(--bm-font-mono); font-size:12px; display:flex; flex-direction:column; gap:12px; }
        .bm-ex-row { display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; }
        .bm-ex-col { display:flex; flex-direction:column; gap:4px; }
        .bm-ex-cap { font-family:var(--bm-font-ui); font-size:9px; text-transform:uppercase; letter-spacing:.6px; color:#6a6a6a; }
        .bm-ex-in { color:var(--bm-color-text-muted); }
        .bm-ex-out { color:#a99cf8; }
        .bm-ex-mod { color:var(--bm-color-modifier); }
        .bm-ex-arrow { color:#5a5a5a; padding-bottom:1px; }
        /* tree-style examples (BEM, HTML Tags, Expand Children) */
        .bm-tree { display:flex; flex-direction:column; gap:4px; font-family:var(--bm-font-mono); font-size:12px; }
        .bm-tree-row { display:flex; align-items:center; gap:8px; padding:6px 9px; border:1px solid #2a2a2a; border-radius:5px; background:#161616; }
        .bm-tree-i { width:14px; height:14px; flex-shrink:0; color:#6a6a6a; display:flex; }
        .bm-tree-i svg { width:14px; height:14px; display:block; }
        .bm-tree-chev { width:12px; flex-shrink:0; color:var(--bm-color-text-muted); }
        .bm-tree-name { color:var(--bm-color-text-soft); }
        .bm-tree-in { color:var(--bm-color-text-muted); }
        .bm-tree-el { color:#a99cf8; }
        .bm-tree-mod { color:var(--bm-color-modifier); }
        .bm-tree-badge { font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px; background:var(--bm-color-tag-soft); color:var(--bm-color-tag); letter-spacing:.3px; }
        .bm-tree-role { margin-left:auto; font-family:var(--bm-font-ui); font-size:9px; text-transform:uppercase; letter-spacing:.5px; color:#5f5f5f; }
        .bm-tree-expand { margin-left:auto; color:var(--bm-color-accent); display:flex; }
        .bm-tree-expand svg { width:13px; height:13px; display:block; }
    </style>

    <div id="bm-settings-panel">
        <div class="bm-panel-header" id="bm-panel-header">
            <h3 class="bm-panel-title">
                <svg class="bm-logo" viewBox="0 0 56 56" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs><linearGradient id="bm-logo-grad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8b76f7"/><stop offset="1" stop-color="#5d44e0"/></linearGradient></defs>
                    <rect width="56" height="56" rx="14" fill="url(#bm-logo-grad)"/>
                    <rect x="13" y="13" width="14" height="14" rx="4" fill="#fff"/>
                    <rect x="29" y="13" width="14" height="14" rx="4" fill="#fff" opacity=".68"/>
                    <rect x="13" y="29" width="14" height="14" rx="4" fill="#fff" opacity=".45"/>
                    <rect x="29" y="29" width="14" height="14" rx="4" fill="#fff" opacity=".26"/>
                </svg>
                BricksMate
            </h3>
            <div class="bm-close-btn" id="bm-close-modal" aria-label="Close" role="button" tabindex="0">&times;</div>
        </div>
        <div class="bm-panel-body">
            <div class="bm-list" id="bm-list"></div>
            <div class="bm-detail" id="bm-detail"></div>
        </div>
        <a class="bm-promo" href="<?php echo esc_url( $bm_ds_app_url ); ?>" target="_blank" rel="noopener noreferrer">
            <svg class="bm-promo-logo" viewBox="0 0 56 56" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                <defs><linearGradient id="bm-promo-grad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8b76f7"/><stop offset="1" stop-color="#5d44e0"/></linearGradient></defs>
                <rect width="56" height="56" rx="14" fill="url(#bm-promo-grad)"/>
                <rect x="13" y="13" width="14" height="14" rx="4" fill="#fff"/>
                <rect x="29" y="13" width="14" height="14" rx="4" fill="#fff" opacity=".68"/>
                <rect x="13" y="29" width="14" height="14" rx="4" fill="#fff" opacity=".45"/>
                <rect x="29" y="29" width="14" height="14" rx="4" fill="#fff" opacity=".26"/>
            </svg>
            <span class="bm-promo-txt"><b>BricksMate DS</b><span>Create your design system &rarr;</span></span>
        </a>
        <div class="bm-panel-footer">
            <span class="bm-count"><b id="bm-count">0</b> active</span>
            <button class="bm-save-btn" id="bm-save-settings">Save &amp; reload</button>
        </div>
    </div>

    <script>
    window.BricksMatePanelData = <?php echo wp_json_encode( $panel_data ); ?>;
    document.addEventListener('DOMContentLoaded', () => {
        const D = window.BricksMatePanelData;
        const M = D.modules, CAT = D.sidebarCatalog;
        let sel = 0;
        let sbActive = Array.isArray(D.sidebarActive) ? D.sidebarActive.slice() : [];

        const listEl = document.getElementById('bm-list');
        const detEl  = document.getElementById('bm-detail');
        const cntEl  = document.getElementById('bm-count');

        const sw = (key, on) =>
            '<label class="bm-switch"><input type="checkbox" data-key="' + key + '"' + (on ? ' checked' : '') + '><span class="bm-slider"></span></label>';

        function renderList() {
            listEl.innerHTML = M.map((m, idx) =>
                '<div class="bm-item' + (idx === sel ? ' bm-sel' : '') + '" data-idx="' + idx + '">' +
                    '<span class="bm-ico">' + m.icon + '</span>' +
                    '<span class="bm-name">' + m.label + '</span>' +
                    sw(m.key, m.enabled) +
                '</div>'
            ).join('');
            cntEl.textContent = M.filter(m => m.enabled).length;
        }

        function exampleHTML(m) {
            if (m.example === '__indicators__') {
                const bar = (name, l, r) =>
                    '<div style="position:relative;background:var(--bm-color-bg);border:1px solid var(--bm-color-border-subtle);border-radius:5px;padding:7px 12px;font-size:11px;color:var(--bm-color-text-soft);">' +
                    (l ? '<span style="position:absolute;left:0;top:0;bottom:0;width:3px;background:' + l + ';border-radius:3px 0 0 3px;"></span>' : '') +
                    (r ? '<span style="position:absolute;right:0;top:0;bottom:0;width:3px;background:' + r + ';border-radius:0 3px 3px 0;"></span>' : '') +
                    name + '</div>';
                return '<div style="display:flex;flex-direction:column;gap:6px;">' +
                    bar('Section', 'var(--bm-color-indicator-class)', null) +
                    bar('Container', 'var(--bm-color-indicator-class)', 'var(--bm-color-indicator-id)') +
                    bar('Heading', null, 'var(--bm-color-indicator-id)') +
                    '</div>' +
                    '<div class="bm-legend">' +
                    '<span><span class="bm-sw" style="background:var(--bm-color-indicator-class);"></span>Styles applied via CSS class</span>' +
                    '<span><span class="bm-sw" style="background:var(--bm-color-indicator-id);"></span>Styles applied via ID</span>' +
                    '</div>';
            }
            if (m.example === '__recipes__') {
                const list = D.recipes.map(r =>
                    '<div class="bm-recipe"><code>' + r.name + ';</code><span>' + r.description + '</span></div>'
                ).join('') || '<span style="font-size:11px;color:var(--bm-color-text-muted);">No recipes found.</span>';
                return '<div style="font-family:var(--bm-font-mono);font-size:12px;"><span style="color:var(--bm-color-accent);">@clickable-parent;</span> <span style="color:var(--bm-color-text-muted);">→</span> <span style="color:var(--bm-color-text-soft);">full CSS block</span></div>' +
                    '<div class="bm-detail-label" style="margin-top:14px;">Available recipes (' + D.recipes.length + ')</div>' +
                    '<div class="bm-recipe-list">' + list + '</div>';
            }
            if (m.example === '__sidebar__') {
                const active = sbActive.map(n => CAT[n]
                    ? '<span class="bm-pill bm-pill-on" draggable="true" data-name="' + n + '" style="cursor:grab;"><span class="bm-pico">' + CAT[n].svg + '</span>' + CAT[n].label + ' <span data-rm="' + n + '" style="font-size:13px;cursor:pointer;padding:0 2px;">&times;</span></span>'
                    : '').join('');
                const avail = Object.keys(CAT).filter(n => sbActive.indexOf(n) === -1).map(n =>
                    '<span class="bm-pill bm-pill-off" data-add="' + n + '"><span style="font-size:12px;">+</span><span class="bm-pico">' + CAT[n].svg + '</span>' + CAT[n].label + '</span>'
                ).join('');
                return '<div style="font-size:11px;color:var(--bm-color-text-muted);margin-bottom:7px;">In the rail (drag to reorder, click &times; to remove):</div>' +
                    '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">' + (active || '<span style="font-size:11px;color:var(--bm-color-text-muted);">No elements. Add some below.</span>') + '</div>' +
                    '<div style="font-size:11px;color:var(--bm-color-text-muted);margin-bottom:7px;">Available to add:</div>' +
                    '<div style="display:flex;flex-wrap:wrap;gap:6px;">' + (avail || '<span style="font-size:11px;color:var(--bm-color-text-muted);">All added.</span>') + '</div>';
            }
            return m.example;
        }

        function renderDetail() {
            const m = M[sel];
            detEl.innerHTML =
                '<div class="bm-detail-head"><div class="bm-detail-title"><span class="bm-ico">' + m.icon + '</span>' + m.label + '</div></div>' +
                '<p class="bm-detail-desc">' + m.desc + '</p>' +
                '<div class="bm-detail-label">' + (m.example === '__sidebar__' ? 'Customize' : 'Example') + '</div>' +
                '<div class="bm-example">' + exampleHTML(m) + '</div>';
        }

        function render() { renderList(); renderDetail(); }

        function toggleKey(key) {
            const m = M.find(x => x.key === key);
            if (m) { m.enabled = !m.enabled; render(); }
        }

        listEl.addEventListener('click', (e) => {
            // Toggle: handle the switch click directly (and stop the native toggle +
            // the row-select that would re-render and swallow the toggle).
            const swEl = e.target.closest('.bm-switch');
            if (swEl) {
                e.preventDefault();
                const input = swEl.querySelector('input[data-key]');
                if (input) toggleKey(input.getAttribute('data-key'));
                return;
            }
            const it = e.target.closest('[data-idx]');
            if (it) { sel = +it.dataset.idx; render(); }
        });
        detEl.addEventListener('click', (e) => {
            const rm = e.target.closest('[data-rm]');
            if (rm) { sbActive = sbActive.filter(n => n !== rm.dataset.rm); renderDetail(); return; }
            const ad = e.target.closest('[data-add]');
            if (ad) { sbActive.push(ad.dataset.add); renderDetail(); }
        });

        // Drag to reorder the active Sidebar Shortcuts rail.
        let dragName = null;
        detEl.addEventListener('dragstart', (e) => {
            const p = e.target.closest('[data-name]');
            if (p) { dragName = p.dataset.name; e.dataTransfer.effectAllowed = 'move'; p.style.opacity = '0.5'; }
        });
        detEl.addEventListener('dragend', (e) => {
            const p = e.target.closest('[data-name]'); if (p) p.style.opacity = '';
        });
        detEl.addEventListener('dragover', (e) => {
            if (dragName && e.target.closest('[data-name]')) e.preventDefault();
        });
        detEl.addEventListener('drop', (e) => {
            const t = e.target.closest('[data-name]');
            if (!dragName || !t) { dragName = null; return; }
            e.preventDefault();
            const target = t.dataset.name;
            if (target === dragName) { dragName = null; return; }
            const from = sbActive.indexOf(dragName);
            if (from > -1) sbActive.splice(from, 1);
            const toIdx = sbActive.indexOf(target);
            sbActive.splice(toIdx, 0, dragName); // insert before the target chip
            dragName = null;
            renderDetail();
        });

        // Inject the topbar launcher icon.
        const injectTopbarButton = setInterval(() => {
            const rightToolbar = document.querySelector('#bricks-toolbar .group-wrapper.right, #bricks-toolbar .group-wrapper.end');
            if (rightToolbar && !document.getElementById('bm-topbar-btn')) {
                const li = document.createElement('li');
                li.id = 'bm-topbar-btn';
                li.className = 'bm-topbar-icon';
                li.title = 'BricksMate Settings';
                li.setAttribute('role', 'button');
                li.setAttribute('tabindex', '0');
                li.setAttribute('aria-label', 'BricksMate Settings');
                li.innerHTML = '<svg viewBox="10 10 36 36" width="20" height="20" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><rect x="13" y="13" width="14" height="14" rx="4"/><rect x="29" y="13" width="14" height="14" rx="4" opacity="0.68"/><rect x="13" y="29" width="14" height="14" rx="4" opacity="0.45"/><rect x="29" y="29" width="14" height="14" rx="4" opacity="0.26"/></svg>';
                rightToolbar.insertBefore(li, rightToolbar.firstChild);
                const openPanel = () => {
                    const panel = document.getElementById('bm-settings-panel');
                    panel.classList.add('bm-active');
                    panel.style.top = '50%'; panel.style.left = '50%'; panel.style.transform = 'translate(-50%,-50%)';
                };
                li.onclick = openPanel;
                li.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(); } });
                clearInterval(injectTopbarButton);
            }
        }, 500);

        const closeBtn = document.getElementById('bm-close-modal');
        const closePanel = () => document.getElementById('bm-settings-panel').classList.remove('bm-active');
        closeBtn.onclick = closePanel;
        closeBtn.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); closePanel(); } });

        // Drag the panel by its header.
        const panel = document.getElementById('bm-settings-panel');
        const header = document.getElementById('bm-panel-header');
        let isDragging = false, startX, startY, initialX, initialY;
        header.addEventListener('mousedown', (e) => {
            if (e.target.closest('.bm-close-btn')) return;
            isDragging = true; startX = e.clientX; startY = e.clientY;
            const rect = panel.getBoundingClientRect();
            panel.style.transform = 'none'; panel.style.left = rect.left + 'px'; panel.style.top = rect.top + 'px';
            initialX = rect.left; initialY = rect.top;
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
        function onMouseMove(e) {
            if (!isDragging) return;
            panel.style.left = (initialX + (e.clientX - startX)) + 'px';
            panel.style.top  = (initialY + (e.clientY - startY)) + 'px';
        }
        function onMouseUp() {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        // Save: module flags + sidebar rail.
        document.getElementById('bm-save-settings').onclick = (e) => {
            const btn = e.target;
            btn.innerText = 'Saving…'; btn.style.opacity = '0.7';
            const data = new URLSearchParams();
            data.append('action', 'bricksmate_save_settings_ajax');
            M.forEach(m => data.append(m.key, m.enabled ? 1 : 0));
            data.append('sidebar_elements', sbActive.join(','));
            data.append('nonce', BricksMateConfig.nonce);
            fetch(BricksMateConfig.ajaxUrl, { method: 'POST', body: data })
                .then(res => res.json())
                .then(response => {
                    if (response.success) { btn.innerText = 'Saved! Reloading…'; setTimeout(() => location.reload(), 500); }
                    else { btn.innerText = 'Error!'; btn.style.background = '#f15f5f'; }
                })
                .catch(() => { btn.innerText = 'Error!'; btn.style.background = '#f15f5f'; });
        };

        render();
    });
    </script>
    <?php
}

// ─────────────────────────────────────────────────────────────────────────────
// 6. AJAX HANDLER
// ─────────────────────────────────────────────────────────────────────────────
add_action( 'wp_ajax_bricksmate_save_settings_ajax', 'bricksmate_save_settings_ajax_handler' );

function bricksmate_save_settings_ajax_handler() {
    check_ajax_referer( 'bm_ajax_nonce', 'nonce' );

    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_send_json_error( 'No permission' );
    }

    // Module on/off flags (absint guarantees 0/1).
    $fields = [
        'bem_generator', 'style_indicator', 'sidebar_shortcuts', 'html_tags',
        'auto_select_class', 'export_id_to_class', 'css_recipes',
        'expand_children', 'smart_values',
    ];
    $new_settings = [];
    foreach ( $fields as $field ) {
        $new_settings[ $field ] = isset( $_POST[ $field ] ) && absint( $_POST[ $field ] ) === 1;
    }
    update_option( 'bricksmate_active_modules', $new_settings );

    // Sidebar Shortcuts rail: comma-separated element names, sanitized against the catalog.
    if ( isset( $_POST['sidebar_elements'] ) ) {
        $catalog = bricksmate_sidebar_catalog();
        $raw     = sanitize_text_field( wp_unslash( $_POST['sidebar_elements'] ) );
        $names   = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
        $clean   = array_values( array_filter( $names, function ( $n ) use ( $catalog ) {
            return isset( $catalog[ $n ] );
        } ) );
        update_option( 'bricksmate_sidebar_elements', $clean );
    }

    wp_send_json_success( 'Settings saved' );
}
