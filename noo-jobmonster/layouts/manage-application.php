<?php
if( is_front_page() || is_home()) {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
} else {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
}

$job_filter = isset( $_REQUEST['job'] ) ? absint( $_REQUEST['job'] ) : 0;
$jobs_list = get_posts(array(
	'post_type'=>'noo_job',
	'post_status'=>array('publish','pending','expired'),
	'author'=>get_current_user_id(),
	'posts_per_page'=>-1,
	'suppress_filters' => false
));
$job_ids = array_map(create_function('$o', 'return $o->ID;'), $jobs_list);

$status_filter = isset( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '';
$all_statuses = Noo_Application::get_application_status();
unset( $all_statuses['inactive'] );

$args = array(
	'post_type'=>'noo_application',
	'paged' => $paged,
	'post_parent__in'=>array_merge($job_ids, array(0)), // make sure return zero application if there's no job.
	'post_status'=> !empty( $status_filter ) ? array( $status_filter ) : array('publish','pending','rejected'),
);
if(!empty($job_filter) && in_array($job_filter, $job_ids)){
	$args['post_parent__in'] = array($job_filter);
}
$r = new WP_Query($args);
$bulk_actions = (array) apply_filters('noo_member_manage_application_bulk_actions', array(
	'approve'=>__('Approve','noo'),
	'reject'=>__('Reject','noo'),
	'delete'=>__('Delete','noo')
));
ob_start();
do_action('noo_member_manage_application_before');
?>
<div class="member-manage">
	<?php if( $r->found_posts > 0 ) : ?>
		<h3><?php echo sprintf( _n( "You've received %s application", "You've received %s applications", $r->found_posts, 'noo'), $r->found_posts ); ?></h3>
	<?php else : ?>
		<h3><?php echo __( "You've received no application", 'noo'); ?></h3>
	<?php endif; ?>
	<form method="get">
		<div class="member-manage-toolbar top-toolbar hidden-xs clearfix">
			<div class="bulk-actions pull-left clearfix">
				<strong><?php _e('Action:','noo')?></strong>
				<div class="form-control-flat">
					<select name="action">
						<option selected="selected" value="-1"><?php _e('Bulk Actions','noo')?></option>
						<?php foreach ($bulk_actions as $action=>$label):?>
						<option value="<?php echo esc_attr($action)?>"><?php echo esc_html($label)?></option>
						<?php endforeach;?>
					</select>
					<i class="fa fa-caret-down"></i>
				</div>
				<button href="#" class="btn btn-primary bulk-manage-application-action"><?php _e('Go', 'noo')?></button>
			</div>
			<div class="bulk-actions pull-right clearfix">
				<strong><?php _e('Filter:','noo')?></strong>
				<div class="form-control-flat" style="width: 200px;">
					<select name="job">
						<option value="0"><?php _e('All Jobs','noo')?></option>
						<?php foreach ($jobs_list as $a_job):?>
						<option value="<?php echo esc_attr($a_job->ID)?>" <?php selected($job_filter,$a_job->ID)?> ><?php echo $a_job->post_title?></option>
						<?php endforeach;?>
					</select>
					<i  class="fa fa-caret-down"></i>
				</div>
				<div class="form-control-flat" style="width: 200px;">
					<select name="status">
						<option value=""><?php _e('All Status','noo')?></option>
						<?php foreach ($all_statuses as $key => $status):?>
						<option value="<?php echo esc_attr($key)?>" <?php selected($status_filter,$key)?> ><?php echo $status; ?></option>
						<?php endforeach;?>
					</select>
					<i  class="fa fa-caret-down"></i>
				</div>
				<button type="submit" class="btn btn-primary"><?php _e('Go', 'noo')?></button>
			</div>
		</div>
		<div style="display: none">
			<?php noo_form_nonce('application-manage-action')?>
		</div>
		<div class="member-manage-table">
			<table class="table">
				<thead>
					<tr>
						<th class="check-column"><div class="form-control-flat"><label class="checkbox"><input type="checkbox"><i></i></label></div></th>
						<th class="appl-col-candidate"><?php _e('Candidate','noo')?></th>
						<th class="appl-col-job"><?php _e('Applied job','noo')?></th>
						<th class="appl-col-message hidden-xs hidden-sm"><?php _e('Message','noo')?></th>
						<th class="appl-col-attachment"><?php _e('Attachment','noo')?></th>
						<th class="appl-col-date hidden-xs hidden-sm"><?php _e('Applied Date','noo')?></th>
						<th class="appl-col-status text-center"><?php _e('Status','noo')?></th>
						<th class="appl-col-action text-center"><?php _e('Action','noo')?></th>
						
					</tr>
				</thead>
				<tbody>
					<?php if($r->have_posts()) : ?>
						<?php 
						while ($r->have_posts()): $r->the_post();global $post;
							
							$mesage_excerpt = !empty($post->post_content) ? wp_trim_words( $post->post_content, 10 ) : '';
							$mesage_excerpt = !empty($mesage_excerpt) ? $mesage_excerpt . __('...', 'noo') : '';
							$candidate_email = noo_get_post_meta($post->ID,'_candidate_email');

							$avatar = noo_get_avatar($candidate_email, 40);

						?>
							<tr>
								<td class="check-column"><div class="form-control-flat"><label class="checkbox"><input type="checkbox" name="ids[]" value="<?php the_ID()?>"><i></i></label></div></td>
								<td>
									<?php $candidate_link = apply_filters( 'noo_application_candidate_link', '', $post->ID, $candidate_email ); ?>
									<span class="candidate-avatar">
										<?php echo ($avatar) ?>
									</span>
									<span class="candidate-name"><?php if(!empty($candidate_link)) echo '<a href="'. $candidate_link . '">'; ?><?php echo get_the_title(); ?><?php if(!empty($candidate_link)) echo '</a>'; ?></span>
								</td>
								<td>
									<?php 
									
									$parent_job = get_post( $post->post_parent );
									if ( $parent_job && $parent_job->post_type === 'noo_job' ) {
										echo ('<a href="' . get_permalink( $parent_job->ID ) . '">' . $parent_job->post_title . '</a>');
									} elseif ( $parent_job = noo_get_post_meta( $post->ID, '_job_applied_for', true ) ) {
										echo esc_html( $parent_job );
									} else {
										echo ('<span class="na">&ndash;</span>');
									}
									?>
								</td>
								<td class="hidden-xs hidden-sm">
									<?php
										$readmore_link = '<a href="#" data-application-id="' . esc_attr($post->ID) . '" class="member-manage-action view-employer-message" data-mode="1"><em class="text-primary">' . __('View Detail', 'noo') . '&nbsp;<i class="fa fa-long-arrow-right"></i></em></a>';
										$readmore_link = apply_filters( 'noo-manage-application-message-link', $readmore_link, $post->ID );
									?>
									<strong class="hidden-xs hidden-sm"><?php echo esc_html($mesage_excerpt); ?></strong><br/><?php echo $readmore_link; ?>
								</td>
								<td>
									<?php 
									$attachment = jm_correct_application_attachment( $post->ID );
									if ( !empty( $attachment ) ) :
										if( is_string( $attachment) && strpos($attachment,'linkedin') ) : ?>
											<a class="application-attachment" data-application-id="<?php echo $post->ID; ?>" href="<?php echo esc_url( $attachment ); ?>" data-toggle="tooltip" title="<?php echo esc_attr__('LinkedIn profile','noo'); ?>" target="_blank"><i class="fa fa-linkedin"></i></a>
										<?php else :
											$attachment = !is_array( $attachment ) ? array( $attachment ) : $attachment;
											foreach ($attachment as $atm) : ?>
												<?php $file_name = basename( $atm ); ?>
												<a class="application-attachment" data-application-id="<?php echo $post->ID; ?>" href="<?php echo esc_url( $atm ); ?>" data-toggle="tooltip" title="<?php echo esc_attr( $file_name ) ?>"><i class="fa fa-cloud-download"></i></a>
											<?php endforeach;
										endif;
										echo '<br/>';
									endif;

									$resume = noo_get_post_meta( $post->ID, '_resume', '' );
									if ( !empty( $resume ) ) :
										$resume_link = add_query_arg('application_id', $post->ID, get_permalink($resume));
									?>
										<a class="application-resume" data-application-id="<?php echo $post->ID; ?>" href="<?php echo esc_url( $resume_link ); ?>" data-toggle="tooltip" title="<?php echo esc_attr__('Resume','noo'); ?>"><i class="fa fa-file-text-o"></i></a>
									<?php endif;

									if( empty( $attachment ) && empty( $resume ) ) {
										echo ('<span class="na">&ndash;</span>');
									}
									?>
								</td>
								<td class="hidden-xs hidden-sm"><span><i class="fa fa-calendar"></i> <em><?php echo date_i18n( get_option('date_format'), strtotime( $post->post_date ) )?></em></span></td>
								<td class="text-center">
									<?php 
									$status   = $post->post_status;
									$status_class = $status;
									$statuses = Noo_Application::get_application_status();
									if ( isset( $statuses[ $status ] ) ) {
										$status = $statuses[ $status ];
									} else {
										$status = __( 'Inactive', 'noo' );
										$status_class = 'inactive';
									}
									?>
									<span class="jm-status jm-status-<?php echo esc_attr($status_class) ?>">
										<?php echo esc_html($status)?>
									</span>
								</td>
								<td class="member-manage-actions text-center">
									<?php
									if( $post->post_status == 'pending' ) :
										$approve_link = '<a href="#" class="member-manage-action approve-reject-action" data-hander="approve" data-application-id="' . $post->ID . '" data-toggle="tooltip" title="' . esc_attr__('Approve Application','noo') . '"><i class="fa fa-check-square-o"></i></a>';
										$reject_link = '<a href="#" class="member-manage-action approve-reject-action" data-hander="reject"  data-application-id="' . $post->ID . '" data-toggle="tooltip" title="' . esc_attr__('Reject Application','noo') . '"><i class="fa fa-ban"></i></a>';

										$approve_link = apply_filters( 'noo-manage-application-approve-link', $approve_link, $post->ID );
										$reject_link = apply_filters( 'noo-manage-application-reject-link', $reject_link, $post->ID );

										echo $approve_link;
										echo $reject_link;
									else: ?>
										<a class="member-manage-action action-no-link" title="<?php esc_attr_e('Approve Application','noo')?>"><i class="fa fa-check-square-o"></i></a>
										<a class="member-manage-action action-no-link" title="<?php esc_attr_e('Reject Application','noo')?>"><i class="fa fa-ban"></i></a>
									<?php endif;?>
									<?php
										$email_link = '<a href="mailto:' . $candidate_email . '" class="member-manage-action" data-toggle="tooltip" title="' . esc_attr__('Email Candidate','noo') . '"><i class="fa fa-envelope-o"></i></a>';

										$email_link = apply_filters( 'noo-manage-application-email-link', $email_link, $post->ID );
										echo $email_link;
									?>
									<?php do_action( 'noo-manage-application-action', $post->ID ); ?>
									<a href="<?php echo wp_nonce_url( add_query_arg(array('action'=>'delete','application_id'=>$post->ID)), 'application-manage-action' );?>" class="member-manage-action action-delete" data-toggle="tooltip" title="<?php esc_attr_e('Delete Application','noo')?>"><i class="fa fa-trash-o"></i></a>
								</td>
								
							</tr>
						<?php endwhile;?>
					<?php else:?>
					<tr>
						<td colspan="8" class="text-center"><h3><?php _e('No Applications','noo')?></h3></td>
					</tr>
					<?php endif;?>
				</tbody>
			</table>
		</div>
		<div class="member-manage-toolbar bottom-toolbar clearfix">
			<div class="member-manage-page pull-right">
				<?php noo_pagination(array(),$r)?>
			</div>
		</div>
	</form>
</div>
<?php
do_action('noo_member_manage_application_after');
wp_reset_query();