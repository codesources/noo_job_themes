<?php 
if( Noo_Member::is_logged_in() ) :
	$user_id = get_current_user_id();
	$submit_label = __('Save New Password','noo');
?>
<div class="form-title">
	<h3><?php _e('Change Password', 'noo'); ?></h3>
</div>
<form method="post" id="noo-ajax-update-password" class="form-horizontal" autocomplete="off" novalidate="novalidate">
	<div class="form-group text-center noo-ajax-result" style="display: none"></div>
	<div class="update-password-form row">
		<div class="col-sm-12">
			<div class="form-group required-field">
				<label for="old_pass" class="col-sm-3 control-label"><?php _e('Current Password','noo')?></label>
				<div class="col-sm-9">
			    	<input type="password" class="form-control" required id="old_pass" value="" name="old_pass">
			    </div>
			</div>
			<div class="form-group required-field">
				<label for="new_pass" class="col-sm-3 control-label"><?php _e('New Password','noo')?></label>
				<div class="col-sm-9">
			    	<input type="password" class="form-control" required id="new_pass" value="" name="new_pass">
			    </div>
			</div>
			<div class="form-group required-field">
				<label for="new_pass_confirm" class="col-sm-3 control-label"><?php _e('Confirm new password','noo')?></label>
				<div class="col-sm-9">
			    	<input type="password" class="form-control" required id="new_pass_confirm" value="" name="new_pass_confirm">
			    </div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<button type="submit" class="btn btn-primary"><?php echo esc_html($submit_label)?></button>
		<input type="hidden" class="security" name="security" value="<?php echo wp_create_nonce( 'update-password' ) ?>" />
		<input type="hidden" name="action" value="noo_update_password">
		<input type="hidden" name="user_id" value="<?php echo esc_attr($user_id) ?>">
	</div>
</form>
<?php endif; ?>