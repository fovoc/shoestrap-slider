<?php
/*
Plugin Name: Shoestrap Slider
Plugin URI: http://wpmu.io
Description: Transforms WordPress Galleries
Version: 1.00
Author: Aristeides Stathopoulos
Author URI: http://aristeides.com
*/

require_once dirname( __FILE__ ) . '/includes/gallery-shortcode.php';

wp_enqueue_style( 'shoestrap_mp_styles', plugins_url( 'assets/css/flexslider.css', __FILE__ ), false, null );

/*
 * Enqueue stylesheets and scripts
 */
function shoestrap_slider_enqueue_resources() {
	wp_register_script( 'shoestrap_slider', plugins_url( 'assets/js/jquery.flexslider-min.js', __FILE__ ), false, null, true );
	wp_enqueue_script( 'shoestrap_slider' );
}
add_action( 'wp_enqueue_scripts', 'shoestrap_slider_enqueue_resources', 102 );