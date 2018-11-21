<?php
/*
Originally forked from wpMandrill Multisite

Automatically propagates the wpMandrill settings from the main site to all subsites, still allowing each subsite to manually override them.
Original Author: tyxla (http://marinatanasov.com/)

*/

/**
 * Class wpMandrill_Multisite
 */
class wpMandrill_Multisite {

	/**
	 * Constructor.
	 *
	 * Initializes and hooks the plugin functionality.
	 *
	 * @access public
	 *
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'distribute_settings' ) );
	}

	/**
	 * Distribute wpMandrill's settings throughout the entire network.
	 *
	 * Called on each of the subsites, populates the wpMandrill settings
	 * if they are not configured. This allows wpMandrill to work on the
	 * entire network without having to set it up manually on each subsite.
	 *
	 * @access public
	 */
	public function distribute_settings() {

		// bail if multisite is not enabled
		if ( ! is_multisite() ) {
			return;
		}

		// allow site ID to be filtered
		$site_id = apply_filters( 'wpmandrill_multisite_site_id', BLOG_ID_CURRENT_SITE );

		// get mandrill settings from main site
		$mainsite_mandrill_settings = get_blog_option( $site_id, 'wpmandrill' );

		// we should not continue if wpMandrill is not setup in the main site
		if ( ! $mainsite_mandrill_settings ) {
			return;
		}

		// the current blog ID
		$current_blog_id = get_current_blog_id();

		// allow propagation to be disabled for a particular site, or for the entire network
		$allow_propagation = apply_filters( 'wpmandrill_multisite_propagation', true, $current_blog_id );

		if ( ! $allow_propagation ) {
			return;
		}

		// get mandrill settings from subsite
		$mandrill_settings = get_option( 'wpmandrill' );

		// if the settings are not setup in the subsite, set them up
		if ( ! $mandrill_settings ) {
			$mandrill_settings = $mainsite_mandrill_settings;

			// use the from name and email from the subsite
			$mandrill_settings['from_name']     = get_bloginfo( 'name' );
			$mandrill_settings['from_username'] = get_bloginfo( 'admin_email' );

			// allow wpMandrill settings to be modified for each blog, or for the entire network
			$mandrill_settings = apply_filters( 'wpmandrill_multisite_settings', $mandrill_settings, $current_blog_id );

			// save wpMandrill options for the subsite
			update_option( 'wpmandrill', $mandrill_settings );
		}
	}
}

// initialize wpMandrill Multisite
global $wp_mandrill_multisite;
$wp_mandrill_multisite = new wpMandrill_Multisite();
