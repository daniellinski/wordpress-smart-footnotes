<?php
/**
 * Plugin Name: Smart Footnotes
 * Description: Adds nice-looking inline footnotes with a native Gutenberg format. The legacy [sfn] shortcode is still supported for existing content.
 * Version: 2.0.3
 * Author: Daniël Dols
 * Author URI: https://www.fuen.nl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMART_FOOTNOTES_VERSION', '2.0.3' );

/**
 * Enqueue front-end JavaScript.
 */
function smart_footnotes_enqueue_assets() {
	wp_enqueue_script(
		'smart-footnotes',
		plugin_dir_url( __FILE__ ) . 'assets/smart-footnotes.js',
		array(),
		SMART_FOOTNOTES_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'smart_footnotes_enqueue_assets' );

/**
 * Enqueue front-end styles.
 *
 * Hooked to `enqueue_block_assets` so the styling also loads inside the
 * block editor iframe, making the footnote markers render exactly like
 * the front end.
 */
function smart_footnotes_enqueue_block_assets() {
	wp_enqueue_style(
		'smart-footnotes',
		plugin_dir_url( __FILE__ ) . 'assets/smart-footnotes.css',
		array(),
		SMART_FOOTNOTES_VERSION
	);
}
add_action( 'enqueue_block_assets', 'smart_footnotes_enqueue_block_assets' );

/**
 * Enqueue block editor assets for the Smart Footnote format.
 */
function smart_footnotes_enqueue_editor_assets() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_script(
		'smart-footnotes-editor',
		$plugin_url . 'assets/smart-footnotes-editor.js',
		array(
			'wp-rich-text',
			'wp-block-editor',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-dom-ready',
		),
		SMART_FOOTNOTES_VERSION,
		true
	);

	wp_enqueue_style(
		'smart-footnotes-editor',
		$plugin_url . 'assets/smart-footnotes-editor.css',
		array(),
		SMART_FOOTNOTES_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'smart_footnotes_enqueue_editor_assets' );

/**
 * Load the editor stylesheet inside the block editor iframe too, so the
 * marker's selected state is styled within the editing canvas.
 */
function smart_footnotes_enqueue_editor_iframe_assets() {
	wp_enqueue_style(
		'smart-footnotes-editor',
		plugin_dir_url( __FILE__ ) . 'assets/smart-footnotes-editor.css',
		array(),
		SMART_FOOTNOTES_VERSION
	);
}
add_action( 'enqueue_block_assets', 'smart_footnotes_enqueue_editor_iframe_assets' );

/**
 * Render a footnote button + popover.
 *
 * @param string $text Footnote text.
 * @param string $url  Optional source URL.
 * @return string
 */
function smart_footnotes_render( $text, $url ) {
	static $number = 0;

	$text = trim( wp_kses_post( $text ) );
	$url  = esc_url_raw( trim( $url ) );

	if ( '' === $text && '' === $url ) {
		return '';
	}

	$number++;

	/*
	 * Render a link-only footnote.
	 */
	if ( '' === $text ) {
		return sprintf(
			'<a class="smart-footnote__button smart-footnote__link" href="%1$s" target="_blank" rel="noopener noreferrer">
				<span aria-hidden="true">%2$d</span>
				<span class="screen-reader-text">%3$s</span>
			</a>',
			esc_url( $url ),
			$number,
			esc_html( sprintf( 'Open source %d', $number ) )
		);
	}

	/*
	 * Render a footnote with text.
	 */
	$id     = sprintf( 'smart-footnote-%d', $number );
	$label  = sprintf( 'Show footnote %d', $number );
	$source = '';

	if ( '' !== $url ) {
		$source = sprintf(
			'<a class="smart-footnote__source" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( $url ),
			esc_html( $url )
		);
	}

	return sprintf(
		'<span class="smart-footnote">
			<button class="smart-footnote__button" type="button" aria-expanded="false" aria-controls="%1$s">
				<span aria-hidden="true">%2$d</span>
				<span class="screen-reader-text">%3$s</span>
			</button>
			<span class="smart-footnote__popover" id="%1$s" role="note">
				<span class="smart-footnote__text">%4$s</span>
				%5$s
			</span>
		</span>',
		esc_attr( $id ),
		$number,
		esc_html( $label ),
		$text, // Sanitized with wp_kses_post().
		$source
	);
}

/**
 * Render inline footnotes inserted with the block editor format.
 *
 * The format serializes to either:
 * <span class="smart-footnote" data-sfn-text="..." data-sfn-url="...">…</span>
 * or (current editor output):
 * <span class="smart-footnote" data-sfn-text="..." data-sfn-url="...">
 *   <span class="smart-footnote__button">N</span>
 * </span>
 *
 * @param string $content Post content.
 * @return string
 */
function smart_footnotes_render_inline( $content ) {
	return preg_replace_callback(
		'/(<span\b[^>]*\bclass="[^"]*\bsmart-footnote\b[^"]*"[^>]*>)(?:<span class="smart-footnote__button">[^<]*<\/span>|.*?)<\/span>/s',
		function ( $matches ) {
			$tag  = $matches[1];
			$text = '';
			$url  = '';

			if ( preg_match( '/data-sfn-text="([^"]*)"/', $tag, $text_match ) ) {
				$text = $text_match[1];
			}

			if ( preg_match( '/data-sfn-url="([^"]*)"/', $tag, $url_match ) ) {
				$url = $url_match[1];
			}

			return smart_footnotes_render( html_entity_decode( $text ), html_entity_decode( $url ) );
		},
		$content
	);
}
add_filter( 'the_content', 'smart_footnotes_render_inline' );

/**
 * Render an inline footnote (legacy shortcode, kept for existing content).
 *
 * @param array|string $attributes Shortcode attributes.
 * @return string
 */
function smart_footnotes_shortcode( $attributes = array() ) {
	$attributes = shortcode_atts(
		array(
			'text' => '',
			'url'  => '',
		),
		(array) $attributes,
		'sfn'
	);

	return smart_footnotes_render( $attributes['text'], $attributes['url'] );
}
add_shortcode( 'sfn', 'smart_footnotes_shortcode' );
