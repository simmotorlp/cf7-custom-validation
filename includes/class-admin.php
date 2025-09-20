<?php
if (!defined('ABSPATH')) {
    exit;
}

class VMCF7_Admin {
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
            'vmcf7-admin',
            VMCF7_URL . 'assets/css/admin.css',
            [],
            VMCF7_VERSION
        );

        wp_enqueue_script(
            'vmcf7-admin',
            VMCF7_URL . 'assets/js/admin.js',
            ['jquery'],
            VMCF7_VERSION,
            true
        );

        wp_localize_script('vmcf7-admin', 'vmcf7', [
            'nonce' => wp_create_nonce('vmcf7_ajax_nonce')
        ]);
    }

    public function add_panel($panels) {
        if (!current_user_can('manage_options')) {
            return $panels;
        }

        $panels['custom-validation'] = [
            'title' => __('Custom Validation', 'validation-muse-for-contact-form-7'),
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
        $enabled = get_post_meta($form_id, '_vmcf7_enabled', true);

        include VMCF7_PATH . 'views/panel.php';
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

        $meta_key = sprintf('_vmcf7_%s_%s', $field_name, $type);

        return wp_kses_post(
            get_post_meta($form_id, $meta_key, true)
        );
    }

    public function save_messages($contact_form) {
        $nonce = filter_input(INPUT_POST, 'vmcf7_nonce', FILTER_SANITIZE_SPECIAL_CHARS);

        if (
            !current_user_can('manage_options') ||
            !$nonce ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), 'vmcf7_save_messages')
        ) {
            return;
        }

        $form_id = absint($contact_form->id());

        $enabled = filter_input(INPUT_POST, 'vmcf7_enabled', FILTER_SANITIZE_NUMBER_INT) ? '1' : '0';
        update_post_meta($form_id, '_vmcf7_enabled', $enabled);

        $raw_fields = filter_input(INPUT_POST, 'vmcf7', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        if (null === $raw_fields || false === $raw_fields) {
            return;
        }

        $fields = wp_unslash($raw_fields);

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

                $meta_key = sprintf('_vmcf7_%s_%s', $field_name, $type);
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
            'email' => __('Please enter a valid email address', 'validation-muse-for-contact-form-7'),
            'url' => __('Please enter a valid URL', 'validation-muse-for-contact-form-7'),
            'tel' => __('Please enter a valid phone number', 'validation-muse-for-contact-form-7'),
            'number' => __('Please enter a valid number', 'validation-muse-for-contact-form-7'),
            'range' => __('Please enter a valid number', 'validation-muse-for-contact-form-7'),
            'date' => __('Please enter a valid date', 'validation-muse-for-contact-form-7')
        ];
        return $messages[ $type ] ?? '';
    }
}
