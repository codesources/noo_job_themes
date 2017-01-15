<?php

//et: email template
if (!function_exists('jm_et_default_field')) :
    function jm_et_default_field()
    {
        $et_default_field = array(
            '[site_name]' => __('inserting your site name', 'noo'),
            '[site_url]' => __('inserting your site URL', 'noo')
        );
        return apply_filters('jm_email_template_default_field', $et_default_field);
    }
endif;

                $message = __('%1$s has just posted a job:<br/><br/>
                <a href="%2$s">View Job Detail</a>.
                <br/><br/>
                Best regards,<br/>
                %3$s','noo');


// Fields

if (!function_exists('jm_email_template_field')) :
    function jm_email_template_field()
    {
        $email_template = array(
            'admin_job_submitted_subject_default' => __('[site_name] New job posted: [job_title]', 'noo'),
            'admin_job_submitted_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
            ),
            'admin_job_submitted_content_default' => __('[job_company] has just submitted a job:
<br/></br>
<a href="[job_url]">View Job</a>.
<br/><br/>
Best regards,<br/>
[site_name]', 'noo'),
            'admin_job_submitted_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
            ),
            'admin_resume_subject_default' => __('[site_name] New resume posted by [candidate_name]', 'noo'),
            'admin_resume_subject' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
            ),
            'admin_resume_content_default' => __('[candidate_name] has just submitted a resume:
<br/></br>
<a href="[resume_url]">View Resume</a>.
<br/><br/>
Best regards,<br/>
[site_name]', 'noo'),
            'admin_resume_content' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
                '[resume_url]' => __('inserting resume URL', 'noo'),
                '[resume_category]' => __('inserting resume category', 'noo'),
                '[resume_location]' => __('inserting resume location', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo')
            ),

            'employer_registration_subject_default' => __('Congratulation! You\'ve successfully created an account on [[site_name]]', 'noo'),
            'employer_registration_subject' => array(
                '[user_name]' => __('inserting username', 'noo'),
                '[user_email]' => __('inserting user email', 'noo'),
                '[user_registered]' => __('inserting user registered time', 'noo'),
            ),
            'employer_registration_content_default' => __('Dear [user_name],<br/>
Thank you for registering an account on [site_name] as an employer. You can start posting jobs or search for your potential candidates now.
<br/><br/>
Best regards,<br/>
[site_name]', 'noo'),
            'employer_registration_content' => array(
                '[user_name]' => __('inserting username', 'noo'),
                '[user_email]' => __('inserting user email', 'noo'),
                '[user_registered]' => __('inserting user registered time', 'noo'),
            ),

            'employer_job_submitted_subject_default' => __('[[site_name]] You\'ve successfully posted a job [job_title]', 'noo'),
            'employer_job_submitted_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
            ),
            'employer_job_submitted_content_default' => __('Hi [job_company],<br/><br/>
				You\'ve successfully post a new job:<br/>
				<a href="[job_url]">View Job Detail</a>.
				<br/><br/>
				You can manage your jobs in <a href="[job_manage_url]">Manage Jobs</a><br/><br/>
				Best regards,<br/>
				[site_name]','noo'),
            'employer_job_submitted_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[job_manage_url]' => __('inserting job manage url', 'noo'),
            ),

            'employer_job_approved_subject_default' => __('[[site_name]] Your job [job_title] has been approved and published','noo'),
            'employer_job_approved_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
            ),
            'employer_job_approved_content_default' => __('Hi [job_company],<br/><br/>
Your submitted job [job_title] has been approved and published now on [site_name]:<br/>
<a href="[job_url]">View Job Detail</a>.
<br/><br/>
You can manage your jobs in <a href="[job_manage_url]">Manage Jobs</a><br/><br/>
Best regards,<br/>
[site_name]','noo'),
            'employer_job_approved_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[job_manage_url]' => __('inserting job manage url', 'noo'),
            ),

            'employer_job_rejected_subject_default' => __('[[site_name]] Your job [job_title] can\'t be published','noo'),
            'employer_job_rejected_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
            ),
            'employer_job_rejected_content_default' => __('Hi [job_company],<br/><br/>
Your submitted job [job_title] can not be published and has been deleted. You will have to submit another job.
<br/><br/>
You can manage your jobs in <a href="[job_manage_url]">Manage Jobs</a><br/><br/>
Best regards,<br/>
[site_name]','noo'),
            'employer_job_rejected_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[job_manage_url]' => __('inserting job manage url', 'noo'),
            ),

            'employer_job_application_subject_default' => __('[[site_name]] [candidate_name] applied for [job_title]','noo'),
            'employer_job_application_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
            ),
            'employer_job_application_content_default' => __( 'Hi [job_company],<br/>
				<br/>
				[candidate_name]\'ve just applied for [job_title].<br/>
				<a href="[resume_url]">View Resume</a><br/>
				You can manage applications for your jobs in <a href="[application_manage_url]">Manage Application</a>.
				<br/><br/>
				Best regards,<br/>
				[site_name]','noo'),
            'employer_job_application_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[resume_url]' => __('inserting resume Url', 'noo'),
                '[application_manage_url]' => __('inserting application manage Url', 'noo'),
            ),

            'candidate_registration_subject_default' => __('Congratulation! You\'ve successfully created an account on [[site_name]]', 'noo'),
            'candidate_registration_subject' => array(
                '[user_name]' => __('inserting username', 'noo'),
                '[user_email]' => __('inserting user email', 'noo'),
                '[user_registered]' => __('inserting user registered time', 'noo'),
            ),
            'candidate_registration_content_default' => __('Dear [user_name],<br/>
Thank you for registering an account on [site_name] as an employer. You can start posting jobs or search for your potential candidates now.
<br/><br/>
Best regards,<br/>
[site_name]', 'noo'),
            'candidate_registration_content' => array(
                '[user_name]' => __('inserting username', 'noo'),
                '[user_email]' => __('inserting user email', 'noo'),
                '[user_registered]' => __('inserting user registered time', 'noo'),
            ),

            'candidate_application_subject_default' => __('You have successfully applied for [job_title]','noo'),
            'candidate_application_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
            ),
            'candidate_application_content_default' => __( 'Congratulation [candidate_name],<br/><br/>
You\'ve successfully applied for [job_title].<br/>
<a href="[job_url]">View Job Detail</a><br/>
You can manage and follow status of your applied jobs and applications in <a href="[application_manage_url]">My Applications</a>.
<br/><br/>
Note: Due to high application volume, employers may not be able to respond to all the application.
<br/><br/>
Good luck on your future career path!
<br/><br/>
Best regards,<br/>
[site_name]','noo'),
            'candidate_application_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[resume_url]' => __('inserting resume Url', 'noo'),
                '[application_manage_url]' => __('inserting application manage Url', 'noo'),
            ),

            'candidate_approved_subject_default' => __('You have successfully applied for [job_title]','noo'),
            'candidate_approved_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[responded_title]' => __('inserting approved apply title responded message', 'noo'),
            ),
            'candidate_approved_content_default' => __( 'Hi [candidate_name],<br/>
[job_company] has just responded to your application for job  <a href="[job_url]">[job_title]</a> with message: 
<br/>
<div style="font-style: italic;">
%5$s
</div>
<br/>
You can manage your applications in <a href="[application_manage_url]">Manage Application</a>.
<br/>
Best regards,<br/>
[site_name]','noo'),
            'candidate_approved_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[application_manage_url]' => __('inserting application manage Url', 'noo'),
                '[responded]' => __('inserting approved apply responded message', 'noo'),
                '[responded_title]' => __('inserting approved apply title responded message', 'noo'),
            ),

            'candidate_rejected_subject_default' => __('[job_company] has responded to your application','noo'),
            'candidate_rejected_subject' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[responded_title]' => __('inserting approved apply title responded message', 'noo'),
            ),
            'candidate_rejected_content_default' => __( 'Hi [candidate_name],<br/>
[job_company] has just responded to your application for job  <a href="[job_url]">[job_title]</a> with message: 
<br/>
<div style="font-style: italic;">
[responded]
</div>
<br/>
You can manage your applications in <a href="[application_manage_url]">Manage Application</a>.
<br/>
Best regards,<br/>
[site_name]','noo'),
            'candidate_rejected_content' => array(
                '[job_title]' => __('inserting job title', 'noo'),
                '[job_url]' => __('inserting job URL', 'noo'),
                '[job_company]' => __('inserting job company name', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[application_manage_url]' => __('inserting application manage Url', 'noo'),
                '[responded]' => __('inserting approved apply responded message', 'noo'),
                '[responded_title]' => __('inserting approved apply title responded message', 'noo'),
            ),


            'candidate_resume_subject_default' => __('You\'ve posted a resume: [resume_title]','noo'),
            'candidate_resume_subject' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
            ),
            'candidate_resume_content_default' => __( 'Hi [candidate_name],
<br/><br/>
You\'ve posted a new resume:<br/>
Title: [resume_title]<br/>
Location: [resume_category]<br/>
Category: [resume_location]<br/>
<br/><br/>
You can manage your resumes in <a href="[resume_manage_url]">Manage Resume</a>.
<br/><br/>
Best regards,<br/>
[site_name]','noo'),
            'candidate_resume_content' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
                '[resume_url]' => __('inserting resume URL', 'noo'),
                '[resume_category]' => __('inserting resume category', 'noo'),
                '[resume_location]' => __('inserting resume location', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[resume_manage_url]' => __('inserting application manage Url', 'noo'),
            ),

            'candidate_resume_approved_subject_default' => __('You\'ve posted a resume: [resume_title]','noo'),
            'candidate_resume_approved_subject' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
            ),
            'candidate_resume_approved_content_default' => __( 'Hi [candidate_name],
<br/><br/>
Your submitted resume [resume_title] has been approved and published now on [site_name]:<br/>
<a href="[resume_url]">View Resume Detail</a>.
<br/><br/>
You can manage your resumes in <a href="[resume_manage_url]">Manage Resumes</a><br/><br/>
Best regards,<br/>
[site_name]','noo'),
            'candidate_resume_approved_content' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
                '[resume_url]' => __('inserting resume URL', 'noo'),
                '[resume_category]' => __('inserting resume category', 'noo'),
                '[resume_location]' => __('inserting resume location', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[resume_manage_url]' => __('inserting application manage Url', 'noo'),
            ),

            'candidate_resume_rejected_subject_default' => __('You\'ve posted a resume: [resume_title]','noo'),
            'candidate_resume_rejected_subject' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
            ),
            'candidate_resume_rejected_content_default' => __( 'Hi [candidate_name],
<br/><br/>
Your submitted resume [resume_title] can not be published and has been deleted. You will have to submit another resume.
<br/><br/>
You can manage your resumes in <a href="[resume_manage_url]">Manage Resumes</a><br/><br/>
Best regards,<br/>
[site_name]','noo'),
            'candidate_resume_rejected_content' => array(
                '[resume_title]' => __('inserting resume title', 'noo'),
                '[resume_url]' => __('inserting resume URL', 'noo'),
                '[resume_category]' => __('inserting resume category', 'noo'),
                '[resume_location]' => __('inserting resume location', 'noo'),
                '[candidate_name]' => __('inserting candidate name', 'noo'),
                '[resume_manage_url]' => __('inserting application manage Url', 'noo'),
            ),

        );
        return apply_filters('jm_email_template_field', $email_template);
    }
