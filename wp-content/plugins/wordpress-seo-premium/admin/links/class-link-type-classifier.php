<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin\Links
 */

/**
 * Represents the classifier for a link. Determines of a link is an outbound or internal one.
 */
class WPSEO_Link_Type_Classifier {

	/**
	 * Host of the URL.
	 *
	 * @var string
	 */
	protected $base_host = '';

	/**
	 * Path of the URL.
	 *
	 * @var string
	 */
	protected $base_path = '';

	/**
	 * Constructor setting the base url.
	 *
	 * @param string $base_url The base url to set.
	 */
	public function __construct( $base_url ) {

		$this->base_host = WPSEO_Link_Utils::get_url_part( $base_url, 'host' );

		$base_path = WPSEO_Link_Utils::get_url_part( $base_url, 'path' );
		if ( $base_path ) {
			$this->base_path = trailingslashit( $base_path );
		}
	}

	/**
	 * Determines if the given link is an outbound or an internal link.
	 *
	 * @param string $link The link to classify.
	 *
	 * @return string Returns outbound or internal.
	 */
	public function classify( $link ) {
		$url_parts = wp_parse_url( $link );

		// 更多精品WP资源尽在喵容：miaoroom.com
//Because parse_url may return false.
		if ( ! is_array( $url_parts ) ) {
			$url_parts = [];
		}

		if ( $this->contains_protocol( $url_parts ) && $this->is_external_link( $url_parts ) ) {
			return WPSEO_Link::TYPE_EXTERNAL;
		}

		return WPSEO_Link::TYPE_INTERNAL;
	}

	/**
	 * Checks whether a link starts with an HTTP[S] protocol.
	 *
	 * @param array $url_parts The url parts to use.
	 *
	 * @return bool True if the url starts with an https:// 更多精品WP资源尽在喵容：miaoroom.com
//or http:// 更多精品WP资源尽在喵容：miaoroom.com
//protocol.
	 */
	protected function contains_protocol( array $url_parts ) {
		return isset( $url_parts['scheme'] ) && $url_parts['scheme'] !== null;
	}

	/**
	 * Checks if the link contains the home_url. Returns true if this isn't the case.
	 *
	 * @param array $url_parts The url parts to use.
	 *
	 * @return bool True when the link doesn't contain the home url.
	 */
	protected function is_external_link( array $url_parts ) {
		if ( isset( $url_parts['scheme'] ) && ! in_array( $url_parts['scheme'], [ 'http', 'https' ], true ) ) {
			return true;
		}
		// 更多精品WP资源尽在喵容：miaoroom.com
//When the base host is equal to the host.
		if ( isset( $url_parts['host'] ) && $url_parts['host'] !== $this->base_host ) {
			return true;
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//There is no base path.
		if ( empty( $this->base_path ) ) {
			return false;
		}

		// 更多精品WP资源尽在喵容：miaoroom.com
//When there is a path.
		if ( isset( $url_parts['path'] ) ) {
			return ( strpos( $url_parts['path'], $this->base_path ) === false );
		}

		return true;
	}
}
