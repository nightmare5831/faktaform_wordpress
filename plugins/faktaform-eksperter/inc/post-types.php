<?php
/**
 * Fil til registrering af Custom Post Type: Ekspert
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Stop direkte adgang
}

function faktaform_register_ekspert_cpt() {

    $labels = array(
        'name'                  => _x( 'Eksperter', 'Post Type General Name', 'faktaform-eksperter' ),
        'singular_name'         => _x( 'Ekspert', 'Post Type Singular Name', 'faktaform-eksperter' ),
        'menu_name'             => __( 'Eksperter', 'faktaform-eksperter' ),
        'name_admin_bar'        => __( 'Ekspert', 'faktaform-eksperter' ),
        'archives'              => __( 'Ekspert Arkiver', 'faktaform-eksperter' ),
        'attributes'            => __( 'Ekspert Attributter', 'faktaform-eksperter' ),
        'parent_item_colon'     => __( 'Forældre Ekspert:', 'faktaform-eksperter' ),
        'all_items'             => __( 'Alle Eksperter', 'faktaform-eksperter' ),
        'add_new_item'          => __( 'Tilføj Ny Ekspert', 'faktaform-eksperter' ),
        'add_new'               => __( 'Tilføj Ny', 'faktaform-eksperter' ),
        'new_item'              => __( 'Ny Ekspert', 'faktaform-eksperter' ),
        'edit_item'             => __( 'Rediger Ekspert', 'faktaform-eksperter' ),
        'update_item'           => __( 'Opdater Ekspert', 'faktaform-eksperter' ),
        'view_item'             => __( 'Se Ekspert', 'faktaform-eksperter' ),
        'view_items'            => __( 'Se Eksperter', 'faktaform-eksperter' ),
        'search_items'          => __( 'Søg Ekspert', 'faktaform-eksperter' ),
        'not_found'             => __( 'Ikke fundet', 'faktaform-eksperter' ),
        'not_found_in_trash'    => __( 'Ikke fundet i papirkurv', 'faktaform-eksperter' ),
        'featured_image'        => __( 'Profilbillede', 'faktaform-eksperter' ),
        'set_featured_image'    => __( 'Vælg profilbillede', 'faktaform-eksperter' ),
        'remove_featured_image' => __( 'Fjern profilbillede', 'faktaform-eksperter' ),
        'use_featured_image'    => __( 'Brug som profilbillede', 'faktaform-eksperter' ),
        'insert_into_item'      => __( 'Indsæt i ekspert', 'faktaform-eksperter' ),
        'uploaded_to_this_item' => __( 'Uploadet til denne ekspert', 'faktaform-eksperter' ),
        'items_list'            => __( 'Ekspertliste', 'faktaform-eksperter' ),
        'items_list_navigation' => __( 'Ekspertliste navigation', 'faktaform-eksperter' ),
        'filter_items_list'     => __( 'Filtrer ekspertliste', 'faktaform-eksperter' ),
    );

    $args = array(
        'label'                 => __( 'Ekspert', 'faktaform-eksperter' ),
        'description'           => __( 'Post Type for FaktaForm Eksperter', 'faktaform-eksperter' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
        'taxonomies'            => array( 'category', 'post_tag' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-businessperson',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'eksperter',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
        'rewrite'               => array( 'slug' => 'eksperter' ),
    );

    register_post_type( 'ekspert', $args );
}

// VIGTIGT: Sørg for, at funktionen rent faktisk bliver hooket til WordPress.
add_action( 'init', 'faktaform_register_ekspert_cpt' );