endif;

//function

function jm_et_get_field($field_name)
{
    $fields = jm_email_template_field();
    return $fields[$field_name];
}

function jm_et_get_list_field($group)
{
    $fields = jm_email_template_field();
    $fields = $fields[$group];

    $default_field = jm_et_default_field();
    return array_keys(array_merge($fields, $default_field));
}

function jm_et_get_default_value($field_name)
{
    $fields = jm_email_template_field();
    return $fields[$field_name . '_default'];
}

function jm_et_render_default_field()
{
    $fields = jm_et_default_field();
    foreach ($fields as $k => $v) {
        echo '<p class="description"><code>' . $k . '</code> - ' . $v . ' </p>';
    }
}


function jm_et_render_field($field, $placeholders = false, $show_default = false, $custom_fields= false)
{
    $fields = jm_email_template_field();
    echo '<div class="wpbc-help-message" style="margin-top:10px;">';

   // show default value des

    if ($show_default) {
        $value_default = $fields[$field . '_default'];
        echo '<p class="description"><strong>' . __('Default content', 'noo') . '</strong> <code>' . $value_default . '</code></p>';
    }

    if( $placeholders ) {
        echo '<p class="description"><strong>' . __('You can use the following placeholders in content of this email:', 'noo') . '</strong></p>';

        $fields = isset( $fields[$field] ) ? $fields[$field] : array();
        foreach ($fields as $k => $v) {
            echo '<p class="description"><code>' . $k . '</code> - ' . $v . ' </p>';
        }
        jm_et_render_default_field();
    }

    if(!empty($custom_fields)) {
        echo '<p class="description"><strong>' . __('You can also use the custom fields:', 'noo') . '</strong></p>';
        if( is_array( $custom_fields ) ) {
            echo '<p class="description">';
            foreach ($custom_fields as $field) {
                echo '<code>[' . $field['name'] . ']</code>';
            }
            echo '</p>';
        }
    }
    echo '</div>';
}