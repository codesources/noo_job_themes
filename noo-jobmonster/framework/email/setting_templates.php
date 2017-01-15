<?php
if (!function_exists('jm_email_template_settings')) :
    function jm_email_template_settings()
    {
        register_setting('noo_email_template', 'noo_email_template');
    }

    add_filter('admin_init', 'jm_email_template_settings');
endif;

if (!function_exists('jm_email_template_settings_tabs')) :
    function jm_email_template_settings_tabs($tabs = array())
    {
        return array_merge(array(
            'email_template' => __('Email Templates', 'noo'),
        ), $tabs);
    }

    add_filter('noo_job_settings_tabs_array', 'jm_email_template_settings_tabs');
endif;

if (!function_exists('jm_et_get_setting')) :
    function jm_et_get_setting($id = null, $default = null)
    {
        $email_template_field = jm_email_template_field();
        $default = ( $default === null && isset( $email_template_field[$id . '_default'] ) ) ? $email_template_field[$id . '_default'] : $default;

        return jm_get_setting('noo_email_template', $id, $default);
    }
endif;

if (!function_exists('jm_email_template_settings_form')) :
    function jm_email_template_settings_form()
    {
        jm_et_correct_settings();

        $admin_job_submitted_activated = jm_et_get_setting('admin_job_submitted_activated', 1);
        $admin_job_submitted_subject = jm_et_get_setting('admin_job_submitted_subject');
        $admin_job_submitted_content = jm_et_get_setting('admin_job_submitted_content');

        $admin_resume_activated = jm_et_get_setting('admin_resume_activated', 0);
        $admin_resume_subject = jm_et_get_setting('admin_resume_subject');
        $admin_resume_content = jm_et_get_setting('admin_resume_content');

        $employer_registration_activated = jm_et_get_setting('employer_registration_activated', 1);
        $employer_registration_subject = jm_et_get_setting('employer_registration_subject');
        $employer_registration_content = jm_et_get_setting('employer_registration_content');

        $employer_job_submitted_activated = jm_et_get_setting('employer_job_submitted_activated', 1);
        $employer_job_submitted_subject = jm_et_get_setting('employer_job_submitted_subject');
        $employer_job_submitted_content = jm_et_get_setting('employer_job_submitted_content');

        $employer_job_approved_activated = jm_et_get_setting('employer_job_approved_activated', 1);
        $employer_job_approved_subject = jm_et_get_setting('employer_job_approved_subject');
        $employer_job_approved_content = jm_et_get_setting('employer_job_approved_content');

        $employer_job_rejected_activated = jm_et_get_setting('employer_job_rejected_activated', 1);
        $employer_job_rejected_subject = jm_et_get_setting('employer_job_rejected_subject');
        $employer_job_rejected_content = jm_et_get_setting('employer_job_rejected_content');

        $employer_job_application_activated = jm_et_get_setting('employer_job_application_activated', 1);
        $employer_job_application_attachment = jm_et_get_setting('employer_job_application_attachment', 1);
        $employer_job_application_subject = jm_et_get_setting('employer_job_application_subject');
        $employer_job_application_content = jm_et_get_setting('employer_job_application_content');

        $candidate_registration_activated = jm_et_get_setting('candidate_registration_activated', 1);
        $candidate_registration_subject = jm_et_get_setting('candidate_registration_subject');
        $candidate_registration_content = jm_et_get_setting('candidate_registration_content');

        $candidate_application_activated = jm_et_get_setting('candidate_application_activated', 1);
        $candidate_application_subject = jm_et_get_setting('candidate_application_subject');
        $candidate_application_content = jm_et_get_setting('candidate_application_content');

        $candidate_approved_activated = jm_et_get_setting('candidate_approved_activated', 1);
        $candidate_approved_subject = jm_et_get_setting('candidate_approved_subject');
        $candidate_approved_content = jm_et_get_setting('candidate_approved_content');

        $candidate_rejected_activated = jm_et_get_setting('candidate_rejected_activated', 1);
        $candidate_rejected_subject = jm_et_get_setting('candidate_rejected_subject');
        $candidate_rejected_content = jm_et_get_setting('candidate_rejected_content');

        $candidate_resume_activated = jm_et_get_setting('candidate_resume_activated', 1);
        $candidate_resume_subject = jm_et_get_setting('candidate_resume_subject');
        $candidate_resume_content = jm_et_get_setting('candidate_resume_content');

        $candidate_resume_approved_activated = jm_et_get_setting('candidate_resume_approved_activated', 1);
        $candidate_resume_approved_subject = jm_et_get_setting('candidate_resume_approved_subject');
        $candidate_resume_approved_content = jm_et_get_setting('candidate_resume_approved_content');

        $candidate_resume_rejected_activated = jm_et_get_setting('candidate_resume_rejected_activated', 1);
        $candidate_resume_rejected_subject = jm_et_get_setting('candidate_resume_rejected_subject');
        $candidate_resume_rejected_content = jm_et_get_setting('candidate_resume_rejected_content');


        $blogname = get_option('blogname');
        $from_name = jm_et_get_setting( 'from_name', $blogname );
        $from_email = jm_et_get_setting( 'from_email', '' );
        $from_email = strtolower( $from_email );

        $job_custom_fields = jm_get_job_custom_fields();
        $resume_custom_fields = jm_get_resume_custom_fields();
        $application_custom_fields = jm_get_application_custom_fields();

        ?>
        <?php settings_fields('noo_email_template'); ?>
        <h3><?php echo __('Email Templates', 'noo') ?></h3>
        <?php
        $active_tab = isset($_GET['sub-tab']) ? $_GET['sub-tab'] : 'admin_email';
        ?>
        <h2 class="nav-tab-wrapper email-setting">
            <a data-tab="admin_email" href="?page=jm-setting&tab=email_template&sub-tab=admin_email"
               class="nav-tab <?php echo $active_tab == 'admin_email' ? 'nav-tab-active' : ''; ?>"><?php echo __('Admin Emails', 'noo' ); ?></a>
            <a data-tab="employer_email" href="?page=jm-setting&tab=email_template&sub-tab=employer_email"
               class="nav-tab <?php echo $active_tab == 'employer_email' ? 'nav-tab-active' : ''; ?>"><?php echo __('Employer Emails', 'noo' ); ?></a>
            <a data-tab="candidate_email" href="?page=jm-setting&tab=email_template&sub-tab=candidate_email"
               class="nav-tab <?php echo $active_tab == 'candidate_email' ? 'nav-tab-active' : ''; ?>"><?php echo __('Candidate Emails', 'noo' ); ?></a>
            <a data-tab="other_email" href="?page=jm-setting&tab=email_template&sub-tab=other_email"
               class="nav-tab <?php echo $active_tab == 'other_email' ? 'nav-tab-active' : ''; ?>"><?php echo __('Other', 'noo' ); ?></a>
        </h2>
        <div id="admin_email" class="email-setting tab-content <?php echo $active_tab == 'admin_email' ? 'tab-wrapper-active' : 'hidden'; ?>">
            <h3><?php _e('Job submitted email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[admin_job_submitted_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[admin_job_submitted_activated]" <?php checked( $admin_job_submitted_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[admin_job_submitted_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($admin_job_submitted_subject) ? $admin_job_submitted_subject : ''; ?>">
                        <?php jm_et_render_field('admin_job_submitted_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php

                        $editor_id = 'textblock' . uniqid();
                        wp_editor($admin_job_submitted_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[admin_job_submitted_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('admin_job_submitted_content', true, false, $job_custom_fields ); ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <h3><?php _e('Resume submitted email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[admin_resume_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[admin_resume_activated]" <?php checked( $admin_resume_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[admin_resume_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($admin_resume_subject) ? $admin_resume_subject : ''; ?>">
                        <?php jm_et_render_field('admin_resume_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php

                        $editor_id = 'textblock' . uniqid();
                        wp_editor($admin_resume_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[admin_resume_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('admin_resume_content', true, false, $resume_custom_fields ); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php do_action('jm_setting_email_template_admin'); ?>
        </div>
        <!--Employer Email-->
        <div id="employer_email" class="email-setting tab-content <?php echo $active_tab == 'employer_email' ? 'tab-wrapper-active' : 'hidden'; ?>">
            <h3><?php _e('Employer registration email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[employer_registration_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[employer_registration_activated]" <?php checked( $employer_registration_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[employer_registration_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($employer_registration_subject) ? $employer_registration_subject : ''; ?>">
                        <?php jm_et_render_field('employer_registration_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php

                        $editor_id = 'textblock' . uniqid();
                        wp_editor($employer_registration_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[employer_registration_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('employer_registration_content', true, false, false); ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <hr/>
            <h3><?php _e('Employer job submitted email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[employer_job_submitted_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[employer_job_submitted_activated]" <?php checked( $employer_job_submitted_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[employer_job_submitted_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($employer_job_submitted_subject) ? $employer_job_submitted_subject : ''; ?>">
                        <?php jm_et_render_field('employer_job_submitted_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($employer_job_submitted_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[employer_job_submitted_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('employer_job_submitted_content', true, false, $job_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Employer job approved email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[employer_job_approved_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[employer_job_approved_activated]" <?php checked( $employer_job_approved_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[employer_job_approved_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($employer_job_approved_subject) ? $employer_job_approved_subject : ''; ?>">
                        <?php jm_et_render_field('employer_job_approved_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($employer_job_approved_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[employer_job_approved_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('employer_job_approved_content', true, false, $job_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Employer job rejected email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[employer_job_rejected_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[employer_job_rejected_activated]" <?php checked( $employer_job_rejected_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[employer_job_rejected_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($employer_job_rejected_subject) ? $employer_job_rejected_subject : ''; ?>">
                        <?php jm_et_render_field('employer_job_rejected_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($employer_job_rejected_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[employer_job_rejected_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('employer_job_rejected_content', true, false, $job_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Employer job application notification email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[employer_job_application_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[employer_job_application_activated]" <?php checked( $employer_job_application_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Email Attachment','noo')?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[employer_job_application_attachment]" value="0" />
                        <p><input type="checkbox" name="noo_email_template[employer_job_application_attachment]" value="1" <?php checked($employer_job_application_attachment,'enable')?>>
                        <small><?php _e( 'Include application attachment in Employer email.', 'noo' ); ?></small></p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[employer_job_application_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($employer_job_application_subject) ? $employer_job_application_subject : ''; ?>">
                        <?php jm_et_render_field('employer_job_application_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($employer_job_application_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[employer_job_application_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('employer_job_application_content', true, false, $application_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php do_action('jm_setting_email_template_employer'); ?>
        </div>
        <!--Candidate Email-->
        <div id="candidate_email" class="email-setting tab-content <?php echo $active_tab == 'candidate_email' ? 'tab-wrapper-active' : 'hidden'; ?>">
            <h3><?php _e('Candidate registration email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_registration_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_registration_activated]" <?php checked( $candidate_registration_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_registration_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_registration_subject) ? $candidate_registration_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_registration_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php

                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_registration_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_registration_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_registration_content', true, false); ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <hr/>
            <h3><?php _e('Candidate job application submitted email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_application_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_application_activated]" <?php checked( $candidate_application_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_application_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_application_subject) ? $candidate_application_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_application_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_application_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_application_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_application_content', true, false, $application_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Candidate job application approved email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_approved_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_approved_activated]" <?php checked( $candidate_approved_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_approved_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_approved_subject) ? $candidate_approved_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_approved_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_approved_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_approved_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_approved_content', true, false, $application_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Candidate job application rejected email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_rejected_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_rejected_activated]" <?php checked( $candidate_rejected_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_rejected_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_rejected_subject) ? $candidate_rejected_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_rejected_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_rejected_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_rejected_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_rejected_content', true, false, $application_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Candidate resume submitted notification email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_resume_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_resume_activated]" <?php checked( $candidate_resume_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_resume_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_resume_subject) ? $candidate_resume_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_resume_subject', false, true); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_resume_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_resume_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_resume_content', true, false, $resume_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Candidate resume approved email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_resume_approved_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_resume_approved_activated]" <?php checked( $candidate_resume_approved_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_resume_approved_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_resume_approved_subject) ? $candidate_resume_approved_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_resume_approved_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_resume_approved_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_resume_approved_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_resume_approved_content', true, false, $resume_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr/>
            <h3><?php _e('Candidate resume rejected email', 'noo'); ?></h3>
            <table class="form-table email-template-setting" cellspacing="0">
                <tbody>
                <tr>
                    <th>
                        <?php esc_html_e('Activate', 'noo') ?>
                    </th>
                    <td>
                        <input type="hidden" name="noo_email_template[candidate_resume_rejected_activated]" value="0" />
                        <input type="checkbox" name="noo_email_template[candidate_resume_rejected_activated]" <?php checked( $candidate_resume_rejected_activated ); ?> value="1" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Subject', 'noo') ?>
                    </th>
                    <td>
                        <input type="text" name="noo_email_template[candidate_resume_rejected_subject]" class="large-text"
                               placeholder="<?php _e('Enter Your Subject', 'noo'); ?>"
                               value="<?php echo !empty($candidate_resume_rejected_subject) ? $candidate_resume_rejected_subject : ''; ?>">
                        <?php jm_et_render_field('candidate_resume_rejected_subject', false, true, false); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php esc_html_e('Content', 'noo') ?>
                    </th>
                    <td>
                        <?php
                        $editor_id = 'textblock' . uniqid();
                        wp_editor($candidate_resume_rejected_content, $editor_id, array(
                            'media_buttons' => false,
                            'quicktags' => true,
                            'textarea_rows' => 10,
                            'textarea_name' => 'noo_email_template[candidate_resume_rejected_content]',
                            'wpautop' => false)); ?>
                        <?php jm_et_render_field('candidate_resume_rejected_content', true, false, $resume_custom_fields); ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php do_action('jm_setting_email_template_candidate'); ?>
        </div>
        <div id="other_email" class="email-setting tab-content <?php echo $active_tab == 'other_email' ? 'tab-wrapper-active' : 'hidden'; ?>">
            <h3><?php _e('Email Configuration', 'noo'); ?></h3>
            <table class="form-table" cellspacing="0">
                <tbody>
                    <tr>
                        <th>
                            <?php _e('From Email','noo')?>
                        </th>
                        <td>
                            <input type="text" name="noo_email_template[from_email]" placeholder="<?php echo noo_mail_do_not_reply(); ?>" size="40" value="<?php echo esc_attr($from_email); ?>">
                            <p><small><?php _e( 'The email address that emails on your site should be sent from. You should leave it blank if you used a 3rd plugin for sending email.', 'noo' ); ?></small></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php _e('From Name','noo')?>
                        </th>
                        <td>
                            <input type="text" name="noo_email_template[from_name]" placeholder="<?php echo get_option('blogname'); ?>" size="40" value="<?php echo esc_attr($from_name); ?>">
                            <p><small><?php _e( 'The name that emails on your site should be sent from. You should leave it blank if you used a 3rd plugin for sending email.', 'noo' ); ?></small></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php do_action('jm_setting_email_template_other'); ?>
        </div>
        <?php
    }

    add_action('noo_job_setting_email_template', 'jm_email_template_settings_form');
endif;

if( !function_exists( 'jm_et_correct_settings') ) :
    function jm_et_correct_settings () {
        if( $et_settings = jm_get_setting('noo_email_template') ) {
            return;
        }

        $et_settings = array( 
            'admin_job_submitted_activated' => 1,
            'admin_resume_activated' => 0,
            'verify_email_activated' => 1,
            'employer_registration_activated' => 1,
            'employer_job_submitted_activated' => 1,
            'employer_job_approved_activated' => 1,
            'employer_job_rejected_activated' => 1,
            'employer_job_application_activated' => 1,
            'employer_job_application_attachment' => 1,
            'candidate_registration_activated' => 1,
            'candidate_application_activated' => 1,
            'candidate_approved_activated' => 1,
            'candidate_rejected_activated' => 1,
            'candidate_resume_activated' => 1,
        );

        if( $noo_email_setting = jm_get_setting( 'noo_email' ) ) {
            $et_settings['admin_job_submitted_activated'] = !$noo_email_setting['noo_notify_job_submitted_admin'];

            $et_settings['employer_registration_activated'] = !$noo_email_setting['noo_notify_register_employer'];
            $et_settings['employer_job_submitted_activated'] = !$noo_email_setting['noo_notify_job_submitted_employer'];
            $et_settings['employer_job_approved_activated'] = !$noo_email_setting['noo_notify_job_review_approve_employer'];
            $et_settings['employer_job_rejected_activated'] = !$noo_email_setting['noo_notify_job_review_reject_employer'];
            $et_settings['employer_job_application_activated'] = !$noo_email_setting['noo_notify_job_apply_employer'];
            $et_settings['employer_job_application_attachment'] = $noo_email_setting['noo_notify_job_apply_attachment'] == 'enable';

            $et_settings['candidate_registration_activated'] = !$noo_email_setting['noo_notify_register_candidate'];
            $et_settings['candidate_application_activated'] = !$noo_email_setting['noo_notify_job_apply_candidate'];
            $et_settings['candidate_approved_activated'] = !$noo_email_setting['noo_notify_job_apply_approve_candidate'];
            $et_settings['candidate_rejected_activated'] = !$noo_email_setting['noo_notify_job_apply_reject_candidate'];
            $et_settings['candidate_resume_activated'] = !$noo_email_setting['noo_notify_resume_submitted_candidate'];

            $et_settings['from_name'] = $noo_email_setting['from_name'];
            $et_settings['from_email'] = $noo_email_setting['from_email'];

            delete_option( 'noo_email' );
        }

        update_option( 'noo_email_template', $et_settings );
    }
endif;