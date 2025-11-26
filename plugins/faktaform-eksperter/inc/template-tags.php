<?php
/**
 * FaktaForm Eksperter: Template Tags (Version 10.0 - Stabil CSS-baseret Styling)
 * @package FaktaForm-Eksperter
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// --- Simple, uafhængige funktioner ---

function faktaform_render_navn( $block_content ) {
    return str_replace( '{{post_title}}', get_the_title( get_queried_object_id() ), $block_content );
}

function faktaform_render_stilling( $block_content ) {
    $post_id = get_queried_object_id();
    if ( ! $post_id ) return $block_content;
    $stilling = get_field('titelstilling', $post_id);
    if ( empty($stilling) ) return $block_content;
    return str_replace( '{{ekspert_stilling}}', esc_html($stilling), $block_content );
}

// --- "Smart Container" Funktion ---

function faktaform_render_ekspert_main( $block_content ) {
    if ( ! is_singular('ekspert') ) return $block_content;

    $post_id = get_queried_object_id();
    if ( ! $post_id ) return $block_content;
    
    // Hent gruppe-felter
    $email = '';
    $telefon = '';
    $kontakt_gruppe = get_field('kontaktoplysninger', $post_id);
    if ( $kontakt_gruppe ) {
        $email = isset($kontakt_gruppe['ekspert_email']) ? $kontakt_gruppe['ekspert_email'] : '';
        $telefon = isset($kontakt_gruppe['telefon']) ? $kontakt_gruppe['telefon'] : '';
    }

    // Hent simple top-niveau felter
    $biografi = get_field('ekspert_biografi', $post_id);
    $overskrift_resume = get_field('Overskrift_resume', $post_id);
    
    // Kald hjælpefunktioner til at bygge HTML for repeater-felter
    $specialer_liste_html = faktaform_build_specialer_list_html( $post_id );
    $profiler_liste_html = faktaform_build_autoritative_profiler_html( $post_id );

    $replacements = [
        '{{overskrift_resume}}'                 => esc_html($overskrift_resume),
        '{{ekspert_email}}'                     => esc_html($email),
        '{{ekspert_telefon}}'                   => esc_html($telefon),
        '{{ekspert_biografi}}'                  => wp_kses_post($biografi),
        '{{ekspert_specialer_html}}'            => $specialer_liste_html,
        '{{ekspert_autoritative_profiler_html}}'=> $profiler_liste_html,
    ];

    return str_replace( array_keys($replacements), array_values($replacements), $block_content );
}

// --- HTML "Generator" Funktioner (til separate blokke) ---

function faktaform_render_cv_tidslinje( $block_content ) {
    $post_id = get_queried_object_id();
    if ( ! is_singular('ekspert') || ! function_exists('have_rows') || ! have_rows('struktureret_cv__tidslinie', $post_id) ) {
        return $block_content;
    }

    $timeline_html = '<div class="faktaform-timeline">';
    while ( have_rows('struktureret_cv__tidslinie', $post_id) ) {
        the_row();
        $arstal = get_sub_field('arstal');
        $virksomhed = get_sub_field('virksomhed');
        $stilling = get_sub_field('type'); // 'type' er feltnavnet for stillingen

        // ÆNDRET STRUKTUR: Grupperer stilling og virksomhed
        $timeline_html .= '<div class="faktaform-timeline-post">';
        if($arstal) $timeline_html .= '<div class="faktaform-timeline-periode">' . esc_html($arstal) . '</div>';
        
        $timeline_html .= '<div class="faktaform-timeline-details">';
        if($stilling) $timeline_html .= '<div class="faktaform-timeline-stilling">' . esc_html($stilling) . '</div>';
        if($virksomhed) $timeline_html .= '<div class="faktaform-timeline-virksomhed">' . esc_html($virksomhed) . '</div>';
        $timeline_html .= '</div>';

        $timeline_html .= '</div>';
    }
    $timeline_html .= '</div>';
    return $timeline_html;
}

function faktaform_render_kundeudtalelser( $block_content ) {
    $post_id = get_queried_object_id();
    if ( ! is_singular('ekspert') || ! function_exists('have_rows') || ! have_rows('kundeudtalelser__referencer', $post_id) ) {
        return $block_content;
    }

    $udtalelser_html = '<div class="faktaform-udtalelser">';
    while ( have_rows('kundeudtalelser__referencer', $post_id) ) {
        the_row();
        $udtalelse = get_sub_field('udtalelse');
        $afsender = get_sub_field('referenceafsender');

        $udtalelser_html .= '<blockquote class="faktaform-udtalelse-post">';
        if($udtalelse) $udtalelser_html .= '<p>' . wp_kses_post($udtalelse) . '</p>';
        if($afsender) $udtalelser_html .= '<footer>— ' . esc_html($afsender) . '</footer>';
        $udtalelser_html .= '</blockquote>';
    }
    $udtalelser_html .= '</div>';
    // ÆNDRING: Returnerer nu HTML direkte for at erstatte hele blokken.
    return $udtalelser_html;
}

function faktaform_render_ekspert_featured_image( $block_content ) {
    $post_id = get_queried_object_id();
    if ( ! is_singular('ekspert') || ! has_post_thumbnail( $post_id ) ) {
        return $block_content;
    }

    $image_url = get_the_post_thumbnail_url( $post_id, 'large' );
    if ( ! $image_url ) {
        return $block_content;
    }

    // 1. Tilføj den dedikerede styling-klasse til blokken.
    // Vi finder logik-klassen og tilføjer styling-klassen lige efter.
    $block_content = str_replace(
        'faktaform-get-ekspert-image', 
        'faktaform-get-ekspert-image faktaform-ekspert-billede', 
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
    $final_content = str_replace('{{ekspert_featured_image}}', '', $modified_content);

    return $final_content;
}


// --- HJÆLPEFUNKTIONER (kaldes af Smart Container) ---

function faktaform_build_specialer_list_html( $post_id ) {
    $html = '';
    if ( function_exists('have_rows') && have_rows('specialer', $post_id) ) {
        // TILFØJET: En specifik CSS-klasse for nemmere styling.
        $html .= '<ul class="faktaform-specialer-liste">';
        while ( have_rows('specialer', $post_id) ) { the_row();
            $speciale = get_sub_field('speciale');
            if ( $speciale ) $html .= '<li>' . esc_html($speciale) . '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}