<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin
 *
 * @uses    string $type
 * @uses    string $dashicon
 * @uses    string $i18n_title
 * @uses    string $i18n_issues
 * @uses    string $i18n_no_issues
 * @uses    string $i18n_muted_issues_title
 * @uses    int    $active_total
 * @uses    int    $dismissed_total
 * @uses    int    $total
 * @uses    array  $active
 * @uses    array  $dismissed
 */

if ( ! function_exists( '_yoast_display_alerts' ) ) {
	/**
	 * Create the alert HTML with restore/dismiss button.
	 *
	 * @param array  $list   List of alerts.
	 * @param string $status Status of the alerts (active/dismissed).
	 *
	 * @return string The output to render.
	 */
	function _yoast_display_alerts( $list, $status ) {
		$alerts = '';

		foreach ( $list as $notification ) {

			switch ( $status ) {
				case 'active':
					$button = sprintf(
						'<button type="button" class="button dismiss"><span class="screen-reader-text">%1$s</span><span class="dashicons dashicons-hidden"></span></button>',
						esc_html__( 'Hide this item.', 'wordpress-seo' )
					);
					break;

				case 'dismissed':
					$button = sprintf(
						'<button type="button" class="button restore"><span class="screen-reader-text">%1$s</span><span class="dashicons yoast-svg-icon-eye"></span></button>',
						esc_html__( 'Show this item.', 'wordpress-seo' )
					);
					break;
			}

			$alerts .= sprintf(
				'<div class="yoast-alert-holder" id="%1$s" data-nonce="%2$s" data-json="%3$s">%4$s%5$s</div>',
				esc_attr( $notification->get_id() ),
				esc_attr( $notification->get_nonce() ),
				esc_attr( $notification->get_json() ),
				// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: Needs to be fixed in https://github.com/Yoast/wordpress-seo-premium/issues/2548.
				$notification,
				// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: $button is properly escaped.
				$button
			);
		}

		return $alerts;
	}
}

$wpseo_i18n_summary = $i18n_issues;
if ( ! $active ) {
	$dashicon           = 'yes';
	$wpseo_i18n_summary = $i18n_no_issues;
}

?>
<h3 class="yoast-alerts-header" id="<?php echo esc_attr( 'yoast-' . $type . '-header' ); ?>">
	<span class="dashicons <?php echo esc_attr( 'dashicons-' . $dashicon ); ?>"></span>
	<?php echo esc_html( $i18n_title ); ?> (<?php echo (int) $active_total; ?>)
</h3>

<div id="<?php echo esc_attr( 'yoast-' . $type ); ?>">

	<?php if ( $total ) : ?>
		<p><?php echo esc_html( $wpseo_i18n_summary ); ?></p>

		<div class="container yoast-alerts-active" id="<?php echo esc_attr( 'yoast-' . $type . '-active' ); ?>">
			<?php
			// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: _yoast_display_alerts is considered a safe function.
			echo _yoast_display_alerts( $active, 'active' );
			?>
		</div>

		<?php
		if ( $dismissed ) {
			$dismissed_paper = new WPSEO_Paper_Presenter(
				esc_html( $i18n_muted_issues_title ),
				null,
				[
					'paper_id'                 => esc_attr( $type . '-dismissed' ),
					'paper_id_prefix'          => 'yoast-',
					'class'                    => 'yoast-alerts-dismissed',
					'content'                  => _yoast_display_alerts( $dismissed, 'dismissed' ),
					'collapsible'              => true,
					'collapsible_header_class' => 'yoast-alert',
				]
			);
			// 更多精品WP资源尽在喵容：miaoroom.com
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: current usage is considered safe.
			echo $dismissed_paper->get_output();
		}
		?>

	<?php else : ?>

		<p><?php echo esc_html( $i18n_no_issues ); ?></p>

	<?php endif; ?>
</div>
