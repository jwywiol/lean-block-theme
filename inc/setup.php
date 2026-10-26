<?php
/**
 * Theme setup: supports, block registration and global assets.
 *
 * @package Lean_Block_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute path inside the compiled assets directory.
 *
 * @param string $relative Path relative to build/.
 */
function lean_build_path( string $relative = '' ): string {
	return get_theme_file_path( 'build/' . ltrim( $relative, '/' ) );
}

/**
 * Theme supports.
 */
function lean_setup(): void {
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );

	// The same global stylesheet the front end uses. WordPress scopes it to the
	// editor canvas, so it never leaks into the admin UI.
	add_editor_style( 'build/css/global.css' );

	// Keep the inserter focused on this theme's patterns, and stop the editor
	// from requesting patterns from the wordpress.org directory on every load.
	remove_theme_support( 'core-block-patterns' );
	add_filter( 'should_load_remote_block_patterns', '__return_false' );
}
add_action( 'after_setup_theme', 'lean_setup' );

/**
 * Registers every block from build/blocks in one pass.
 *
 * The build step writes a PHP manifest with all block.json files, so WordPress
 * reads one cached PHP array instead of locating and decoding a JSON file per
 * block on every request (WordPress 6.8+).
 */
function lean_register_blocks(): void {
	$blocks_dir = lean_build_path( 'blocks' );
	$manifest   = lean_build_path( 'blocks-manifest.php' );

	if ( ! is_readable( $manifest ) ) {
		return;
	}

	wp_register_block_types_from_metadata_collection( $blocks_dir, $manifest );
}
add_action( 'init', 'lean_register_blocks' );

/**
 * Adds a "Lean blocks" category to the inserter.
 *
 * @param array<int, array<string, mixed>> $categories Block categories.
 * @return array<int, array<string, mixed>>
 */
function lean_block_category( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'lean',
			'title' => __( 'Lean blocks', 'lean-block-theme' ),
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'lean_block_category' );

/**
 * Pattern category for the bundled patterns in patterns/.
 */
function lean_register_pattern_category(): void {
	register_block_pattern_category(
		'lean',
		array( 'label' => __( 'Lean Block Theme', 'lean-block-theme' ) )
	);
}
add_action( 'init', 'lean_register_pattern_category' );

/**
 * Front-end global stylesheet: small base layer and shared components.
 *
 * Block styles are not part of this file. Each block ships its own style.css,
 * declared in block.json, and WordPress prints it only on pages that contain
 * that block.
 */
function lean_enqueue_global_assets(): void {
	$file = lean_build_path( 'css/global.css' );

	if ( ! is_readable( $file ) ) {
		return;
	}

	wp_enqueue_style(
		'lean-global',
		get_theme_file_uri( 'build/css/global.css' ),
		array(),
		(string) filemtime( $file )
	);

	// With a file path attached, WordPress may print the stylesheet inline in
	// <head> (wp_maybe_inline_styles(), within its size budget), as it does for
	// block styles. A few kB inline is faster than an extra render-blocking request.
	wp_style_add_data( 'lean-global', 'path', $file );
}
add_action( 'wp_enqueue_scripts', 'lean_enqueue_global_assets' );

/**
 * Warns administrators when the theme is active but has not been built yet.
 */
function lean_missing_build_notice(): void {
	if ( is_readable( lean_build_path( 'blocks-manifest.php' ) ) || ! current_user_can( 'switch_themes' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Lean Block Theme: compiled assets are missing. Run "npm install && npm run build" in the theme folder, or install a release ZIP.', 'lean-block-theme' )
	);
}
add_action( 'admin_notices', 'lean_missing_build_notice' );
