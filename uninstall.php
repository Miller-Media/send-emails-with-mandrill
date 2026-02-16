<?php
/**
 * Uninstall handler for Send Emails with Mandrill.
 *
 * @package Send_Emails_With_Mandrill
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = get_option( 'wpmandrill' );
$delete_data = is_array( $options ) && ! empty( $options['delete_data_on_uninstall'] );

if ( ! $delete_data ) {
	return;
}

// Delete plugin options.
delete_option( 'wpmandrill' );
delete_option( 'wpmandrill-test' );
delete_option( 'wpmandrill-stats' );
delete_option( 'sewm_activated_on' );

// Delete transients.
delete_transient( 'mandrill-stats' );

// Clear scheduled cron events.
wp_clear_scheduled_hook( 'wpm_update_stats' );

// Clean up site options and user meta.
if ( function_exists( 'delete_site_option' ) ) {
	delete_site_option( 'wpmandrill_notice_shown' );
}

global $wpdb;
$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'send-emails-with-mandrill_review_dismissed' ) );
