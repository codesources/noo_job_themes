<?php do_action('noo_lost_password_form_before'); ?>
<div class="account-form">
    <div class="account-lost-password-form">
        <form class="form-horizontal" id="noo-lost-password-form" method="post">
        	<div style="display: none">
        		<input type="hidden" name="action" value="lost_password"> 
				<?php wp_nonce_field('lost-password')?>
        	</div>
        	<p class="lost-pass-desc"><?php _e('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.','noo')?></p>
            <div class="form-group row required-field">
                <label class="col-sm-3 control-label" for="user_login"><?php _e('Username or email','noo')?></label>
                <div class="col-sm-9">
                    <input type="text" placeholder="<?php __('Username', 'noo'); ?>" required autofocus name="user_login" id="user_login" class="form-control">
                </div>
            </div>
            <div class="form-actions form-group text-center">
                <button class="btn btn-primary" type="submit"><?php esc_html_e('Reset Password','noo')?></button>
            </div>
        </form>
    </div>
</div>
<?php do_action('noo_lost_password_form_after'); ?>