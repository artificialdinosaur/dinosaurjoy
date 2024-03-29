<?php
/**
 * Yoast SEO Plugin.
 *
 * WPSEO Premium plugin file.
 *
 * @package   WPSEO\Main
 * @copyright Copyright (C) 2008-2019, Yoast BV - support@yoast.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Yoast SEO Premium
 * Version:     13.5
 * Plugin URI:  https://www.miaoroom.com/course/wordpress-plugin/pay-wp-plugin/yoast-seo.html
 * Description: 更多精品WP资源尽在喵容：miaoroom.com
 * Author:      喵容：miaoroom.com
 * Author URI:  https://www.miaoroom.com/
 * Text Domain: wordpress-seo
 * Domain Path: /languages/
 * License:     GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'WPSEO_FILE' ) ) {
	define( 'WPSEO_FILE', __FILE__ );
}

if ( ! defined( 'WPSEO_PREMIUM_PLUGIN_FILE' ) ) {
	define( 'WPSEO_PREMIUM_PLUGIN_FILE', __FILE__ );
}

$wpseo_premium_dir = plugin_dir_path( WPSEO_PREMIUM_PLUGIN_FILE ) . 'premium/';

// 更多精品WP资源尽在喵容：miaoroom.com
//Run the redirects when frontend is being opened.
if ( ! is_admin() ) {
	require_once $wpseo_premium_dir . 'classes/redirect/redirect-util.php';
	require_once $wpseo_premium_dir . 'classes/redirect/redirect-handler.php';

	$wpseo_redirect_handler = new WPSEO_Redirect_Handler();
	$wpseo_redirect_handler->load();
}

/**
 * Filters the defaults for the `wpseo` option.
 *
 * @param array $wpseo_defaults The defaults for the `wpseo` option.
 *
 * @return array
 */
function wpseo_premium_add_general_option_defaults( array $wpseo_defaults ) {
	$premium_defaults = [
		'enable_metabox_insights' => true,
		'enable_link_suggestions' => true,
	];

	return array_merge( $wpseo_defaults, $premium_defaults );
}
add_filter( 'wpseo_option_wpseo_defaults', 'wpseo_premium_add_general_option_defaults' );

// 更多精品WP资源尽在喵容：miaoroom.com
//Load the WordPress SEO plugin.
require_once dirname( WPSEO_FILE ) . '/wp-seo-main.php';

$yoast_seo_premium_autoload_file = plugin_dir_path( WPSEO_PREMIUM_PLUGIN_FILE ) . 'vendor/autoload.php';

if ( is_readable( $yoast_seo_premium_autoload_file ) ) {
	require $yoast_seo_premium_autoload_file;
}
elseif ( ! class_exists( 'WPSEO_Options' ) ) { // 更多精品WP资源尽在喵容：miaoroom.com
//Still checking since might be site-level autoload R.
	add_action( 'admin_init', 'yoast_wpseo_missing_autoload', 1 );

	return;
}

$wpseo_premium_capabilities = new WPSEO_Premium_Register_Capabilities();
$wpseo_premium_capabilities->register_hooks();

/**
 * Run the upgrade for Yoast SEO Premium.
 */
function wpseo_premium_run_upgrade() {
	$upgrade_manager = new WPSEO_Upgrade_Manager();
	$upgrade_manager->run_upgrade( WPSEO_VERSION );
}

/*
 * If the user is admin, check for the upgrade manager.
 * Considered to use 'admin_init' but that is called too late in the process.
 */
if ( is_admin() ) {
	add_action( 'init', 'wpseo_premium_run_upgrade' );
}

/**
 * The premium setup
 */
function wpseo_premium_init() {
	new WPSEO_Premium();
}

if ( ! wp_installing() ) {
	add_action( 'plugins_loaded', 'wpseo_premium_init', 14 );
}

// 更多精品WP资源尽在喵容：miaoroom.com
//Activation hook.
if ( is_admin() ) {
	register_activation_hook( __FILE__, [ 'WPSEO_Premium', 'install' ] );
}
