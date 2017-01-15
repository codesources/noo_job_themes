<?php
if( !function_exists('jm_is_enabled_job_package_view_resume') ) :
	function jm_is_enabled_job_package_view_resume() {
		return 'package' == jm_get_action_control( 'view_resume' );
	}
endif;

if( !function_exists('jm_job_package_view_resume_data') ) :
	function jm_job_package_view_resume_data() {
		global $post;
		if( jm_is_enabled_job_package_view_resume() ) {
			woocommerce_wp_checkbox(
				array(
					'id' => '_can_view_resume',
					'label' => __( 'Can view Resume', 'noo' ),
					'description' => __( 'Allow buyers to access resumes.', 'noo' ),
					'cbvalue' => 1,
					'desc_tip' => false,) );

			$disable_field = get_post_meta( $post->ID, '_can_view_resume', true ) === '1' ? '' : 'disabled';
			woocommerce_wp_text_input(
				array(
					'id' => '_resume_view_limit',
					'label' => __( 'Resume view limit', 'noo' ),
					'description' => __( 'The maximum number of resumes this package allows employers to view, input -1 for unlimited.', 'noo' ),
					'placeholder' => '', 
					'type' => 'number', 
					'value' => get_post_meta( $post->ID, '_resume_view_limit', true ),
					'desc_tip' => true,
					'custom_attributes' => array( 'min' => '', 'step' => '1', $disable_field => $disable_field )
				)
			);
			?>
			<script type="text/javascript">
				jQuery('.pricing').addClass( 'show_if_job_package' );
				jQuery(document).ready(function($) {
					$("#_can_view_resume").change(function() {
						if(this.checked) {
							$('#_resume_view_limit').prop('disabled', false);
						} else {
							$('#_resume_view_limit').prop('disabled', true);
						}
					});
				});
			</script>
			<?php
		}
	}

	add_action( 'noo_job_package_data', 'jm_job_package_view_resume_data' );
endif;

