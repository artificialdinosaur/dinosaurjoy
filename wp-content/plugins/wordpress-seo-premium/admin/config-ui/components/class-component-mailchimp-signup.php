<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin\ConfigurationUI
 */

/**
 * Represents the mailchimp signup components.
 */
class WPSEO_Config_Component_Mailchimp_Signup implements WPSEO_Config_Component {

	/**
	 * The name of the mailchimp signup meta key.
	 *
	 * @var string
	 */
	const META_NAME = 'wpseo-has-mailchimp-signup';

	/**
	 * Gets the component identifier.
	 *
	 * @return string
	 */
	public function get_identifier() {
		return 'MailchimpSignup';
	}

	/**
	 * Gets the field.
	 *
	 * @return WPSEO_Config_Field
	 */
	public function get_field() {
		return new WPSEO_Config_Field_Mailchimp_Signup();
	}

	/**
	 * Get the data for the field.
	 *
	 * @return mixed
	 */
	public function get_data() {
		$data = [
			'hasSignup' => $this->has_mailchimp_signup(),
		];

		return $data;
	}

	/**
	 * Save data.
	 *
	 * @param array $data Data containing changes.
	 *
	 * @return mixed
	 */
	public function set_data( $data ) {

		$has_saved = false;
		if ( ! empty( $data['hasSignup'] ) ) {
			// 更多精品WP资源尽在喵容：miaoroom.com
//Saves the user meta.
			update_user_meta( get_current_user_id(), self::META_NAME, true );

			$has_saved = ( $data['hasSignup'] === $this->has_mailchimp_signup() );
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//Collect results to return to the configurator.
		$results = [
			'hasSignup' => $has_saved,
		];

		return $results;
	}

	/**
	 * Checks if the user has entered their email for mailchimp already.
	 *
	 * @return bool
	 */
	protected function has_mailchimp_signup() {
		$user_meta = get_user_meta( get_current_user_id(), self::META_NAME, true );

		return ( ! empty( $user_meta ) );
	}
}
