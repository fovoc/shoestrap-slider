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
    'desc'      => '',
    'id'        => 'shoestrap_slider_height',
    'default'   => 450,
    'type'      => 'slider',
    'min'       => 100,
    'max'       => 800,
  );


  $section['fields'] = $fields;

  $section = apply_filters( 'shoestrap_slider_module_options_modifier', $section );
  
  $sections[] = $section;
  return $sections;
}
add_filter( 'redux-sections-' . REDUX_OPT_NAME, 'shoestrap_slider_module_options', 1 );   
endif;