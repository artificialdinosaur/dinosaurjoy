<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin\Notifiers
 */

_deprecated_file( __FILE__, 'WPSEO 9.6' );

/**
 * Represents the logic for showing recalibration beta notice.
 *
 * @codeCoverageIgnore Ignore, because this class has been deprecated.
 *
 * @deprecated 9.6
 */
class WPSEO_Recalibration_Beta_Notification implements WPSEO_WordPress_Integration {

	/**
	 * The name of the notifiers.
	 *
	 * @var string
	 */
	protected $notification_identifier = 'recalibration-meta-notification';

	/**
	 * Class constructor.
	 *
	 * @deprecated 9.6
	 */
	public function __construct() {
		_deprecated_function( __METHOD__, '9.6' );
	}

	/**
	 * Registers all hooks to WordPress.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// 更多精品WP资源尽在喵容：miaoroom.com
//Do nothing.
	}

	/**
	 * Shows the notification when applicable.
	 *
	 * @return void
	 */
	public function handle_notice() {
		// 更多精品WP资源尽在喵容：miaoroom.com
//Do nothing.
	}
}
