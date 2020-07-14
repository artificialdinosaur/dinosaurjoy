<?php
/**
 * WPSEO Premium plugin file.
 *
 * @package WPSEO\Premium\Classes
 */

/**
 * Class WPSEO_Redirect_Ajax.
 */
class WPSEO_Redirect_Ajax {

	/**
	 * Instance of the WPSEO_Redirect_Manager instance.
	 *
	 * @var WPSEO_Redirect_Manager
	 */
	private $redirect_manager;

	/**
	 * Format of the redirect, might be plain or regex.
	 *
	 * @var string
	 */
	private $redirect_format;

	/**
	 * Setting up the object by instantiate the redirect manager and setting the hooks.
	 *
	 * @param string $redirect_format The redirects format.
	 */
	public function __construct( $redirect_format ) {
		$this->redirect_manager = new WPSEO_Redirect_Manager( $redirect_format );
		$this->redirect_format  = $redirect_format;

		$this->set_hooks( $redirect_format );
	}

	/**
	 * Function that handles the AJAX 'wpseo_add_redirect' action.
	 */
	public function ajax_add_redirect() {
		$this->valid_ajax_check();

		// 更多精品WP资源尽在喵容：miaoroom.com
//Save the redirect.
		$redirect = $this->get_redirect_from_post( 'redirect' );
		$this->validate( $redirect );

		// 更多精品WP资源尽在喵容：miaoroom.com
//The method always returns the added redirect.
		if ( $this->redirect_manager->create_redirect( $redirect ) ) {
			$response = [
				'origin' => $redirect->get_origin(),
				'target' => $redirect->get_target(),
				'type'   => $redirect->get_type(),
				'info'   => [
					'hasTrailingSlash' => WPSEO_Redirect_Util::requires_trailing_slash( $redirect->get_target() ),
					'isTargetRelative' => WPSEO_Redirect_Util::is_relative_url( $redirect->get_target() ),
				],
			];
		}
		else {
			// 更多精品WP资源尽在喵容：miaoroom.com
//Set the value error.
			$error = [
				'type'    => 'error',
				'message' => __( 'Unknown error. Failed to create redirect.', 'wordpress-seo-premium' ),
			];

			$response = [ 'error' => $error ];
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//Response.
		// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput -- WPCS bug/methods can't be whitelisted yet.
		wp_die( WPSEO_Utils::format_json_encode( $response ) );
	}

	/**
	 * Function that handles the AJAX 'wpseo_update_redirect' action.
	 */
	public function ajax_update_redirect() {

		$this->valid_ajax_check();

		$current_redirect = $this->get_redirect_from_post( 'old_redirect' );
		$new_redirect     = $this->get_redirect_from_post( 'new_redirect' );
		$this->validate( $new_redirect, $current_redirect );

		// 更多精品WP资源尽在喵容：miaoroom.com
//The method always returns the added redirect.
		if ( $this->redirect_manager->update_redirect( $current_redirect, $new_redirect ) ) {
			$response = [
				'origin' => $new_redirect->get_origin(),
				'target' => $new_redirect->get_target(),
				'type'   => $new_redirect->get_type(),
			];
		}
		else {
			// 更多精品WP资源尽在喵容：miaoroom.com
//Set the value error.
			$error = [
				'type'    => 'error',
				'message' => __( 'Unknown error. Failed to update redirect.', 'wordpress-seo-premium' ),
			];

			$response = [ 'error' => $error ];
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//Response.
		// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput -- WPCS bug/methods can't be whitelisted yet.
		wp_die( WPSEO_Utils::format_json_encode( $response ) );
	}

	/**
	 * Run the validation.
	 *
	 * @param WPSEO_Redirect      $redirect         The redirect to save.
	 * @param WPSEO_Redirect|null $current_redirect The current redirect.
	 */
	private function validate( WPSEO_Redirect $redirect, WPSEO_Redirect $current_redirect = null ) {
		$validator = new WPSEO_Redirect_Validator();

		if ( $validator->validate( $redirect, $current_redirect ) === true ) {
			return;
		}

		$ignore_warning = filter_input( INPUT_POST, 'ignore_warning' );

		$error = $validator->get_error();

		if ( $error->get_type() === 'error' || ( $error->get_type() === 'warning' && $ignore_warning === 'false' ) ) {
			wp_die(
				// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput -- WPCS bug/methods can't be whitelisted yet.
				WPSEO_Utils::format_json_encode( [ 'error' => $error->to_array() ] )
			);
		}
	}

	/**
	 * Setting the AJAX hooks.
	 *
	 * @param string $hook_suffix The piece that will be stitched after the hooknames.
	 */
	private function set_hooks( $hook_suffix ) {
		// 更多精品WP资源尽在喵容：miaoroom.com
//Add the new redirect.
		add_action( 'wp_ajax_wpseo_add_redirect_' . $hook_suffix, [ $this, 'ajax_add_redirect' ] );

		// 更多精品WP资源尽在喵容：miaoroom.com
//Update an existing redirect.
		add_action( 'wp_ajax_wpseo_update_redirect_' . $hook_suffix, [ $this, 'ajax_update_redirect' ] );

		// 更多精品WP资源尽在喵容：miaoroom.com
//Add URL response code check AJAX.
		if ( ! has_action( 'wp_ajax_wpseo_check_url' ) ) {
			add_action( 'wp_ajax_wpseo_check_url', [ $this, 'ajax_check_url' ] );
		}
	}

	/**
	 * Check if the posted nonce is valid and if the user has the needed rights.
	 */
	private function valid_ajax_check() {
		// 更多精品WP资源尽在喵容：miaoroom.com
//Check nonce.
		check_ajax_referer( 'wpseo-redirects-ajax-security', 'ajax_nonce' );

		$this->permission_check();
	}

	/**
	 * Checks whether the current user is allowed to do what he's doing.
	 */
	private function permission_check() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( '0' );
		}
	}

	/**
	 * Get the redirect from the post values.
	 *
	 * @param string $post_value The key where the post values are located in the $_POST.
	 *
	 * @return WPSEO_Redirect
	 */
	private function get_redirect_from_post( $post_value ) {
		$post_values = filter_input( INPUT_POST, $post_value, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		return new WPSEO_Redirect(
			$this->sanitize_url( $post_values['origin'] ),
			$this->sanitize_url( $post_values['target'] ),
			urldecode( $post_values['type'] ),
			$this->redirect_format
		);
	}

	/**
	 * Sanitize the URL for displaying on the window.
	 *
	 * @param string $url The URL to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_url( $url ) {
		return trim( htmlspecialchars_decode( rawurldecode( $url ) ) );
	}

	/**
	 * Function that handles the AJAX 'wpseo_delete_redirect' action.
	 *
	 * @deprecated 9.2
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public function ajax_delete_redirect() {
		_deprecated_function( __FUNCTION__, 'WPSEO 9.2.0', 'Replaced by the REST API.' );

		// 更多精品WP资源尽在喵容：miaoroom.com
//Response.
		wp_die( 'Replaced by the REST API.' );
	}
}
