<?php

if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function shoestrap_slider_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'shoestrap_slider_addon_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( 'http://shoestrap.org', SHOESTRAPSLIDERFILE, array(
		'version'   => '1.3',
		'license'   => $license_key,
		'item_name' => 'Shoestrap 3 Slider Addon',
		'author'    => 'aristath, fovoc'
	) );

}
add_action( 'admin_init', 'shoestrap_slider_plugin_updater' );

/**
 * Add page to menu under "Settings"
 */
if ( ! function_exists( 'ss_updater_add_menus' ) ) {
	function ss_updater_add_menus() {
		add_theme_page( 'Shoestrap Extensions & Addons', 'Shoestrap Extensions', 'manage_options', 'ss-updater', 'ss_updater_settings_page' );
	}

	add_action( 'admin_menu', 'ss_updater_add_menus' );
}

/**
 * Build our Options page
 */
if ( ! function_exists( 'ss_updater_settings_page' ) ) {
	function ss_updater_settings_page() {

		echo '<div class="wrap">';

			echo '<h2>' . __( 'Shoestrap Licensing' ) . '</h2><hr><br>';

			// If there are no products that need licensing, display a message
			if ( ! has_action( 'shoestrap_updater_form_content' ) ) {
				_e( 'No products require a license key.' );
			}

			?>
			<style>tr td.plugin-title { border-left: 3px solid #faebcc; } tr.valid td.plugin-title { border-left: 3px solid #3c763d; } tr.invalid. td.plugin-title { border-left: 3px solid #a94442; } input.button-activate { border: none; background: none; color: #31708f; padding: 0; } input.button-deactivate { border: none; background: none; color: #a94442; padding: 0; } tr td { border-bottom: 1px solid rgb(229, 229, 229); } tr.valid { background: #dff0d8; } tr.invalid { background: #f2dede; }</style>

			<table class="wp-list-table widefat plugins">
				<thead>
					<tr>
						<th scope="col" id="name" class="manage-column column-name" style="">Product name</th>
						<th scope="col" id="license" class="manage-column column-license" style="">License</th>
						<th scope="col" id="save" class="manage-column column-save" style=""></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php
						// Include our custom licensing fields for all our plugins & themes.
						do_action( 'shoestrap_updater_form_content' );
					?>
				</tbody>
			</table>

			<?php

			// Display the addons section.
			do_action( 'shoestrap_installer_form_content' );

		echo '</div>';
	}
}

/*
 * The license form that is added in the admin page.
 */
if ( ! function_exists( 'shoestrap_slider_addon_license_form' ) ) {
	function shoestrap_slider_addon_license_form() {
		$field_name = 'shoestrap_slider_addon_license';

		$license 	= get_option( 'shoestrap_slider_addon_license_key' );
		$status 	= get_option( 'shoestrap_slider_addon_license_key_status' );
		?>

		<tr id="shoestrap-slider" class="<?php echo $status; ?>">
			<form method="post" action="options.php">
				<?php settings_fields( 'shoestrap_slider_addon_license' ); ?>
				<td class="plugin-title">
					<strong><?php _e( 'Shoestrap 3 Slider Addon', 'shoestrap_slider' ); ?></strong>
					<div class="row-actions visible">
						<?php wp_nonce_field( 'shoestrap_slider_nonce', 'shoestrap_slider_nonce' ); ?>

						<?php if ( false !== $license ) : ?>
							<?php if ( $status !== false && $status == 'valid' ) : ?>
								<input type="submit" class="button-deactivate" name="shoestrap_slider_addon_license_deactivate" value="<?php _e( 'Deactivate License', 'shoestrap_slider' ); ?>"/>
							<?php else : ?>
								<input type="submit" class="button-activate" name="shoestrap_slider_addon_license_activate" value="<?php _e( 'Activate License', 'shoestrap_slider' ); ?>"/>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</td>

				<td>
					<input id="shoestrap_slider_addon_license_key" name="shoestrap_slider_addon_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
				</td>

				<td>
					<?php submit_button(); ?>
				</td>
			</form>
		</tr>
		<?php
	}
}
add_action( 'shoestrap_updater_form_content', 'shoestrap_slider_addon_license_form' );

/**
 * Register the option
 */
if ( ! function_exists( 'shoestrap_slider_licensing_register_option' ) ) {
	function shoestrap_slider_licensing_register_option() {
		// creates our settings in the options table
		register_setting( 'shoestrap_slider_addon_license', 'shoestrap_slider_addon_license_key', 'shoestrap_slider_addon_license_sanitize' );
	}
	add_action('admin_init', 'shoestrap_slider_licensing_register_option');
}

/*
 * Gets rid of the local license status option when adding a new one
 */
if ( ! function_exists( 'shoestrap_slider_addon_license_sanitize' ) ) {
	function shoestrap_slider_addon_license_sanitize( $new ) {
		$old = get_option( 'shoestrap_slider_addon_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'shoestrap_slider_addon_license_key_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}
}

/*
 * Activate the license
 */
function shoestrap_slider_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['shoestrap_slider_addon_license_activate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'shoestrap_slider_nonce', 'shoestrap_slider_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'shoestrap_slider_addon_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
	 		'item_name'  => urlencode( 'Shoestrap 3 Slider Addon' ),
	 		'url'        => home_url()
	 	);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, 'http://shoestrap.org' ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'shoestrap_slider_addon_license_key_status', $license_data->license );

	}

}
add_action( 'admin_init', 'shoestrap_slider_activate_license' );

/*
 * De-activate the license
 */
function shoestrap_slider_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['shoestrap_slider_addon_license_deactivate'] ) ) {

		// run a quick security check 
	 	if ( ! check_admin_referer( 'shoestrap_slider_nonce', 'shoestrap_slider_nonce' ) ) {
	 		return; // get out if we didn't click the Activate button
	 	}

	 	// retrieve the license from the database
	 	$license = trim( get_option( 'shoestrap_slider_addon_license_key' ) );

	 	// data to send in our API request
	 	$api_params = array(
	 		'edd_action' => 'deactivate_license',
	 		'license'    => $license,
	 		'item_name'  => urlencode( 'Shoestrap 3 Slider Addon' ),
	 		'url'        => home_url()
	 	);

	 	// Call the custom API.
	 	$response = wp_remote_get( add_query_arg( $api_params, 'http://shoestrap.org' ), array( 'timeout' => 15, 'sslverify' => false ) );

	 	// make sure the response came back okay
	 	if ( is_wp_error( $response ) ) {
	 		return false;
	 	}

	 	// decode the license data
	 	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	 	if ( $license_data->license == 'deactivated' ) {
	 		delete_option( 'shoestrap_slider_addon_license_key_status' );
	 	}

	 }

}
add_action('admin_init', 'shoestrap_slider_deactivate_license');
