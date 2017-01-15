<?php
if( !function_exists('jm_is_enabled_resume_package_view_job') ) :
	function jm_is_enabled_resume_package_view_job() {
		return 'package' == jm_get_action_control( 'view_job' );
	}
endif;

if( !function_exists('jm_resume_package_view_job_data') ) :
	function jm_resume_package_view_job_data() {
		global $post;
		if( jm_is_enabled_resume_package_view_job() ) {
			woocommerce_wp_checkbox(
				array(
					'id' => '_can_view_job',
					'label' => __( 'Can view Job', 'noo' ),
					'description' => __( 'Allow buyers to view jobs detail.', 'noo' ),
					'cbvalue' => 1,
					'desc_tip' => false,) );

			$disable_field = get_post_meta( $post->ID, '_can_view_job', true ) === '1' ? '' : 'disabled';
			woocommerce_wp_text_input(
				array(
					'id' => '_job_view_limit',
					'label' => __( 'Job view limit', 'noo' ),
					'description' => __( 'The maximum number of jobs this package allows candidates to view, input -1 for unlimited.', 'noo' ),
					'placeholder' => '', 
					'type' => 'number', 
					'value' => get_post_meta( $post->ID, '_job_view_limit', true ),
					'desc_tip' => true,
					'custom_attributes' => array( 'min' => '', 'step' => '1', $disable_field => $disable_field )
				)
			);
			?>
			<script type="text/javascript">
				jQuery('.pricing').addClass( 'show_if_resume_package' );
				jQuery(document).ready(function($) {
					$("#_can_view_job").change(function() {
						if(this.checked) {
							$('#_job_view_limit').prop('disabled', false);
						} else {
							$('#_job_view_limit').prop('disabled', true);
						}
					});
				});
			</script>
			<?php
		}
	}

	add_action( 'noo_resume_package_data', 'jm_resume_package_view_job_data' );
endif;

