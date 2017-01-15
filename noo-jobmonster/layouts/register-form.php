<?php
$submit_label = empty( $submit_label ) ? __('Sign Up', 'noo') : $submit_label;
$allow_register = Noo_Member::get_setting('allow_register', 'both');
$page_tempate = get_page_template();
$page_tempate = basename( $page_tempate );

$redirect_to = '';
if( 'page-post-job.php' == $page_tempate || 'page-post-resume.php' == $page_tempate ) {
	$redirect_to = get_permalink() . '#jform';
}
$redirect_to = isset($_GET['redirect_to']) && !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : $redirect_to;

$role = '';
if('page-post-job.php' == $page_tempate ) {
	$role = Noo_Member::EMPLOYER_ROLE;
} elseif( 'page-post-resume.php' == $page_tempate ) {
	$role = Noo_Member::CANDIDATE_ROLE;
}
$role = isset($_GET['role']) && !empty($_GET['role']) ? $_GET['role'] : $role;
if( $allow_register != 'none' ) :
	$prefix = uniqid();
	?>
	<form class="noo-ajax-register-form form-horizontal" action="<?php echo esc_url( wp_registration_url() ); ?>" method="post" id="registerform">
		<div style="display: none">
			<input type="hidden" class="redirect_to" name="redirect_to" value="<?php echo esc_url(apply_filters('noo_register_redirect',$redirect_to)); ?>" />
			<input type="hidden" name="action" value="noo_ajax_register">
			<input type="hidden" class="security" name="security" value="<?php echo wp_create_nonce( 'noo-ajax-register' ) ?>" />
		</div>
		<div class="form-group text-center noo-ajax-result" style="display: none"></div>
		<?php do_action( 'noo_register_form_start' ); ?>
		<?php if( Noo_Member::get_setting('register_using_email', false) ) : ?>
			<input type="hidden" id="<?php echo $prefix; ?>_user_login" name="user_login">
		<?php else : ?>
			<div class="form-group row user_login_container required-field">
				<label for="<?php echo $prefix; ?>_user_login" class="col-sm-3 control-label"><?php _e('Username','noo')?></label>
				<div class="col-sm-9">
					<input type="text" class="user_login form-control" id="<?php echo $prefix; ?>_user_login" name="user_login" required placeholder="<?php echo esc_attr__('Username','noo')?>">
				</div>
			</div>
		<?php endif; ?>
		<div class="form-group row required-field">
			<label for="<?php echo $prefix; ?>_user_email" class="col-sm-3 control-label"><?php _e('Email','noo')?></label>
			<div class="col-sm-9">
				<input type="email" class="user_email form-control" id="<?php echo $prefix; ?>_user_email"  name="user_email" required placeholder="<?php echo esc_attr__('Email','noo')?>">
			</div>
		</div>
		<div class="form-group row required-field">
			<label for="<?php echo $prefix; ?>_user_password" class="col-sm-3 control-label"><?php _e('Password','noo')?></label>
			<div class="col-sm-9">
				<input type="password" id="<?php echo $prefix; ?>_user_password" class="user_password form-control" required value="" name="user_password" placeholder="<?php echo esc_attr__('Password','noo')?>">
			</div>
		</div>
		<div class="form-group row required-field">
			<label for="<?php echo $prefix; ?>_cuser_password" class="col-sm-3 control-label"><?php _e('Retype your password','noo')?></label>
			<div class="col-sm-9">
				<input type="password" id="<?php echo $prefix; ?>_cuser_password" class="cuser_password form-control" required value="" name="cuser_password" placeholder="<?php echo esc_attr__('Repeat password','noo')?>">
			</div>
		</div>
		<?php if( $allow_register == 'both' ) : ?>
			<div class="form-group row required-field">
				<label class="col-sm-3 control-label"><?php _e('You are','noo')?></label>
				<div class="col-sm-9">
					<div class="form-control-flat">
						<select class="user_role" name="user_role" required>
							<option value=""><?php esc_html_e('-Select-','noo')?></option>
							<option value="employer" <?php selected( $role, 'employer' ); ?>><?php esc_html_e('I\'m an employer looking to hire','noo')?></option>
							<option value="candidate" <?php selected( $role, 'candidate' ); ?>><?php esc_html_e('I\'m a candidate looking for a job','noo')?></option>
						</select>
						<i class="fa fa-caret-down"></i>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php do_action( 'noo_register_form' ); ?>
		<div class="form-group text-center">
			<?php
				$term_page = Noo_Member::get_setting('term_page_id');
				$term_of_use_link = !empty($term_page) ? esc_url(apply_filters('noo_term_url', get_permalink( $term_page ))) : '';
				if( !empty( $term_of_use_link ) ) :
			?>
				<div class="checkbox account-reg-term">
					<div class="form-control-flat">
						<label class="checkbox">
							<input class="account_reg_term" type="checkbox"  title="<?php esc_attr_e('Please agree with the term','noo')?>"><i></i> 
							<?php _e('I agree with the','noo')?> <a href="<?php echo $term_of_use_link; ?>" target="_blank"><?php _e('Terms of use', 'noo')?></a>
						</label>
					</div>
				</div>
			<?php endif; ?>
			<button type="submit" class="btn btn-primary"><?php echo esc_html($submit_label)?></button>
		</div>
	 	<div class="login-form-links">
	 		<span><?php echo sprintf(__('Already have an account? <a href="%s" class="member-login-link" >Login Now <i class="fa fa-long-arrow-right"></i></a>','noo'),Noo_Member::get_login_url())?></span>
	 	</div>
		<?php do_action( 'noo_register_form_end' ); ?>
	</form>
<?php endif; ?>