<?php
/*
Template Name: Post Resume
*/
if ( empty( $_POST[ 'action' ] ) ) {
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

$resume_id = isset( $_REQUEST['resume_id'] ) ? absint( $_REQUEST['resume_id'] ) : '';
$package_id = isset( $_REQUEST['package_id'] ) ? absint( $_REQUEST['package_id'] ) : '';

$steps = jm_get_page_post_resume_steps();
$step_keys = array_keys($steps);
if( !in_array($action, $step_keys) ) {
	$action = $step_keys[0];
}

$next_step = current(array_slice($step_keys, array_search($action, $step_keys) + 1, 1));
$step_content='';
$enable_resume_detail = Noo_Resume::enable_resume_detail();

jm_page_post_resume_login_check( $action );
switch ($action){
	case 'login':
		if(Noo_Member::is_logged_in()) {
			jm_force_redirect(esc_url_raw(add_query_arg( 'action', $next_step)));
		}
	break;
	case 'resume_package':
		if( jm_is_woo_resume_posting() ) {
			if( !empty( $package_id ) || jm_get_resume_posting_remain() > 0 ) {
				jm_force_redirect(esc_url_raw(add_query_arg( 'action', $next_step)));
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
					<?php $package_page_id = Noo_Resume_Package::get_setting( 'resume_package_page_id' );
					$package_page_id = apply_filters( 'wpml_object_id', $package_page_id, 'page' );
					if( $package_page_id ) :
						$content = get_post_field( 'post_content', $package_page_id );
						$content = apply_filters( 'the_content', $content );

						echo $content;
					?>
					<?php else : ?>
						<?php noo_get_layout('resume_package')?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		$step_content = ob_get_clean();
	break;
	case 'resume_post':
		if( !jm_can_post_resume() ) {
			noo_message_add(__('Sorry, you can\'t post resume.', 'noo'),'error');
			if(jm_is_woo_resume_posting() && Noo_Member::is_candidate()){
                jm_force_redirect(Noo_Member::get_endpoint_url('manage-plan'));
			}else{
				jm_force_redirect(Noo_Member::get_member_page_url());
			}
		}
		Noo_Form_Handler::post_resume_action();
		ob_start();
		?>
		<div id="step_content_form" class="jstep-content">
			<div class="jpanel jpanel-resume-form">
				<div class="jpanel-title">
					<h3><?php _e('Your Resume','noo')?></h3>
				</div>
				<div class="jpanel-body">
					<?php noo_get_layout('resume_candidate_profile'); ?>
					<?php noo_get_layout('resume_form')?>
				</div>
			</div>
			<div class="form-actions form-group text-center clearfix">
				<?php if( jm_is_woo_resume_posting() && !empty( $package_id ) ):?>
					<a type="button" class="btn btn-primary" href="<?php echo esc_url(remove_query_arg('package_id', add_query_arg('action','resume_package')));?>"><?php _e('Back','noo')?></a>
		 		<?php endif;?>
			 	<button type="submit" class="btn btn-primary"><?php echo __('Preview','noo'); ?></button>
		 	</div>
		</div>
		<?php
		$step_content = ob_get_clean();
	break;
	case 'resume_preview':
		Noo_Form_Handler::preview_resume_action();
		ob_start();
		?>
		<div id="step_content_form" class="jstep-content">
			<div class="jpanel jpanel-resume-form">
				<div class="jpanel-title">
					<h3><?php _e('Preview and Finish','noo')?></h3>
				</div>
				<div class="jpanel-body">
					<?php do_action('noo_resume_preview_before', $action); ?>
					<?php noo_get_layout('resume_preview')?>
					<?php do_action('noo_resume_preview_after', $action); ?>
				</div>
			</div>
		</div>
		<?php
		$step_content = ob_get_clean();
	break;
	default:
		do_action( 'jm_page_post_resume_action', $action );
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
								<ul class="jsteps jsteps-<?php echo count($steps); ?>">
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
								<form id="post_resume_form" class="form-horizontal" autocomplete="on" method="post" novalidate="novalidate">
									<div style="display: none;">
										<input type="hidden" name="action" id="hiddenaction" value="<?php echo esc_attr($action)?>">
										<input type="hidden" name="page_id" value="<?php echo get_the_ID()?>">
										<input type="hidden" name="resume_id" value="<?php echo !empty( $resume_id ) ? absint($resume_id) : 0; ?>">
										<input type="hidden" name="candidate_id" value="<?php echo get_current_user_id();?>">
										<?php if(jm_is_woo_resume_posting() && !empty( $package_id )):?>
											<input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
										<?php endif;?>
										<?php wp_nonce_field('noo-post-resume')?>
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