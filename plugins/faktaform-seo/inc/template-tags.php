<?php
/**
 * FaktaForm SEO: Template Tags
 * Håndterer rendering af SEO-relaterede komponenter som bylines.
 * @package FaktaForm-SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Gengiver det dynamiske indhold for 'Kombineret Byline'-modulet. (Original Version)
 */
function faktaform_render_byline_content( $block_content ) {
    $post_obj = get_post(); 
    if ( ! $post_obj instanceof WP_Post ) {
        return $block_content; 
    }
    $post_id = $post_obj->ID;

    $visningstype = get_field('byline_visningstype', $post_id);
    $journalister = get_field('artikel_journalister', $post_id); // KORREKT FELTNAVN
    $eksperter = get_field('artikel_eksperter', $post_id);       // KORREKT FELTNAVN

    // Start med et komplet kort over alle placeholders.
    $replacement_map = [
        '{{journalist_rolle}}'      => '',
        '{{journalist_navn}}'       => '',
        '{{journalist_billede_html}}' => '',
        '{{journalist_stilling}}'   => '',
        '{{ekspert_rolle}}'         => '',
        '{{ekspert_navn}}'          => '',
        '{{ekspert_billede_html}}'  => '',
        '{{ekspert_stilling}}'      => '',
        '{{publish_date}}'          => '',
        '{{updated_date_html}}'     => '',
    ];

    // Håndter visning af journalist
    if ( ($visningstype === 'journalist_og_ekspert' || $visningstype === 'kun_journalist') && !empty($journalister) && function_exists('faktaform_build_person_html') ) {
        $journalist_data = faktaform_build_person_html($journalister[0], 'Research og journalistik');
        $replacement_map = array_merge($replacement_map, $journalist_data);
    }

    // Håndter visning af ekspert
    if ( ($visningstype === 'journalist_og_ekspert' || $visningstype === 'kun_ekspert') && !empty($eksperter) && function_exists('faktaform_build_person_html') ) {
        $ekspert_data = faktaform_build_person_html($eksperter[0], 'Faglig ekspertise');
        $replacement_map = array_merge($replacement_map, $ekspert_data);
    }

    // Hent og formater datoer
    $publish_date = get_the_date('j. F Y', $post_id);
    $updated_date = get_the_modified_date('j. F Y', $post_id);
    $updated_date_html = '';

    if ($updated_date !== $publish_date) {
        $updated_date_html = ' / Opdateret: ' . $updated_date;
    }

    $replacement_map['{{publish_date}}'] = $publish_date;
    $replacement_map['{{updated_date_html}}'] = $updated_date_html;

    // Udfør én enkelt, samlet udskiftning til sidst.
    return str_replace(array_keys($replacement_map), array_values($replacement_map), $block_content);
}