<?php
/**
 * Plugin Name:       FaktaForm - SEO
 * Plugin URI:        https://faktaform.dk
 * Description:       Håndterer avanceret SEO, schema-markup og bylines.
 * Version:           1.0.0
 * Author:            Thomas Kainø
 * Author URI:        https://faktaform.dk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       faktaform-seo
 */

// Stop direkte adgang til filen
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF JSON: Save point
 */
add_filter( 'acf/settings/save_json', function( $path ) {
    return plugin_dir_path( __FILE__ ) . 'acf-json';
} );

/**
 * ACF JSON: Load point
 */
add_filter( 'acf/settings/load_json', function( $paths ) {
    $paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';
    return $paths;
} );

/**
 * Initialiserer SEO-plugin'et, når WordPress er klar.
 */
function faktaform_seo_init() {
    require_once plugin_dir_path( __FILE__ ) . 'inc/template-tags.php';
    require_once plugin_dir_path( __FILE__ ) . 'inc/schema-hooks.php';
}
add_action( 'init', 'faktaform_seo_init' );