<?php
/**
 * Handles the generation of the full JSON-LD Schema graph.
 * Implements the "Two-Pillar Expertise" model from SDD 3.1.
 * @package FaktaForm-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Genererer og printer en avanceret, nestet JSON-LD schema-graf for artikler.
 */
function faktaform_generate_full_schema() {
    if ( ! is_singular( 'post' ) ) {
        return;
    }

    global $post;
    
    // Grundlæggende artikeldata
    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'NewsArticle',
        'headline'      => get_the_title( $post ),
        'datePublished' => get_the_date( 'c', $post ),
        'dateModified'  => get_the_modified_date( 'c', $post ),
        'author'        => [
            '@type' => 'Person',
            'name'  => 'Pia Kainø', // Hardkodet jf. SDD
            'url'   => 'https://kaino.dk/pia-kaino'
        ],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => 'Kainø & Co', // Hardkodet jf. SDD
            'url'   => 'https://kaino.dk'
        ],
    ];

    // Tilføj "Faglig Reviewer" (reviewedBy) fra ACF-felt
    $reviewer_posts = get_field( 'faglig_reviewer', $post->ID );
    if ( ! empty( $reviewer_posts ) ) {
        $reviewers = [];
        foreach ( $reviewer_posts as $reviewer_post ) {
            $reviewers[] = [
                '@type' => 'Person',
                'name'  => get_the_title( $reviewer_post ),
                'url'   => get_permalink( $reviewer_post ),
            ];
        }
        // Tilføj enten et enkelt objekt eller et array afhængig af antallet
        $schema['reviewedBy'] = ( count( $reviewers ) === 1 ) ? $reviewers[0] : $reviewers;
    }

    // Eksempel på "funder" - dette skal evt. gøres dynamisk i fremtiden
    $schema['funder'] = [
        '@type' => 'Organization',
        'name'  => 'Green Business', // Eksempel fra SDD
        'url'   => 'https://greenbusiness.dk'
    ];

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>';
}
add_action( 'wp_footer', 'faktaform_generate_full_schema' );