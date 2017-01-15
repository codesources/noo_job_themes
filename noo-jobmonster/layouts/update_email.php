<?php 
if( Noo_Member::is_logged_in() ) :
	$user = wp_get_current_user();
	$user_id = $user->ID;
	$submit_label = __('Save New Email','noo');
?>
<div class="form-title">
	<h3><?php _e('Change Email', 'noo'); ?></h3>
</div>
<form method="post" id="noo-ajax-update-email" class="form-horizontal" autocomplete="off" novalidate="novalidate">
	<div class="form-group text-center noo-ajax-result" style="display: none"></div>
	<div class="update-email-form row">
		<div class="col-sm-12">
			<div class="form-group">
				<label for="old_pass" class="col-sm-3 control-label"><?php _e('Current Email','noo')?></label>
				<div class="col-sm-9">
			    	<label><?php echo $user->user_email; ?></label>
			    </div>
			</div>
			<div class="form-group required-field">
				<label for="new_email" class="col-sm-3 control-label"><?php _e('New Email','noo')?></label>
				<div class="col-sm-9">
			    	<input type="email" class="form-control" required id="new_email" value="" name="new_email">
			    </div>
			</div>
			<div class="form-group required-field">
				<label for="new_email_confirm" class="col-sm-3 control-label"><?php _e('Confirm new email','noo')?></label>
				<div class="col-sm-9">
			    	<input type="email" class="form-control" required id="new_email_confirm" value="" name="new_email_confirm">
			    </div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<button type="submit" class="btn btn-primary"><?php echo esc_html($submit_label)?></button>
		<input type="hidden" class="security" name="security" value="<?php echo wp_create_nonce( 'update-email' ) ?>" />
		<input type="hidden" name="action" value="noo_update_email">
		<input type="hidden" name="user_id" value="<?php echo esc_attr($user_id) ?>">
	</div>
</form>
<?php endif; ?>