<?php
/*
Plugin Name: Shoestrap 3 Slider
Plugin URI: http://wpmu.io
Description: Transform WordPress Galleries to Sliders
Version: 1.2
Author: Aristeides Stathopoulos
Author URI: http://aristeides.com
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHOESTRAPSLIDERFILE', __FILE__ );

/**
 * include the necessary files
 */
function shoestrap_slider_include_files() {
	// Add the admin options
	require_once dirname( __FILE__ ) . '/includes/admin-options.php';

	// Add the gallery shortcode, replacing the WordPress default.
	require_once dirname( __FILE__ ) . '/includes/functions.php';
}
add_action( 'shoestrap_include_files', 'shoestrap_slider_include_files' );

// Enqueue stylesheets and scripts
add_action( 'wp_enqueue_scripts', 'shoestrap_slider_enqueue_resources', 102 );

/*
 * Enqueue stylesheets and scripts
 */
if ( ! function_exists( 'shoestrap_slider_enqueue_resources' ) ) :
function shoestrap_slider_enqueue_resources() {
	wp_enqueue_style( 'flexslider', plugins_url( 'assets/css/flexslider.css', __FILE__ ), false, null );
	wp_register_script( 'shoestrap_slider', plugins_url( 'assets/js/jquery.flexslider-min.js', __FILE__ ), false, null, true );
	wp_enqueue_script( 'shoestrap_slider' );
}
endif;


/*
 * Fuction copied from the JetPack plugin
 * Original function name: add_gallery_type()
 */
function shoestrap_slider_add_gallery_type( $types = array() ) {
	$types['bootstrap']         = esc_html__( 'Bootstrap Carousel', 'shoestrap_slider' );
	$types['flexslider']        = esc_html__( 'FlexSlider', 'shoestrap_slider' );
	$types['flexslider_thumbs'] = esc_html__( 'FlexSlider with Thumbnails', 'shoestrap_slider' );
	return $types;
}
add_filter( 'jetpack_gallery_types', 'shoestrap_slider_add_gallery_type', 10 );



if ( ! class_exists( 'Jetpack_Gallery_Settings' ) ) {
	/*
	 * Fuction copied from the JetPack plugin
	 * Original function name: admin_init()
	 */
	function shoestrap_slider_admin_init() {
		add_action( 'wp_enqueue_media', 'shoestrap_slider_wp_enqueue_media' );
		add_action( 'print_media_templates', 'shoestrap_slider_print_media_templates' );
	}
	add_action( 'admin_init', 'shoestrap_slider_admin_init' );


	/**
	 * Registers/enqueues the gallery settings admin js.
	 */
	function shoestrap_slider_wp_enqueue_media() {
		if ( ! wp_script_is( 'jetpack-gallery-settings', 'registered' ) ) {
			wp_register_script( 'jetpack-gallery-settings', plugins_url( 'assets/js/gallery-settings.js', __FILE__ ), array( 'media-views' ), '20121225' );
		}

		wp_enqueue_script( 'jetpack-gallery-settings' );
	}


	/**
	 * Outputs a view template which can be used with wp.media.template
	 */
	function shoestrap_slider_print_media_templates() {
		$default_gallery_type = apply_filters( 'jetpack_default_gallery_type', 'default' );
		$gallery_types = apply_filters( 'jetpack_gallery_types', array( 'default' => __( 'Thumbnail Grid', 'jetpack' ) ) );
		?>
		<script type="text/html" id="tmpl-jetpack-gallery-settings">
			<label class="setting">
				<span><?php _e( 'Type', 'shoestrap_slider' ); ?></span>
				<select class="type" name="type" data-setting="type">
					<?php foreach ( $gallery_types as $value => $caption ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $default_gallery_type ); ?>><?php echo esc_html( $caption ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</script>
		<?php
	}
}

function shoestrap_slider_updater() {

	$args = array(
		'remote_api_url' => 'http://shoestrap.org',
		'item_name'      => 'Shoestrap 3 Slider Addon',
		'version'        => '1.2',
		'author'         => 'aristath',
		'mode'           => 'theme',
		'title'          => 'Shoestrap 3 Slider Addon License',
		'field_name'     => 'shoestrap_slider_addon_license',
		'description'    => '',
		'single_license' => false
	);

	if ( class_exists( 'SS_EDD_SL_Updater' ) ) {
		$updater = new SS_EDD_SL_Updater( $args );
	}

}
add_action( 'admin_init', 'shoestrap_slider_updater' );