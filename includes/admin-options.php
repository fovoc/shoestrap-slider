<?php

require_once get_template_directory() . '/lib/modules/core.redux/module.php';
/*
 * Shoestrap EDD Addon options
 */
if ( !function_exists( 'shoestrap_slider_module_options' ) ) :
function shoestrap_slider_module_options( $sections ) {

  $section = array(
    'title' => __( 'Slider', 'shoestrap' ),
    'icon'  => 'elusive icon-shopping-cart icon-large'
  );

  $fields[] = array( 
    'title'     => __( 'Use Flexslider for galleries', 'shoestrap_edd' ),
    'desc'      => __( 'Default: On.', 'shoestrap' ),
    'id'        => 'shoestrap_slider_flex_on',
    'default'   => 1,
    'customizer'=> array(),
    'type'      => 'switch'
  );

  $fields[] = array( 
    'title'     => __( 'Slider Type', 'shoestrap' ),
    'desc'      => __( 'Select what type of slider you want.', 'shoestrap' ),
    'id'        => 'shoestrap_slider_type',
    'type'      => 'button_set',
    'options'   => array(
      'bootstrap'  => 'Bootstrap',
      'flexslider' => 'FlexSlider',
    ),
    'default' => 'bootstrap'
  );

  $fields[] = array( 
    'title'     => __( 'Flexslider Images Height', 'shoestrap_edd' ),
    'desc'      => '',
    'id'        => 'shoestrap_slider_height',
    'default'   => 320,
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