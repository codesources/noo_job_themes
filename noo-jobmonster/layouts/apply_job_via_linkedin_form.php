<?php
global $post;
$job_id = empty($job_id) ? $post->ID : $job_id;
$job_title = get_the_title($job_id);
$company_name = '';
?>
<a id="apply_via_linkedin" class="btn btn-default" href="#"><?php _e('Apply via LinkedIn','noo');?></a>
<?php Noo_Job::load_linkedin_script(); ?>
<div id="applyJobviaLinkedInModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="applyJobviaLinkedInModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="applyJobviaLinkedInModalLabel"><?php esc_html_e('Apply for job via LinkedIn','noo')?></h4>
			</div>
			<div class="modal-body">
				<form id="apply_job_via_linkedin_form" class="form-horizontal jform-validate" method="post" enctype="multipart/form-data">
					<div style="display: none">
						<input type="hidden" name="action" value="apply_job_via_linkedin"> 
						<input type="hidden" name="in-profile-data" id="in-profile-data" />
						<input type="hidden" name="job_id" value="<?php echo esc_attr($job_id)?>">			
						<?php wp_nonce_field('noo-apply-job-via-linkedin', '_wpnonce')?>
					</div>
					<div class="form-group text-center noo-ajax-result" style="display: none"></div>
					<div class="apply-via-linkedin-profile">
						<input type="hidden" name="_attachment" class="in-profile-url">
						<div class="form-group">
							<div class="row">
								<div class="col-md-4">
									<div class="in-profile-picture">
										<!-- <img alt="" src=""> -->
									</div>
								</div>
								<div class="col-md-8">
									<div class="in-profile-overview">
										<div class="in-profile-name"></div>
										<div class="in-profile-headline"></div>
										<div class="in-profile-location"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<dl>
								<dt class="in-profile-positions"><?php esc_html_e('Current','noo')?></dt>
								<dd class="in-profile-positions"><ul></ul></dd>
					
								<dt class="in-profile-past"><?php esc_html_e('Past','noo')?></dt>
								<dd class="in-profile-past"><ul></ul></dd>
					
								<dt class="in-profile-educations"><?php esc_html_e('Education','noo')?></dt>
								<dd class="in-profile-educations"><ul></ul></dd>
					
								<dt class="in-profile-email"><?php esc_html_e('Email','noo')?></dt>
								<dd class="in-profile-email in-profile-email-value"></dd>
								<?php 
								$cover_letter_field = jm_get_setting('noo_job_linkedin','cover_letter_field');
								if($cover_letter_field!='hidden'){
								?>
								<dt><?php esc_html_e('Cover letter','noo')?><?php if ( $cover_letter_field === 'optional' ) _e( '(optional)', 'noo' ); ?></dt>
								<dd class="apply-via-linkedin-cover-letter <?php if ( $cover_letter_field === 'required' ) echo 'required-field'; ?>">
									<textarea id="apply-via-linkedin-cover-letter" class="form-control <?php if ( $cover_letter_field === 'required' ) echo 'jform-validate' ?>" <?php if ( $cover_letter_field === 'required' ) echo 'required="required"'; ?> rows="5" name="linkedin-cover-letter"><?php echo sprintf( __("I am very interested in the %s position at %s. I believe my skills and work experience make me an ideal candidate for this role. I look forward to speaking to you about this position soon. Thank you for your consideration.\nBest regards \n", 'noo' ) , $job_title, $company_name ); ?></textarea>
								</dd>
								<?php } ?>
							</dl>
						</div>
					</div>
					<div class="modal-actions">
						<button type="submit" class="btn btn-primary"><?php _e('Send application','noo')?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		if ( typeof IN === 'object' ) {
			$('#apply_via_linkedin').click(function(e){
				e.stopPropagation();
				e.preventDefault();
				
				if ( IN.User.isAuthorized() ) {
					onLinkedInAuth();
				} else {
					IN.UI.Authorize().place();
					IN.Event.on(IN, "auth", onLinkedInAuth);
				}
				return false;
			});
			function displayProfileData(data){
				var data = data.values[0]; 
				var _in_profile = jQuery('.apply-via-linkedin-profile');

				_in_profile.find('.in-profile-name').html( data.formattedName );
				_in_profile.find('.in-profile-headline').html( data.headline );
				_in_profile.find('.in-profile-location').html( data.location.name );
				_in_profile.find('dd.in-profile-email').html( data.emailAddress );
				_in_profile.find('input.in-profile-url').val( data.publicProfileUrl );
				$('textarea#apply-via-linkedin-cover-letter').append( data.formattedName );
				$('input#in-profile-data').val( JSON.stringify( data, null, '' ) );

				if ( data.pictureUrl ) {
					// reset img
					$('.in-profile-picture').html('');
					$('<img/>').attr('src', data.pictureUrl ).attr('alt', data.formattedName ).appendTo('.in-profile-picture');
					// _in_profile.find('img').attr('src', data.pictureUrl );
					// _in_profile.find('img').attr('alt', data.formattedName );
				} else {
					_in_profile.find('.in-profile-picture').parent().hide();
				}

				if (  typeof data.threeCurrentPositions != 'undefined' &&  data.threeCurrentPositions._total > 0 ) {
					$( data.threeCurrentPositions.values ).each( function( index ) {
						_in_profile.find('dd.in-profile-positions ul').append( '<li>' + data.threeCurrentPositions.values[ index ].title + " - " + data.threeCurrentPositions.values[ index ].company.name + '</li>' );
					});
				} else {
					_in_profile.find('.in-profile-positions').hide();
				}

				if ( typeof data.threePastPositions != 'undefined' &&  data.threePastPositions._total > 0 ) {
					$( data.threePastPositions.values ).each( function( index ) {
						_in_profile.find('dd.in-profile-past ul').append( '<li>' + data.threePastPositions.values[ index ].title + " - " + data.threePastPositions.values[ index ].company.name + '</li>' );
					});
				} else {
					_in_profile.find('.in-profile-past').hide();
				}

				if ( typeof data.educations != 'undefined' &&  data.educations._total > 0 ) {
					$( data.educations.values ).each( function( index ) {
						if ( data.educations.values[ index ].degree ) {
							_in_profile.find('dd.in-profile-educations ul').append( '<li>' + data.educations.values[ index ].schoolName + ', ' + data.educations.values[ index ].degree + '</li>' );
						} else {
							_in_profile.find('dd.in-profile-educations ul').append( '<li>' + data.educations.values[ index ].schoolName + '</li>' );
						}
					});
				} else {
					_in_profile.find('.in-profile-educations').hide();
				}
				$('#applyJobviaLinkedInModal').modal('show');
			}
			function onLinkedInAuth(){
				IN.API.Profile("me")
				.fields( 
					[ 
						"firstName", 
						"lastName", 
						"formattedName", 
						"headline", 
						"summary", 
						"specialties", 
						"associations",
						"interests",
						"pictureUrl", 
						"publicProfileUrl", 
						"emailAddress",
						"location:(name)",
						"dateOfBirth",
						"threeCurrentPositions:(title,company,summary,startDate,endDate,isCurrent)",
						"threePastPositions:(title,company,summary,startDate,endDate,isCurrent)",
						"positions:(title,company,summary,startDate,endDate,isCurrent)",
						"educations:(schoolName,degree,fieldOfStudy,startDate,endDate,activities,notes)", 
						"skills:(skill)",
						"phoneNumbers",
						"primaryTwitterAccount"
					] 
				).result( function(result) {
					if($('.in-profile-email-value').html() === '' && $('.in-profile-name').html() === ''){
						displayProfileData( result );
					} else{
						$('#applyJobviaLinkedInModal').modal('show');
					}
				});
			}
		}
	});
</script>