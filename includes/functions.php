<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Replace gallery_shortcode()
 */
if ( !function_exists( 'shoestrap_slider_gallery' ) ) :
function shoestrap_slider_gallery( $attr ) {
	global $ss_layout, $ss_settings;

	$post = get_post();

	$shoestrap_slider_height = $ss_settings['shoestrap_slider_height'];

	if ( !isset( $shoestrap_slider_height ) || empty( $shoestrap_slider_height ) )
		$shoestrap_slider_height = 450;

	static $instance = 0;
	$instance++;

	if ( !empty( $attr['ids'] ) ) {
		$attr['orderby'] = ( empty( $attr['orderby'] ) ) ? 'post__in' : '';
		$attr['include'] = $attr['ids'];
	}

	$output = apply_filters( 'post_gallery', '', $attr );

	if ( $output != '' )
		return $output;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract( shortcode_atts( array( 
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => '',
		'icontag'    => '',
		'captiontag' => '',
		'columns'    => 4,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => 'file',
		'type'       => 'default',
		'height'     => $shoestrap_slider_height,
	 ), $attr ) );

	// If type is set to default, return the default Shoetrap gallery
	if ( $type == 'default' ) {
		return shoestrap_gallery( $attr );
	} else {
		// if type is not default, continue processing
		$id = intval( $id );
		$columns = ( 12 % $columns == 0 ) ? $columns: 4;

		if ( $order === 'RAND' )
			$orderby = 'none';

		if ( !empty( $include ) ) {
			$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( !empty( $exclude ) ) {
			$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
		} else {
			$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
		}

		if ( empty( $attachments ) )
			return '';

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
			}

			return $output;
		}

		$unique = ( get_query_var( 'page' ) ) ? $instance . '-p' . get_query_var( 'page' ) : $instance;
		$output = shoestrap_slider_helper( 'wrapper_start', 'gallery-' . $post->ID . '-' . $unique, '', $type );
		$i = 0; foreach ( $attachments as $id => $attachment ) { $i++; }
		$output .= shoestrap_slider_helper( 'before_inner_start', 'gallery-' . $post->ID . '-' . $unique, $i, $type );
		$output .= shoestrap_slider_helper( 'inner_start', 'slides', '', $type );

		$width = $ss_layout->content_width_px();

		$i = 0;
		foreach ( $attachments as $id => $attachment ) {
			$imageurl = wp_get_attachment_url( $id );
			$image_args = array( "url" => $imageurl, "width" => $width, "height" => $height );

			$image = Shoestrap_Image::image_resize( $image_args );
			$image_url = $image['url'];
			$output .= shoestrap_slider_helper( 'slide_element_start', $imageurl, $i, $type ) . '<img src="' . $image_url . '" />';
			
			if ( trim( $attachment->post_excerpt ) ) {
				$output .= shoestrap_slider_helper( 'caption_start', '', '', $type );
				$output .= wptexturize( $attachment->post_excerpt );
				$output .= shoestrap_slider_helper( 'caption_end', '', '', $type );
			}
			$output .= shoestrap_slider_helper( 'slide_element_end', '', '', $type );
			$i++;
		}

		$output .= shoestrap_slider_helper( 'inner_end', '', '', $type );
		$output .= shoestrap_slider_helper( 'before_wrapper_end', 'gallery-' . $post->ID . '-' . $unique, '', $type );
		$output .= shoestrap_slider_helper( 'wrapper_end', '', '', $type );

		if ( $type == 'flexslider_thumbs' ) {
			$output .= '<div id="carousel" class="flexslider"><ul class="slides">';

			$i = 0;
			foreach ( $attachments as $id => $attachment ) {
				$imageurl = wp_get_attachment_url( $id );
				$image_args = array( "url" => $imageurl, "width" => $width, "height" => $height );
				$image = shoestrap_image_resize( $image_args );
				$image_url = $image['url'];
				$output .= shoestrap_slider_helper( 'slide_element_start', $imageurl, $i, $type ) . '<img src="' . $image_url . '" />';
				$output .= shoestrap_slider_helper( 'slide_element_end', '', '', $type );
				$i++;
			}
			$output .= '</ul></div>';
		}

		$output .= shoestrap_slider_gallery_script( '.gallery-' . $post->ID . '-' . $unique, $type );

		return $output;
	}
}
endif;


/*
 * Replace default gallery with our custom shortcode
 */
