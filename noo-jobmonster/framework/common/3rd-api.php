<?php
if( !function_exists( 'jm_3rd_api_admin_init' ) ) :
	function jm_3rd_api_admin_init(){
		register_setting('jm_3rd_api', 'jm_3rd_api');
	}
	
	add_filter('admin_init', 'jm_3rd_api_admin_init' );
endif;

if( !function_exists( 'jm_3rd_api_settings_tabs' ) ) :
	function jm_3rd_api_settings_tabs( $tabs = array() ) {
		$tabs['3rd_api'] = __('3rd APIs','noo');

		return $tabs;
	}
	
	add_filter('noo_job_settings_tabs_array', 'jm_3rd_api_settings_tabs', 99 );
endif;

if( !function_exists( 'jm_get_3rd_api_setting' ) ) :
	function jm_get_3rd_api_setting($id = null ,$default = null){
		$api = jm_get_setting('jm_3rd_api', $id, $default);

		if( empty( $api ) ) {
			// old option
			switch( $id ) {
				case 'linkedin_app_id':
					return jm_get_application_setting( 'api_key', $api );
				case 'linkedin_app_secret':
					return jm_get_application_setting( 'api_secret', $api );
				case 'facebook_app_id':
					return Noo_Member::get_setting( 'id_facebook', $api );
				case 'facebook_app_secret':
					return Noo_Member::get_setting( 'secret_facebook', $api );
				case 'google_client_id':
					return Noo_Member::get_setting( 'google_client_id', $api );
				case 'google_client_secret':
					return Noo_Member::get_setting( 'google_client_secret', $api );
			}
		}

		return $api;
	}
endif;

if( !function_exists( 'jm_3rd_api_settings_form' ) ) :
	function jm_3rd_api_settings_form(){
		// $linkedin_app_id = jm_get_application_setting('api_key');
		// $linkedin_app_secret = jm_get_application_setting('api_secret');
		$linkedin_app_id = jm_get_3rd_api_setting('linkedin_app_id');
		$linkedin_app_secret = jm_get_3rd_api_setting('linkedin_app_secret');

		?>
		<?php settings_fields('jm_3rd_api'); ?>
		<h3><?php echo __('APIs','noo')?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
				<tr id="google-recaptcha-key">
					<th>
						<?php _e('Google reCaptcha Key','noo')?>
					</th>
					<td>
						<input type="text" name="jm_3rd_api[google_recaptcha_key]" value="<?php echo jm_get_3rd_api_setting('google_recaptcha_key', ''); ?>" placeholder="<?php _e('Google reCaptcha Public Key','noo')?>" size="60" />
						<input type="text" name="jm_3rd_api[google_recaptcha_secret_key]" value="<?php echo jm_get_3rd_api_setting('google_recaptcha_secret_key', ''); ?>" placeholder="<?php _e('Google reCaptcha Secret key','noo')?>" size="50" />
						<p><?php _e('Please enter Google reCaptcha public and secret key', 'noo') ;?></p>
						<p><a target="_blank" href="https://www.google.com/recaptcha/admin#list"><?php _e('Click here to get Google reCaptcha Key ', 'noo') ;?></a></p>

					</td>
				</tr>
				<tr id="linkedin-app-api">
					<th>
						<?php esc_html_e('LinkedIn App API','noo')?>
					</th>
					<td>
						<input id="linkedin_app_id" type="text" name="jm_3rd_api[linkedin_app_id]" value="<?php echo ($linkedin_app_id ? $linkedin_app_id :'') ?>" placeholder="<?php _e( 'Client ID', 'noo' ); ?>" size="50" >
						<input id="linkedin_app_secret" type="text" name="jm_3rd_api[linkedin_app_secret]" value="<?php echo ($linkedin_app_secret ? $linkedin_app_secret :'') ?>" placeholder="<?php _e( 'Client Secret', 'noo' ); ?>" size="50" >
						<p>
							<?php echo sprintf( __('<b>%s</b> requires that you create an application inside its framework to allow access from your website to their API.<br/> To know how to create this application, ', 'noo' ), 'LinkedIn' ); ?>
							<a href="javascript:void(0)" onClick="jQuery('#linkedin-help').toggle();return false;"><?php _e('click here and follow the steps.', 'noo'); ?></a>
						</p>
						<div id="linkedin-help" class="noo-setting-help" style="display: none; max-width: 1200px;" >
							<hr/>
							<br/>
							<?php _e('<em>Application ID</em> and <em>Secret</em> (also sometimes referred as <em>Consumer Key</em> and <em>Secret</em> or <em>Client ID</em> and <em>Secret</em>) are what we call an application credential', 'noo') ?>. 
							<?php echo sprintf( __( 'This application will link your website <code>%s</code> to <code>%s API</code> and these credentials are needed in order for <b>%s</b> users to access your website', 'noo'), $_SERVER["SERVER_NAME"], 'LinkedIn', 'LinkedIn' ) ?>. 
							<br/>
							<br/>
							<?php echo sprintf( __('To register a new <b>%s API Application</b> and enable authentication, follow the steps', 'noo'), 'LinkedIn' ) ?>
							<br/>
							<?php $setupsteps = 0; ?>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e( 'Go to', 'noo'); ?>&nbsp;<a href="https://www.linkedin.com/secure/developer" target ="_blank">https://www.linkedin.com/secure/developer</a></p>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Select <b>Create Application</b> button', 'noo') ?>.</p> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Fill in required information then click <b>Submit</b> button', 'noo') ?>.</p> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Check on <em>r_emailaddress</em> as your <b>App permission</b> to get the email from your users', 'noo') ?> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Add your site URL to the <b>OAuth 2.0 - Authorized Redirect URLs</b>. It should match the current site', 'noo') ?> <em><?php echo get_option('siteurl'); ?></em></p> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('In the sidebar, choose Javascript then Add your domain to the <b>Valid SDK Domains</b>. It should match with the current domain', 'noo') ?> <em><?php echo ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER["SERVER_NAME"]; ?></em></p> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Go back to the <b>Authentication</b> tab, then copy the <em>Client ID</em> and <em>Client Secret</em>', 'noo') ?>.</p> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Then paste into the setting above', 'noo') ?>.</p> 
							<p>
								<b><?php _e("And that's it!", 'noo') ?></b> 
								<br />
								<?php echo __( 'For more reference, you can see: ', 'noo' ); ?><a href="https://developer.linkedin.com/docs/oauth2", target="_blank"><?php _e('LinkedIn Document', 'noo'); ?></a>, <a href="https://www.google.com/search?q=LinkedIn API create application" target="_blank"><?php _e('Google', 'noo'); ?></a>, <a href="http://www.youtube.com/results?search_query=LinkedIn API create application " target="_blank"><?php _e('Youtube', 'noo'); ?></a>
							</p> 
							<div style="margin-bottom:12px;" class="noo-thumb-wrapper">
								<a href="http://update.nootheme.com/wp-content/uploads/2015/09/linkedin_api_1.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/linkedin_api_1.png"></a>
								<a href="http://update.nootheme.com/wp-content/uploads/2015/09/linkedin_api_2.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/linkedin_api_2.png"></a>
								<a href="http://update.nootheme.com/wp-content/uploads/2015/09/linkedin_api_3.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2015/09/linkedin_api_3.png"></a>
							</div> 
							<br/>
							<hr/>
						</div>
					</td>
				</tr>
				<?php do_action( 'jm_setting_3rd_api_fields' ); ?>
			</tbody>
		</table>
		<?php 
	}

	add_action('noo_job_setting_3rd_api', 'jm_3rd_api_settings_form');
endif;
