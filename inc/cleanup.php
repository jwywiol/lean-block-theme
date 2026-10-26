<?php
/**
 * Front-end cleanup and light hardening.
 *
 * Each feature can be switched off from a child theme or plugin:
 *
 *     add_filter( 'lean_cleanup_features', function ( $features ) {
 *         $features['security_headers'] = false;
 *         return $features;
 *     } );
 *
 * This file only removes things a typical brochure site does not need. It is
 * not a security plugin, and it leaves hosting-level settings
 * (DISALLOW_FILE_EDIT, HSTS, rate limiting) to wp-config.php and the server.
 *
 * @package Lean_Block_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enabled cleanup features.
 *
 * @return array<string, bool>
 */
function lean_cleanup_features(): array {
	$defaults = array(
		'emoji'            => true,
		'head_links'       => true,
		'security_headers' => true,
		'rest_users'       => true,
		'login_errors'     => true,
	);

	/**
	 * Filters which cleanup features are active.
	 *
	 * @param array<string, bool> $defaults Feature flags.
	 */
	return array_merge( $defaults, (array) apply_filters( 'lean_cleanup_features', $defaults ) );
}

/**
 * Hooks the enabled features. Runs on after_setup_theme, so filters added by
 * plugins and child themes are already registered.
 */
function lean_cleanup_boot(): void {
	$features = lean_cleanup_features();

	if ( $features['emoji'] ) {
		lean_disable_emoji();
	}

	if ( $features['head_links'] ) {
		// Generator tag reveals the exact WordPress version.
		remove_action( 'wp_head', 'wp_generator' );
		// Really Simple Discovery: only used by legacy XML-RPC clients.
		remove_action( 'wp_head', 'rsd_link' );
		// ?p=123 shortlink duplicates the canonical URL.
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}

	if ( $features['security_headers'] ) {
		add_action( 'send_headers', 'lean_send_security_headers' );
	}

	if ( $features['rest_users'] ) {
		add_filter( 'rest_pre_dispatch', 'lean_restrict_rest_users', 10, 3 );
	}

	if ( $features['login_errors'] ) {
		add_filter( 'authenticate', 'lean_generic_login_error', 30 );
	}
}
add_action( 'after_setup_theme', 'lean_cleanup_boot' );

/**
 * Removes the emoji detection script and styles on the front end, in the admin,
 * in feeds and in e-mails. Emoji keep working as plain Unicode characters, and
 * every current browser renders them natively.
 */
function lean_disable_emoji(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
	// Back-compat hooks that wp_enqueue_emoji_styles() normally unhooks itself.
	// Left in place they print the styles anyway and trigger a deprecation notice.
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter(
		'tiny_mce_plugins',
		static fn ( $plugins ) => is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : $plugins
	);
}

/**
 * Baseline response headers for front-end pages.
 *
 * No Content-Security-Policy here on purpose: a correct CSP depends on every
 * third-party script a site loads (analytics, consent banner, embeds), so it
 * belongs in the server config of each project, not in a starter theme.
 */
function lean_send_security_headers(): void {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	/**
	 * Filters the security headers sent with front-end responses.
	 *
	 * @param array<string, string> $headers Header name => value.
	 */
	$headers = (array) apply_filters(
		'lean_security_headers',
		array(
			'X-Content-Type-Options' => 'nosniff',
			'X-Frame-Options'        => 'SAMEORIGIN',
			'Referrer-Policy'        => 'strict-origin-when-cross-origin',
		)
	);

	foreach ( $headers as $name => $value ) {
		header( sprintf( '%s: %s', $name, $value ) );
	}
}

/**
 * Blocks anonymous access to /wp/v2/users, which otherwise lists every author's
 * login slug. Logged-in requests, including application passwords, are not
 * affected.
 *
 * @param mixed           $result  Response to replace the dispatch result with.
 * @param WP_REST_Server  $server  REST server.
 * @param WP_REST_Request $request Current request.
 * @return mixed
 */
function lean_restrict_rest_users( $result, $server, $request ) {
	$route = untrailingslashit( $request->get_route() );

	if ( ( '/wp/v2/users' === $route || str_starts_with( $route, '/wp/v2/users/' ) ) && ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to do that.', 'lean-block-theme' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return $result;
}

/**
 * Replaces "unknown username" and "wrong password" with one generic message,
 * so the login form cannot be used to confirm which accounts exist.
 *
 * Other errors, such as lockouts from a rate-limiting plugin, pass through
 * unchanged.
 *
 * @param WP_User|WP_Error|null $user Authentication result so far.
 * @return WP_User|WP_Error|null
 */
function lean_generic_login_error( $user ) {
	if ( ! is_wp_error( $user ) ) {
		return $user;
	}

	$enumeration_codes = array( 'invalid_username', 'invalid_email', 'incorrect_password' );

	if ( array_intersect( $user->get_error_codes(), $enumeration_codes ) ) {
		return new WP_Error(
			'invalid_login',
			__( '<strong>Error:</strong> The username, e-mail address or password is incorrect.', 'lean-block-theme' )
		);
	}

	return $user;
}
