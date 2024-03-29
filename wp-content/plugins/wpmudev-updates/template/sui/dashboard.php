<?php
/**
 * Dashboard home template
 *
 * @package WPMUDEV DASHBOARD 4.9.0
 */

$page_title = __( 'Dashboard', 'wpmudev' );
$page_slug  = 'dashboard';
$this->render_sui_header( $page_title, $page_slug );
$queue = WPMUDEV_Dashboard::$site->get_option( 'notifications' );

// if already dismissed don't show.
if ( ( ! isset( $queue[ $this->_membership_notice ] ) || ! isset( $queue[ $this->_membership_notice ]['dismissed'] ) ) && ( 'free' === $type || 'single' === $type ) ) {
	$this->render_upgrade_header( $type, $licensed_projects );
}

// @var WPMUDEV_Dashboard_Sui_Page_Urls $urls.
// Support & update stats
$support_thread_url = $urls->support_url;

$support_threads = count( $member['forum']['support_threads'] );
$support_threads = $support_threads > 0 ? sprintf( '<span class="sui-tag sui-tag-sm sui-tag-branded"><a href="%s" style="color:#fff">%s</a></span>', esc_url( $support_thread_url ), absint( $support_threads ) ) : absint( $support_threads );

$update_plugins_html  = $update_plugins > 0 ? sprintf( '<span class="sui-tag sui-tag-sm sui-tag-warning"><a href="%s" style="color:#333">%s</a></span>', esc_url( $urls->plugins_url ), $update_plugins ) : $update_plugins;
$total_active_plugins = isset( $active_projects['all'] ) ? absint( $active_projects['all'] ) : 0;

// Find the 5 most popular plugins, that are not installed yet.
$selected_plugins = array();
asort( $data['projects'] );
$projects = wp_list_pluck( $data['projects'], 'id', 'name' );

// sort by name.
ksort( $projects );
if ( $update_plugins > 0 ) :
	foreach ( $projects as $key => $item ) {
		// if update is complete break.
		if ( $update_plugins <= count( $selected_plugins ) ) {
			break;
		}

		// Skip themes.
		if ( 'plugin' !== $data['projects'][ $item ]['type'] ) {
			continue;
		}

		$wpmu_plugin = WPMUDEV_Dashboard::$site->get_project_info( $item );
		// get the updates first.
		if ( ! $wpmu_plugin->has_update ) {
			continue;
		}

		$selected_plugins[] = $wpmu_plugin->pid;
	}
endif;

foreach ( $projects as $key => $item ) {
	// Skip themes.
	if ( 'plugin' !== $data['projects'][ $item ]['type'] ) {
		continue;
	}

	$wpmu_plugin = WPMUDEV_Dashboard::$site->get_project_info( $item );

	// if update is complete break.
	if ( 5 <= count( $selected_plugins ) ) {
		break;
	}

	// ignore plugin with updates.
	if ( $wpmu_plugin->has_update ) {
		continue;
	}

	// Skip plugin if it's already installed.
	if ( ! $wpmu_plugin->is_active ) {
		continue;
	}

	// Skip plugins that are not compatible with current site.
	if ( ! $wpmu_plugin->is_compatible ) {
		continue;
	}

	// Skip hidden/deprecated projects.
	if ( $wpmu_plugin->is_hidden ) {
		continue;
	}

	$selected_plugins[] = $wpmu_plugin->pid;

}

?>
<div class="sui-box sui-summary sui-summary-sm">

	<div class="sui-summary-image-space" aria-hidden="true"></div>

		<div class="sui-summary-segment">

			<div class="sui-summary-details">

				<span class="sui-summary-large"><?php echo absint( $total_active_plugins ); ?></span>
				<span class="sui-summary-sub"><?php echo esc_html( _n( 'Active Pro plugin', 'Active Pro plugins', $total_active_plugins, 'wpmudev' ) ); ?></span>

			</div>

		</div>

	<div class="sui-summary-segment">

		<ul class="sui-list">

			<li>
				<span class="sui-list-label"><?php esc_html_e( 'Plugin Updates Available', 'wpmudev' ); ?> </span>
				<span class="sui-list-detail"><?php echo $update_plugins_html; //phpcs:ignore ?></span>
			</li>

			<li>
				<span class="sui-list-label"><?php esc_html_e( 'Active Support Tickets', 'wpmudev' ); ?></span>
				<span class="sui-list-detail">
					<?php echo $support_threads; //phpcs:ignore  ?>
				</span>
			</li>

		</ul>

	</div>

</div><!-- End Overview -->

<div class="sui-row dashui-table-widgets">
	<div class="sui-col-md-6">
		<?php // BOX: Installed Plugins. ?>
		<?php $this->render( 'sui/dashboard-templates/installed-plugins', compact( 'data', 'urls', 'selected_plugins', 'membership_data' ) ); ?>

		<?php // BOX: Services. ?>
		<?php $this->render( 'sui/dashboard-templates/services', compact( 'urls', 'membership_data' ) ); ?>

		<?php // BOX: Support. ?>
		<?php $this->render( 'sui/dashboard-templates/support', compact( 'urls', 'member', 'staff_login', 'membership_data' ) ); ?>
	</div>

	<div class="sui-col-md-6">

<?php if ( 'free' === $membership_data['membership'] ) : ?>
		<?php // BOX: Expired Membership Info. ?>
		<?php $this->render( 'sui/dashboard-templates/expired_membership_info', compact( 'urls', 'whitelabel_settings', 'analytics_enabled', 'total_visits', 'membership_data' ) ); ?>
<?php endif; ?>

		<?php // BOX: Tools. ?>
		<?php $this->render( 'sui/dashboard-templates/tools', compact( 'urls', 'whitelabel_settings', 'analytics_enabled', 'total_visits', 'membership_data' ) ); ?>

		<?php // BOX: Resources. ?>
		<?php $this->render( 'sui/dashboard-templates/resources', compact( 'urls', 'membership_data' ) ); ?>

	</div>
</div>
<?php
$this->render( 'sui/element-last-refresh', array(), true );

$this->render( 'sui/footer', array(), true );

$this->render( 'sui/popup-logged-welcome', compact( 'urls', 'type', 'licensed_projects' ), true );