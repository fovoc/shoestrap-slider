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

	if ( $order === 'RAND' ) {
		$orderby = 'none';
	}

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

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
		}
		return $output;
	}

	$unique = ( get_query_var( 'page' ) ) ? $instance . '-p' . get_query_var( 'page' ): $instance;
	$output = '<div class="gallery flexslider gallery-' . $id . '-' . $unique . '">';
	$output .= '<ul class="slides">';

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$image = ( 'file' == $link ) ? wp_get_attachment_link( $id, $size, false, false ) : wp_get_attachment_link( $id, $size, true, false );
		$output .= '<li>' . $image;
		
		if ( trim( $attachment->post_excerpt ) ) {
			$output .= '<div class="caption hidden">' . wptexturize( $attachment->post_excerpt ) . '</div>';
		}
		
		$output .= '</li>';
		$i++;
	}
	$output .= '</ul>';
	$output .= '</div>';

	$output .="<script>$(window).load(function() { $('.flexslider').flexslider({ animation: 'slide' }); });</script>";
	return $output;
}

function shoestrap_slider_gallery_setup_after_theme() {
	remove_shortcode( 'gallery' );
	add_shortcode( 'gallery', 'shoestrap_slider_gallery' );
}
add_action( 'after_setup_theme', 'shoestrap_slider_gallery_setup_after_theme' );
