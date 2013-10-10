<?php

/*
 * Replace gallery_shortcode()
 */
function shoestrap_slider_gallery( $attr ) {
	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( !empty( $attr['ids'] ) ) {
		if ( empty( $attr['orderby'] ) ) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	$output = apply_filters( 'post_gallery', '', $attr );

	if ( $output != '' ) {
		return $output;
	}

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] ) {
			unset( $attr['orderby'] );
		}
	}

	extract( shortcode_atts( array( 
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'icontag'    => 'li',
		'captiontag' => 'p',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
	 ), $attr ) );

	$id = intval( $id );

	if ( $order === 'RAND' ) {
		$orderby = 'none';
	}

	if ( !empty( $include ) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty( $exclude ) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
		return $output;
	}
	
	$output = '<div id="slider" class="flexslider">';
	$output .= '<ul class="slides">';

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$image_attributes = wp_get_attachment_image_src( $id, 'full' );
		$imagesrc = $image_attributes[0];

		$output .= '<li><img src="' . $imagesrc . '"></li>';
	}

	$output .= '</ul></div>';

	$output .= '<div id="carousel" class="flexslider">';
	$output .= '<ul class="slides">';

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$image_attributes = wp_get_attachment_image_src( $id, 'thumbnail' );
		$imagesrc = $image_attributes[0];

		$output .= '<li class="thumbnail"><img src="' . $imagesrc . '"></li>';
	}

	$output .= '</ul></div>';
	
	$output .="
	<script>
	$( window ).load( function() {
		// The slider being synced must be initialized first
		$( '#carousel' ).flexslider( {
			animation: 'slide',
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			itemWidth: 70,
			itemMargin: 5,
			asNavFor: '#slider'
		} );
		 
		$( '#slider' ).flexslider( {
			animation: 'slide',
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			sync: '#carousel'
		} );
	} );
	</script>";

	return $output;
}

function shoestrap_mp_gallery_setup_after_theme() {
	remove_shortcode( 'gallery' );
	add_shortcode( 'gallery', 'shoestrap_mp_gallery' );
}
add_action( 'after_setup_theme', 'shoestrap_mp_gallery_setup_after_theme' );
