<?php
/**
 * Plugin Name:       FaktaForm - Core
 * Plugin URI:        https://faktaform.dk
 * Description:       Kernefunktionalitet for FaktaForm-platformen. Håndterer grundlæggende logik og API'er, som alle moduler er afhængige af.
 * Version:           1.0.0
 * Author:            Thomas Kainø
 * Author URI:        https://faktaform.dk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       faktaform-core
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Deaktiver GeneratePress' indbyggede template tag-system for at undgå konflikter.
 * Dette giver vores egen SDD v7.0 renderings-motor fuld kontrol.
 */
add_action( 'init', function() {
    remove_filter( 'render_block', 'generate_do_template_tags', 10 );
}, 9 );

/**
 * Initialiserer Core-plugin'et, når WordPress er klar.
 */
function faktaform_core_init() {
    require_once plugin_dir_path( __FILE__ ) . 'inc/template-tags.php';
}
// KORREKTION: Vi skifter til 'init' for at sikre, at ACF er fuldt indlæst.
add_action( 'init', 'faktaform_core_init' );
/**
 * Registrerer den private taksonomi 'ff_module' til at "tagge" Elements.
 * Dette er den centrale mekanisme for at binde Elements til moduler,
 * som beskrevet i SDD v7.0, sektion 4.2.
 */
function faktaform_core_register_element_taxonomy() {

    $labels = array(
        'name'              => _x( 'FaktaForm Moduler', 'taxonomy general name', 'faktaform-core' ),
        'singular_name'     => _x( 'FaktaForm Modul', 'taxonomy singular name', 'faktaform-core' ),
        'search_items'      => __( 'Søg Moduler', 'faktaform-core' ),
        'all_items'         => __( 'Alle Moduler', 'faktaform-core' ),
        'parent_item'       => __( 'Overordnet Modul', 'faktaform-core' ),
        'parent_item_colon' => __( 'Overordnet Modul:', 'faktaform-core' ),
        'edit_item'         => __( 'Rediger Modul', 'faktaform-core' ),
        'update_item'       => __( 'Opdater Modul', 'faktaform-core' ),
        'add_new_item'      => __( 'Tilføj nyt Modul', 'faktaform-core' ),
        'new_item_name'     => __( 'Nyt Modul-navn', 'faktaform-core' ),
        'menu_name'         => __( 'FaktaForm Modul', 'faktaform-core' ),
    );

    $args = array(
        'hierarchical'      => true, // Gør det muligt at have over/under-kategorier (ikke i brug nu)
        'labels'            => $labels,
        'public'            => false, // Skjuler den fra frontend
        'show_ui'           => true,  // Viser den i admin-interfacet
        'show_admin_column' => false, // Viser den ikke i liste-oversigten
        'query_var'         => false, // Ingen grund til at kunne forespørge på den via URL
        'rewrite'           => false, // Ingen rewrite rules
        'show_in_rest'      => true,  // KRITISK: Denne linje sikrer, at den virker i den moderne editor!
    );

    // Her registreres taksonomien og bindes til 'gp_elements' post-typen.
    register_taxonomy( 'ff_module', 'gp_elements', $args );
}
// Vi hooker den på 'init' med prioritet 20 for at sikre,
// at 'gp_elements' post-typen allerede er registreret af GeneratePress.
add_action( 'init', 'faktaform_core_register_element_taxonomy', 20 );