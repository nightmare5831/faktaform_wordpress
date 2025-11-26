<?php
/**
 * FaktaForm Journalister: Template Tags
 * Håndterer rendering af Journalist-specifikke komponenter ("Mikro-funktioner").
 *
 * @package FaktaForm-Journalister
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Henter og erstatter alle kernefelter for en Journalist.
 * CSS Klasse: faktaform-get-journalist-main
 *
 * @param string $block_content Det oprindelige blok-indhold.
 * @return string Det modificerede blok-indhold.
 */
function faktaform_render_journalist_main( $block_content ) {
    $post_id = get_queried_object_id();
    if ( ! is_singular('journalist') || ! $post_id ) return $block_content;

    // Henter eksisterende felter
    $biografi = get_field('biografi_journalist', $post_id); 
    $stilling = get_field('titelstilling', $post_id); 
    
    // NYE FELTER HENTES FRA ACF (jf. din felt-liste)
    $virksomhed = get_field('journalisten_virksomhed', $post_id);
    $websted_url = get_field('journalist_websted', $post_id);
    $linkedin_url = get_field('journalist_linkedin_url', $post_id);
    
    // Henter liste over eksterne profiler (Core Helper Function)
    $profiler_liste_html = faktaform_build_autoritative_profiler_html( $post_id );

    // Oprettelse af links, som Sikander kan bruge
    $websted_html = ! empty($websted_url) ? '<a href="' . esc_url($websted_url) . '" target="_blank" rel="noopener">Websted</a>' : '';
    $linkedin_html = ! empty($linkedin_url) ? '<a href="' . esc_url($linkedin_url) . '" target="_blank" rel="noopener">LinkedIn</a>' : '';


    $replacements = [
        '{{journalist_navn}}'                     => esc_html(get_the_title($post_id)),
        '{{journalist_stilling}}'                 => esc_html($stilling),
        '{{journalist_biografi}}'                 => wp_kses_post($biografi),
        // NYE PLACEHOLDERS:
        '{{journalist_virksomhed}}'               => esc_html($virksomhed),
        '{{journalist_websted_url}}'              => esc_url($websted_url),
        '{{journalist_linkedin_url}}'             => esc_url($linkedin_url),
        '{{journalist_websted_html}}'             => $websted_html, // Til brug for hurtigt link
        '{{journalist_linkedin_html}}'            => $linkedin_html, // Til brug for hurtigt link
        '{{journalist_autoritative_profiler_html}}'=> $profiler_liste_html,
    ];

    return str_replace( array_keys($replacements), array_values($replacements), $block_content );
}

/**
 * Henter Journalistens navn (til simpel brug).
 */
function faktaform_render_journalist_navn( $block_content ) {
    return str_replace( '{{journalist_navn}}', get_the_title( get_queried_object_id() ), $block_content );
}

/**
 * Gengiver Journalistens Featured Image (Profilbillede).
 * Indsætter billed-URL'en som en CSS variabel (--bg-image-url)
 * @package FaktaForm-Journalister
 */
function faktaform_render_journalist_featured_image( $block_content ) {
    $post_id = get_queried_object_id();
    if ( ! is_singular('journalist') || ! has_post_thumbnail( $post_id ) ) {
        return $block_content;
    }

    $image_url = get_the_post_thumbnail_url( $post_id, 'large' );
    if ( ! $image_url ) {
        return $block_content;
    }

    // 1. Tilføj en dedikeret styling-klasse til blokken.
    $block_content = str_replace(
        'faktaform-get-journalist-image', 
        'faktaform-get-journalist-image faktaform-journalist-billede', // <-- Denne klasse bruges til styling i CSS
        $block_content
    );

    // 2. Indsæt CSS-variablen med billedets URL.
    $style_attribute = 'style="--bg-image-url: url(' . esc_url( $image_url ) . ');"';
    $tag_end_pos = strpos( $block_content, '>' );
    if ( $tag_end_pos === false ) {
        return $block_content;
    }
    $modified_content = substr_replace( $block_content, ' ' . $style_attribute, $tag_end_pos, 0 );
    
    // 3. Fjern eventuel placeholder-tekst.
    $final_content = str_replace('{{journalist_featured_image}}', '', $modified_content);

    return $final_content;
}