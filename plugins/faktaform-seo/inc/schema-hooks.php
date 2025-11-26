<?php
/**
 * FaktaForm SEO: Schema Hooks
 * Integrates with Rank Math to enhance JSON-LD schema output.
 *
 * @package FaktaForm-SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Primary Rank Math filter hook (Milestone 1)
 * Intercepts and enhances Rank Math's JSON-LD schema output.
 *
 * @param array $data   The schema data array from Rank Math
 * @param object $jsonld The Rank Math JSON-LD object
 * @return array Modified schema data
 */
function faktaform_seo_enhance_rank_math_schema( $data, $jsonld ) {
    // Only process on single article pages
    if ( ! is_singular( 'post' ) ) {
        return $data;
    }

    // test purpose
    error_log('✅ Rank Math filter is working!');

    // Milestone 2: E-E-A-T implementation 
    // - Author (Journalist) Person schema
    // - Reviewer (Expert) Person schema
    // - Citation schema from kildeliste

    // Milestone 3: Rich results implementation 
    // - FAQ schema from Faktaboks

    return $data;
}
add_filter( 'rank_math/json_ld', 'faktaform_seo_enhance_rank_math_schema', 99, 2 );
