<?php
/**
 * AIOSEOP (Pro) Schema Builder Extension Class
 *
 * Designed to shadow \AIOSEOP_Schema_Builder, and uses filters for extended operations.
 */

/**
 * AIOSEOP Schema Builder Ext
 *
 * @since 3.4.0
 */
class AIOSEOP_Schema_Builder_Ext {

	/**
	 * AIOSEOP_Schema_Builder_Ext constructor.
	 *
	 * @since 3.4.0
	 */
	public function __construct() {
		add_filter( 'aioseop_register_schema_objects', array( $this, 'register_schema_graphs' ) );
		add_filter( 'aioseop_schema_layout', array( $this, 'schema_layout' ) );

		add_filter( 'aioseop_schema_class_data_AIOSEOP_Graph_WebPage', array( $this, 'pro_attachment_breadcrumb' ) );
	}

	/**
	 * Register Additional Schema Graphs
	 *
	 * @since 3.4.0
	 *
	 * @param array $graphs
	 * @return array
	 */
	public function register_schema_graphs( $graphs ) {

		return $graphs;
	}

	/**
	 * Schema (Shortcode) Layout
	 *
	 * @since 3.4.0
	 *
	 * @param array $layout
	 * @return array
	 */
	public function schema_layout( $layout ) {

		if ( is_singular() || is_single() ) {
			if ( is_attachment() ) {
				$layout = array(
					'@context' => 'https://schema.org',
					'@graph'   => array(
						'[aioseop_schema_Organization]',
						'[aioseop_schema_WebSite]',
					),
				);

				array_push( $layout['@graph'], '[aioseop_schema_WebPage]' );
				array_push( $layout['@graph'], '[aioseop_schema_BreadcrumbList]' );
			}
		}

		return $layout;
	}

	/**
	 * Filter (Webpage) Attachment Breadcrumb.
	 *
	 * @since 3.4.0
	 *
	 * @param array $graph
	 * @return mixed
	 */
	public function pro_attachment_breadcrumb( $graph ) {
		$context    = AIOSEOP_Context::get_instance();
		$current_is = AIOSEOP_Context::get_is();

		if (
				'attachment' === $current_is ||
				'single_attachment' === $current_is
		) {
			$graph['breadcrumb'] = array(
				'@id' => $context->get_url() . '#breadcrumblist',
			);
		}

		return $graph;
	}
}