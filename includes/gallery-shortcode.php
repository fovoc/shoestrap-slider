<?php

/*
 * Replace gallery_shortcode()
 */
function shoestrap_slider_gallery( $attr ) {
	$post = get_post();
	$options = get_option( 'shoestrap' );

	static $instance = 0;
	$instance++;

	if ( !empty( $attr['ids'] ) ) :
		if ( empty( $attr['orderby'] ) ) :
			$attr['orderby'] = 'post__in';
		endif;
		$attr['include'] = $attr['ids'];
	endif;

	$output = apply_filters( 'post_gallery', '', $attr );

	if ( $output != '' ) :
		return $output;
	endif;

	if ( isset( $attr['orderby'] ) ) :
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] ) :
			unset( $attr['orderby'] );
		endif;
	endif;

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
		'link'       => 'file'
	 ), $attr ) );

	$id = intval( $id );
	$columns = ( 12 % $columns == 0 ) ? $columns: 4;

	if ( $order === 'RAND' ) :
		$orderby = 'none';
	endif;

	if ( !empty( $include ) ) :
		$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) :
			$attachments[$val->ID] = $_attachments[$key];
		endforeach;
	elseif ( !empty( $exclude ) ) :
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	else :
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	endif;

	if ( empty( $attachments ) ) :
		return '';
	endif;

	if ( is_feed() ) :
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) :
			$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
		endforeach;
		return $output;
	endif;

	$unique = ( get_query_var( 'page' ) ) ? $instance . '-p' . get_query_var( 'page' ): $instance;
	$output = shoestrap_slider_helper( 'wrapper_start', 'gallery-' . $post->ID . '-' . $unique );
	$i = 0; foreach ( $attachments as $id => $attachment ) : $i++; endforeach;
	$output .= shoestrap_slider_helper( 'before_inner_start', 'gallery-' . $post->ID . '-' . $unique, $i );
	$output .= shoestrap_slider_helper( 'inner_start', 'slides' );

	$width    = $options['screen_large_desktop'];
	$height   = $options['shoestrap_slider_height'];

	$i = 0;
	foreach ( $attachments as $id => $attachment ) :

		$imageurl = wp_get_attachment_url( $id );
		$image_args = array( "url" => $imageurl, "width" => $width, "height" => $height );

		$image = shoestrap_image_resize( $image_args );
		$image_url = $image['url'];
		$output .= shoestrap_slider_helper( 'slide_element_start', $imageurl, $i ) . '<img src="' . $image_url . '" />';
		
		if ( trim( $attachment->post_excerpt ) ) :
			$output .= shoestrap_slider_helper( 'caption_start', '' );
			$output .= wptexturize( $attachment->post_excerpt );
			$output .= shoestrap_slider_helper( 'caption_end', '' );
		endif;
		
		$output .= shoestrap_slider_helper( 'slide_element_end', '' );
		$i++;
	endforeach;

	$output .= shoestrap_slider_helper( 'inner_end', '' );
	$output .= shoestrap_slider_helper( 'before_wrapper_end', 'gallery-' . $post->ID . '-' . $unique );
	$output .= shoestrap_slider_helper( 'wrapper_end', '' );

	if ( $options['shoestrap_slider_flextype'] == 'w_thumb' ) :
		$output .= '<div id="carousel" class="flexslider"><ul class="slides">';

		$i = 0;
		foreach ( $attachments as $id => $attachment ) :

			$imageurl = wp_get_attachment_url( $id );
			$image_args = array( "url" => $imageurl, "width" => $width, "height" => $height );

			$image = shoestrap_image_resize( $image_args );
			$image_url = $image['url'];
			$output .= shoestrap_slider_helper( 'slide_element_start', $imageurl, $i ) . '<img src="' . $image_url . '" />';
			$output .= shoestrap_slider_helper( 'slide_element_end', '' );
			$i++;
		endforeach;

		$output .= '</ul></div>';
	endif;

	$output .= shoestrap_slider_gallery_script( '.gallery-' . $post->ID . '-' . $unique );

	return $output;
}


/*
 * Replace default gallery with our custom shortcode
 */
