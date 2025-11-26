<?php
/**
 * FaktaForm Child Theme Functions
 *
 * @package FaktaForm-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Indlæser forælder-temaets og child-temaets stylesheets.
 */
function faktaform_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(), array( 'parent-style' ), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'faktaform_child_enqueue_styles' );

