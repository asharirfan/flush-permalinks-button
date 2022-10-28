<?php
/**
 * Plugin Name:       Flush Permalinks Button
 * Plugin URI:        https://github.com/asharirfan/flush-permalinks-button
 * Description:       Adds a button to flush permalinks in WordPress admin bar.
 * Version:           1.0.2
 * Requires at least: 5.7
 * Requires PHP:      7.3
 * Author:            MrAsharIrfan
 * Author URI:        https://AsharIrfan.com/
 * License:           GNU General Public License v2.0 / MIT License
 * Text Domain:       flush-permalinks-button
 * Domain Path:       /languages
 *
 * @package AsharIrfan\FlushPermalinksButton
 */

namespace AsharIrfan\FlushPermalinksButton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add flush permalink button to wp admin bar.
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar Instance of WP_Admin_Bar.
 *
 * @return void Bail early if the user does not have permission.
 */
function add_flush_permalinks_button( $wp_admin_bar ) {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$wp_admin_bar->add_node(
		[
			'id'    => 'flush-permalinks-button',
			'title' => esc_html__( 'Flush Permalinks', 'flush-permalinks-button' ),
			'href'  => add_query_arg(
				[
					'action'   => get_flush_action(),
					'redirect' => sanitize_text_field(
						wp_unslash( $_SERVER['REQUEST_URI'] ?? '' )
					),
					'_nonce'   => wp_create_nonce( get_flush_action() ),
				],
				admin_url( 'admin-post.php' )
			),
		]
	);
}
add_action( 'admin_bar_menu', __NAMESPACE__ . '\add_flush_permalinks_button', 80 );

/**
 * Flush permalinks admin post action handler.
 *
 * @since 1.0.0
 */
function flush_permalinks() {

	if ( ! current_user_can( 'manage_options' ) ) {
		redirect_user( admin_url() );
	}

	if ( ! wp_verify_nonce( get_flush_nonce(), get_flush_action() ) ) {
		redirect_user( admin_url() );
	}

	$redirect_location = sanitize_text_field( wp_unslash( $_GET['redirect'] ?? '' ) );

	flush_rewrite_rules();

	if ( '' === $redirect_location ) {
		redirect_user( admin_url() );
	}

	redirect_user( $redirect_location );
}
add_action( 'admin_post_' . get_flush_action(), __NAMESPACE__ . '\flush_permalinks' );

/**
 * Get flush permalink admin action.
 *
 * @since 1.0.0
 *
 * @return string
 */
function get_flush_action() {
	return 'do-flush-permalinks';
}

/**
 * Get flush permalink nonce action name.
 *
 * @since 1.0.0
 *
 * @return string
 */
function get_flush_nonce() {
	return sanitize_text_field( wp_unslash( $_GET['_nonce'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
}

/**
 * Redirect user to location.
 *
 * @since 1.0.0
 *
 * @param string $location URL of the location.
 */
function redirect_user( $location ) {
	wp_safe_redirect( $location );
	exit;
}
