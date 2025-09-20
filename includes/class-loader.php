<?php
if (!defined('ABSPATH')) {
    exit;
}

class CF7CV_Loader {
    public function init() {
        require_once __DIR__ . '/class-admin.php';

        foreach ($this->get_validation_tag_types() as $tag_type) {
            add_filter("wpcf7_validate_{$tag_type}", [$this, 'validate_field'], 9, 2);
            add_filter("wpcf7_validate_{$tag_type}*", [$this, 'validate_field'], 9, 2);
        }

        if (is_admin()) {
            $admin = new CF7CV_Admin();
            add_action('admin_enqueue_scripts', [$admin, 'enqueue_scripts']);
            add_filter('wpcf7_editor_panels', [$admin, 'add_panel']);
            add_action('wpcf7_save_contact_form', [$admin, 'save_messages']);
        }
    }

    public function validate_field($result, $tag) {
        $form = wpcf7_get_current_contact_form();
        if (!$form || !$this->is_enabled($form->id())) {
            return $result;
        }

        $field_name = $this->normalize_field_name($tag->name);
        if (!$field_name) {
            return $result;
        }

        $value = $this->get_posted_value($tag);

        $required_message = $this->get_custom_message($form->id(), $field_name, 'required');
        if ($required_message && $tag->is_required() && $this->value_is_empty($value)) {
            $result->invalidate($tag, $required_message);
            return $result;
        }

        $invalid_message = $this->get_custom_message($form->id(), $field_name, 'invalid');
        if (!$invalid_message || $this->value_is_empty($value) || is_array($value)) {
            return $result;
        }

        switch ($tag->basetype) {
            case 'email':
                if (!wpcf7_is_email($value)) {
                    $result->invalidate($tag, $invalid_message);
                }
                break;
            case 'url':
                if (!wpcf7_is_url($value)) {
                    $result->invalidate($tag, $invalid_message);
                }
                break;
            case 'tel':
                if (!wpcf7_is_tel($value)) {
                    $result->invalidate($tag, $invalid_message);
                }
                break;
            case 'number':
            case 'range':
                if (!wpcf7_is_number($value)) {
                    $result->invalidate($tag, $invalid_message);
                }
                break;
            case 'date':
                if (!wpcf7_is_date($value)) {
                    $result->invalidate($tag, $invalid_message);
                }
                break;
        }

        return $result;
    }

    private function get_validation_tag_types() {
        return [
            'text',
            'email',
            'url',
            'tel',
            'number',
            'range',
            'date',
            'textarea',
            'select',
            'checkbox',
            'radio',
            'file',
        ];
    }

    private function get_posted_value($tag) {
        $name = $tag->name;

        if ('file' === $tag->basetype) {
            if (!isset($_FILES[$name])) {
                return null;
            }

            $file = $_FILES[$name];

            if (isset($file['tmp_name']) && is_array($file['tmp_name'])) {
                return array_values(array_filter(array_map('strval', $file['tmp_name']), 'strlen'));
            }

            return !empty($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        }

        if (!isset($_POST[$name])) {
            return null;
        }

        $value = $_POST[$name];

        if (is_array($value)) {
            $value = array_map('wp_unslash', $value);
            return array_map('trim', $value);
        }

        return trim(strtr((string) wp_unslash($value), "\n", ' '));
    }

    private function value_is_empty($value) {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ('' !== $item) {
                    return false;
                }
            }
            return true;
        }

        return null === $value || '' === $value;
    }

    private function is_enabled($form_id) {
        return '1' === get_post_meta($form_id, '_cf7cv_enabled', true);
    }

    private function get_custom_message($form_id, $field_name, $type) {
        $field_name = $this->normalize_field_name($field_name);
        $type = $this->normalize_field_name($type);

        if (!$field_name || !$type) {
            return '';
        }

        $meta_key = sprintf('_cf7cv_%s_%s', $field_name, $type);
        $message = get_post_meta($form_id, $meta_key, true);

        return is_string($message) ? $message : '';
    }

    private function normalize_field_name($field_name) {
        return sanitize_key($field_name);
    }
}
