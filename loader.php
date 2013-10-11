<?php
/*
Plugin Name: Shoestrap Slider
Plugin URI: http://wpmu.io
Description: Transforms WordPress Galleries
Version: 1.00
Author: Aristeides Stathopoulos
Author URI: http://aristeides.com
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Add the admin options
require_once dirname( __FILE__ ) . '/includes/admin-options.php';

// If the user has selected not to use flexslider, do not process.
$options = get_option( 'shoestrap' );
if ( $options['shoestrap_slider_toggle'] != 0 ) :
	// Add the flexslider gallery shortcode
	require_once dirname( __FILE__ ) . '/includes/functions.php';

	// Enqueue stylesheets and scripts
	add_action( 'wp_enqueue_scripts', 'shoestrap_slider_enqueue_resources', 102 );
endif;

/*
 * Enqueue stylesheets and scripts
 */
function shoestrap_slider_enqueue_resources() {
	wp_enqueue_style( 'flexslider', plugins_url( 'assets/css/flexslider.css', __FILE__ ), false, null );
	wp_register_script( 'shoestrap_slider', plugins_url( 'assets/js/jquery.flexslider-min.js', __FILE__ ), false, null, true );
	wp_enqueue_script( 'shoestrap_slider' );
}