if ( !function_exists( 'shoestrap_slider_gallery_setup_after_theme' ) ) :
function shoestrap_slider_gallery_setup_after_theme() {
	remove_shortcode( 'gallery' );
	add_shortcode( 'gallery', 'shoestrap_slider_gallery' );
}
endif;
add_action( 'after_setup_theme', 'shoestrap_slider_gallery_setup_after_theme' );


/*
 * The script required for the sliders.
 */
if ( !function_exists( 'shoestrap_slider_gallery_script' ) ) :
function shoestrap_slider_gallery_script( $element = '', $type = 'default' ) {
	if ( $type != 'default' ) {

		// The Bootstrap Carousel script
		if ( $type == 'bootstrap' ) {
			$script = '$("' . $element . '").carousel();';
		} elseif ( $type == 'flexslider' || $type == 'flexslider_thumbs' ) { // If flexslider is selected, process the below
			// The basic script
			$script = '$("' . $element . '").flexslider({ animation: "slide" });';

			// The script that adds thumbs if selected.
			if ( $type == 'flexslider_thumbs' ) {
				$script = '
					$("#carousel").flexslider({
						animation: "slide",
						controlNav: false,
						animationLoop: false,
						slideshow: false,
						itemWidth: ' . ( shoestrap_content_width_px() / 4 ) . ',
						itemMargin: 0,
						asNavFor: "' . $element . '"
					});

					$("' . $element . '").flexslider({
						animation: "slide",
						controlNav: false,
						animationLoop: false,
						slideshow: false,
						sync: "#carousel"
					});';
			}
		}

		return '<script>$(window).load(function() {' . $script . '});</script>';
	}
}
endif;


/*
 * Slider Helper function
 */
if ( !function_exists( 'shoestrap_slider_helper' ) ) :
function shoestrap_slider_helper( $element, $class, $count = 0, $type = 'default' ) {
	if ( $type != 'default' ) {

		$content = '';

		// Elements for flexslider
		if ( $type == 'flexslider' || $type == 'flexslider_thumbs' ) {
			if ( $element == 'wrapper_start' )
				$content = '<div class="flexslider ' . $class . '">';
			elseif ( $element == 'wrapper_end' )
				$content = '</div>';
			elseif ( $element == 'inner_start' )
				$content = '<ul class="slides">';
			elseif ( $element == 'inner_end' )
				$content = '</ul>';
			elseif ( $element == 'slide_element_start' )
				$content = '<li data-thumb="' . $class . '">';
			elseif ( $element == 'slide_element_end' )
				$content = '</li>';
			elseif ( $element == 'caption_start' )
				$content = '<p class="flex-caption caption hidden">';
			elseif ( $element == 'caption_end' )
				$content = '</p>';

		// Elements for Bootstrap Carousel
		} elseif ( $type == 'bootstrap' ) {
			if ( $element == 'wrapper_start' ) {
				$content = '<div id="' . $class . '" class="carousel slide ' . $class . '">';
			} elseif ( $element == 'wrapper_end' ) {
				$content = '</div>';
			} elseif ( $element == 'inner_start' ) {
				$content = '<div class="carousel-inner ' . $class . '">';
			} elseif ( $element == 'inner_end' ) {
				$content = '</div>';
			} elseif ( $element == 'slide_element_start' ) {
				$content = ( $count == 0 ) ? '<div class="item active">' : '<div class="item ' . $count . '">';
			} elseif ( $element == 'slide_element_end' ) {
				$content = '</div>';
			} elseif ( $element == 'before_wrapper_end' ) {
				$content = '<a class="left carousel-control" href="#' . $class . '" data-slide="prev"><span class="el-icon-prev"></span></a><a class="right carousel-control" href="#' . $class . '" data-slide="next"><span class="el-icon-next"></span></a>';
			} elseif ( $element == 'caption_start' ) {
				$content = '<div class="carousel-caption">';
			} elseif ( $element == 'caption_end' ) {
				$content = '</div>';
			} elseif ( $element == 'before_inner_start' ) {
				$content = '<ol class="carousel-indicators">';
				for ( $i=0; $i<$count ; $i++ ) {
					$content .= ( $i == 0 ) ? '<li data-target="#' . $class . '" data-slide-to="' . $i . '" class="active"></li>' : '<li data-target="#' . $class . '" data-slide-to="' . $i . '"></li>';
				}
				$content .= '</ol>';
			}
		}

		return $content;
	}
}
endif;