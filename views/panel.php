<?php defined('ABSPATH') || exit; ?>

<div class="cf7cv-panel">
    <div class="cf7cv-enable-toggle">
        <label>
            <input type="checkbox"
                   name="cf7cv_enabled"
                   value="1"
                <?php checked($enabled, 1); ?>>
            <?php esc_html_e('Enable custom validation messages for this form', 'cf7-custom-validation'); ?>
        </label>
    </div>

    <?php if (!empty($fields)): ?>
    <table class="cf7cv-fields-table">
        <thead>
        <tr>
            <th><?php esc_html_e('Field', 'cf7-custom-validation'); ?></th>
            <th><?php esc_html_e('Required Message', 'cf7-custom-validation'); ?></th>
            <th><?php esc_html_e('Invalid Format Message', 'cf7-custom-validation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($fields as $field): ?>
            <tr>
                <td>
                    <div class="cf7cv-field-name"><?php echo esc_html($field['name']); ?></div>
                    <div class="cf7cv-field-type"><?php echo esc_html($field['type']); ?></div>
                </td>
                <td>
                    <input type="text"
                           name="cf7cv[<?php echo esc_attr($field['name']); ?>][required]"
                           value="<?php echo esc_attr($field['required_message']); ?>"
                           class="large-text"
                           placeholder="<?php esc_attr_e('This field is required', 'cf7-custom-validation'); ?>">
                </td>
                <td>
                    <?php if (in_array($field['type'], ['email', 'url', 'tel', 'number', 'range', 'date'], true)): ?>
                        <input type="text"
                               name="cf7cv[<?php echo esc_attr($field['name']); ?>][invalid]"
                               value="<?php echo esc_attr($field['invalid_message']); ?>"
                               class="large-text"
                               placeholder="<?php echo esc_attr($this->get_default_invalid_message($field['type'])); ?>">
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="cf7cv-no-fields">
            <?php esc_html_e('No required fields found in this form.', 'cf7-custom-validation'); ?>
        </div>
    <?php endif; ?>

    <?php wp_nonce_field('cf7cv_save_messages', 'cf7cv_nonce'); ?>
</div>
