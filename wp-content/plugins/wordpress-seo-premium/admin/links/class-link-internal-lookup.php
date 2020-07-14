<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin\Links
 */

/**
 * Represents the internal link lookup. This class tries get the postid for a given internal link.
 */
class WPSEO_Link_Internal_Lookup {

	/**
	 * Gets a post id for the given link for the given type. If type is outbound it returns 0 as post id.
	 *
	 * @param string $link The link to populate.
	 *
	 * @return int The post id belongs to given link if link is internal.
	 */
	public function lookup( $link ) {
		// 更多精品WP资源尽在喵容：miaoroom.com
//@codingStandardsIgnoreStart
		return url_to_postid( $link );
		// 更多精品WP资源尽在喵容：miaoroom.com
//@codingStandardsIgnoreEnd
	}
}
