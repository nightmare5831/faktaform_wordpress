<?php
/**
 * FaktaForm Artikler: Template Tags
 * Håndterer rendering af artikel-specifikke komponenter ("Mikro-funktioner").
 * @package FaktaForm-Artikler
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function faktaform_get_current_post_id( $block ) {
    if ( ! empty( $block['context']['postId'] ) ) return $block['context']['postId'];
    if ( function_exists('get_the_ID') ) return get_the_ID();
    return false;
}

/* =============================================
 * HJÆLPEFUNKTIONER TIL BYLINE
 * ============================================= */

/**
 * Henter og formaterer data for en given person-type (journalist eller ekspert).
 * Returnerer et array med data eller null, hvis personen ikke findes.
 */
function faktaform_get_person_data( $type, $post_id ) {
    $person_field_name = ($type === 'journalist') ? 'artikel_journalister' : 'artikel_eksperter';
    $persons = get_field($person_field_name, $post_id);

    if ( !is_array($persons) || empty($persons) ) return null;

    $person_id = is_object($persons[0]) ? $persons[0]->ID : $persons[0];
    
    $image_html = '';
    if ( has_post_thumbnail($person_id) ) {
        $image_url = get_the_post_thumbnail_url($person_id, 'thumbnail');
        $image_alt = get_post_meta(get_post_thumbnail_id($person_id), '_wp_attachment_image_alt', true);
        $image_html = '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '">';
    }

    return [
        'navn'      => esc_html(get_the_title($person_id)),
        'stilling'  => esc_html(get_field('titelstilling', $person_id)),
        'link'      => esc_url(get_permalink($person_id)),
        'rolle'     => esc_html(get_field($type . '_rolle', $post_id)),
        'billede'   => $image_html,
    ];
}

/**
 * Bygger HTML for en enkelt person (enten i en grid-celle eller centreret).
 */
function faktaform_build_person_html( $person_data, $type, $is_single = false ) {
    if (!$person_data) return '';

    $text_align_class = ($type === 'journalist') ? 'faktaform-byline__details--right' : '';
    $person_class = 'faktaform-byline__person faktaform-byline__person--' . $type;
    if ($is_single) {
        $person_class .= ' faktaform-byline__person--single';
    }

    $rolle_tekst = ($type === 'journalist') ? 'Skrevet af:' : 'Fagekspert:';

    $html = '<div class="' . $person_class . '">';
    $html .= '<div class="faktaform-byline__details ' . $text_align_class . '">';
    if ($person_data['rolle']) {
        $html .= '<p class="faktaform-byline__rolle"><strong>' . $person_data['rolle'] . '</strong></p>';
    } else {
        $html .= '<p class="faktaform-byline__rolle"><strong>' . $rolle_tekst . '</strong></p>';
    }
    $html .= '<p class="faktaform-byline__navn"><a href="' . $person_data['link'] . '">' . $person_data['navn'] . '</a></p>';
    $html .= '<p class="faktaform-byline__stilling">' . $person_data['stilling'] . '</p>';
    $html .= '</div>';
    $html .= '<div class="faktaform-byline__image"><a href="' . $person_data['link'] . '">' . $person_data['billede'] . '</a></div>';
    $html .= '</div>';

    return $html;
}

/* =============================================
 * MASTER BYLINE FUNKTION
 * ============================================= */

