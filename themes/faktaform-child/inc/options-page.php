<?php
/**
 * Registers the Theme Options Page.
 * @package FaktaForm-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Modul Indstillinger',
        'menu_title'    => 'Modul Indstillinger',
        'menu_slug'     => 'faktaform-module-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}