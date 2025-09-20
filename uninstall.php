<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$forms = get_posts([
    'post_type'      => 'wpcf7_contact_form',
    'post_status'    => 'any',
    'fields'         => 'ids',
    'posts_per_page' => -1,
    'no_found_rows'  => true,
]);

if ($forms) {
    foreach ($forms as $form_id) {
        $meta = get_post_meta($form_id);

        foreach ($meta as $key => $values) {
            if (0 !== strpos((string) $key, '_cf7cv_')) {
                continue;
            }

            delete_post_meta($form_id, $key);
        }
    }
}

delete_option('cf7cv_version');
