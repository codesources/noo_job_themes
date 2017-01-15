<?php
if( !function_exists( 'noo_wc_wp_select_multiple' ) ) :
    function noo_wc_wp_select_multiple($field)
    {
        global $thepostid, $post;

        $thepostid = empty($thepostid) ? $post->ID : $thepostid;
        $field['class'] = isset($field['class']) ? $field['class'] : 'select short';
        $field['style'] = isset($field['style']) ? $field['style'] : '';
        $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
        $field['value'] = isset($field['value']) ? $field['value'] : get_post_meta($thepostid, $field['id'], true);
        $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];

        // Custom attribute handling
        $custom_attributes = array();

        if (!empty($field['custom_attributes']) && is_array($field['custom_attributes'])) {

            foreach ($field['custom_attributes'] as $attribute => $value) {
                $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
            }
        }
        echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><select multiple="multiple" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['name']) . '[]" class="' . esc_attr($field['class']) . '" style="' . esc_attr($field['style']) . '" ' . implode(' ', $custom_attributes) . '>';

        $val = array();
        if ( !empty($field['value']) ){
            $val = !is_array( $field['value'] ) ? unserialize($field['value']) : $field['value'];
            $val = !is_array( $val ) ? array( $val ) : $val;
        }

        $all_selected = in_array('all', $val) ? "selected='selected'" : '';
        foreach ($field['options'] as $key => $value) {
            if (in_array($key, $val)) {
                echo '<option value="' . $key . '" selected="selected">' . $value . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $value . '</option>';
            }

        }

        echo '</select> ';

        if (!empty($field['description'])) {

            if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
                echo wc_help_tip($field['description']);
            } else {
                echo '<span class="description">' . wp_kses_post($field['description']) . '</span>';
            }
        }
        echo '</p>';
    }
endif;

if( !function_exists( 'noo_wc_wp_time_interval' ) ) :
    function noo_wc_wp_time_interval($field)
    {
        global $thepostid, $post;

        $thepostid = empty($thepostid) ? $post->ID : $thepostid;
        $field['class'] = isset($field['class']) ? $field['class'] : 'time-interval';
        $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
        $field['value'] = isset($field['value']) ? $field['value'] : get_post_meta($thepostid, $field['id'], true);
        $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];
        $field['unit_value'] = esc_attr(get_post_meta($thepostid, $field['id'] . '_unit', true));
        $field['unit_value']  = empty( $field['unit_value'] ) ? 'day' : $field['unit_value'];

        // Custom attribute handling
        $custom_attributes = array();

        if (!empty($field['custom_attributes']) && is_array($field['custom_attributes'])) {

            foreach ($field['custom_attributes'] as $attribute => $value) {
                $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
            }
        }

        echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label>';

        echo '<span class="' . esc_attr($field['class']) . '">';
        echo '<input type="number" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['name']) . '" value="' . $field['value'] . '" ' . implode(' ', $custom_attributes) . '/>';
        echo '<select name="' . esc_attr($field['name']) . '_unit" >';
        echo '<option value="day" ' . selected( $field['unit_value'], 'day', false ) . '>' . __( 'Days', 'noo') . '</option>';
        echo '<option value="week" ' . selected( $field['unit_value'], 'week', false ) . '>' . __( 'Weeks', 'noo') . '</option>';
        echo '<option value="month" ' . selected( $field['unit_value'], 'month', false ) . '>' . __( 'Months', 'noo') . '</option>';
        echo '<option value="year" ' . selected( $field['unit_value'], 'year', false ) . '>' . __( 'Years', 'noo') . '</option>';
        echo '</select>';
        echo '</span>';

        if (!empty($field['description'])) {

            if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
                echo wc_help_tip($field['description']);
            } else {
                echo '<span class="description">' . wp_kses_post($field['description']) . '</span>';
            }
        }
        echo '</p>';
    }
endif;