<?php
/* 
Plugin Name: Send E-mails with Mandrill
Description: Send e-mails using Mandrill. This is a forked version of the now unsupported plugin <a href="https://wordpress.org/plugins/wpmandrill/">wpMandrill</a>.
Author: Miller Media ( Matt Miller )
Author URI: http://www.millermedia.io
Version: 1.2.9
Requires PHP: 5.6
Text Domain: send-emails-with-mandrill
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

if ( !defined( 'SEWM_BASE' ) )
	define( 'SEWM_BASE', plugin_basename( __FILE__ ) );

if ( !defined( 'SEWM_URL' ) )
	define( 'SEWM_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'SEWM_PATH' ) )
	define( 'SEWM_PATH', plugin_dir_path( __FILE__ ) );

include( plugin_dir_path( __FILE__ ) . 'lib/pluginActivation.class.php');
include( plugin_dir_path( __FILE__ ) . 'lib/wpMandrill.class.php');

wpMandrill::on_load();