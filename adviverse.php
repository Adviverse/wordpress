<?php
/**
 * Plugin Name: Adviverse Ads
 * Description: Embed Adviverse ad slots with the [adviverse_ad] shortcode or the Adviverse Ad block. Loads the Adviverse SDK only on pages that use a slot.
 * Version: 0.1.0
 * Author: Adviverse
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: adviverse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADVIVERSE_SDK_URL', 'https://serve.adviverse.com/sdk/v1.js' );

/**
 * Enqueue the SDK once (async, footer). Called lazily from the renderer so it
 * only loads on pages that actually contain a slot.
 */
function adviverse_enqueue_sdk() {
	if ( ! wp_script_is( 'adviverse-sdk', 'enqueued' ) ) {
		wp_enqueue_script( 'adviverse-sdk', ADVIVERSE_SDK_URL, array(), null, true );
	}
}

/** Add the async attribute to the SDK tag. */
function adviverse_async_tag( $tag, $handle ) {
	if ( 'adviverse-sdk' === $handle && false === strpos( $tag, ' async' ) ) {
		$tag = str_replace( ' src=', ' async src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'adviverse_async_tag', 10, 2 );

/**
 * Render a slot. The SDK scans for <script data-tag> markers and inserts the ad
 * right after the marker, sizing from data-sizes against the parent element.
 */
function adviverse_render_slot( $atts ) {
	$atts = shortcode_atts(
		array(
			'tag'   => '',
			'sizes' => '',
		),
		(array) $atts,
		'adviverse_ad'
	);
	$tag   = sanitize_text_field( $atts['tag'] );
	$sizes = sanitize_text_field( $atts['sizes'] );
	if ( '' === $tag || ! preg_match( '/^[A-Za-z0-9_\-]+$/', $tag ) ) {
		return '';
	}
	if ( '' !== $sizes && ! preg_match( '/^(fluid|\d+x\d+)$/i', $sizes ) ) {
		$sizes = '';
	}
	adviverse_enqueue_sdk();

	$marker = '<script data-tag="' . esc_attr( $tag ) . '"';
	if ( '' !== $sizes ) {
		$marker .= ' data-sizes="' . esc_attr( $sizes ) . '"';
	}
	$marker .= '></script>';

	// The wrapper is the sized box the SDK measures. The marker is a fixed,
	// fully-escaped literal (not user HTML).
	$style = 'max-width:100%';
	if ( preg_match( '/^(\d+)x(\d+)$/', $sizes, $m ) ) {
		$style = 'width:' . (int) $m[1] . 'px;min-height:' . (int) $m[2] . 'px;max-width:100%';
	}
	return '<div class="adviverse-ad" style="' . esc_attr( $style ) . '">' . $marker . '</div>';
}
add_shortcode( 'adviverse_ad', 'adviverse_render_slot' );

/** Server-rendered block: adviverse/ad, reusing the shortcode output. */
function adviverse_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	register_block_type(
		'adviverse/ad',
		array(
			'api_version'     => 2,
			'title'           => 'Adviverse Ad',
			'category'        => 'widgets',
			'attributes'      => array(
				'tag'   => array( 'type' => 'string', 'default' => '' ),
				'sizes' => array( 'type' => 'string', 'default' => '' ),
			),
			'render_callback' => 'adviverse_render_slot',
		)
	);
}
add_action( 'init', 'adviverse_register_block' );
