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

    // Save original schema for testing
    $upload_dir = wp_upload_dir();
    $schema_dir = $upload_dir['basedir'] . '/faktaform-schema';
    if ( ! file_exists( $schema_dir ) ) {
        wp_mkdir_p( $schema_dir );
    }
    $post_id = get_the_ID();
    file_put_contents( $schema_dir . "/original-{$post_id}.json", json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

    $journalist_schema = faktaform_seo_build_journalist_schema( $post_id );
    if ( $journalist_schema ) {
        $data['author'] = $journalist_schema;
    }

    $expert_schema = faktaform_seo_build_expert_schema( get_the_ID() );
    if ( $expert_schema ) {
        $data['reviewedBy'] = $expert_schema;
    }

    $citations = faktaform_seo_build_citation_schema( get_the_ID() );
    if ( ! empty( $citations ) ) {
        $data['citation'] = $citations;
    }

    // Milestone 3: Rich results implementation
    // - FAQ schema from Faktaboks

    // Save final schema for testing
    file_put_contents( $schema_dir . "/final-{$post_id}.json", json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

    return $data;
}
add_filter( 'rank_math/json_ld', 'faktaform_seo_enhance_rank_math_schema', 99, 2 );

/**
 * Build Person Schema for Journalist (Author)
 */
function faktaform_seo_build_journalist_schema( $post_id ) {
    $journalists = get_field( 'artikel_journalister', $post_id );
    if ( empty( $journalists ) ) {
        return false;
    }

    $journalist_id = $journalists[0]->ID;

    $schema = array(
        '@type' => 'Person',
        'name'  => get_the_title( $journalist_id ),
    );

    $job_title = get_field( 'titelstilling', $journalist_id );
    if ( $job_title ) {
        $schema['jobTitle'] = $job_title;
    }

    $company = get_field( 'journalist_virksomhed', $journalist_id );
    if ( $company ) {
        $schema['affiliation'] = array( '@type' => 'Organization', 'name' => $company );
    }

    $same_as = array();
    $linkedin = get_field( 'journalist_linkedin_url', $journalist_id );
    $website = get_field( 'journalist_websted', $journalist_id );
    if ( $linkedin ) $same_as[] = $linkedin;
    if ( $website ) $same_as[] = $website;
    $same_as[] = get_permalink( $journalist_id );

    $schema['sameAs'] = $same_as;

    return $schema;
}

/**
 * Build Person Schema for Expert (Reviewer)
 */
function faktaform_seo_build_expert_schema( $post_id ) {
    $experts = get_field( 'artikel_eksperter', $post_id );
    if ( empty( $experts ) ) {
        return false;
    }

    $expert_id = $experts[0]->ID;

    $schema = array(
        '@type' => 'Person',
        'name'  => get_the_title( $expert_id ),
    );

    $job_title = get_field( 'titelstilling', $expert_id );
    if ( $job_title ) {
        $schema['jobTitle'] = $job_title;
    }

    $same_as = array();
    if ( have_rows( 'autoritative_profiler', $expert_id ) ) {
        while ( have_rows( 'autoritative_profiler', $expert_id ) ) {
            the_row();
            $url = get_sub_field( 'profil_url' );
            if ( $url ) $same_as[] = $url;
        }
    }
    $same_as[] = get_permalink( $expert_id );

    $schema['sameAs'] = $same_as;

    if ( have_rows( 'specialer', $expert_id ) ) {
        $expertise = array();
        while ( have_rows( 'specialer', $expert_id ) ) {
            the_row();
            $speciale = get_sub_field( 'speciale' );
            if ( $speciale ) $expertise[] = $speciale;
        }
        if ( ! empty( $expertise ) ) {
            $schema['knowsAbout'] = $expertise;
        }
    }

    return $schema;
}

/**
 * Build Citation Schema from kildeliste repeater
 */
function faktaform_seo_build_citation_schema( $post_id ) {
    $citations = array();

    if ( ! have_rows( 'kildeliste', $post_id ) ) {
        return $citations;
    }

    while ( have_rows( 'kildeliste', $post_id ) ) {
        the_row();

        $source_name = get_sub_field( 'navn_pa_kilde' );
        $source_url  = get_sub_field( 'kilde_kilde_url' );
        $pub_name    = get_sub_field( 'navn_pa_publikation' );
        $pub_url     = get_sub_field( 'link_til_publikation' );

        if ( $pub_name && $pub_url ) {
            $citation = array(
                '@type' => 'CreativeWork',
                'name'  => $pub_name,
                'url'   => $pub_url
            );
            if ( $source_name ) {
                $citation['author'] = array( '@type' => 'Person', 'name' => $source_name );
            }
            $citations[] = $citation;
        } elseif ( $source_name ) {
            $citation = array( '@type' => 'Thing', 'name' => $source_name );
            if ( $source_url ) {
                $citation['url'] = $source_url;
            }
            $citations[] = $citation;
        }
    }

    return $citations;
}