function faktaform_render_intelligent_byline( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( !$post_id || !function_exists('get_field') ) return '';

    // Hent byline-data
    $visningstype = get_field('byline_visningstype', $post_id);
    $journalist_data = faktaform_get_person_data('journalist', $post_id);
    $ekspert_data = faktaform_get_person_data('ekspert', $post_id );
    
    // Hent dato-data
    $publish_date_formatted = get_the_date( 'j. F Y', $post_id );
    $date_html_text = 'Udgivet: ' . $publish_date_formatted;
    $publish_date_compare = get_the_date( 'Ymd', $post_id );
    $updated_date_compare = get_the_modified_date( 'Ymd', $post_id );
    if ( $updated_date_compare !== $publish_date_compare ) {
        $updated_date_formatted = get_the_modified_date( 'j. F Y', $post_id );
        $date_html_text .= ' - Opdateret: ' . $updated_date_formatted;
    }

    // Auto-korriger visningstype, hvis en person mangler
    if ($visningstype === 'journalist_og_ekspert') {
        if ($journalist_data && !$ekspert_data) $visningstype = 'kun_journalist';
        if (!$journalist_data && $ekspert_data) $visningstype = 'kun_ekspert';
    }

    $output_html = '';

    switch ($visningstype) {
        case 'journalist_og_ekspert':
            if ($journalist_data && $ekspert_data) {
                $person_html = faktaform_build_person_html($journalist_data, 'journalist');
                $person_html .= faktaform_build_person_html($ekspert_data, 'ekspert');
                
                // Byg den komplekse grid-struktur for to personer
                $output_html .= '<div class="faktaform-byline__grid">';
                $output_html .= $person_html;
                $output_html .= '<p class="faktaform-byline__date">' . $date_html_text . '</p>';
                $output_html .= '</div>';
            }
            break;
            
        case 'kun_journalist':
        case 'kun_ekspert':
            $person_data = ($visningstype === 'kun_journalist') ? $journalist_data : $ekspert_data;
            $person_type = ($visningstype === 'kun_journalist') ? 'journalist' : 'ekspert';
            
            if ($person_data) {
                $person_html = faktaform_build_person_html($person_data, $person_type, true);

                // Byg den simple wrapper-struktur for én person
                $output_html .= '<div class="faktaform-byline-wrapper--single">';
                $output_html .= $person_html;
                $output_html .= '<p class="faktaform-byline__date">' . $date_html_text . '</p>';
                $output_html .= '</div>';
            }
            break;
    }

    return $output_html;
}


/* =============================================
 * ANDRE TEMPLATE TAGS (UÆNDRET)
 * ============================================= */

function faktaform_render_artikel_titel( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || get_post_type( $post_id ) !== 'post' ) return $block_content;
    $post_title = get_the_title( $post_id );
    return str_replace( '{{post_title}}', esc_html( $post_title ), $block_content );
}

function faktaform_render_artikel_underrubrik( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || get_post_type( $post_id ) !== 'post' ) return $block_content;
    $underrubrik = get_field( 'Underrubrik', $post_id );
    $clean_underrubrik = ! empty( $underrubrik ) ? wp_kses_post( $underrubrik ) : '';
    return str_replace( '{{underrubrik}}', $clean_underrubrik, $block_content );
}

function faktaform_render_publish_update_date( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || get_post_type( $post_id ) !== 'post' ) return $block_content;
    $publish_date_formatted = get_the_date( 'j. F Y', $post_id );
    $output_html = 'Udgivet: ' . $publish_date_formatted;
    $publish_date_compare = get_the_date( 'Ymd', $post_id );
    $updated_date_compare = get_the_modified_date( 'Ymd', $post_id );
    if ( $updated_date_compare !== $publish_date_compare ) {
        $updated_date_formatted = get_the_modified_date( 'j. F Y', $post_id );
        $output_html .= ' - Opdateret: ' . $updated_date_formatted;
    }
    return str_replace( '{{publish_update}}', $output_html, $block_content );
}

function faktaform_render_featured_image_html( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || ! has_post_thumbnail( $post_id ) ) return '';
    $image_url = get_the_post_thumbnail_url( $post_id, 'full' );
    $image_id = get_post_thumbnail_id( $post_id );
    $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
    $image_html = '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '">';
    return str_replace( '{{featured_image_html}}', $image_html, $block_content );
}

function faktaform_render_featured_image_caption( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    $caption = $post_id ? get_the_post_thumbnail_caption( $post_id ) : '';
    if ( ! has_post_thumbnail( $post_id ) || empty( $caption ) ) return '';
    $clean_caption = wp_kses_post( $caption );
    return str_replace( '{{featured_image_caption}}', $clean_caption, $block_content );
}