if( !function_exists('jm_resume_package_save_view_job_data') ) :
	function jm_resume_package_save_view_job_data($post_id) {
		if( jm_is_enabled_resume_package_view_job() ) {
			// Save meta
			$fields = array(
				'_can_view_job'		=> '',
				'_job_view_limit' 	=> 'int',
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

	add_action( 'noo_resume_package_save_data', 'jm_resume_package_save_view_job_data' );
endif;

if( !function_exists('jm_resume_package_view_job_user_data') ) :
	function jm_resume_package_view_job_user_data( $data, $product ) {
		if( jm_is_enabled_resume_package_view_job() && is_object( $product ) ) {
			$data['can_view_job'] = $product->can_view_job;
			$data['job_view_limit'] = $product->job_view_limit;
		}

		return $data;
	}

	add_filter( 'jm_resume_package_user_data', 'jm_resume_package_view_job_user_data', 10, 2 );
endif;

if( !function_exists('jm_resume_package_view_job_order_completed') ) :
	function jm_resume_package_view_job_order_completed( $product, $user_id ) {
		if( jm_is_enabled_resume_package_view_job() && $product->can_view_job == '1' ) {
			update_user_meta( $user_id, '_job_view_count', '0' );
			$package = get_user_meta( $user_id, '_resume_package', true );
		}
	}

	add_action( 'jm_resume_package_order_completed', 'jm_resume_package_view_job_order_completed', 10, 2 );
endif;



if( !function_exists('jm_resume_package_features_view_job') ) :
	function jm_resume_package_features_view_job( $product ) {
    	if( jm_is_enabled_resume_package_view_job() && $product->can_view_job == '1' ) : 
    		$job_view_limit = $product->job_view_limit;
    	?>
			<?php if( $product->job_view_limit == -1 ) : ?>
    			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php _e('View Unlimited Resumes','noo');?></li>
    		<?php elseif( $job_view_limit > 0 ) : ?>
    			<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo sprintf( _n( 'View %d job', 'View %d jobs', $job_view_limit, 'noo' ), $job_view_limit ); ?></li>
			<?php endif; ?>
    	<?php endif;
	}

	add_action( 'jm_resume_package_features_list', 'jm_resume_package_features_view_job' );
endif;

if( !function_exists('jm_manage_plan_features_view_job') ) :
	function jm_manage_plan_features_view_job( $package ) {
		if( Noo_Member::is_candidate() && jm_is_enabled_resume_package_view_job() ) :
	    	$job_view_limit = isset( $package['job_view_limit'] ) && !empty( $package['job_view_limit'] ) ? intval( $package['job_view_limit']) : 0;
	    	$job_view_remain = jm_get_job_view_remain();

			if( isset( $package['can_view_job'] ) && $package['can_view_job'] == '1' ) : ?>
				<?php if( $job_view_limit ) : ?>
					<div class="col-xs-6"><strong><?php _e('Job View Limit','noo')?></strong></div>
					<?php if( $job_view_limit == -1) : ?>
						<div class="col-xs-6"><?php _e('Unlimited','noo');?></div>
		    		<?php elseif( $job_view_limit > 0 ) : ?>
		    			<div class="col-xs-6"><?php echo sprintf( _n( '%d job', '%d jobs', $job_view_limit, 'noo' ), $job_view_limit ); ?>
		    				<?php if( $job_view_remain < $job_view_limit ) echo '&nbsp;' . sprintf( __('( %d remain )', 'noo'), $job_view_remain ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
	    	<?php endif;
	    endif;
	}

	add_action( 'jm_manage_plan_features_list', 'jm_manage_plan_features_view_job' );
endif;

if( !function_exists('jm_get_job_view_remain') ) :
	function jm_get_job_view_remain( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$package = jm_get_resume_posting_info( $user_id );
		$job_view_limit = empty( $package ) || !is_array( $package ) || !isset( $package['job_view_limit'] ) ? 0 : $package['job_view_limit'];
		if( $job_view_limit == -1 ) return -1;

		$job_viewed = jm_get_job_viewed_count( $user_id );

		return max( absint($job_view_limit) - absint($job_viewed), 0 );
	}
endif;

if( !function_exists('jm_get_job_viewed_count') ) :
	function jm_get_job_viewed_count( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$job_viewed = get_user_meta($user_id,'_job_view_count',true);

		return empty( $job_viewed ) ? 0 : absint( $job_viewed );
	}
endif;

if( !function_exists('jm_get_viewed_jobs') ) :
	function jm_get_viewed_jobs( $user_id = '' ) {
		if(empty($user_id)){
			$user_id = get_current_user_id();
		}

		$viewed_jobs = get_user_meta($user_id,'_jobs_saved',true);
		$viewed_jobs = !is_array( $viewed_jobs ) || empty( $viewed_jobs ) ? array() : $viewed_jobs;

		return $viewed_jobs;
	}
endif;

if( !function_exists('jm_resume_package_view_job') ) :
	function jm_resume_package_view_job( $can_view_job, $job_id ) {
		if( jm_is_enabled_resume_package_view_job() ) {
			$viewed_jobs = jm_get_viewed_jobs();
			if( !$can_view_job ) {
				if( in_array( $job_id, $viewed_jobs ) ) {
					$can_view_job = true;
				}
			} else {
				if( !in_array($job_id, $viewed_jobs) ) {
					$viewed_jobs[] = $job_id;
					$user_id = get_current_user_id();
					
					$job_view_count = jm_get_job_viewed_count( $user_id );
					update_user_meta( $user_id, '_job_view_count', $job_view_count + 1 );
					update_user_meta( $user_id, '_jobs_saved', $viewed_jobs );
					jm_job_set_bookmarked( $user_id, $job_id );
				}
			}
		}

		return $can_view_job;
	}

	add_filter( 'jm_can_view_job', 'jm_resume_package_view_job', 10, 2 );
endif;
