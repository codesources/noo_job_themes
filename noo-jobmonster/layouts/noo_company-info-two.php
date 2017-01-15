<?php
$company_name = get_post_field('post_title', $company_id);
$all_socials = noo_get_social_fields();
?>
<div class="company-desc">
    <div class="company-info">
        <?php
        // Custom Fields
        $fields = jm_get_company_custom_fields();
        $html = array();

        foreach ($fields as $field) {
            if( $field['name'] == '_address' ) {
                continue;
            }

            $id = jm_company_custom_fields_name($field['name'], $field);
            $value = noo_get_post_meta($company_id, $id, '');

            if (!empty($value)) {
                $html[] = '<li>' . noo_display_field($field, $id, $value, array('label_tag' => 'strong', 'label_class' => 'company-cf', 'value_tag' => 'span'), false) . '</li>';
            }
        }
        if (!empty($html) && count($html) > 0) : ?>
            <div class="company-custom-fields">
                <strong class="company-cf-title"><?php _e('Company Information', 'noo'); ?></strong>
                <ul>
                    <?php echo implode("\n", $html); ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php
        $address = noo_get_post_meta($company_id, '_address', true);
        if (!empty($address)):
            wp_enqueue_script('google-map');
            wp_enqueue_script('google-map-custom');

            // $location = noo_address_to_lng_lat($address);
            $location = jm_job_get_term_geolocation( $address );
            if(!empty($location)):
            $image = get_template_directory_uri() . '/assets/images/map-marker-icon.png';
            ?>
            <div class="google-map">
                <div id="googleMap" style="height: 250px;" 
                     data-map_style="apple"
                     data-address="<?php echo esc_html($location['formatted_address']); ?>"
                     data-icon="<?php echo esc_url($image); ?>"
                     data-lat="<?php echo esc_attr($location['lat']); ?>"
                     data-lon="<?php echo esc_attr($location['long']); ?>">

                </div>
            </div>
        <?php endif; endif; ?>
        <?php
        // Job's social info
        $socials = jm_get_company_socials();
        $html = array();

        foreach ($socials as $social) {
            if (!isset($all_socials[$social])) continue;
            $data = $all_socials[$social];
            $value = get_post_meta($company_id, "_{$social}", true);
            if (!empty($value)) {
                $url = $social == 'email' ? 'mailto:' . $value : esc_url($value);
                $html[] = '<a title="' . sprintf(esc_attr__('Connect with us on %s', 'noo'), $data['label']) . '" class="noo-icon fa ' . $data['icon'] . '" href="' . $url . '" target="_blank"></a>';
            }
        }

        if (!empty($html) && count($html) > 0) : ?>
            <div class="job-social clearfix">
                <?php echo implode("\n", $html); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
if (noo_get_option('noo_single_company_contact_form', true)):
    $company = get_post(get_the_ID());
    $company_author = $company->post_author;
    $company_author = get_user_by('id', $company_author);
    if ($company_author):
        $company_email = $company_author->user_email;
        if (!empty($company_email)):
            ?>
                <div class="noo-company-contact">
                    <div class="noo-company-contact-title">
                        <?php _e('Contact Us', 'noo'); ?>
                    </div>
                    <div class="noo-company-contact-form">
                        <form id="contact_company_form" class="form-horizontal jform-validate">
                            <div style="display: none">
                                <input type="hidden" name="action" value="noo_ajax_send_contact">
                                <input type="hidden" name="to_email" value="<?php echo $company_email; ?>"/>
                                <input type="hidden" class="security" name="security"
                                       value="<?php echo wp_create_nonce('noo-ajax-send-contact') ?>"/>
                            </div>
                            <div class="form-group">
                                    <span class="input-icon">
                                        <input type="text" class="form-control jform-validate" id="name"
                                               name="from_name"
                                               autofocus="" required=""
                                               placeholder="<?php _e('Enter Your Name', 'noo'); ?>"/>
                                        <i class="fa fa-home"></i>
                                    </span>
                            </div>
                            <div class="form-group">
                                    <span class="input-icon">
                                        <input type="email" class="form-control jform-validate jform-validate-email"
                                               id="email"
                                               name="from_email" required=""
                                               placeholder="<?php _e('Email Address', 'noo'); ?>"/>
                                        <i class="fa fa-envelope"></i>
                                        </span>
                            </div>
                            <div class="form-group">
                                    <span class="input-icon">
                                        <textarea class="form-control jform-validate" id="message" name="from_message"
                                                  rows="5"
                                                  placeholder="<?php _e('Message...', 'noo'); ?>"></textarea>
                                        <i class="fa fa-comment"></i>
                                    </span>

                            </div>
                            <?php do_action('noo_company_contact_form'); ?>
                            <?php
                            $term_page = Noo_Member::get_setting('term_page_id');
                            $term_of_use_link = !empty($term_page) ? esc_url(apply_filters('noo_term_url', get_permalink($term_page))) : '';
                            if (!empty($term_of_use_link)) :
                                echo sprintf(__('You accepts our <a href="%s" >Terms and Conditions</a>', 'noo'), $term_of_use_link);
                            endif;
                            ?>
                            <div class="form-actions">
                                <button type="submit"
                                        class="btn btn-primary"><?php _e('Send Message', 'noo'); ?></button>
                            </div>
                            <div class="noo-ajax-result"></div>
                        </form>
                    </div>
                </div>
        <?php
        endif;
    endif;
endif; ?>
