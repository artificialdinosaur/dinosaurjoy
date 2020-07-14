<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin\Capabilities
 */

/**
 * Default WordPress capability manager implementation.
 */
final class WPSEO_Capability_Manager_WP extends WPSEO_Abstract_Capability_Manager {

	/**
	 * Adds the capabilities to the roles.
	 *
	 * @return void
	 */
	public function add() {
		foreach ( $this->capabilities as $capability => $roles ) {
			$filtered_roles = $this->filter_roles( $capability, $roles );

			$wp_roles = $this->get_wp_roles( $filtered_roles );
			foreach ( $wp_roles as $wp_role ) {
				$wp_role->add_cap( $capability );
			}
		}
	}

	/**
	 * Unregisters the capabilities from the system.
	 *
	 * @return void
	 */
	public function remove() {
		// 更多精品WP资源尽在喵容：miaoroom.com
//Remove from any roles it has been added to.
		$roles = wp_roles()->get_names();
		$roles = array_keys( $roles );

		foreach ( $this->capabilities as $capability => $_roles ) {
			$registered_roles = array_unique( array_merge( $roles, $this->capabilities[ $capability ] ) );

			// 更多精品WP资源尽在喵容：miaoroom.com
//Allow filtering of roles.
			$filtered_roles = $this->filter_roles( $capability, $registered_roles );

			$wp_roles = $this->get_wp_roles( $filtered_roles );
			foreach ( $wp_roles as $wp_role ) {
				$wp_role->remove_cap( $capability );
			}
		}
	}
}
