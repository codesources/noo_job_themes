<?php
/*
Template Name: Post Job
*/
if ( !isset( $_POST['action'] ) || empty( $_POST[ 'action' ] ) ) {
	if ( empty( $_GET[ 'action' ] ) ) {
		$GLOBALS[ 'action' ] = '';
	} else {
		$GLOBALS[ 'action' ] = $_GET[ 'action' ];
	}
} else {
	$GLOBALS[ 'action' ] = $_POST[ 'action' ];
}

$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : '';
if( $action == 'register' ) {
	$action = 'login';
	$mode = 'register';
}

$job_id = isset( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : '';
$package_id = isset( $_REQUEST['package_id'] ) ? absint( $_REQUEST['package_id'] ) : '';

$steps = jm_get_page_post_job_steps();
$step_keys = array_keys($steps);
if( !in_array($action, $step_keys) ) {
	$action = $step_keys[0];
}

$next_step = current(array_slice($step_keys, array_search($action, $step_keys) + 1, 1));
$step_content='';

jm_page_post_job_login_check( $action );
switch ($action){
	case 'login':
		if(Noo_Member::is_logged_in()) {
			jm_force_redirect(esc_url_raw(add_query_arg( 'action', $next_step)));
		}
		break;
	case 'job_package':
		if( jm_is_woo_job_posting() ) {
			if( !empty( $package_id ) || jm_get_job_posting_remain() > 0 ) {
				jm_force_redirect(esc_url_raw(add_query_arg( 'action', $next_step)));
			}

			if( jm_get_job_posting_remain() == 0 ) {
				$package_info = jm_get_job_posting_info();
				if( isset( $package_info['product_id'] ) && !empty( $package_info['product_id'] ) ) {
					noo_message_add(__('You can not add job','noo'),'error');
					jm_force_redirect(jm_force_redirect(Noo_Member::get_endpoint_url('manage-plan')));
				}
			}
		} else {
			jm_force_redirect(esc_url_raw(add_query_arg( 'action', $next_step)));
		}

		ob_start();
		?>
		<div id="step_content_package" class="jstep-content">
			<div class="jpanel jpanel-package">
				<div class="jpanel-title">
					<h3><?php _e('Choose a Package That Fits Your Needs','noo')?></h3>
				</div>
				<div class="jpanel-body">
					<?php $package_page_id = Noo_Job_Package::get_setting( 'package_page_id' );
					$package_page_id = apply_filters( 'wpml_object_id', $package_page_id, 'page' );
					if( $package_page_id ) :
						$content = get_post_field( 'post_content', $package_page_id );
						$content = apply_filters( 'the_content', $content );

						echo $content;
					?>
					<?php else : ?>
						<?php noo_get_layout('job_package')?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		$step_content = ob_get_clean();
	break;
	case 'post_job':
		if( !jm_can_post_job() ) {
			noo_message_add(__('You can not add job','noo'),'error');
			if(jm_is_woo_job_posting() && Noo_Member::is_employer()){
                jm_force_redirect(Noo_Member::get_endpoint_url('manage-plan'));
			}else{
				jm_force_redirect(Noo_Member::get_member_page_url());
			}
		}
		Noo_Form_Handler::post_job_action();
		ob_start();
		?>
		<div id="step_content_form" class="jstep-content">
			<div class="jpanel jpanel-job-form">
				<div class="jpanel-title">
					<h3><?php _e('Describe your company and vacancy','noo')?></h3>
				</div>
				<div class="jpanel-body">
					<div class="form-title">
						<h3><?php _e('Job Details','noo')?></h3>
					</div>
					<?php noo_get_layout('job_form')?>
				</div>
			</div>
			<div class="form-actions form-group text-center clearfix">
				<?php if( jm_is_woo_job_posting() && !empty( $package_id ) ): ?>
				<a type="button" class="btn btn-primary" href="<?php echo esc_url(remove_query_arg('package_id', add_query_arg('action','job_package')))?>"><?php _e('Back','noo')?></a>
		 		<?php endif;?>
		 		<button type="submit" class="btn btn-primary"><?php _e('Continue','noo')?></button>
		 	</div>
		</div>
		<?php
		$step_content = ob_get_clean();
	break;
	case 'preview_job':
		Noo_Form_Handler::preview_job_action();
		if(!jm_can_post_job() && jm_is_woo_job_posting()){
			jm_force_redirect(esc_url_raw(add_query_arg( 'action', 'job_package',get_permalink())));
		}
		ob_start();
		?>
		<div id="step_content_preview" class="jstep-content">
			<?php noo_get_layout('job_preview')?>
		</div>
		<?php
		$step_content = ob_get_clean();
	break;
	default:
		do_action( 'jm_page_post_job_action', $action );
		break;
}
?>
<?php get_header(); ?>
<div class="container-wrap">	
	<div class="main-content container-fullwidth">
		<div class="row">
			<div class="<?php noo_main_class(); ?>" role="main">
				<div class="jform" id="jform">
					<div class="jform-header">
						<div class="container-boxed max">
							<div class="jform-steps">
								<ul class="jsteps jsteps-<?php echo count( $steps ); ?>">
									<?php $count = 0; $is_previous = true; $is_current = false; $is_next = false; ?>
									<?php foreach ($steps as $key => $step) : ?>
										<?php
										$count++;
										$is_current = in_array( $action, $step['actions'] );
										$is_previous = $is_previous && !$is_current;
										$class = '';
										$class .= ( $is_current ) ? ' active' : '';
										$class .= ( $is_previous ) ? ' completed' : '';
										$link = ( $is_previous && !empty( $step['link'] ) ) ? $step['link'] : 'javascript:void(0);';
										?>
										<li class="<?php echo $class; ?>">
											<span class="jstep-num">
												<a href="<?php echo $link; ?>"><?php echo $is_previous ? '<i class="fa fa-check"></i>' : $count;?></a>
											</span>
											<div class="jstep-line">
												<span class="jstep-dot"></span>
											</div>
											<div class="jstep-label"><?php echo $step['title']; ?></div>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
					<div class="jform-body">
						<div class="container-boxed max">
							<?php if($action=='login' || $action == 'register'):?>
								<div id="step_content_login" class="jstep-content" style="display: block;">
									<?php noo_get_layout('login_register')?>
								</div>
							<?php else:?>
								<form id="post_job_form" class="form-horizontal" autocomplete="on" method="post" novalidate="novalidate">
									<div style="display: none;">
										<input type="hidden" name="action" id="hiddenaction" value="<?php echo esc_attr($action)?>">
										<input type="hidden" name="page_id" value="<?php echo get_the_ID()?>">
										<input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
										<?php if(jm_is_woo_job_posting() && !empty($package_id)):?>
											<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
										<?php endif;?>
										<?php wp_nonce_field('noo-post-job')?>
									</div>
									<?php echo ($step_content);?>
								</form>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div> <!-- /.main -->
		</div><!--/.row-->
	</div><!--/.container-full-->
</div><!--/.container-wrap-->
	
<?php get_footer(); ?>