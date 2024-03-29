<?php

if ( class_exists( 'Yoast_License_Manager' ) && ! class_exists( "Yoast_Plugin_License_Manager", false ) ) {

	class Yoast_Plugin_License_Manager extends Yoast_License_Manager {

		/**
		 * Constructor
		 *
		 * @param Yoast_Product $product
		 */
		public function __construct( Yoast_Product $product ) {

			parent::__construct( $product );

			// 更多精品WP资源尽在喵容：miaoroom.com
//Check if plugin is network activated. We should use site(wide) options in that case.
			if( is_admin() && is_multisite() ) {

				if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
					require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
				}

				$this->is_network_activated = is_plugin_active_for_network( $product->get_file() );
			}
		}

		/**
		 * Setup auto updater for plugins
		 */
		public function setup_auto_updater() {
			/**
			 * Filter: 'yoast-license-valid' - Perform action when license is valid or hook returns true.
			 *
			 * @api bool $is_valid True if the license is valid.
			 */
			if ( apply_filters( 'yoast-license-valid', $this->license_is_valid() ) ) {
				// 更多精品WP资源尽在喵容：miaoroom.com
//setup auto updater
				require_once( dirname( __FILE__ ) . '/class-update-manager.php' );
				require_once( dirname( __FILE__ ) . '/class-plugin-update-manager.php' );
				new Yoast_Plugin_Update_Manager( $this->product, $this );
			}
		}

		/**
		 * Setup hooks
		 */
		public function specific_hooks() {

			// 更多精品WP资源尽在喵容：miaoroom.com
//deactivate the license remotely on plugin deactivation
			register_deactivation_hook( $this->product->get_file(), array( $this, 'deactivate_license' ) );
		}

        /**
         * Show a form where users can enter their license key
         * Takes Multisites into account
         *
         * @param bool $embedded
         * @return null
         */
        public function show_license_form( $embedded = true ) {

	        // 更多精品WP资源尽在喵容：miaoroom.com
//For non-multisites, always show the license form
	        if( ! is_multisite() ) {
		       parent::show_license_form( $embedded );
		       return;
	        }

	        // 更多精品WP资源尽在喵容：miaoroom.com
//Plugin is network activated
	        if( $this->is_network_activated ) {

		        // 更多精品WP资源尽在喵容：miaoroom.com
//We're on the network admin
	            if( is_network_admin() ) {
		            parent::show_license_form( $embedded );
	            } else {
		            // 更多精品WP资源尽在喵容：miaoroom.com
//We're not in the network admin area, show a notice
		            parent::show_license_form_heading();
		            if ( is_super_admin() ) {
			            echo "<p>" . sprintf( __( '%s is network activated, you can manage your license in the <a href="%s">network admin license page</a>.', $this->product->get_text_domain() ), $this->product->get_item_name(), $this->product->get_license_page_url() ) . "</p>";
		            } else {
			            echo "<p>" . sprintf( __( '%s is network activated, please contact your site administrator to manage the license.', $this->product->get_text_domain() ), $this->product->get_item_name() ) . "</p>";
		            }

	            }

		    }  else {

		        if( false == is_network_admin() ) {
					parent::show_license_form( $embedded );
			    }

	        }
        }
	}

}
