<?php
/**
 * Backend and Admin Customizations (Final Clean Version).
 * @package FaktaForm-Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Giver ACF Flexible Content feltet mere højde i admin for bedre overblik.
 */
add_action('admin_head', 'faktaform_custom_admin_css');
function faktaform_custom_admin_css() {
  echo '<style> .acf-field-flexible-content .acf-layouts { min-height: 450px; } </style>';
}
