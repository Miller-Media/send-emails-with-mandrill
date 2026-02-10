<?php
/* 
Plugin Name: Send E-mails with Mandrill
Description: Send e-mails using Mandrill. This is a forked version of the now unsupported plugin <a href="https://wordpress.org/plugins/wpmandrill/">wpMandrill</a>.
Author: Miller Media ( Matt Miller )
Author URI: http://www.millermedia.io
Version: 1.5.0
Requires PHP: 8.1
Tested up to: 6.9.1
Text Domain: send-emails-with-mandrill
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*  Copyright 2012  MailChimp (email : will@mailchimp.com )
	Copyright 2018 	Miller Media (email : support@miller-media.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation using version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
if ( !defined('SEWM_VERSION'))
	define( 'SEWM_VERSION', '1.4.7' );

if ( !defined( 'SEWM_BASE' ) )
	define( 'SEWM_BASE', plugin_basename( __FILE__ ) );

if ( !defined( 'SEWM_URL' ) )
	define( 'SEWM_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'SEWM_PATH' ) )
	define( 'SEWM_PATH', plugin_dir_path( __FILE__ ) );

include( plugin_dir_path( __FILE__ ) . 'lib/pluginActivation.class.php');
include( plugin_dir_path( __FILE__ ) . 'lib/wpMandrill.class.php');
include( plugin_dir_path( __FILE__ ) . 'lib/reviewNotice.class.php');

register_activation_hook( __FILE__, function() {
	if ( ! get_option( 'sewm_activated_on' ) ) {
		update_option( 'sewm_activated_on', time() );
	}
});

wpMandrill::on_load();
new wpMandrill_ReviewNotice( 'Send Emails with Mandrill', 'send-emails-with-mandrill', 'sewm_activated_on', 'send-emails-with-mandrill', SEWM_URL . 'assets/icon-256x256.png' );
