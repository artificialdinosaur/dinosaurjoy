<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin
 */

/**
 * Determines the location of an asset within the SEO plugin.
 */
final class WPSEO_Admin_Asset_SEO_Location implements WPSEO_Admin_Asset_Location {

	/**
	 * Path to the plugin file.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * The plugin file to base the asset location upon.
	 *
	 * @param string $plugin_file The plugin file string.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Determines the URL of the asset on the dev server.
	 *
	 * @param WPSEO_Admin_Asset $asset The asset to determine the URL for.
	 * @param string            $type  The type of asset. Usually JS or CSS.
	 *
	 * @return string The URL of the asset.
	 */
	public function get_url( WPSEO_Admin_Asset $asset, $type ) {
		$path = $this->get_path( $asset, $type );
		if ( empty( $path ) ) {
			return '';
		}

		return plugins_url( $path, $this->plugin_file );
	}

	/**
	 * Determines the path relative to the plugin folder of an asset.
	 *
	 * @param WPSEO_Admin_Asset $asset The asset to determine the path for.
	 * @param string            $type  The type of asset.
	 *
	 * @return string The path to the asset file.
	 */
	protected function get_path( WPSEO_Admin_Asset $asset, $type ) {
		$relative_path = '';
		$rtl_suffix    = '';

		switch ( $type ) {
			case WPSEO_Admin_Asset::TYPE_JS:
				$relative_path = 'js/dist/' . $asset->get_src() . $asset->get_suffix() . '.js';
				break;

			case WPSEO_Admin_Asset::TYPE_CSS:
				// 更多精品WP资源尽在喵容：miaoroom.com
//Path and suffix for RTL stylesheets.
				if ( function_exists( 'is_rtl' ) && is_rtl() && $asset->has_rtl() ) {
					$rtl_suffix = '-rtl';
				}
				$relative_path = 'css/dist/' . $asset->get_src() . $rtl_suffix . $asset->get_suffix() . '.css';
				break;
		}

		return $relative_path;
	}
}