function faktaform_render_faktaboks_pattern_content( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || ! function_exists('get_field') ) return $block_content;

    $rubrik = get_field('faktaboks_rubrik', $post_id);
    $indhold = get_field('faktaboks_indhold', $post_id);
    if ( empty($rubrik) && empty($indhold) ) return '';
    $replacements = [ '{{faktaboks_rubrik}}' => esc_html( $rubrik ), '{{faktaboks_indhold}}' => wp_kses_post( $indhold ) ];
    return str_replace( array_keys( $replacements ), array_values( $replacements ), $block_content );
}

function faktaform_render_unified_featured_image( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || ! has_post_thumbnail($post_id) ) return '';

    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ( ! $thumbnail_id ) return '';
    $image_url = get_the_post_thumbnail_url($post_id, 'full');
    $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
    $attachment_post = get_post($thumbnail_id);
    $caption_text = !empty($attachment_post) ? $attachment_post->post_excerpt : '';
    $caption_html = !empty($caption_text) ? '<figcaption class="wp-element-caption">' . wp_kses_post( $caption_text ) . '</figcaption>' : '';
    $replacements = [ '{{featured_image_url}}' => esc_url($image_url), '{{featured_image_alt}}' => esc_attr($image_alt), '{{featured_image_caption_html}}' => $caption_html ];
    return str_replace( array_keys( $replacements ), array_values($replacements), $block_content );
}

function faktaform_render_kildeliste_content( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || ! function_exists('have_rows') || ! have_rows('kildeliste', $post_id) ) {
        return '';
    }
    
    $all_list_items = '';
    while ( have_rows('kildeliste', $post_id) ) {
        the_row();
        $kilde_navn = get_sub_field('navn_pa_kilde');
        $kilde_url = get_sub_field('kilde_kilde_url');
        $publikation_navn = get_sub_field('navn_pa_publikation');
        $publikation_url = get_sub_field('link_til_publikation');
        $institut_navn = get_sub_field('navn_pa_institutvirksomhed');
        $institut_url = get_sub_field('link_til_institutvirksomhed');
        $create_link = function($text, $url) {
            return (!empty($url) && !empty($text)) ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($text) . '</a>' : esc_html($text);
        };
        $kilde_html = $create_link($kilde_navn, $kilde_url);
        $publikation_html = $create_link($publikation_navn, $publikation_url);
        $institut_html = $create_link($institut_navn, $institut_url);
        $li_content = '';
        if ( !empty($publikation_navn) ) {
            $li_content .= $publikation_html;
            if ( !empty($kilde_navn) ) {
                $li_content .= ' udarbejdet af ' . $kilde_html;
                if ( !empty($institut_navn) ) {
                    $li_content .= ' fra ' . $institut_html;
                }
            } elseif ( !empty($institut_navn) ) {
                $li_content .= ' udgivet af ' . $institut_html;
            }
        } elseif ( !empty($kilde_navn) ) {
            $li_content .= $kilde_html;
            if ( !empty($institut_navn) ) {
                $li_content .= ' fra ' . $institut_html;
            }
        } else {
            $li_content = $institut_html;
        }
        if (!empty($li_content)) {
            $all_list_items .= '<li class="has-small-font-size">' . $li_content . '</li>';
        }
    }
    return str_replace( '{{kildeliste_items}}', $all_list_items, $block_content );
}

