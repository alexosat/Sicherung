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
		// Animation für den Aktuelles-/Termine-Bereich (Fluss-Zeitstrahl).
		wp_enqueue_script(
			'aws-river-news',
			get_theme_file_uri( 'assets/js/river-news.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
		// Hell-/Dunkelmodus-Umschalter (im <head>, damit die Wahl ohne Aufblitzen greift).
		wp_enqueue_script(
			'aws-theme-toggle',
			get_theme_file_uri( 'assets/js/theme-toggle.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			false
		);
		// Kopf-Bogen (Masthead): Icon-Buttons auf dem Bogen platzieren + einblenden.
		wp_enqueue_script(
			'aws-masthead-arc',
			get_theme_file_uri( 'assets/js/masthead-arc.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
		// Sprach-Hinweis (Browser-Übersetzung): Popover öffnen/schließen.
		wp_enqueue_script(
			'aws-lang-hint',
			get_theme_file_uri( 'assets/js/lang-hint.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
		// Karte (OpenStreetMap) mit Klick-zum-Laden (Datenschutz).
		wp_enqueue_script(
			'aws-map-consent',
			get_theme_file_uri( 'assets/js/map-consent.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'aws_enqueue' );

/**
 * Favicon (Browser-Logo) und Link-Vorschau (Open Graph / Twitter Card) im <head>.
 * WhatsApp, Facebook, Signal & Co. lesen die og:*-Angaben für die Vorschau.
 */
if ( ! function_exists( 'aws_head_meta' ) ) {
	function aws_head_meta() {
		$img = trailingslashit( get_theme_file_uri( 'assets/img' ) );

		// Browser-Logo / Favicon – nur, wenn kein WordPress-Website-Icon gesetzt ist.
		if ( ! ( function_exists( 'has_site_icon' ) && has_site_icon() ) ) {
			echo '<link rel="icon" href="' . esc_url( $img . 'icon-32.png' ) . '" sizes="32x32" type="image/png">' . "\n";
			echo '<link rel="icon" href="' . esc_url( $img . 'icon-192.png' ) . '" sizes="192x192" type="image/png">' . "\n";
			echo '<link rel="apple-touch-icon" href="' . esc_url( $img . 'apple-touch-icon.png' ) . '">' . "\n";
		}

		// Open Graph nur ausgeben, wenn kein SEO-Plugin die Tags bereits liefert (keine Doppelung).
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || class_exists( '\\The_SEO_Framework\\Load' ) ) {
			return;
		}

		$name = get_bloginfo( 'name' );
		if ( is_singular() ) {
			$title = wp_strip_all_tags( get_the_title() );
			$url   = get_permalink();
			$desc  = has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : get_bloginfo( 'description' );
		} else {
			$title = $name;
			$url   = home_url( '/' );
			$desc  = get_bloginfo( 'description' );
		}
		if ( empty( $desc ) ) {
			$desc = 'Oberschule für die Klassen 5 bis 10 in Dörverden – Ganztag und alle Abschlüsse der Sekundarstufe I.';
		}
		$og_image = $img . 'og-image.jpg';

		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		echo '<meta property="og:type" content="website">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $name ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		echo '<meta property="og:locale" content="de_DE">' . "\n";
		echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
		echo '<meta property="og:image:width" content="1200">' . "\n";
		echo '<meta property="og:image:height" content="630">' . "\n";
		echo '<meta property="og:image:alt" content="' . esc_attr( $name ) . '">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
		echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'aws_head_meta', 5 );

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
