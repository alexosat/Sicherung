<?php
/**
 * Aller-Weser-Oberschule – Theme-Funktionen
 *
 * @package aws-oberschule
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aws_setup' ) ) {
	function aws_setup() {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		// Stylesheet auch im Editor laden, damit die Vorschau passt.
		add_editor_style( 'style.css' );
	}
}
add_action( 'after_setup_theme', 'aws_setup' );

if ( ! function_exists( 'aws_enqueue' ) ) {
	function aws_enqueue() {
		wp_enqueue_style(
			'aws-oberschule-style',
			get_stylesheet_uri(),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'aws_enqueue' );

/**
 * Eigene Kategorie für die Block-Muster (Patterns) dieser Schule.
 */
if ( ! function_exists( 'aws_register_pattern_category' ) ) {
	function aws_register_pattern_category() {
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				'aws',
				array( 'label' => __( 'Aller-Weser-Oberschule', 'aws-oberschule' ) )
			);
		}
	}
}
add_action( 'init', 'aws_register_pattern_category' );
