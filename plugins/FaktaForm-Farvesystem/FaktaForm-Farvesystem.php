<?php
/**
 * Plugin Name:       FaktaForm - Farvesystem
 * Plugin URI:        https://faktaform.dk
 * Description:       Genererer WCAG-sikre 7-farvers paletter baseret på én accentfarve ved hjælp af OKLCH farverummet.
 * Version:           2.1
 * Author:            FaktaForm by Kainø & Co
 * Author URI:        https://faktaform.dk
 * License:           GPL2
 */

if ( !defined('ABSPATH') ) exit; // Stop direkte adgang

/**
 * Tilføjer menupunktet i WordPress admin-menuen.
 */
add_action('admin_menu', 'faktaform_add_admin_menu');
function faktaform_add_admin_menu() {
    add_menu_page(
        'FaktaForm Farvesystem',
        'Farvesystem',
        'manage_options',
        'faktaform-farvesystem',
        'faktaform_admin_page',
        'dashicons-art',
        80
    );
}

/**
 * Indlæser CSS og JavaScript assets på pluginets admin-side.
 */
add_action('admin_enqueue_scripts', 'faktaform_farvesystem_enqueue_admin_assets');
function faktaform_farvesystem_enqueue_admin_assets($hook) {
    // Indlæs kun assets på denne plugins side for at undgå konflikter.
    if ('toplevel_page_faktaform-farvesystem' !== $hook) {
        return;
    }

    $css_file_path = plugin_dir_path(__FILE__) . 'FaktaForm-Farvesystem-admin.css';
    $js_file_path  = plugin_dir_path(__FILE__) . 'FaktaForm-Farvesystem-admin.js';

    wp_enqueue_style(
        'faktaform-farvesystem-admin-style',
        plugin_dir_url(__FILE__) . 'FaktaForm-Farvesystem-admin.css',
        ['wp-color-picker'],
        file_exists($css_file_path) ? filemtime($css_file_path) : '2.1'
    );

    wp_enqueue_script(
        'faktaform-farvesystem-admin-script',
        plugin_dir_url(__FILE__) . 'FaktaForm-Farvesystem-admin.js',
        ['jquery', 'wp-color-picker'],
        file_exists($js_file_path) ? filemtime($js_file_path) : '2.1',
        true // Indlæs i footer
    );
    
    // Gør AJAX URL og nonce tilgængelig for JavaScript.
    wp_localize_script(
        'faktaform-farvesystem-admin-script',
        'faktaform_ajax',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('faktaform_save_colors_nonce')
        ]
    );
}

/**
 * Håndterer AJAX-kaldet for at gemme farver til GeneratePress.
 */
add_action('wp_ajax_faktaform_save_generatepress_colors', 'faktaform_save_generatepress_colors');
function faktaform_save_generatepress_colors() {
    check_ajax_referer('faktaform_save_colors_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Du har ikke tilladelse til at udføre denne handling.', 403);
    }

    if (!isset($_POST['colors'])) {
        wp_send_json_error('Mangler farvedata.', 400);
    }

    $colors = json_decode(stripslashes($_POST['colors']), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($colors)) {
        wp_send_json_error('Ugyldigt farveformat.', 400);
    }

    // Opdater GeneratePress' globale farveindstillinger.
    $gp_settings = get_option('generate_settings', []);
    $gp_settings['global_colors'] = $colors;
    update_option('generate_settings', $gp_settings);

    // Tving GeneratePress til at regenerere sin dynamiske CSS.
    if ( function_exists( 'generate_update_dynamic_css_cache' ) ) {
        generate_update_dynamic_css_cache();
    }

    // Ryd cache i de mest almindelige caching-plugins.
    if ( function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain();
    if ( class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all') ) LiteSpeed_Cache_API::purge_all();
    if ( function_exists( 'w3tc_flush_all' ) ) w3tc_flush_all();
    if ( function_exists( 'sg_cachepress_purge_cache' ) ) sg_cachepress_purge_cache();
    if ( has_action('wp_cache_clear_cache') ) do_action('wp_cache_clear_cache');

    wp_send_json_success('Farverne er blevet gemt og sidens cache er ryddet.');
}

/**
 * Gengiver HTML-strukturen for admin-siden.
 */
function faktaform_admin_page() { 
    $default_color = '#0058E6';
    ?>
    <div class="wrap">
        <h1>FaktaForm Farvesystem</h1>
        <p class="faktaform-palette-intro">
            Vælg en accentfarve, og generer en komplet 7-farvers palette, der er optimeret til webtilgængelighed (WCAG). 
            Paletten er bygget i OKLCH-farverummet for at sikre ensartet visuel lysstyrke.
        </p>

        <div class="faktaform-controls">
            <h3>1. Vælg din accentfarve</h3>
            <div class="faktaform-color-picker-wrapper">
                <div id="faktaform-custom-swatch" class="faktaform-custom-swatch"></div>
                <input type="text" id="faktaform-accent-hex" class="faktaform-hex-display" value="<?php echo esc_attr($default_color); ?>">
                <input type="text" id="faktaform-accent" value="<?php echo esc_attr($default_color); ?>" />
                <button id="faktaform-eyedropper-btn" class="button" title="Vælg farve fra skærmen"><span class="dashicons dashicons-admin-customizer"></span></button>
            </div>

            <h3>2. Vælg palettetype</h3>
            <fieldset>
                <label><input type="radio" name="palette-type" value="monochromatic" checked> Monokromatisk</label>
                <label><input type="radio" name="palette-type" value="complementary"> Komplementær</label>
                <label><input type="radio" name="palette-type" value="analogous"> Analog</label>
            </fieldset>

            <h3>3. Generér og gem</h3>
            <button id="faktaform-generate-btn" class="button button-primary">Hent farvepalette</button>
        </div>

        <hr>

        <div id="faktaform-results-wrapper" style="display:none;">
            <h2>Resultat</h2>
            <div id="faktaform-palette-container" class="faktaform-palette-container">
                <!-- Resultater indsættes her af JavaScript -->
            </div>
            <br>
            <button id="faktaform-save-to-gp" class="button button-primary">Gem farver til GeneratePress</button>
            <span class="spinner"></span>
        </div>
    </div>
<?php
}