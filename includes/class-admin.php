<?php
if (!defined('ABSPATH')) {
    exit;
}

class CF7CV_Admin {
    public function __construct() {
        if (!current_user_can('manage_options')) {
            return;
        }
    }

    public function enqueue_scripts($hook) {
        if (false === strpos($hook, 'wpcf7')) {
            return;
        }

        wp_enqueue_style(
            'cf7cv-admin',
            CF7CV_URL . 'assets/css/admin.css',
            [],
            CF7CV_VERSION
        );

        wp_enqueue_script(
            'cf7cv-admin',
            CF7CV_URL . 'assets/js/admin.js',
            ['jquery'],
            CF7CV_VERSION,
            true
        );

        wp_localize_script('cf7cv-admin', 'cf7cv', [
            'nonce' => wp_create_nonce('cf7cv_ajax_nonce')
        ]);
    }

    public function add_panel($panels) {
        if (!current_user_can('manage_options')) {
            return $panels;
        }

        $panels['custom-validation'] = [
            'title' => __('Custom Validation', 'cf7-custom-validation'),
            'callback' => [$this, 'display_panel']
        ];
        return $panels;
    }

    public function display_panel($post) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $form_id = absint($post->id());
        $fields = $this->get_form_fields($post);
        $enabled = get_post_meta($form_id, '_cf7cv_enabled', true);

        include CF7CV_PATH . 'views/panel.php';
    }

    private function get_form_fields($post) {
        $fields = [];
        $tags = $post->scan_form_tags();

        foreach ($tags as $tag) {
            if ($tag->is_required()) {
                $fields[] = [
                    'name' => sanitize_key($tag->name),
                    'type' => sanitize_key($tag->basetype),
                    'required_message' => $this->get_message($post->id(), $tag->name, 'required'),
                    'invalid_message' => $this->get_message($post->id(), $tag->name, 'invalid')
                ];
            }
        }

        return $fields;
    }

    private function get_message($form_id, $field_name, $type) {
        $field_name = sanitize_key($field_name);
        $type = sanitize_key($type);

        if (!$field_name || !$type) {
            return '';
        }

        $meta_key = sprintf('_cf7cv_%s_%s', $field_name, $type);

        return wp_kses_post(
            get_post_meta($form_id, $meta_key, true)
        );
    }

    public function save_messages($contact_form) {
        if (
            !current_user_can('manage_options') ||
            !isset($_POST['cf7cv_nonce']) ||
            !wp_verify_nonce(wp_unslash($_POST['cf7cv_nonce']), 'cf7cv_save_messages')
        ) {
            return;
        }

        $form_id = absint($contact_form->id());

        $enabled = isset($_POST['cf7cv_enabled']) ? '1' : '0';
        update_post_meta($form_id, '_cf7cv_enabled', $enabled);

        if (!isset($_POST['cf7cv']) || !is_array($_POST['cf7cv'])) {
            return;
        }

        $fields = wp_unslash($_POST['cf7cv']);

        foreach ($fields as $field_name => $messages) {
            if (!is_array($messages)) {
                continue;
            }

            $field_name = sanitize_key($field_name);
            if (!$field_name) {
                continue;
            }

            foreach ($messages as $type => $message) {
                $type = sanitize_key($type);
                if (!$type) {
                    continue;
                }

                $meta_key = sprintf('_cf7cv_%s_%s', $field_name, $type);
                $clean_message = wp_kses_post($message);

                if ('' === $clean_message) {
                    delete_post_meta($form_id, $meta_key);
                    continue;
                }

                update_post_meta($form_id, $meta_key, $clean_message);
            }
        }
    }

    private function get_default_invalid_message($type) {
        $messages = [
            'email' => __('Please enter a valid email address', 'cf7-custom-validation'),
            'url' => __('Please enter a valid URL', 'cf7-custom-validation'),
            'tel' => __('Please enter a valid phone number', 'cf7-custom-validation'),
            'number' => __('Please enter a valid number', 'cf7-custom-validation'),
            'range' => __('Please enter a valid number', 'cf7-custom-validation'),
            'date' => __('Please enter a valid date', 'cf7-custom-validation')
        ];
        return $messages[ $type ] ?? '';
    }
}
