<?php
if( !function_exists('jm_is_enabled_resume_package_apply_job') ) :
	function jm_is_enabled_resume_package_apply_job() {
		return 'package' == jm_get_action_control( 'apply_job' );
	}
endif;

if( !function_exists('jm_resume_package_apply_job_data') ) :
	function jm_resume_package_apply_job_data() {
		global $post;
		if( jm_is_enabled_resume_package_apply_job() ) {
			woocommerce_wp_checkbox(
				array(
					'id' => '_can_apply_job',
					'label' => __( 'Can apply for Job', 'noo' ),
					'description' => __( 'Allow buyers to apply for jobs.', 'noo' ),
					'cbvalue' => 1,
					'desc_tip' => false,) );

			$disable_field = get_post_meta( $post->ID, '_can_apply_job', true ) === '1' ? '' : 'disabled';
			woocommerce_wp_text_input(
				array(
					'id' => '_job_apply_limit',
					'label' => __( 'Job apply limit', 'noo' ),
					'description' => __( 'The maximum number of jobs this package allows candidates to apply, input -1 for unlimited.', 'noo' ),
					'placeholder' => '', 
					'type' => 'number', 
					'value' => get_post_meta( $post->ID, '_job_apply_limit', true ),
					'desc_tip' => true,
					'custom_attributes' => array( 'min' => '', 'step' => '1', $disable_field => $disable_field )
				)
			);
			?>
			<script type="text/javascript">
				jQuery('.pricing').addClass( 'show_if_resume_package' );
				jQuery(document).ready(function($) {
					$("#_can_apply_job").change(function() {
						if(this.checked) {
							$('#_job_apply_limit').prop('disabled', false);
						} else {
							$('#_job_apply_limit').prop('disabled', true);
						}
					});
				});
			</script>
			<?php
		}
	}

	add_action( 'noo_resume_package_data', 'jm_resume_package_apply_job_data' );
endif;

if( !function_exists('jm_resume_package_save_apply_job_data') ) :
	function jm_resume_package_save_apply_job_data($post_id) {
		if( jm_is_enabled_resume_package_apply_job() ) {
			// Save meta
			$fields = array(
				'_can_apply_job'		=> '',
				'_job_apply_limit' 	=> 'int',
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

	add_action( 'noo_resume_package_save_data', 'jm_resume_package_save_apply_job_data' );
endif;

if( !function_exists('jm_resume_package_apply_job_user_data') ) :
	function jm_resume_package_apply_job_user_data( $data, $product ) {
		if( jm_is_enabled_resume_package_apply_job() && is_object( $product ) ) {
			$data['can_apply_job'] = $product->can_apply_job;
			$data['job_apply_limit'] = $product->job_apply_limit;
		}

		return $data;
	}

	add_filter( 'jm_resume_package_user_data', 'jm_resume_package_apply_job_user_data', 10, 2 );
endif;

if( !function_exists('jm_resume_package_apply_job_order_completed') ) :
	function jm_resume_package_apply_job_order_completed( $product, $user_id ) {
		if( jm_is_enabled_resume_package_apply_job() && $product->can_apply_job == '1' ) {
			update_user_meta( $user_id, '_job_apply_count', '0' );
			$package = get_user_meta( $user_id, '_resume_package', true );
		}
	}

	add_action( 'jm_resume_package_order_completed', 'jm_resume_package_apply_job_order_completed', 10, 2 );
endif;



if( !function_exists('jm_resume_package_features_apply_job') ) :
	function jm_resume_package_features_apply_job( $product ) {
    	if( jm_is_enabled_resume_package_apply_job() && $product->can_apply_job == '1' ) : 
    		$job_apply_limit = $product->job_apply_limit;
    	?>
			<?php if( $product->job_apply_limit == -1 ) : ?>
    			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php _e('Apply for unlimited Jobs','noo');?></li>
    		<?php elseif( $job_apply_limit > 0 ) : ?>
    			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo sprintf( _n( 'Apply for %d job', 'Apply for %d jobs', $job_apply_limit, 'noo' ), $job_apply_limit ); ?></li>
			<?php endif; ?>
    	<?php endif;
	}

	add_action( 'jm_resume_package_features_list', 'jm_resume_package_features_apply_job' );
endif;

if( !function_exists('jm_manage_plan_features_apply_job') ) :
	function jm_manage_plan_features_apply_job( $package ) {
		if( Noo_Member::is_candidate() && jm_is_enabled_resume_package_apply_job() ) :
	    	$job_apply_limit = isset( $package['job_apply_limit'] ) && !empty( $package['job_apply_limit'] ) ? intval( $package['job_apply_limit']) : 0;
	    	$job_apply_remain = jm_get_job_apply_remain();

			if( isset( $package['can_apply_job'] ) && $package['can_apply_job'] == '1' ) : ?>
				<?php if( $job_apply_limit ) : ?>
					<div class="col-xs-6"><strong><?php _e('Job Apply Limit','noo')?></strong></div>
					<?php if( $job_apply_limit == -1) : ?>
						<div class="col-xs-6"><?php _e('Unlimited','noo');?></div>
		    		<?php elseif( $job_apply_limit > 0 ) : ?>
		    			<div class="col-xs-6"><?php echo sprintf( _n( '%d job', '%d jobs', $job_apply_limit, 'noo' ), $job_apply_limit ); ?>
		    				<?php if( $job_apply_remain < $job_apply_limit ) echo '&nbsp;' . sprintf( __('( %d remain )', 'noo'), $job_apply_remain ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
	    	<?php endif;
	    endif;
	}

	add_action( 'jm_manage_plan_features_list', 'jm_manage_plan_features_apply_job' );
endif;

if( !function_exists('jm_get_job_apply_remain') ) :
	function jm_get_job_apply_remain( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$package = jm_get_resume_posting_info( $user_id );
		$job_apply_limit = empty( $package ) || !is_array( $package ) || !isset( $package['job_apply_limit'] ) ? 0 : $package['job_apply_limit'];
		if( $job_apply_limit == -1 ) return -1;

		$job_applied = jm_get_job_applied_count( $user_id );

		return max( absint($job_apply_limit) - absint($job_applied), 0 );
	}
endif;

if( !function_exists('jm_get_job_applied_count') ) :
	function jm_get_job_applied_count( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$job_applied = get_user_meta($user_id,'_job_apply_count',true);

		return empty( $job_applied ) ? 0 : absint( $job_applied );
	}
endif;

if( !function_exists('jm_resume_package_apply_job_count') ) :
	function jm_resume_package_apply_job_count( $application_id ) {
		$user_id = get_current_user_id();
		if( !empty( $user_id ) ) {
			$job_apply_count = jm_get_job_applied_count( $user_id );
			update_user_meta( $user_id, '_job_apply_count', $job_apply_count + 1 );
		}
	}

	add_action( 'new_job_application', 'jm_resume_package_apply_job_count', 10, 2 );
endif;
