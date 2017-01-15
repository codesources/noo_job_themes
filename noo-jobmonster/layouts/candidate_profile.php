<?php 
$current_user = wp_get_current_user();
?>
<div class="candidate-profile-form row">
	<div class="col-sm-6">
		<?php 
			$fields = jm_get_candidate_custom_fields();
			if( !empty( $fields ) ) {
				foreach ($fields as $field) {
					jm_candidate_render_form_field( $field, $current_user->ID );
				}
			}
		?>
		<?php $socials = jm_get_candidate_socials();
			if(!empty($socials)) {
				foreach ($socials as $social) {
					jm_candidate_render_social_field( $social, $current_user->ID );
				}
			}
		?>
	</div>

	<div class="col-sm-6">
		<div class="form-group">
		    <label for="description" class="control-label"><?php _e('Introduce Yourself','noo')?> <small><?php _e('(Optional)','noo')?></small></label>
			<?php
			$user_content = $current_user->description ? $current_user->description : '';
			noo_wp_editor($user_content, 'description');
			?>
		</div>
		<div class="form-group">
			<label for="profile_image" class="col-sm-3 control-label"><?php _e('Profile Image','noo')?></label>
			<div class="col-sm-9">
				<?php
					$profile_image = ($current_user->ID ? get_user_meta( $current_user->ID, 'profile_image', true) : '');
					noo_image_upload_form_field( 'profile_image', $profile_image );
				?>
			</div>
		</div>
	</div>
</div>