<?php
/**
 * Plugin Name:       FaktaForm - Journalister
 * Description:       Modul til håndtering af Journalist-profiler.
 * Version:           1.0.1
 * Author:            Thomas Kainø
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// REGEL 1: Inkluder alle nødvendige filer med det samme (Loader)
// post-types.php hooker selv CPT-registreringen på 'init'.
require_once plugin_dir_path( __FILE__ ) . 'inc/post-types.php';
// NYT: Inkluder template-tags for mikro-funktioner
require_once plugin_dir_path( __FILE__ ) . 'inc/template-tags.php'; 


// REGEL 2: Registrer alle hooks med det samme (Registrar)

/**
 * Registrerer dette moduls mikro-funktioner hos FaktaForm Core Dispatcher.
 */
function faktaform_journalister_register_micro_hooks( $hooks ) {
    $journalister_hooks = [
        // Den store funktion Sikander skal bruge
        'faktaform-get-journalist-main'  => 'faktaform_render_journalist_main',
        // Simpel funktion til kun at hente navnet
        'faktaform-get-journalist-navn'  => 'faktaform_render_journalist_navn',
        
        // NY REGISTRERING: Til featured image
        'faktaform-get-journalist-image' => 'faktaform_render_journalist_featured_image',
    ];
    return array_merge( $hooks, $journalister_hooks );
}
add_filter( 'faktaform_register_micro_hooks', 'faktaform_journalister_register_micro_hooks' );

// FJERNET: Den lokale kopi af 'faktaform_build_autoritative_profiler_html' er slettet herfra.
// Funktionen kaldes nu fra FaktaForm Core.
