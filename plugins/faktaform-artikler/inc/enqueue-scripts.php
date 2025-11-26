<?php
/**
 * FaktaForm Artikler: Scripts & Styles
 * @package FaktaForm-Artikler
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Indlæser plugin-specifikke scripts og styles med optimeret, betinget logik.
 */
function faktaform_artikler_enqueue_assets() {

    // --- DEL 1: STYLING ---
    // Indlæs stylesheet'et HVIS det er en single-post ELLER den specifikke 'artikler'-side (ID 1435).
    if ( is_singular('post') || is_page(1435) ) {
        wp_enqueue_style(
            'faktaform-artikler-styles',
            plugin_dir_url( __FILE__ ) . '../assets/css/styles.css',
            [],
            '1.0.1'
        );
    }

    // --- DEL 2: JAVASCRIPT (UÆNDRET LOGIK) ---
    // Kør den eksisterende, velfungerende JavaScript-logik KUN på en single-post.
    if ( is_singular('post') ) {
        if ( function_exists('have_rows') && have_rows('artikel_indhold') ) {
            
            $post_id = get_the_ID();
            $har_faktaboks = false;

            while ( have_rows('artikel_indhold', $post_id) ) {
                the_row();
                if ( get_row_layout() == 'faktaboks' ) {
                    $har_faktaboks = true;
                    break;
                }
            }
            reset_rows();

            if ( $har_faktaboks ) {
                wp_enqueue_script(
                    'faktaform-artikler-interactions',
                    plugin_dir_url( __FILE__ ) . '../assets/js/faktaform-interactions.js',
                    [],
                    '1.0.1',
                    true
                );
            }
        }
    }
}
add_action( 'wp_enqueue_scripts', 'faktaform_artikler_enqueue_assets' );