<?php
/**
 * Plugin Name: CF7 Custom Validation Messages
 * Description: Customize validation messages for each Contact Form 7 field
 * Version: 1.1.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: simmotorlp
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cf7-custom-validation
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CF7CV_VERSION', '1.1.0');
define('CF7CV_FILE', __FILE__);
define('CF7CV_PATH', plugin_dir_path(CF7CV_FILE));
define('CF7CV_URL', plugin_dir_url(CF7CV_FILE));
define('CF7CV_BASENAME', plugin_basename(CF7CV_FILE));

/**
 * Make translation files available.
 */
function cf7cv_load_textdomain() {
    load_plugin_textdomain('cf7-custom-validation', false, dirname(CF7CV_BASENAME) . '/languages/');
}
add_action('init', 'cf7cv_load_textdomain');

register_activation_hook(CF7CV_FILE, function() {
    if (!class_exists('WPCF7_ContactForm')) {
        deactivate_plugins(CF7CV_BASENAME);
        wp_die(
            esc_html__('This plugin requires Contact Form 7 to be installed and activated.', 'cf7-custom-validation'),
            esc_html__('Plugin dependency check', 'cf7-custom-validation'),
            ['back_link' => true]
        );
    }

    update_option('cf7cv_version', CF7CV_VERSION);
});

function cf7cv_init() {
    if (!class_exists('WPCF7_ContactForm')) {
        add_action('admin_notices', function() {
            $install_link_open = sprintf(
                '<a href="%s">',
                esc_url(admin_url('plugin-install.php?tab=search&s=contact+form+7'))
            );
            $install_link_close = '</a>';
            $message = sprintf(
                __('CF7 Custom Validation requires Contact Form 7 plugin to be installed and activated. %1$sInstall Contact Form 7%2$s', 'cf7-custom-validation'),
                $install_link_open,
                $install_link_close
            );

            printf('<div class="notice notice-error"><p>%s</p></div>', wp_kses_post($message));
        });
        return;
    }

    require_once CF7CV_PATH . 'includes/class-loader.php';
    $loader = new CF7CV_Loader();
    $loader->init();
}
add_action('plugins_loaded', 'cf7cv_init');
