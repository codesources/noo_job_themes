<?php

if( !function_exists( 'jm_admin_job_updated_messages' ) ) :
	function jm_admin_job_updated_messages($messages){
		global $post, $post_ID, $wp_post_types;
		
		$messages['noo_job'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( '%s updated. <a href="%s">View</a>', 'noo' ), $wp_post_types['noo_job']->labels->singular_name, esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'noo' ),
			3 => __( 'Custom field deleted.', 'noo' ),
			4 => sprintf( __( '%s updated.', 'noo' ), $wp_post_types['noo_job']->labels->singular_name ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'noo' ), $wp_post_types['noo_job']->labels->singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%s published. <a href="%s">View</a>', 'noo' ), $wp_post_types['noo_job']->labels->singular_name, esc_url( get_permalink( $post_ID ) ) ),
			7 => sprintf( __( '%s saved.', 'noo' ), $wp_post_types['noo_job']->labels->singular_name ),
			8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview</a>', 'noo' ), $wp_post_types['noo_job']->labels->singular_name, esc_url( add_query_arg( 'job_id', $post_ID, Noo_Member::get_endpoint_url('preview-job') ) ) ),
			// 8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview</a>', 'noo' ), $wp_post_types['noo_job']->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview</a>', 'noo' ), $wp_post_types['noo_job']->labels->singular_name,
				date_i18n( __( 'M j, Y @ G:i', 'noo' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview</a>', 'noo' ), $wp_post_types['noo_job']->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		); 
		
		return $messages;
	}
	add_filter( 'post_updated_messages', 'jm_admin_job_updated_messages' );
endif;

if( !function_exists( 'jm_admin_job_edit_title_placeholder' ) ) :
	function jm_admin_job_edit_title_placeholder($text, $post){
		if ( $post->post_type == 'noo_job' )
			return __( 'Job Title', 'noo' );
		return $text;
	}
	add_filter( 'enter_title_here', 'jm_admin_job_edit_title_placeholder', 10, 2 );
endif;

if( !function_exists( 'jm_extend_job_status' ) ) :
	function jm_extend_job_status(){
		global $post, $post_type;
		if($post_type === 'noo_job'){
			$html = $selected_label = '';
			foreach ((array) jm_get_job_status() as $status=>$label){
				$seleced = selected($post->post_status,esc_attr($status),false);
				if($seleced)
					$selected_label = $label;
				$html .= "<option ".$seleced." value='".esc_attr($status)."'>".$label."</option>";
			}  
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function($) {
					<?php if ( ! empty( $selected_label ) ) : ?>
						jQuery( '#post-status-display' ).html( '<?php echo esc_js( $selected_label ); ?>' );
					<?php endif; ?>
					var select = jQuery( '#post-status-select' ).find( 'select' );
					jQuery( select ).html( "<?php echo ($html); ?>" );
				} );
			</script>
			<?php
		}
	}

	foreach ( array( 'post', 'post-new' ) as $hook ) {
		add_action( "admin_footer-{$hook}.php", 'jm_extend_job_status' );
	}
endif;

if( !function_exists( 'jm_job_meta_boxes' ) ) :
	function jm_job_meta_boxes() {
		$helper = new NOO_Meta_Boxes_Helper( '', array( 'page' => 'noo_job' ) );

		// $args = array(
		// 	'post_type'			  => 'noo_company',
		// 	'post_status'         => 'publish',
		// 	'posts_per_page'	  => -1,
		// 	'orderby'			  => 'title',
		// 	'order'				  => 'ASC',
		// 	'suppress_filters'	  => false
		// );
		
		// $companies = get_posts( $args );
		// $company_option_arr = array( array( 'value' => '', 'label' => '' ) );
		// foreach ($companies as $company) {
		// 	if( !empty( $company->post_title ) ) {
		// 		$company_option_arr[] = array(
		// 			'value' => $company->ID,
		// 			'label' => $company->post_title
		// 		);
		// 	}
		// }

		$meta_box = array( 
			'id' => "job_settings", 
			'title' => __( 'Job Settings', 'noo' ), 
			'page' => 'noo_job', 
			'context' => 'normal', 
			'priority' => 'high', 
			'fields' => array( 
				array( 'id' => '_cover_image', 'label' => __( 'Cover Image', 'noo' ), 'type' => 'image' ), 
				array( 
					'id' => '_application_email', 
					'label' => __( 'Notification Email', 'noo' ), 
					'type' => 'text',
					'desc'=>__( 'Email to receive application notification. Leave it blank to use Employer\'s profile email.', 'noo' )
				),
				array( 'id' => 'author', 'label' => __( 'Posted by ( Employer - Company )', 'noo' ), 'type' => 'author','callback' => 'jm_meta_box_field_job_author' ),
				array(
					'id' => '_company_id',
					'label' => __( 'Company', 'noo' ),
					'type' => 'company',
					'callback' => 'jm_meta_box_field_company',
					'desc'=>__( 'Use this option when you want to assign job to company without creating an employer.', 'noo' )
				),
				// array(
				// 	'id' => '_company_id',
				// 	'label' => __( 'Company', 'noo' ),
				// 	'type' => 'select',
				// 	'desc'=>__( 'Use this option when you want to assign job to company without creating an employer.', 'noo' ),
				// 	'options' => $company_option_arr
				// ), 
				// array(
				// 	'id' => '_closing',
				// 	'label' => __( 'Job Closing Date', 'noo' ),
				// 	'type' => 'datepicker',
				// ),
				array(
					'id' => '_expires',
					'label' => __( 'Job\'s Expiration Date', 'noo' ),
					'type' => 'datepicker',
				),
			)
		);

		$custom_apply_link = jm_get_setting('noo_job_linkedin', 'custom_apply_link' );
		if( !empty( $custom_apply_link ) ) {
			$meta_box['fields'][] = array(
				'id' => '_custom_application_url', 
				'label' => __( 'Custom Application link', 'noo' ), 
				'type' => 'text',
				'desc'=>__('Job seekers will be redirected to this URL when they want to apply for this job.','noo')
			);
		}
		
		$helper->add_meta_box($meta_box);

		$fields = jm_get_job_custom_fields();
		if($fields){
			foreach ($fields as $field){
				if( isset( $field['is_tax'] ) )
					continue;

				$id = jm_job_custom_fields_name($field['name'], $field);

				$new_field = noo_custom_field_to_meta_box( $field, $id );

				$meta_box['fields'][] = $new_field;
			}
		}

		$helper->add_meta_box($meta_box);
	}

	add_action( 'add_meta_boxes', 'jm_job_meta_boxes', 30 );

endif;

if( !function_exists( 'jm_meta_box_field_company' ) ) :
	function jm_meta_box_field_company( $post, $id, $type, $meta, $std, $field){
		$args = array(
			'post_type'			  => 'noo_company',
			'post_status'         => 'publish',
			'posts_per_page'	  => -1,
			'orderby'			  => 'title',
			'order'				  => 'ASC',
			'suppress_filters'	  => false
		);
		
		$companies = get_posts( $args );
		$company_option_arr = array( array( 'value' => '', 'label' => '' ) );
		foreach ($companies as $company) {
			if( !empty( $company->post_title ) ) {
				$company_option_arr[] = array(
					'value' => $company->ID,
					'label' => $company->post_title
				);
			}
		}

		$company_id = noo_get_post_meta( $post->ID, '_company_id', '' );

		echo '<select id='.$id.' name="noo_meta_boxes['.$id.']" class="noo-admin-chosen' . ( is_rtl() ? ' chosen-rtl' : '' ) . '" data-placeholder="' . __('- Select a Company - ', 'noo') . '">';
		echo '	<option value=""></option>';
		foreach ( $companies as $company ) {
			echo'<option value="' . $company->ID . '"';
			selected( $company_id, $company->ID, true );
			echo '>' . $company->post_title;
			echo '</option>';
		}
		echo '</select>';
	}
endif;

if( !function_exists( 'jm_meta_box_field_job_author' ) ) :
	function jm_meta_box_field_job_author( $post, $id, $type, $meta, $std, $field){

		// $meta = !empty($meta) ? $meta : $std;
		$user_list = jm_get_members( Noo_Member::EMPLOYER_ROLE );
		$admin_list = jm_get_members( 'administrator' );
		$user_list = array_merge($admin_list, $user_list);

		echo'<select name="post_author_override" id="post_author_override" class="noo-admin-chosen' . ( is_rtl() ? ' chosen-rtl' : '' ) . '" data-placeholder="' . __('- Select an Employer - ', 'noo') . '">';
		echo'	<option value=""></option>';
		foreach ( $user_list as $user ) {
			$company_id = jm_get_employer_company($user->ID);
			echo'<option value="' . $user->ID . '"';
			selected( $post->post_author, $user->ID, true );
			echo '>' . $user->display_name;
			if( !empty($company_id) ) {
				$company_name = get_the_title( $company_id );
				echo ( !empty($company_name) ? ' - ' . $company_name : '' );
			}
			echo '</option>';
		}
		echo '</select>';
	}
endif;

if( !function_exists( 'jm_job_save_meta_box' ) ) :
	function jm_job_save_meta_box( $post_id ) {
		$meta_box = $_POST['noo_meta_boxes'];
		if( ( !isset($meta_box['_company_id']) || empty($meta_box['_company_id']) ) && !defined( 'ICL_SITEPRESS_VERSION' ) ) {
			// don't auto save company when there's WPML to prevent error with post duplicate function

			$employer_id = get_post_field( 'post_author', $post_id );
			$company_id = jm_get_employer_company( $employer_id );

			if( $company_id ) {
				update_post_meta( $post_id, '_company_id', $company_id );
			}
		}
	}
	
	add_action( 'noo_save_meta_box', 'jm_job_save_meta_box' );
endif;


if( !function_exists( 'jm_wpml_duplicate_job_company_field' ) ) :
	function jm_wpml_duplicate_job_company_field( $master_post_id, $lang, $post_array, $id )  {
		if( empty( $id ) || empty( $master_post_id ) ) return false;
		if( $post_array['post_type'] == 'noo_job' ) {
			$company_id = get_post_meta( $master_post_id, '_company_id', true );

			if( !empty( $company_id ) ) {
				$company_id = apply_filters( 'wpml_object_id', $company_id, 'noo_company', true, $lang );

				update_post_meta( $id, '_company_id', $company_id );
			}
		}
	}

	add_action( 'icl_make_duplicate', 'jm_wpml_duplicate_job_company_field', 10, 4 );
endif;