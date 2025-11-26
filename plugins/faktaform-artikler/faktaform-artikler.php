<?php
// Cache-buster v2 - tvinger genlæsning af browser-cached filer ved opdatering
/**
 * Plugin Name:       FaktaForm - Artikler
 * Plugin URI:        https://faktaform.dk
 * Description:       Modul til håndtering af Artikler. Tilføjer 'artikel' CPT og funktionalitet for blog-forside og artikel-singlesider.
 * Version:           1.1.0
 * Author:            Thomas Kainø
 * Author URI:        https://faktaform.dk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       faktaform-artikler
 */

// Stop direkte adgang til filen
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// RETTET: Alle nødvendige filer indlæses samlet fra start.
require_once plugin_dir_path( __FILE__ ) . 'inc/template-tags.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/enqueue-scripts.php';

/**
 * Initialiserer Artikler-plugin'et. Bruges nu kun til CPT-registrering.
 */
function faktaform_artikler_init() {
    // require_once plugin_dir_path( __FILE__ ) . 'inc/post-types.php';
}
add_action( 'init', 'faktaform_artikler_init' );

/**
 * Registrerer dette moduls mikro-funktioner hos FaktaForm Core Dispatcher.
 */
function faktaform_artikler_register_micro_hooks( $hooks ) {
    $artikler_hooks = [
        // Artikel Header
        'faktaform-get-titel'               => 'faktaform_render_artikel_titel',
        'faktaform-get-underrubrik'         => 'faktaform_render_artikel_underrubrik',
        'faktaform-get-post-dates'          => 'faktaform_render_post_dates', // NYT: Tilføjet den nye dato-funktion
        
        // Byline (den nye, samlede løsning INKL. dato)
        'faktaform-get-intelligent-byline'  => 'faktaform_render_intelligent_byline',

        // NY LINJE TILFØJET HER:
        'faktaform-get-underrubrik-kort'    => 'faktaform_render_artikel_underrubrik_kort',

        // Fremhævet Billede
        'faktaform-get-featured-image-html' => 'faktaform_render_featured_image_html',
        'faktaform-get-featured-caption'    => 'faktaform_render_featured_image_caption',

        // Artikel Indhold
        'faktaform-get-flexible-content'    => 'faktaform_render_flexible_content',
        'faktaform-get-kildeliste'          => 'faktaform_render_kildeliste_content',
    ];
    return array_merge( $hooks, $artikler_hooks );
}
add_filter( 'faktaform_register_micro_hooks', 'faktaform_artikler_register_micro_hooks' );

/**
 * =========================================================================
 * Logik til Magasin-forside (Version 8.0 - Korrekt 'terms' array-syntaks)
 *
 * Krog:       template_redirect (Korrekt timing)
 * Filter:     generateblocks_query_loop_args (Korrekt blok-type)
 * Sti:        $block['className'] (Korrekt sti)
 * RETTELSE:   'terms' => array('artikler') (Korrekt syntaks)
 * =========================================================================
 */

/**
 * HOVEDAFBRYDER (V8)
 */
function faktaform_artikler_v8_init_filters() {
    
    if ( is_page(1435) ) {
        
        global $faktaform_v8_displayed_posts;
        $faktaform_v8_displayed_posts = array();

        add_filter( 'generateblocks_query_loop_args', 'faktaform_artikler_v8_gb_query_filter', 10, 2 );
        add_action( 'the_post', 'faktaform_artikler_v8_track_post' );
    }
}
add_action( 'template_redirect', 'faktaform_artikler_v8_init_filters' );


/**
 * Funktion 1: Registrerer et indlæg som "vist" (V8)
 */
function faktaform_artikler_v8_track_post( $post ) {
    global $faktaform_v8_displayed_posts;
    if ( ! in_the_loop() || ! is_a( $post, 'WP_Post' ) ) {
        return;
    }
    if ( ! in_array( $post->ID, $faktaform_v8_displayed_posts ) ) {
        $faktaform_v8_displayed_posts[] = $post->ID;
    }
}


/**
 * Funktion 2: "HJERNE"-FUNKTIONEN (V8)
 */
function faktaform_artikler_v8_gb_query_filter( $query_args, $block ) {
    global $faktaform_v8_displayed_posts;

    $classes = ! empty( $block['className'] ) ? $block['className'] : '';
    
    // =========================================================================
    // DEN AFGØRENDE RETTELSE:
    // 'terms' skal være et array: array('artikler')
    // =========================================================================
    $artikler_tax_query = array(
        'taxonomy'         => 'category',
        'field'            => 'slug',
        'terms'            => array('artikler'), // <-- RETTELSEN ER HER
        'include_children' => true
    );

    // =========================================================================
    // LOGIK 1: Tilføj "Meta Query" for specifikke blokke
    // =========================================================================
    
    if ( strpos( $classes, 'ff-query-featured-artikel' ) !== false ) {
        
        $query_args['tax_query'] = array( 'relation' => 'AND', $artikler_tax_query );
        $query_args['meta_query'] = array(
            'relation' => 'AND',
            array(
                'key'     => 'is_featured_artikel',
                'value'   => '1',
                'compare' => '=',
            ),
        );
    
    } elseif ( strpos( $classes, 'ff-query-archive-grid' ) !== false ) {
        
        $query_args['tax_query'] = array( 'relation' => 'AND', $artikler_tax_query );
    }
    
    // =========================================================================
    // LOGIK 2: Anvend det globale "Dublet-filter" (Kører altid)
    // =========================================================================
    
    if ( ! empty( $faktaform_v8_displayed_posts ) ) {
        if ( ! isset( $query_args['post__not_in'] ) ) {
            $query_args['post__not_in'] = array();
        }
        $query_args['post__not_in'] = array_merge( (array) $query_args['post__not_in'], $faktaform_v8_displayed_posts );
        $query_args['post__not_in'] = array_unique( $query_args['post__not_in'] );
    }

    return $query_args;
}