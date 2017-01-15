<?php do_action('noo_reset_password_form_before'); 
    $rp_key = isset( $_GET['key'] ) ? $_GET['key'] : '';
    $rp_login = isset( $_GET['login'] ) ? $_GET['login'] : '';
?>
<div class="account-form">
    <div class="account-reset-password-form">
        <form class="form-horizontal" id="noo-reset-password-form" method="post">
        	<div style="display: none">
        		<input type="hidden" name="action" value="reset_password"> 
                <input type="hidden" name="rp_key" value="<?php echo esc_attr( $rp_key ); ?>" />
                <input type="hidden" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>" />
				<?php wp_nonce_field('reset-password')?>
        	</div>
            <div class="noo-messages noo-message-notice">
                <ul>
                    <li><?php _e('Enter your new password below.', 'noo'); ?></li>
                </ul>
            </div>
            <div class="form-group row required-field">
                <label class="col-sm-3 control-label" for="password"><?php _e('New password','noo')?></label>
                <div class="col-sm-9">
                    <input type="text" required autofocus name="password" id="password" class="form-control" autocomplete="off">
                </div>
            </div>
            <div class="form-actions form-group text-center">
                <button class="btn btn-primary" type="submit"><?php esc_html_e('Reset Password','noo')?></button>
            </div>
        </form>
    </div>
</div>
<?php do_action('noo_reset_password_form_after'); ?>