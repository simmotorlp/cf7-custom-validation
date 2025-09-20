<?php
/**
 * Plugin Name: Validation Muse for Contact Form 7
 * Description: Customize validation messages for each Contact Form 7 field
 * Version: 1.1.2
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: simmotorlp
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: validation-muse-for-contact-form-7
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('VMCF7_VERSION', '1.1.2');
define('VMCF7_FILE', __FILE__);
define('VMCF7_PATH', plugin_dir_path(VMCF7_FILE));
define('VMCF7_URL', plugin_dir_url(VMCF7_FILE));
define('VMCF7_BASENAME', plugin_basename(VMCF7_FILE));

register_activation_hook(VMCF7_FILE, function() {
    if (!class_exists('WPCF7_ContactForm')) {
        deactivate_plugins(VMCF7_BASENAME);
        wp_die(
            esc_html__('This plugin requires Contact Form 7 to be installed and activated.', 'validation-muse-for-contact-form-7'),
            esc_html__('Plugin dependency check', 'validation-muse-for-contact-form-7'),
            ['back_link' => true]
        );
    }

    update_option('vmcf7_version', VMCF7_VERSION);
});

function vmcf7_init() {
    if (!class_exists('WPCF7_ContactForm')) {
        add_action('admin_notices', function() {
            $install_link_open = sprintf(
                '<a href="%s">',
                esc_url(admin_url('plugin-install.php?tab=search&s=contact+form+7'))
            );
            $install_link_close = '</a>';
            $message = sprintf(
                /* translators: 1: opening link tag, 2: closing link tag. */
                __('Validation Muse requires Contact Form 7 to be installed and activated. %1$sInstall Contact Form 7%2$s', 'validation-muse-for-contact-form-7'),
                $install_link_open,
                $install_link_close
            );

            printf('<div class="notice notice-error"><p>%s</p></div>', wp_kses_post($message));
        });
        return;
    }

    require_once VMCF7_PATH . 'includes/class-loader.php';
    $loader = new VMCF7_Loader();
    $loader->init();
}
add_action('plugins_loaded', 'vmcf7_init');