function faktaform_render_flexible_content( $block_content, $block ) {
    $post_id = faktaform_get_current_post_id( $block );
    if ( ! $post_id || ! function_exists('have_rows') || ! have_rows('artikel_indhold', $post_id) ) return '';
    
    $output = '';
    while ( have_rows( 'artikel_indhold', $post_id ) ) {
        the_row();
        $layout = get_row_layout();
        $pattern_id = get_field('selector_' . $layout, 'option');
        if ( $pattern_id ) {
            $pattern_content = get_post_field('post_content', $pattern_id);
            $row_data = get_row(true);
            $placeholders = []; $replacements = [];
            if( is_array($row_data) ){
                foreach ($row_data as $key => $value) {
                    if ($key === 'acf_fc_layout') continue;
                    if (is_array($value) && isset($value['url'])) {
                        $placeholders[] = '{{' . $key . '_url' . '}}'; $replacements[] = esc_url($value['url']);
                        $placeholders[] = '{{' . $key . '_alt' . '}}'; $replacements[] = esc_attr($value['alt']);
                        $placeholders[] = '{{' . $key . '_caption' . '}}'; $replacements[] = esc_html($value['caption']);
                    } else if (is_string($value) || is_numeric($value)) {
                        $placeholders[] = '{{' . $key . '}}'; $replacements[] = wp_kses_post($value);
                    }
                }
            }
            $flettet_content = str_replace($placeholders, $replacements, $pattern_content);
            $module_html = do_blocks($flettet_content);
            
            $justering = get_sub_field('modul_justering');
            $wrapper_classes = ['faktaform-module-wrapper'];
            if ($justering) $wrapper_classes[] = 'faktaform-align-' . esc_attr($justering);
            $output .= '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '">' . $module_html . '</div>';
        }
    }
    return $output;
}

/**
 * Gengiver en intelligent datostreng (publiceret og evt. opdateret).
 * Erstatter placeholderen {{post_dates}}.
 *
 * @param string $block_content Det oprindelige blok-indhold.
 * @param array  $block         Den fulde blok-array.
 * @return string              HTML med de formaterede datoer.
 */
function faktaform_render_post_dates( $block_content, $block ) {
    
    // Få det korrekte ID (robust metode)
    $post_id = faktaform_get_current_post_id( $block ); 

    if ( ! $post_id ) {
        return $block_content; 
    }

    // Get timestamps (UNIX-tid) for præcis sammenligning
    $published_timestamp = get_the_date( 'U', $post_id );
    $modified_timestamp  = get_the_modified_date( 'U', $post_id );

    $published_format = '\U\d\g\i\v\e\t \d\e\n j. F Y'; // F.eks: Udgivet den 6. maj 2025
    $modified_format  = '\O\p\d\a\t\e\r\e\t \d\e\n j. F Y'; // F.eks: Opdateret den 7. maj 2025

    // Få de rå datostrenge
    $published_string = get_the_date( $published_format, $post_id );
    
    // RETTET: Punktum efter publiceringsdato er fjernet.
    $html = '<span class="post-date post-date--published">' . esc_html( $published_string ) . '</span>';

    // Tjek om opdateringsdatoen er nyere end publiceringsdatoen
    if ( $modified_timestamp > ( $published_timestamp + 60 ) ) {
        $modified_string = get_the_modified_date( $modified_format, $post_id );
        
        // RETTET: Punktum efter opdateringsdato er fjernet.
        $html .= ' <span class="post-date-separator" aria-hidden="true">-</span> <span class="post-date post-date--modified">' . esc_html( $modified_string ) . '</span>';
    }

    // Erstat placeholderen {{post_dates}} med vores genererede HTML
    return str_replace( '{{post_dates}}', $html, $block_content );
}

/**
 * Henter og afkorter 'Underrubrik'-feltet til brug på arkiv-sider.
 * Erstatter placeholderen: {{underrubrik_kort}}
 * (STABIL, KORREKT VERSION)
 */
function faktaform_render_artikel_underrubrik_kort( $block_content, $block ) {

    // RETTELSE 1: Få post ID korrekt
    $post_id = faktaform_get_current_post_id( $block ); 

    if ( ! $post_id ) {
        return $block_content; // RETTELSE 2: Returner placeholder
    }

    // RETTELSE 3: Brug korrekt feltnavn ('Underrubrik')
    $underrubrik = get_field('Underrubrik', $post_id);

    if ( empty( $underrubrik ) ) {
        return $block_content; // RETTELSE 4: Returner placeholder
    }

    $word_limit = 20; 
    $trimmed_text = wp_trim_words( $underrubrik, $word_limit, '...' );

    return str_replace( '{{underrubrik_kort}}', $trimmed_text, $block_content );
}
