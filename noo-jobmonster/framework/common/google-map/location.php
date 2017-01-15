<?php

if( !function_exists( 'jm_get_location_setting' ) ) :
	function jm_get_location_setting($id = null ,$default = null){
		return jm_get_setting('jm_location', $id, $default);
	}
endif;

if( !function_exists( 'jm_location_admin_init' ) ) :
	function jm_location_admin_init(){
		register_setting('jm_location','jm_location');
		add_action('noo_job_setting_location', 'jm_location_settings_form');
	}
	
	add_filter('admin_init', 'jm_location_admin_init' );
endif;

if( !function_exists( 'jm_location_settings_tabs' ) ) :
	function jm_location_settings_tabs( $tabs = array() ) {
		$location_tab = array( 'location' => __('Location','noo') );
		return array_merge($tabs, $location_tab);
	}
	
	add_filter('noo_job_settings_tabs_array', 'jm_location_settings_tabs', 11 );
endif;

if( !function_exists( 'jm_location_settings_form' ) ) :
	function jm_location_settings_form(){
		wp_enqueue_style('vendor-chosen-css');
		wp_enqueue_script('vendor-chosen-js');
		?>
		<?php settings_fields('jm_location'); ?>
		<?php
			// setting value
			$location_mode = jm_get_location_setting( 'location_mode', 'taxonomy' );
			$allow_user_input = jm_get_location_setting( 'allow_user_input', 1 );
			$enable_auto_complete = jm_get_location_setting( 'enable_auto_complete', 1 );
			$country_restriction = jm_get_location_setting( 'country_restriction', '' );
			$location_type = jm_get_location_setting( 'location_type', '(regions)' );
		?>
		<h3><?php echo __('Google Map Location Settings','noo')?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
				<!-- <tr>
					<th>
						<?php _e('Location Mode','noo')?>
					</th>
					<td>
						<fieldset>
							<label><input type="radio" <?php checked( $location_mode, 'taxonomy' ); ?> name="jm_location[location_mode]" value="taxonomy"><?php _e('Taxonomy list ( easy to manage )', 'noo'); ?></label><br/>
							<label><input type="radio" <?php checked( $location_mode, 'google' ); ?> name="jm_location[location_mode]" value="google"><?php _e('Real Google Map address', 'noo'); ?></label><br/>
						</fieldset>
					</td>
				</tr> -->
				<tr>
					<th>
						<?php esc_html_e('Google Maps API Key','noo')?>
					</th>
					<td>
						<input type="text" class="regular-text" value="<?php echo jm_get_location_setting('google_api','')?>" name="jm_location[google_api]">
						<p>
							<?php echo __('<b>Google</b> requires that you register an API Key to display <b>Maps</b> on from your website.', 'noo' ); ?><br/>
							<?php echo __('To know how to create this application,', 'noo'); ?> <a href="javascript:void(0)" onClick="jQuery('#google-map-help').toggle();return false;"><?php _e('click here and follow the steps.', 'noo'); ?></a>
						</p>
						<div id="google-map-help" class="noo-setting-help" style="display: none; max-width: 1200px;" >
							<hr/>
							<br/>
							<?php echo __('To register a new <b> Google Map API Key</b>, follow the steps', 'noo'); ?>:
							<br/>
							<?php $setupsteps = 0; ?>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e( 'Go to', 'noo'); ?>&nbsp;<a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true&pli=1" target ="_blank">
								<?php echo __('Google Developers Console', 'noo'); ?></a>. <?php echo __('Login to your Google account if needed', 'noo'); ?>.</p>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Agree with the service Terms of Service ( only the first time ).', 'noo') ?>.</p>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Select an existed project or Create new, then click Continue', 'noo') ?>.</p>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Fill in your domain. You can use multiple domains, but one should match the current domain.', 'noo') ?> <em><?php echo '*' . $_SERVER["SERVER_NAME"] . '/*'; ?></em></p> 
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Click <b>Create</b>', 'noo') ?>.</p>
							<p><b><?php echo ++$setupsteps; ?></b>. <?php _e('Copy the <em>API Key</em> then paste into the setting above', 'noo') ?>.</p> 
							<p>
								<b><?php _e("And that's it!", 'noo') ?></b> 
								<br />
								<?php echo __( 'For more reference, you can see: ', 'noo' ); ?><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php echo __('Official Document', 'noo') ?></a>, <a href="http://googlegeodevelopers.blogspot.com.au/2016/06/building-for-scale-updates-to-google.html" target="_blank"><?php echo __('Google Blog', 'noo') ?></a>
							</p> 
							<div style="margin-bottom:12px;" class="noo-thumb-wrapper">
								<a href="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_0.jpg" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_0.jpg"></a>
								<a href="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_1.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_1.png"></a>
								<a href="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_2.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_2.png"></a>
								<a href="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_3.png" target="_blank"><img src="http://update.nootheme.com/wp-content/uploads/2016/07/map_api_3.png"></a>
							</div>
							<br/>
							<hr/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Enable Google Auto-Complete','noo')?>
					</th>
					<td>
						<input type="hidden" name="jm_location[enable_auto_complete]" value="0">
						<label><input type="checkbox" <?php checked( $enable_auto_complete, true ); ?> name="jm_location[enable_auto_complete]" value="1"><?php _e('Using Auto-Complete from Google Map for your location input', 'noo'); ?></label>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Country Restriction','noo')?>
						<p><small><?php _e('Select your country will limit all suggestions to your local locations. Leave it blank to use all the locations around the world.', 'noo'); ?></small></p>
					</th>
					<td>
						<select name="jm_location[country_restriction]" data-placeholder="Select your country" class="jm-setting-chosen">
							<option value=""></option>
							<?php $country_list = _get_country_ISO_code(); ?>
							<?php if( !empty( $country_list ) ) : ?>
								<?php foreach ($country_list as $country ) : ?>
									<option value="<?php echo $country->Code; ?>" <?php selected( $country->Code, $country_restriction ); ?>><?php echo $country->Name; ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
						
					</td>
				</tr>
				<tr>
					<th>
						<?php _e('Location Type','noo')?>
					</th>
					<td>
						<fieldset>
							<label><input type="radio" <?php checked( $location_type, '(regions)' ); ?> name="jm_location[location_type]" value="(regions)"><?php _e('Administrative Regions', 'noo'); ?></label><br/>
							<label><input type="radio" <?php checked( $location_type, '(cities)' ); ?> name="jm_location[location_type]" value="(cities)"><?php _e('Cities', 'noo'); ?></label><br/>
							<label><input type="radio" <?php checked( $location_type, 'establishment' ); ?> name="jm_location[location_type]" value="establishment"><?php _e('Establishment ( Business location )', 'noo'); ?></label><br/>
							<label><input type="radio" <?php checked( $location_type, 'geocode' ); ?> name="jm_location[location_type]" value="geocode"><?php _e('Full address', 'noo'); ?></label><br/>
						</fieldset>
						<p><small><?php _e('Select the location type that matches your business.', 'noo'); ?></small></p>
					</td>
				</tr>
				<script>
					jQuery(document).ready( function($) {
						// Font functions
						$( 'select.jm-setting-chosen' ).chosen({
							allow_single_deselect: true,
							width: '240px'
						});

						$('input[name="jm_location[enable_auto_complete]"]').change(function(event) {
							var $input = $( this );
							if ( $input.is( ":checked" ) ) {
								$('.enable_auto_complete-child').show();
							} else {
								$('.enable_auto_complete-child').hide();
							}
						});

						$('input[name="jm_location[enable_auto_complete]"]').change();
					});
				</script>
				<?php do_action( 'jm_setting_location_fields' ); ?>
			</tbody>
		</table>
		<?php 
	}
endif;

function _get_country_ISO_code() {
	$dataFile = dirname( __FILE__ ) . '/data.json';
	$content = json_decode( file_get_contents( $dataFile ) );

	$coutries = array();
	if ( !empty( $content ) ) {
		$coutries = $content;
	}

	return apply_filters( 'jm_location_country_list', $coutries );
}
