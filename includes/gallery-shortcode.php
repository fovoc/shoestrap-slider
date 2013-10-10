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
	$output = '<div class="gallery flexslider gallery-' . $id . '-' . $unique . '">';
	$output .= '<ul class="slides">';
	$output .= shoestrap_slider_gallery_script( '.gallery-' . $id . '-' . $unique );

	foreach ( $attachments as $id => $attachment ) :

		$imageurl = wp_get_attachment_url( $id );
		$width    = $options['screen_large_desktop'];
		$height   = $options['shoestrap_slider_height'];
		$image_args = array( "url" => $imageurl, "width" => $width, "height" => $height );

		$image = shoestrap_image_resize( $image_args );
		$image_url = $image['url'];
		$output .= '<li><img src="' . $image_url . '" />';
		
		if ( trim( $attachment->post_excerpt ) ) :
			$output .= '<p class="flex-caption caption hidden">' . wptexturize( $attachment->post_excerpt ) . '</p>';
		endif;
		
		$output .= '</li>';
	endforeach;

	$output .= '</ul>';
	$output .= '</div>';

	return $output;
}

function shoestrap_slider_gallery_setup_after_theme() {
	remove_shortcode( 'gallery' );
	add_shortcode( 'gallery', 'shoestrap_slider_gallery' );
}
add_action( 'after_setup_theme', 'shoestrap_slider_gallery_setup_after_theme' );

function shoestrap_slider_gallery_script( $element = '.flexslider' ) {
	$script = '<script>$(window).load(function() { $("' . $element . '").flexslider({ animation: "slide" }); });</script>';

	return $script;
}