if( !function_exists('jm_job_package_save_view_resume_data') ) :
	function jm_job_package_save_view_resume_data($post_id) {
		if( jm_is_enabled_job_package_view_resume() ) {
			// Save meta
			$fields = array(
				'_can_view_resume'		=> '',
				'_resume_view_limit' 	=> 'int',
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

	add_action( 'noo_job_package_save_data', 'jm_job_package_save_view_resume_data' );
endif;


if( !function_exists('jm_job_package_viewed_resume_user_menu') ) :
	function jm_job_package_viewed_resume_user_menu( $product ) {
		if( jm_is_enabled_job_package_view_resume() ) : ?>
			<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class(array( 'viewed-resume' )))?>"><a href="<?php echo Noo_Member::get_endpoint_url('viewed-resume')?>"><i class="fa fa-file-text-o"></i> <?php _e('Viewed Resumes','noo')?></a></li>
    	<?php endif;
	}

	add_action( 'noo-member-employer-menu', 'jm_job_package_viewed_resume_user_menu' );
endif;

if( !function_exists('jm_job_package_viewed_resume_heading') ) :
	function jm_job_package_viewed_resume_heading( $product ) {
		if( jm_is_enabled_job_package_view_resume() ) : ?>
			<li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('viewed-resume'))?>" ><a href="<?php echo Noo_Member::get_endpoint_url('viewed-resume')?>" style="white-space: nowrap;"><i class="fa fa-file-text-o"></i> <?php _e('Viewed Resumes','noo')?></a></li>
    	<?php endif;
	}

	add_action( 'noo-member-employer-heading', 'jm_job_package_viewed_resume_heading' );
endif;

if( !function_exists('jm_job_package_features_view_resume') ) :
	function jm_job_package_features_view_resume( $product ) {
    	if( jm_is_enabled_job_package_view_resume() && $product->can_view_resume == '1' ) : 
    		$resume_view_limit = $product->resume_view_limit;
    	?>
			<?php if( $product->resume_view_limit == -1 ) : ?>
    			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php _e('View Unlimited Resumes','noo');?></li>
    		<?php elseif( $resume_view_limit > 0 ) : ?>
    			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo sprintf( _n( 'View %d resume', 'View %d resumes', $resume_view_limit, 'noo' ), $resume_view_limit ); ?></li>
			<?php endif; ?>
    	<?php endif;
	}

	add_action( 'jm_job_package_features_list', 'jm_job_package_features_view_resume' );
endif;

if( !function_exists('jm_manage_plan_features_view_resume') ) :
	function jm_manage_plan_features_view_resume( $package ) {
		if( Noo_Member::is_employer() && jm_is_enabled_job_package_view_resume() ) :
	    	$resume_view_limit = isset( $package['resume_view_limit'] ) && !empty( $package['resume_view_limit'] ) ? intval( $package['resume_view_limit']) : 0;
	    	$resume_view_remain = jm_get_resume_view_remain();
	    	$resume_view_until = jm_get_resume_view_expire();
	    	if( $resume_view_until == -1 ) {
	    		$resume_view_until = __('Forever', 'noo');
	    	} elseif( is_numeric( $resume_view_until ) ) {
	    		$resume_view_until = $resume_view_until > time() ? date_i18n( get_option('date_format') . ' ' . get_option('time_format'), $resume_view_until ) : '<strong>' . __('Expired', 'noo') . '</strong>';
	    	}
			if( isset( $package['can_view_resume'] ) && $package['can_view_resume'] == '1' ) : ?>
				<?php if( $resume_view_limit ) : ?>
					<div class="col-xs-6"><strong><?php _e('Resume View Limit','noo')?></strong></div>
					<?php if( $resume_view_limit == -1) : ?>
						<div class="col-xs-6"><?php _e('Unlimited','noo');?></div>
		    		<?php elseif( $resume_view_limit > 0 ) : ?>
		    			<div class="col-xs-6"><?php echo sprintf( _n( '%d resume', '%d resumes', $resume_view_limit, 'noo' ), $resume_view_limit ); ?>
		    				<?php if( $resume_view_remain < $resume_view_limit ) echo '&nbsp;' . sprintf( __('( %d remain )', 'noo'), $resume_view_remain ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
				<?php if( $resume_view_until ) : ?>
					<div class="col-xs-6"><strong><?php _e('View Resume Until','noo')?></strong></div>
	    			<div class="col-xs-6"><?php echo $resume_view_until; ?></div>
				<?php endif; ?>
	    	<?php endif;
	    endif;
	}

	add_action( 'jm_manage_plan_features_list', 'jm_manage_plan_features_view_resume' );
endif;

if( !function_exists('jm_get_resume_view_remain') ) :
	function jm_get_resume_view_remain( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		if( jm_is_resume_view_expired( $user_id ) ) return 0;

		$package = jm_get_job_posting_info( $user_id );
		$resume_view_limit = empty( $package ) || !is_array( $package ) || !isset( $package['resume_view_limit'] ) ? 0 : $package['resume_view_limit'];
		if( $resume_view_limit == -1 ) return -1;

		$resume_viewed = jm_get_resume_viewed_count( $user_id );

		return max( absint($resume_view_limit) - absint($resume_viewed), 0 );
	}
endif;

if( !function_exists('jm_get_resume_viewed_count') ) :
	function jm_get_resume_viewed_count( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$resume_viewed = get_user_meta($user_id,'_resume_view_count',true);

		return empty( $resume_viewed ) ? 0 : absint( $resume_viewed );
	}
endif;

if( !function_exists('jm_get_resume_view_expire') ) :
	function jm_get_resume_view_expire( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$resume_view_expire = get_user_meta($user_id,'_resume_view_expire',true);

		if( $resume_view_expire == '-1' ) return -1;

		return empty( $resume_view_expire ) ? 0 : absint( $resume_view_expire );
	}
endif;

if( !function_exists('jm_is_resume_view_expired') ) :
	function jm_is_resume_view_expired( $user_id = '' ) {
		$resume_view_expire = jm_get_resume_view_expire( $user_id );
		return $resume_view_expire != -1 && $resume_view_expire <= time();
	}
endif;

if( !function_exists('jm_get_viewed_resumes') ) :
	function jm_get_viewed_resumes( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$viewed_resumes = get_user_meta($user_id,'_resumes_saved',true);
		$viewed_resumes = !is_array( $viewed_resumes ) || empty( $viewed_resumes ) ? array() : $viewed_resumes;

		return $viewed_resumes;
	}
endif;

if( !function_exists('jm_job_package_view_single_resume') ) :
	function jm_job_package_view_single_resume( $can_view_resume, $resume_id ) {
		$viewed_resumes = jm_get_viewed_resumes();
		if( !$can_view_resume ) {
			if( in_array( $resume_id, $viewed_resumes ) ) {
				$can_view_resume = true;
			}
		} else {
			if( !in_array($resume_id, $viewed_resumes) ) {
				$viewed_resumes[] = $resume_id;
				$user_id = get_current_user_id();
				
				$resume_view_count = jm_get_resume_viewed_count( $user_id );
				update_user_meta( $user_id, '_resume_view_count', $resume_view_count + 1 );
				update_user_meta( $user_id, '_resumes_saved', $viewed_resumes );
			}
		}

		return $can_view_resume;
	}

	add_filter( 'jm_can_view_single_resume', 'jm_job_package_view_single_resume', 10, 2 );
endif;

if( !function_exists('jm_job_package_view_resume_user_data') ) :
	function jm_job_package_view_resume_user_data( $data, $product ) {
		if( jm_is_enabled_job_package_view_resume() && is_object( $product ) ) {
			$data['can_view_resume'] = $product->can_view_resume;
			$data['resume_view_limit'] = $product->resume_view_limit;
		}

		return $data;
	}

	add_filter( 'jm_job_package_user_data', 'jm_job_package_view_resume_user_data', 10, 2 );
endif;

if( !function_exists('jm_job_package_view_resume_order_completed') ) :
	function jm_job_package_view_resume_order_completed( $product, $user_id ) {
		if( jm_is_enabled_job_package_view_resume() && $product->can_view_resume == '1' ) {
			update_user_meta( $user_id, '_resume_view_count', '0' );
			$package = get_user_meta( $user_id, '_job_package', true );
			$resume_view_expire = isset( $package['expired'] ) ? absint( $package['expired'] ) : '-1';
			// $resume_view_expire = ( $product->resume_view_duration > 0 ) ? strtotime('+'.absint($product->resume_view_duration).' day') : '-1';
			update_user_meta( $user_id, '_resume_view_expire', $resume_view_expire );
		}
	}

	add_action( 'jm_job_package_order_completed', 'jm_job_package_view_resume_order_completed', 10, 2 );
endif;
