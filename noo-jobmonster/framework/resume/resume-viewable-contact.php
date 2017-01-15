<?php
if( !function_exists('jm_is_enabled_job_package_view_candidate_contact') ) :
	function jm_is_enabled_job_package_view_candidate_contact() {
		return 'package' == jm_get_action_control( 'view_candidate_contact' );
	}
endif;

if( !function_exists('jm_can_view_candidate_contact') ) :
	function jm_can_view_candidate_contact( $resume_id = null ) {
		if( jm_is_resume_posting_page() ) {
			return true;
		}
		if( empty( $resume_id ) ) {
			return false;
		}
		
		$can_view_candidate_contact_setting = jm_get_action_control('view_candidate_contact');
		if( empty( $can_view_candidate_contact_setting ) ) return true;

		// Resume's author can view his/her contact
		$candidate_id = get_post_field( 'post_author', $resume_id );
		if( $candidate_id == get_current_user_id() ) {
			return true;
		}

		if( isset($_GET['application_id'] ) && !empty($_GET['application_id']) ) {
			// Employers can view candidate contact from their applications

			$job_id = get_post_field( 'post_parent', $_GET['application_id'] );
			
			$employer_id = get_post_field( 'post_author', $job_id );
			if( $employer_id == get_current_user_id() ) {
				if( $resume_id == noo_get_post_meta( $_GET['application_id'], '_resume', '' ) ) {
					return true;
				}
			}
		}

		$can_view_candidate_contact = true;
		switch( $can_view_candidate_contact_setting ) {
			case 'noone':
				$can_view_candidate_contact = false;
				break;
			case 'employer':
				$can_view_candidate_contact = Noo_Member::is_employer();
				break;
			case 'package':
				$can_view_candidate_contact = false;
				$package = jm_get_job_posting_info();
				if( Noo_Member::is_employer() ) {
					$can_view_candidate_contact = isset( $package['can_view_candidate_contact'] ) && $package['can_view_candidate_contact'] == '1';
				}
				break;
			default:
				$can_view_candidate_contact = true;
				break;
		}

		return apply_filters( 'jm_can_view_candidate_contact', $can_view_candidate_contact, $resume_id );
	};
endif;

if( !function_exists('jm_resume_is_show_candidate_contact') ) :
	function jm_resume_is_show_candidate_contact( $show_contact = true, $resume_id = '' ) {
		return jm_can_view_candidate_contact( $resume_id );
	};

	add_filter( 'jm_resume_show_candidate_contact', 'jm_resume_is_show_candidate_contact', 10, 2 );
endif;

if( !function_exists('jm_job_package_view_candidate_contact_data') ) :
	function jm_job_package_view_candidate_contact_data() {
		global $post;
		if( jm_is_enabled_job_package_view_candidate_contact() ) {
			woocommerce_wp_checkbox(
				array(
					'id' => '_can_view_candidate_contact',
					'label' => __( 'Can view Candidate Contact', 'noo' ),
					'description' => __( 'Allowing buyers to see Candidate Contact.', 'noo' ),
					'cbvalue' => 1,
					'desc_tip' => false,) );
		}
	}

	add_action( 'noo_job_package_data', 'jm_job_package_view_candidate_contact_data' );
endif;

if( !function_exists('jm_job_package_save_view_candidate_contact_data') ) :
	function jm_job_package_save_view_candidate_contact_data($post_id) {
		if( jm_is_enabled_job_package_view_candidate_contact() ) {
			// Save meta
			$fields = array(
				'_can_view_candidate_contact'  => '',
			);
			foreach ( $fields as $key => $value ) {
				$value = ! empty( $_POST[ $key ] ) ? $_POST[ $key ] : '';
				switch ( $value ) {
					case 'int' :
						$value = intval( $value );
						break;
					case 'float' :
						$value = floatval( $value );
						break;
					default :
						$value = sanitize_text_field( $value );
				}
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	add_action( 'noo_job_package_save_data', 'jm_job_package_save_view_candidate_contact_data' );
endif;

if( !function_exists('jm_job_package_view_candidate_contact_user_data') ) :
	function jm_job_package_view_candidate_contact_user_data( $data, $product ) {
		if( jm_is_enabled_job_package_view_candidate_contact() && is_object( $product ) ) {
			$data['can_view_candidate_contact'] = $product->can_view_candidate_contact;
		}

		return $data;
	}

	add_filter( 'jm_job_package_user_data', 'jm_job_package_view_candidate_contact_user_data', 10, 2 );
endif;

if( !function_exists('jm_job_package_view_candidate_contact_features') ) :
	function jm_job_package_view_candidate_contact_features( $product ) {
		if( jm_is_enabled_job_package_view_candidate_contact() && $product->can_view_candidate_contact == '1' ) : ?>
			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php _e('Allow viewing Candidate Contact','noo');?></li>
    	<?php endif;
	}

	add_action( 'jm_job_package_features_list', 'jm_job_package_view_candidate_contact_features' );
endif;

if( !function_exists('jm_manage_plan_view_candidate_contact_features') ) :
	function jm_manage_plan_view_candidate_contact_features( $package ) {
		if(is_array( $package ) && isset( $package['product_id'] ) && !empty( $package['product_id'] ) ) {
			$product = wc_get_product( absint( $package['product_id'] ) );
			
			if( $product && $product->product_type === 'job_package' ) {
				if( jm_is_enabled_job_package_view_candidate_contact() && $product->can_view_candidate_contact == '1' ) : ?>
					<div class="col-xs-6"><strong><?php _e('View Candidate Contact','noo')?></strong></div>
					<div class="col-xs-6"><?php _e('Yes','noo')?></div>
		    	<?php endif;
			}
		}
	}

	add_action( 'jm_manage_plan_features_list', 'jm_manage_plan_view_candidate_contact_features' );
endif;