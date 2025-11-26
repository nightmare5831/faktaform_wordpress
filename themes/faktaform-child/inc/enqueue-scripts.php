<?php
/**
 * Enqueue scripts and styles.
 * @package FaktaForm-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action( 'wp_enqueue_scripts', 'faktaform_enqueue_styles' );
function faktaform_enqueue_styles() {
    $parent_style = 'parent-style'; // Navn til forældre-temaet

    // Indlæs forældre-temaets stylesheet
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    
    // Indlæs child-temaets stylesheet og gør det afhængigt af forældre-temaet.
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );

    // Det gamle wp_enqueue_script-kald er nu fjernet herfra.
}