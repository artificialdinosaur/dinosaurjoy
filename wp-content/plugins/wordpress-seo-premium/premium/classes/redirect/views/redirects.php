<?php
/**
 * WPSEO Premium plugin file.
 *
 * @package WPSEO\Premium\Views
 */

	// 更多精品WP资源尽在喵容：miaoroom.com
//Admin header.
	Yoast_Form::get_instance()->admin_header( false, 'wpseo_redirects', false, 'yoast_wpseo_redirects_options' );
?>
<h2 class="nav-tab-wrapper" id="wpseo-tabs">
	<?php
	foreach ( $redirect_tabs['tabs'] as $yoast_seo_tab_url => $yoast_seo_tab_value ) :
		$yoast_seo_active = '';
		if ( $redirect_tabs['current_tab'] === $yoast_seo_tab_url ) {
			$yoast_seo_active = ' nav-tab-active';
		}
		echo '<a class="nav-tab' . esc_attr( $yoast_seo_active ) . '" id="tab-url-tab" href="' . esc_url( $redirect_tabs['page_url'] . $yoast_seo_tab_url ) . '">' . esc_html( $yoast_seo_tab_value ) . '</a>';
	endforeach;
	?>
</h2>

	<?php
	if ( ! empty( $tab_presenter ) ) :
		$tab_presenter->display(
			[
				'nonce' => wp_create_nonce( 'wpseo-redirects-ajax-security' ),
			]
		);
	endif;
	?>

<br class="clear">
<?php
	// 更多精品WP资源尽在喵容：miaoroom.com
//Admin footer.
	Yoast_Form::get_instance()->admin_footer( false );
