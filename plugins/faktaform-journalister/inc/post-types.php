<?php
/**
 * Fil til registrering af 'Journalist' Custom Post Type.
 *
 * @package FaktaForm-Journalister
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Registrerer Custom Post Type for Journalister.
 */
function faktaform_register_journalist_cpt() {
    $labels = array(
        'name'               => _x( 'Journalister', 'post type general name', 'faktaform-journalister' ),
        'singular_name'      => _x( 'Journalist', 'post type singular name', 'faktaform-journalister' ),
        'menu_name'          => _x( 'Journalister', 'admin menu', 'faktaform-journalister' ),
        'name_admin_bar'     => _x( 'Journalist', 'add new on admin bar', 'faktaform-journalister' ),
        'add_new'            => _x( 'Tilføj ny', 'journalist', 'faktaform-journalister' ),
        'add_new_item'       => __( 'Tilføj ny journalist', 'faktaform-journalister' ),
        'new_item'           => __( 'Ny journalist', 'faktaform-journalister' ),
        'edit_item'          => __( 'Rediger journalist', 'faktaform-journalister' ),
        'view_item'          => __( 'Se journalist', 'faktaform-journalister' ),
        'all_items'          => __( 'Alle journalister', 'faktaform-journalister' ),
        'search_items'       => __( 'Søg journalister', 'faktaform-journalister' ),
        'parent_item_colon'  => __( 'Forældre-journalister:', 'faktaform-journalister' ),
        'not_found'          => __( 'Ingen journalister fundet.', 'faktaform-journalister' ),
        'not_found_in_trash' => __( 'Ingen journalister fundet i papirkurven.', 'faktaform-journalister' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'journalister' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 21,
        'menu_icon'          => 'dashicons-edit',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
    );

    register_post_type( 'journalist', $args );
}
add_action( 'init', 'faktaform_register_journalist_cpt' );