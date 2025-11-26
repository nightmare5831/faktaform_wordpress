<?php
/**
 * Plugin Name:       FaktaForm - Eksperter
 * Description:       Modul til håndtering af Ekspert-profiler.
 * Version:           10.1.0 (Korrekt Enqueue Struktur)
 * Author:            Thomas Kainø
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Inkluder undersystemer
require_once plugin_dir_path( __FILE__ ) . 'inc/post-types.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/template-tags.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/enqueue-scripts.php'; // NYT: Inkluderer den korrekte fil

/**
 * Registrer alle funktioner hos Dispatcher'en.
 */
function faktaform_eksperter_register_micro_hooks( $hooks ) {
    $eksperter_hooks = [
        'faktaform-get-ekspert-navn'     => 'faktaform_render_navn',
        'faktaform-get-ekspert-stilling' => 'faktaform_render_stilling',
        'faktaform-get-ekspert-main'     => 'faktaform_render_ekspert_main',
        'faktaform-get-ekspert-image'      => 'faktaform_render_ekspert_featured_image',
        'faktaform-get-ekspert-cv'         => 'faktaform_render_cv_tidslinje',
        'faktaform-get-ekspert-udtalelser' => 'faktaform_render_kundeudtalelser',
    ];
    return array_merge( $hooks, $eksperter_hooks );
}
add_filter( 'faktaform_register_micro_hooks', 'faktaform_eksperter_register_micro_hooks' );