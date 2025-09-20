<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$meta_key_like = $wpdb->esc_like('_cf7cv_') . '%';
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
        $meta_key_like
    )
);

delete_option('cf7cv_version');