function shoestrap_slider_gallery_setup_after_theme() {
	remove_shortcode( 'gallery' );
	add_shortcode( 'gallery', 'shoestrap_slider_gallery' );
}
add_action( 'after_setup_theme', 'shoestrap_slider_gallery_setup_after_theme' );


/*
 * The script required for the sliders.
 */
function shoestrap_slider_gallery_script( $element = '' ) {
	$options = get_option( 'shoestrap' );

	// The Bootstrap Carousel script
	if ( $options['shoestrap_slider_type'] == 'bootstrap' ) :
		$script = '$("' . $element . '").carousel();';

	// If flexslider is selected, process the below
	elseif ( $options['shoestrap_slider_type'] == 'flexslider' ) :
		// The basic script
		$script = '$("' . $element . '").flexslider({ animation: "slide" });';

		// The script that adds thumbs if selected.
		if ( $options['shoestrap_slider_flextype'] == 'w_thumb' ) :
			$script = '
				$("#carousel").flexslider({
					animation: "slide",
					controlNav: false,
					animationLoop: false,
					slideshow: false,
					itemWidth: ' . ( $options['screen_large_desktop'] / 4 ) . ',
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
		endif;

	endif;

	return '<script>$(window).load(function() {' . $script . '});</script>';
}

/*
 * Slider Helper function
 */
function shoestrap_slider_helper( $element, $class, $count = 0 ) {
	$options = get_option( 'shoestrap' );

	$content = '';

	// Elements for flexslider
	if ( $options['shoestrap_slider_type'] == 'flexslider' ) :

		if ( $element == 'wrapper_start' ) :
			$content = '<div class="flexslider ' . $class . '">';

		elseif ( $element == 'wrapper_end' ) :
			$content = '</div>';

		elseif ( $element == 'inner_start' ) :
			$content = '<ul class="slides">';

		elseif ( $element == 'inner_end' ) :
			$content = '</ul>';

		elseif ( $element == 'slide_element_start' ) :
			$content = '<li data-thumb="' . $class . '">';

		elseif ( $element == 'slide_element_end' ) :
			$content = '</li>';

		elseif ( $element == 'caption_start' ) :
			$content = '<p class="flex-caption caption hidden">';

		elseif ( $element == 'caption_end' ) :
			$content = '</p>';

		endif;

	// Elements for Bootstrap Carousel
	elseif ( $options['shoestrap_slider_type'] == 'bootstrap' ) :

		if ( $element == 'wrapper_start' ) :
			$content = '<div id="' . $class . '" class="carousel slide ' . $class . '">';

		elseif ( $element == 'wrapper_end' ) :
			$content = '</div>';

		elseif ( $element == 'inner_start' ) :
			$content = '<div class="carousel-inner ' . $class . '">';

		elseif ( $element == 'inner_end' ) :
			$content = '</div>';

		elseif ( $element == 'slide_element_start' ) :
			$content = '<div class="item ' . $count . '">';

			if ( $class == 0 ) :
				$content = '<div class="item active">';
			endif;

		elseif ( $element == 'slide_element_end' ) :
			$content = '</div>';

		elseif ( $element == 'before_wrapper_end' ) :
			$content = '<a class="left carousel-control" href="#' . $class . '" data-slide="prev"><span class="elusive icon-prev"></span></a>';
			$content .= '<a class="right carousel-control" href="#' . $class . '" data-slide="next"><span class="elusive icon-next"></span></a>';

		elseif ( $element == 'caption_start' ) :
			$content = '<div class="carousel-caption">';

		elseif ( $element == 'caption_end' ) :
			$content = '</div>';

		elseif ( $element == 'before_inner_start' ) :
			$content = '<ol class="carousel-indicators">';

			for ( $i=0; $i<$count ; $i++ ) :

				if ( $i == 0 ) :
					$content .= '<li data-target="#' . $class . '" data-slide-to="' . $i . '" class="active"></li>';
				else :
					$content .= '<li data-target="#' . $class . '" data-slide-to="' . $i . '"></li>';
				endif;

			endfor;

			$content .= '</ol>';

		endif;
	endif;

	return $content;
}