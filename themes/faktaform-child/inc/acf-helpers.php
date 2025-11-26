<?php
/*****************************************************************
 * Funktion til dynamisk at hente Patterns til ACF Select-felter.
 *****************************************************************/

/**
 * Henter alle publicerede 'wp_block' (Patterns) til brug i ACF.
 *
 * @return array
 */
function faktaform_get_all_patterns_for_acf() {
    $choices = [
        '' => '--- Vælg et modul ---'
    ];

    $args = [
        'post_type'      => 'wp_block',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish', // VIGTIGT: Vi henter nu KUN publicerede.
        'suppress_filters' => true,
    ];

    $patterns = new WP_Query( $args );

    if ( $patterns->have_posts() ) {
        while ( $patterns->have_posts() ) {
            $patterns->the_post();
            $choices[ get_the_ID() ] = get_the_title();
        }
    }
    
    wp_reset_postdata();

    return $choices;
}

/**
 * Registrerer ACF-filtre, når ACF er klar.
 */
function faktaform_register_acf_field_filters() {
    /**
     * Forbinder ovenstående funktion til alle ACF 'Select'-felter 
     * der har CSS-klassen 'acf_pattern_selector'.
     */
    add_filter('acf/load_field', function ( $field ) {

        // Tjek om feltets 'wrapper' har den korrekte CSS-klasse.
        if ( empty($field['wrapper']['class']) || strpos($field['wrapper']['class'], 'acf_pattern_selector') === false ) {
            return $field; // Hvis ikke, returner feltet uændret.
        }

        // Hvis klassen er fundet, populér feltets valgmuligheder.
        $field['choices'] = faktaform_get_all_patterns_for_acf();
        
        return $field;
    });
}
add_action( 'acf/init', 'faktaform_register_acf_field_filters' );