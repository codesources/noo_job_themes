<?php 
global $post;
$candidate_id = isset($_GET['candidate_id']) ? absint($_GET['candidate_id']) : '';
$enable_upload = (bool) jm_get_resume_setting('enable_upload_resume', '1');
$enable_print = (bool) jm_get_resume_setting('enable_print_resume', '1');
if( get_the_ID() == Noo_Member::get_member_page_id() || jm_is_resume_posting_page() ) {
	$candidate_id = get_current_user_id();
	$resume_id = 0;
} else {
	$resume_id = isset( $_GET['resume_id'] ) ? $_GET['resume_id'] : get_the_ID();
	if( 'noo_resume' == get_post_type( $resume_id ) ) {
		$candidate_id = get_post_field( 'post_author', $resume_id);
	}
}

$file_cv = noo_json_decode( noo_get_post_meta( $post->ID, '_noo_file_cv' ) );

$candidate = !empty($candidate_id) ? get_userdata($candidate_id) : false;

if( $candidate ) :
	$fields = jm_get_candidate_custom_fields();
	$all_socials = noo_get_social_fields();
	$socials = jm_get_candidate_socials();
	$email = $candidate ? $candidate->user_email : '';

?>
	<div class="resume-candidate-profile">
		<div class="row">
			<div class="col-sm-3 profile-avatar">
				<?php echo noo_get_avatar( $candidate_id, 160); ?>
			</div>
			<div class="col-sm-9 candidate-detail">
				<div class="candidate-title clearfix">
					<h2><?php echo esc_html( $candidate->display_name ); ?></h2>
					<?php if( $candidate_id == get_current_user_id() ) : ?>
						<a class="pull-right resume-action" href="<?php echo esc_url( Noo_Member::get_candidate_profile_url('candidate-profile') ); ?>" title="<?php echo esc_attr__('Edit Profile', 'noo'); ?>">
							<i class="fa fa-pencil"></i>
						</a>
					<?php endif; ?>
					<?php if ( $enable_print ) : ?>
						<a class="pull-right resume-action" href="javascript:void(0)" onclick="return window.print();" title="<?php echo __('Print', 'noo'); ?>" title="<?php echo esc_attr__('Print', 'noo'); ?>">
							<i class="fa fa-print"></i>
						</a>
					<?php endif; ?>
				</div>
				<?php do_action( 'noo_resume_candidate_profile_before', $resume_id ); ?>
				<?php if( apply_filters( 'jm_resume_show_candidate_contact', true, $resume_id ) ) : ?>
					<div class="candidate-info">
						<div class="row">
							<?php if( !empty( $fields ) ) : ?>
								<?php foreach ( $fields as $field ) :
									if( isset( $field['is_default'] ) ) {
										if( in_array( $field['name'], array( 'first_name', 'last_name', 'full_name', 'email' ) ) )
											continue; // don't display WordPress default user fields
									}
									$field_id = jm_candidate_custom_fields_name( $field['name'], $field );
									$value = get_user_meta( $candidate->ID, $field_id, true );
									$value = noo_convert_custom_field_value( $field, $value );
									if( is_array( $value ) ) {
										$value = implode(', ', $value);
									}
									if( !empty( $value ) ) : ?>
										<div class="<?php echo esc_attr( $field_id ); ?> col-sm-6">
											<span class="candidate-field-icon"><i class="fa text-primary"></i></span>
											<?php echo esc_html($value); ?>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>
							<?php if( !empty( $email ) ) : ?>
								<div class="email col-sm-6">
									<a href="mailto:<?php echo esc_attr($email); ?>">
										<i class="fa fa-envelope text-primary"></i>&nbsp;&nbsp;<?php echo esc_html($email); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
						<div class="row">
							<div class="candidate-social col-sm-6 pull-left" >
								<?php if ( $enable_upload && !empty( $file_cv ) && isset( $file_cv[0] ) && !empty( $file_cv[0] ) ) : ?>
									<div class="download pull-left">
										<span class="action-download">
											<i class="fa fa-download text-primary"></i>
											<a target="_blank" class="link-alt" href="<?php echo noo_get_file_upload( $file_cv[0] ); ?>" title="<?php echo esc_attr__('Download My Attachment', 'noo'); ?>"><?php echo esc_html__('Download My Attachment', 'noo'); ?></a>
										</span>
										<br/>
									</div>
								<?php endif; ?>
							</div>
							<div class="candidate-social col-sm-6 pull-right" >
								<?php if( !empty( $socials ) ) : ?>
									<?php foreach ( $socials as $social ) :
										if( empty( $social ) || !isset( $all_socials[$social] ) ) continue;
										$value = get_user_meta( $candidate->ID, $social, true );
										$data = $all_socials[$social];
										if( !empty( $value ) ) :
											$url = $social == 'email' ? 'mailto:' . $value : esc_url( $value );
										?>
											<a class="noo-icon fa <?php echo $data['icon']; ?>" href="<?php echo $url; ?>" target="_blank"></a>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php if( !empty( $candidate->description ) ) : ?>
						<div class="candidate-desc">
							<?php echo $candidate->description; ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<?php 
						$private_message = '<strong>' . __('The Candidate\'s contact information is private', 'noo') . '</strong>';
						echo apply_filters( 'noo_resume_candidate_private_message', $private_message, $resume_id );
					?>
				<?php endif; ?>
				<?php do_action( 'noo_resume_candidate_profile_after', $resume_id ); ?>
			</div>
		</div>
	</div>
<?php else: 
	echo '<h2 class="text-center" style="min-height:200px">'.__('Can not find this Candidate !','noo').'</h2>';
endif; ?>
<hr/>