<?php
/**
 * Håndterer indlæsning af plugin'ets scripts og styles.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function faktaform_eksperter_enqueue_assets() {
    // RETTET: Tjekker nu for den korrekte post-type 'ekspert'.
    if ( is_singular('ekspert') ) {
        wp_enqueue_style(
            'faktaform-eksperter-styles',
            // RETTET: Stien peger nu korrekt til styles.css i plugin'ets rodmappe.
            plugin_dir_url( __DIR__ ) . 'styles.css',
            [],
            '10.0.0' // Matcher plugin version
        );
    }
}
add_action( 'wp_enqueue_scripts', 'faktaform_eksperter_enqueue_assets' );