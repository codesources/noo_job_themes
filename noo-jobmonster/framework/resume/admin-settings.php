<?php
if( !function_exists( 'jm_resume_admin_init' ) ) :
	function jm_resume_admin_init(){
		register_setting('noo_resume', 'noo_resume');
		register_setting('noo_resume_general', 'noo_resume_general');
		register_setting('noo_resume_custom_field', 'noo_resume_custom_field');
	}
	
	add_filter('admin_init', 'jm_resume_admin_init' );
endif;

if( !function_exists( 'jm_resume_settings_tabs' ) ) :
	function jm_resume_settings_tabs( $tabs = array() ) {
		$temp1 = array_slice($tabs, 0, 1);
		$temp2 = array_slice($tabs, 1);

		$resume_tab = array( 'resume' => __('Resumes','noo') );
		return array_merge($temp1, $resume_tab, $temp2);
	}
	
	add_filter('noo_job_settings_tabs_array', 'jm_resume_settings_tabs', 11 );
endif;

if( !function_exists( 'jm_resume_setting_form' ) ) :
	function jm_resume_setting_form(){
		if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
			flush_rewrite_rules();
		}
		$customizer_resume_link = esc_url( add_query_arg( array('autofocus%5Bsection%5D' => 'noo_customizer_section_resume'), admin_url( '/customize.php' ) ) );
		
		// Sample setting fields
		$fields = array(
			array(
				'id' => 'enable_resume',
				'label' => __( 'Enable Resumes', 'noo' ),
				'type' => 'checkbox',
				'default' => '1',
				'child_fields' => array( 'on' => 'archive_slug,resume_display,max_viewable_resumes,can_view_resume,enable_upload_resume,extensions_upload_resume,enable_education,enable_experience,enable_skill' )
			),
			array(
				'id' => 'archive_slug',
				'label' => __( 'Resume Archive base (slug)', 'noo' ),
				'type' => 'text',
				'default' => 'resumes'
			),
			array(
				'id' => 'resume_display',
				'label' => __( 'Resume Display', 'noo' ),
				'type' => 'label',
				'default' => sprintf( __('Go to <a href="%s">Customizer</a> to change settings for Resume(s) layout or displayed sections.','noo'), $customizer_resume_link )
			),
			array(
				'id' => 'max_viewable_resumes',
				'label' => __( 'Max Viewable Resume', 'noo' ),
				'desc' => __( 'The maximum number of resumes each Candidate can set to be viewable ( and searchable too ). This number helps prevent candidates from posting multiple resumes just to gain possibility of being viewed.<br/> Set 0 to make all resumes not viewable ( you still can use resumes for job applying ), and -1 to make all resumes viewable ( disable this function ).', 'noo' ),
				'type' => 'text',
				'default' => '1'
			),
		);

		noo_render_setting_form( apply_filters( 'jm_resume_setting_display_fields', $fields ), 'noo_resume_general', __('Resume Displaying', 'noo') );
		echo '<hr/>';

		$fields = array(
			array(
				'id' => 'resume_approve',
				'label' => __( 'Resume Approval', 'noo' ),
				'desc' => __('Each newly submitted resume needs the manual approval of Admin.','noo'),
				'type' => 'checkbox',
				'default' => ''
			),
			array(
				'id' => 'enable_upload_resume',
				'label' => __( 'Enable Upload CV', 'noo' ),
				'desc' => '',
				'type' => 'checkbox',
				'default' => '1'
			),
			array(
				'id' => 'extensions_upload_resume',
				'label' => __( 'Allowed Upload File Types', 'noo' ),
				'desc' => __( 'File types that are allowed for uploading to CV. Default only allows Word and PDF files', 'noo' ),
				'type' => 'text',
				'default' => 'doc,docx,pdf'
			),
			array(
				'id' => 'enable_education',
				'label' => __( 'Enable Education', 'noo' ),
				'desc' => '',
				'type' => 'checkbox',
				'default' => '1'
			),
			array(
				'id' => 'enable_experience',
				'label' => __( 'Enable Experience', 'noo' ),
				'desc' => '',
				'type' => 'checkbox',
				'default' => '1'
			),
			array(
				'id' => 'enable_skill',
				'label' => __( 'Enable Skill', 'noo' ),
				'desc' => '',
				'type' => 'checkbox',
				'default' => '1'
			),
			array(
				'id' => 'enable_print_resume',
				'label' => __( 'Enable Print Resume', 'noo' ),
				'desc' => '',
				'type' => 'checkbox',
				'default' => '0',
			),
		);

		noo_render_setting_form( $fields, 'noo_resume_general', __('Resume Posting', 'noo') );
	}

	add_action('noo_job_setting_resume', 'jm_resume_setting_form');
endif;
