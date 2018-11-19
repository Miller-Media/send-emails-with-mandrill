<?php

class pluginActivation
{
    function __construct()
    {
        add_action('admin_init', array($this, 'check_version'));

        if (!self::compatible_version()) {
            return;
        }
    }

    static function activation_check()
    {
        if (!self::compatible_version()) {
            deactivate_plugins(SEWM_BASE);
            wp_die(__('Send Emails with Mandrill requires PHP 5.6 or higher. Please upgrade your version of PHP to activate.', 'send-emails-with-mandrill'));
        }
    }

    function check_version()
    {
        if (!self::compatible_version()) {
            if (is_plugin_active(SEWM_BASE)) {
                deactivate_plugins(SEWM_BASE);
                add_action('admin_notices', array($this, 'disabled_notice'));

                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }
            }
        }
    }

    function disabled_notice()
    {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Send Emails with Mandrill requires PHP 5.6 or higher. Please upgrade your version of PHP to activate.', 'send-emails-with-mandrill') . '</p></div>';
    }

    static function compatible_version()
    {
        if (version_compare(PHP_VERSION, '5.6', '<'))
            return false;

        // Add sanity checks for other version requirements here
        return true;
    }
}

global $pluginCheck;
$pluginCheck = new pluginActivation();

register_activation_hook(__FILE__, array('pluginActivation', 'activation_check'));