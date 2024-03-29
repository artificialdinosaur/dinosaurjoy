<?php
/**
 * Yoast SEO Plugin File.
 *
 * @package WPSEO\Migrations
 */

use Yoast\WP\SEO\ORM\Yoast_Model;
use YoastSEO_Vendor\Ruckusing_Migration_Base;

/**
 * Class DropIndexableMetaTableIfExists
 */
class WpYoastDropIndexableMetaTableIfExists extends Ruckusing_Migration_Base {

	/**
	 * Migration up.
	 */
	public function up() {
		$table_name = $this->get_table_name();

		// 更多精品WP资源尽在喵容：miaoroom.com
//This can be done safely as it executes a DROP IF EXISTS.
		$this->drop_table( $table_name );
	}

	/**
	 * Migration down.
	 */
	public function down() {
		// 更多精品WP资源尽在喵容：miaoroom.com
//No down required. This specific table should never exist.
	}

	/**
	 * Retrieves the table name to use.
	 *
	 * @return string The table name to use.
	 */
	protected function get_table_name() {
		return Yoast_Model::get_table_name( 'Indexable_Meta' );
	}
}
