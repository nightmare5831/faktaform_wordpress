<?php
/**
 * FaktaForm Core: Template Tags
 * Håndterer den centrale logik for at rendere dynamiske blokke.
 *
 * @package FaktaForm-Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Den centrale Dispatcher-funktion, der processerer en blok.
 * Den er bygget i henhold til SDD v7.0.
 *
 * @param string $block_content Blokkens oprindelige HTML-indhold.
 * @param array  $block         Den fulde blok-array med attributter og kontekst.
 * @return string              Det modificerede blok-indhold.
 */
/**
 * Indsamler alle mikro-funktioner fra aktive moduler.
 *
 * FOR UDVIKLERE: For at registrere et modul, brug add_filter()
 * på 'faktaform_register_micro_hooks' direkte i dit modules
 * hoved-pluginfil.
 */
function faktaform_dispatch_hook( $block_content, $block ) {
    // Hent CSS-klassen fra blokkens attributter.
    $class_name = isset( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
    if ( empty( $class_name ) ) {
        return $block_content;
    }

    // Hent alle registrerede hooks dynamisk fra modulerne.
    $registered_hooks = apply_filters( 'faktaform_register_micro_hooks', [] );

    // Returner, hvis ingen hooks er registreret.
    if ( empty( $registered_hooks ) ) {
        return $block_content;
    }

    // Gennemløb alle registrerede hooks.
    foreach ( $registered_hooks as $hook_class => $function_name ) {
        // Hvis blokkens CSS-klasse indeholder vores hook, og funktionen findes...
        if ( strpos( $class_name, $hook_class ) !== false && function_exists( $function_name ) ) {
            // ...kald den tilhørende "Mikro-funktion" og returner resultatet.
            return call_user_func( $function_name, $block_content, $block );
        }
    }

    // Returner uændret, hvis intet match blev fundet.
    return $block_content;
}
add_filter( 'render_block', 'faktaform_dispatch_hook', 10, 2 );

// --- FÆLLES HJÆLPEFUNKTIONER (BRUGES AF FLERE MODULER) ---

/**
 * BYGGER HTML FOR AUTORITATIVE PROFILER (Fælles for Ekspert og Journalist)
 * Denne funktion hører til i core, da den bruges af flere moduler (E-E-A-T).
 * @package FaktaForm-Core
 */
if ( ! function_exists( 'faktaform_build_autoritative_profiler_html' ) ) {
    function faktaform_build_autoritative_profiler_html( $post_id ) {
        $html = '';
        if ( function_exists('have_rows') && have_rows('autoritative_profiler', $post_id) ) {
            $html .= '<ul class="faktaform-profiler-liste">';
            while ( have_rows('autoritative_profiler', $post_id) ) { the_row();
                $profil_navn = get_sub_field('navn_pa_profilside');
                $profil_url = get_sub_field('profil_url');
                if ( $profil_navn && $profil_url ) {
                    $html .= '<li><a href="' . esc_url($profil_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($profil_navn) . '</a></li>';
                }
            }
            $html .= '</ul>';
        }
        return $html;
    }
}
