<?php

if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option( 'wpmandrill' );
delete_option( 'wpmandrill-test' );
delete_option( 'wpmandrill-stats' );
delete_transient('mandrill-stats');

wp_clear_scheduled_hook('wpm_update_stats');

if ( function_exists('delete_site_option') )  delete_site_option('wpmandrill_notice_shown');

?>
