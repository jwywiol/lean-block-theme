<?php
/**
 * Builds the demo site used for the README screenshots and Lighthouse runs.
 *
 * Run from the WordPress root:
 *
 *     wp theme activate lean-block-theme
 *     wp eval-file wp-content/themes/lean-block-theme/demo/setup.php
 *
 * Safe to run again: it reuses the images and the page it created before.
 * Intended for a fresh local install, not for a site with real content.
 *
 * @package Lean_Block_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

/**
 * Imports a file from demo/images once and returns its attachment ID.
 */
$lean_import = static function ( string $file, string $alt ): int {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_lean_demo_file', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $file, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'      => 'ids',
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$tmp = wp_tempnam( $file );
	copy( __DIR__ . '/images/' . $file, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0
	);

	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}

	update_post_meta( $id, '_lean_demo_file', $file );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );

	return (int) $id;
};

$lean_hero  = $lean_import( 'hero.webp', 'Abstract illustration of a dashboard with a bar chart' );
$lean_logos = array();
foreach ( array( 'Aurora Labs', 'Bluepine', 'Fieldwork', 'Monolith', 'Orbitra', 'Kestrel & Co' ) as $lean_name ) {
	$lean_slug                = sanitize_title( str_replace( '&', '', $lean_name ) );
	$lean_logos[ $lean_name ] = $lean_import( 'logo-' . $lean_slug . '.png', $lean_name );
}

// Landing page: the bundled pattern, plus the demo images.
$lean_pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'lean-block-theme/landing' );
if ( ! $lean_pattern ) {
	WP_CLI::error( 'Pattern lean-block-theme/landing is not registered. Is the theme active?' );
}

$lean_blocks = parse_blocks( $lean_pattern['content'] );
foreach ( $lean_blocks as &$lean_block ) {
	if ( 'lean/hero' === $lean_block['blockName'] ) {
		$lean_block['attrs']['imageId'] = $lean_hero;
	}
	if ( 'lean/logos' === $lean_block['blockName'] ) {
		foreach ( $lean_block['attrs']['items'] as &$lean_item ) {
			$lean_item['imageId'] = $lean_logos[ $lean_item['name'] ] ?? 0;
		}
		unset( $lean_item );
	}
}
unset( $lean_block );

$lean_page_id = (int) get_option( 'page_on_front' );
$lean_page    = array(
	'post_type'     => 'page',
	'post_status'   => 'publish',
	'post_title'    => 'Home',
	'post_content'  => serialize_blocks( $lean_blocks ),
	'page_template' => 'page-landing',
);

if ( $lean_page_id && get_post( $lean_page_id ) ) {
	$lean_page['ID'] = $lean_page_id;
	wp_update_post( wp_slash( $lean_page ) );
} else {
	$lean_page_id = (int) wp_insert_post( wp_slash( $lean_page ) );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $lean_page_id );
update_option( 'blogname', 'Northwind Studio' );
update_option( 'blogdescription', 'Fast WordPress websites without page builders' );

// Header navigation: anchors to the landing sections.
$lean_links = '';
foreach ( array(
	'Results'      => '#results',
	'Testimonials' => '#testimonials',
	'FAQ'          => '#faq',
	'Contact'      => '#contact',
) as $lean_label => $lean_url ) {
	$lean_links .= get_comment_delimited_block_content(
		'core/navigation-link',
		array(
			'label' => $lean_label,
			'url'   => home_url( '/' . $lean_url ),
			'kind'  => 'custom',
		),
		''
	);
}

$lean_nav      = get_posts(
	array(
		'post_type'   => 'wp_navigation',
		'name'        => 'lean-demo-menu',
		'post_status' => 'publish',
		'fields'      => 'ids',
	)
);
$lean_nav_post = array(
	'post_type'    => 'wp_navigation',
	'post_status'  => 'publish',
	'post_title'   => 'Main menu',
	'post_name'    => 'lean-demo-menu',
	'post_content' => $lean_links,
);
if ( $lean_nav ) {
	$lean_nav_post['ID'] = $lean_nav[0];
}
wp_insert_post( wp_slash( $lean_nav_post ) );

// Remove WordPress' sample content.
foreach ( array(
	'hello-world' => 'post',
	'sample-page' => 'page',
) as $lean_slug => $lean_type ) {
	$lean_sample = get_page_by_path( $lean_slug, OBJECT, $lean_type );
	if ( $lean_sample ) {
		wp_delete_post( $lean_sample->ID, true );
	}
}

WP_CLI::success( sprintf( 'Demo ready: %s', home_url( '/' ) ) );
