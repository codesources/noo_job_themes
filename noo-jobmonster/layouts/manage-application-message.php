<?php
$employer_message_title = noo_get_post_meta($application_id, '_employer_message_title', '');
$employer_message_body = noo_get_post_meta($application_id, '_employer_message_body', '');
$mesage_excerpt = empty($employer_message_title) ? wp_trim_words( $employer_message_body, 10 ) : $employer_message_title;
$mesage_excerpt = !empty($mesage_excerpt) ? $mesage_excerpt . __('...', 'noo') : '';
						
?>
<div id="employerMsgModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="employerMsgModalLabel" aria-hidden="false" style="display: block;">
	<div class="modal-dialog">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    			<h4 class="modal-title text-center" id="employerMsgModalLabel">
    				<?php 
    				if($mode):
    					echo sprintf( __('%s\'s Application','noo'), $application->post_title );;
    				else:
    					_e('Employer\'s Message', 'noo');
    				endif; ?>
    			</h4>
    		</div>
    		<div class="modal-body">
    			<?php if($mode):?>
    			<div class="application-message clearfix">
    				<p class="employer_title"><?php echo wpautop( $application->post_content );?></p>
    				<?php
    				$fields = jm_get_application_custom_fields();
    				if( !empty( $fields ) ) : ?>
    					<ul class="custom-fields">
	    					<?php foreach ( $fields as $field ) :
	    						if( $field['name'] == 'application_message' ) continue;
								$label = isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'];
								$value = jm_get_application_field_value( $application->ID, $field );
								$field_id = jm_application_custom_fields_name($field['name'], $field);

								if( empty($value) ) continue;
								?>
								<li class="<?php echo esc_attr( $field_id ); ?>">
									<?php noo_display_field( $field, $field_id, $value ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
    			</div>
    			<?php else:?>
    			<div class="row">
	    			<div class="loop-item-wrap col-sm-9">
					    <div class="item-featured">
							<a href="<?php echo get_permalink( $job->ID ); ?>">
								<?php echo $logo_company;?>
							</a>
						</div>
						<div class="loop-item-content">
							<h2 class="loop-item-title">
								<a href="<?php echo get_permalink( $job->ID ); ?>" title="<?php echo esc_attr( sprintf( __( 'Permanent link to: "%s"','noo' ), the_title_attribute( 'echo=0' ) ) ); ?>"><?php echo get_the_title( $job->ID ); ?></a>
							</h2>
						</div>
					</div>
	    			<div class="application-status col-sm-3">
	    				<?php 
						$status   = $application->post_status;
						$statuses = Noo_Application::get_application_status();
						$status_class = $status;
						if ( isset( $statuses[ $status ] ) ) {
							$status = $statuses[ $status ];
						} else {
							$status = __( 'Inactive', 'noo' );
							$status_class = 'inactive';
						}
						?>
						<span class="job-application-status job-application-status-<?php echo sanitize_html_class($status_class) ?>">
						<?php echo esc_html($status)?>
						</span>
	    			</div>
	    			<div class="loop-item-wrap col-xs-12">
						<div class="loop-item-content">
							<?php jm_the_job_meta(array( 'fields' => array( 'job_type', 'job_location', 'job_category' ) ), $job);?>
						</div>
	    			</div>
				</div>
    			<hr/>
    			<div class="employer-message clearfix">
    				<strong class="employer_title"><?php echo esc_html( $employer_message_title );?></strong>
    				<p class="employer_title" style="overflow: auto;"><?php echo wpautop( $employer_message_body );?></p>
    			</div>
    			<?php endif;?>
    		</div>
		</div>
	</div>
</div>
