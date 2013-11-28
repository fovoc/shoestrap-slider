<?php

require_once get_template_directory() . '/lib/modules/core.redux/module.php';
/*
 * Shoestrap EDD Addon options
 */
if ( !function_exists( 'shoestrap_slider_module_options' ) ) :
function shoestrap_slider_module_options( $sections ) {

  $section = array(
    'title' => __( 'Slider', 'shoestrap' ),
    'icon'  => 'el-icon-th-large icon-large'
  );

  $fields[] = array(
    'title'     => __( 'Slider Images Height', 'shoestrap_edd' ),
    'desc'      => __( 'This will be used as a fallback in case you do not specify a height manually in your shortcode', 'shoestrap_slider' ),
    'id'        => 'shoestrap_slider_height',
    'default'   => 450,
    'type'      => 'slider',
    'min'       => 100,
    'max'       => 800,
  );

  $fields[] = array(
    'desc'      => shoestrap_slider_admin_help(),
    'id'        => 'shoestrap_slider_help',
    'type'      => 'info',
  );


  $section['fields'] = $fields;

  $section = apply_filters( 'shoestrap_slider_module_options_modifier', $section );
  
  $sections[] = $section;
  return $sections;
}
add_filter( 'redux-sections-' . REDUX_OPT_NAME, 'shoestrap_slider_module_options', 1 );   
endif;


if ( !function_exists( 'shoestrap_addon_slider_licensing' ) ) :
function shoestrap_addon_slider_licensing($section) {
  $section['fields'][] = array( 
    'title'            => __( 'Shoestrap Slider Licence', 'shoestrap' ),
    'id'              => 'shoestrap_slider_license_key',
    'default'         => '',
    'type'            => 'edd_license',
    'mode'            => 'plugin', // theme|plugin
    'path'            => SHOESTRAPSLIDERFILE, // Path to the plugin/template main file
    'remote_api_url'  => 'http://shoestrap.org',    // our store URL that is running EDD
    'field_id'        => "shoestrap_license_key", // ID of the field used by EDD
  ); 
  return $section;
}
endif;
add_filter( 'shoestrap_module_licencing_options_modifier', 'shoestrap_addon_slider_licensing' );


function shoestrap_slider_admin_help() {
  $content = '<div style="font-size: 1.2em;">';
  $content .= '<h4>Plugin Usage:</h4>';
  $content .= '<p>' . __( 'You can specify the fallback height using the slider above.', 'shoestrap_slider' ) . '</p>';
  $content .= '<p>' . __( 'To change the type of slider you want to use on a gallery, you can select it from the gallery editing screen:', 'shoestrap_slider' ) . '</p>';
  $content .= '<p>' . __( 'For more instructions and details, please consult the <a target="_blank" href="http://shoestrap.org/downloads/shoestrap-3-slider-addon/">plugin page</a>' ) . '</p>';
  $content .= '<p><img style="max-width: 100%;" src="' . plugins_url( '../assets/img/mediaui-selector.png', __FILE__ ) . '"><p>';
  $content .= '</div>';

  return $content;
}