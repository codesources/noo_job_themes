<?php
$current_user = wp_get_current_user();

$status_filter = isset( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '';

$all_statuses = jm_get_job_status();
unset( $all_statuses['draft'] );

$job_need_approve = jm_get_job_setting( 'job_approve','' ) == 'yes';
if( !$job_need_approve ) {
	unset( $all_statuses['pending'] );
}
if( !jm_is_woo_job_posting() ) {
	unset( $all_statuses['pending_payment'] );
}
$r = jm_user_job_query( $current_user->ID, true, ( !empty( $status_filter ) ? array( $status_filter ) : array() ) );
$package_data = jm_get_job_posting_info();
$remain_featured_job = jm_get_feature_job_remain();
$can_set_featured_job = jm_can_set_feature_job();
ob_start();
do_action('noo_member_manage_job_before');
$bulk_actions = (array) apply_filters('noo_member_manage_job_bulk_actions', array(
	'publish'=>__('Publish','noo'),
	'unpublish'=>__('Unpublish','noo'),
	'delete'=>__('Delete','noo')
));

$user_email = $current_user->user_email;
?>
<div class="member-manage">
	<?php ?>
		<?php if( $r->found_posts ) : ?>
			<h3><?php echo sprintf( _n( "We found %s job", "We found %s jobs", $r->found_posts, 'noo'), $r->found_posts ); ?></h3>
		<?php else : ?>
			<h3><?php echo __("No jobs found",'noo')?></h3>
		<?php endif; ?>
		<em><strong><?php _e('Note:','noo')?></strong> <?php _e('Expired listings will be removed from public view.','noo')?></em><br/>
		<?php if( $remain_featured_job > 0 ) : ?>
			<em><?php echo sprintf( _n( 'You can set %d more job to be featured. Featured jobs cannot be reverted.', 'You can set %d more jobs to be featured. Featured jobs cannot be reverted.', $remain_featured_job, 'noo' ), $remain_featured_job ); ?></em>
		<?php endif; ?>
		<form method="get">
			<div class="member-manage-toolbar top-toolbar hidden-xs clearfix">
				<div class="bulk-actions pull-left clearfix">
					<strong><?php _e('Action:','noo')?></strong>
					<div class="form-control-flat">
						<select name="action">
							<option selected="selected" value="-1"><?php _e('-Bulk Actions-','noo')?></option>
							<?php foreach ($bulk_actions as $action=>$label):?>
							<option value="<?php echo esc_attr($action)?>"><?php echo esc_html($label)?></option>
							<?php endforeach;?>
						</select>
						<i class="fa fa-caret-down"></i>
					</div>
					<button type="submit" class="btn btn-primary"><?php _e('Go', 'noo')?></button>
				</div>
				<div class="bulk-actions pull-right clearfix">
					<strong><?php _e('Filter:','noo')?></strong>
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
				<?php noo_form_nonce('job-manage-action')?>
			</div>
			<div class="member-manage-table">
				<table class="table">
					<thead>
						<tr>
							<th class="check-column"><div class="form-control-flat"><label class="checkbox"><input type="checkbox"><i></i></label></div></th>
							<th><?php _e('Title','noo')?></th>
							<th class="hidden-xs"><?php _e('Featured?', 'noo'); ?></th>
							<th class="hidden-xs hidden-sm"><?php _e('Location','noo')?></th>
							<th class="hidden-xs"><?php _e('Closing','noo')?></th>
							<th class="text-center"><?php _e('Apps','noo')?></th>
							<th class="text-center"><?php _e('Status','noo')?></th>
							<th class="text-center"><?php _e('Action','noo')?></th>
						</tr>
					</thead>
					<tbody>
						<?php if($r->have_posts()) : ?>
							<?php while ($r->have_posts()): $r->the_post(); global $post;
								$status = $status_class = jm_correct_job_status( $post->ID, $post->post_status );
								$statuses = jm_get_job_status();
								$status_text = '';
								if ( isset( $statuses[ $status ] ) ) {
									$status_text = $statuses[ $status ];
								} else {
									$status_text = __( 'Inactive', 'noo' );
									$status_class = 'inactive';
								}
							?>
								<tr>
									<td class="check-column"><div class="form-control-flat"><label class="checkbox"><input type="checkbox" name="ids[]" value="<?php the_ID()?>"><i></i></label></div></td>
									<td>
										<?php if( $status == 'pending' || $status == 'pending_payment' ) : ?>
											<a href="<?php echo esc_url(add_query_arg( 'job_id', get_the_ID(), Noo_Member::get_endpoint_url('preview-job') )); ?>"><strong><?php the_title()?></strong></a>
										<?php else : ?>
											<a href="<?php the_permalink()?>"><strong><?php the_title()?></strong></a>
											<?php $notify_email = get_post_meta( get_the_ID(), '_application_email', true );
											if( !empty( $notify_email ) && $notify_email != $user_email ) : ?>
												<br/>
												<em class="hidden-xs"><?php echo sprintf( __('Notify email: %s', 'noo'), $notify_email ); ?></em>
											<?php endif; ?>
										<?php endif; ?>
									</td>
									<td class="hidden-xs text-center">
										<?php 
										$featured = noo_get_post_meta($post->ID,'_featured');
										if( empty( $featured ) ) {
											// Update old data
											update_post_meta( $post->ID, '_featured', 'no' );
										}
										if ( 'yes' === $featured ) :
											echo '<span class="noo-job-feature" data-toggle="tooltip" title="'.esc_attr__('Featured','noo').'"><i class="fa fa-star"></i></span>';
										elseif( $can_set_featured_job ) :
										?>
											<a href="<?php echo wp_nonce_url( add_query_arg(array('action'=>'featured','job_id'=>get_the_ID())), 'job-manage-action' )?>">
												<span class="noo-job-feature not-featured" data-toggle="tooltip"  title="<?php _e('Set Featured','noo'); ?>"><i class="fa fa-star-o"></i></span>
											</a>
										<?php else : ?>
											<span class="noo-job-feature not-featured" title="<?php _e('Set Featured','noo'); ?>"><i class="fa fa-star-o"></i></span>
										<?php endif; ?>
									</td>
									<td class="hidden-xs hidden-sm"><i class="fa fa-map-marker"></i>&nbsp;<em><?php echo get_the_term_list(get_the_ID(),'job_location','',', ')?></em></td>
									<td class="job-manage-expires hidden-xs">
										<?php 
											$closing = noo_get_post_meta( $post->ID, '_closing' );
											$closing = !is_numeric( $closing ) ? strtotime( $closing ) : $closing;
											$closing = !empty( $closing ) ? date_i18n( get_option('date_format'), $closing ) : '';
											if( !empty( $closing ) ) :
										?>
											<span><i class="fa fa-calendar"></i>&nbsp;<em><?php echo $closing; ?></em></span>
										<?php else : ?>
											<span class="text-center"><?php echo __('Equal to expired date', 'noo'); ?></span>
										<?php endif; ?>
									</td>
									<td class="job-manage-app text-center">
										<span>
										<?php 
										$applications = get_posts(array(
											'post_type' => 'noo_application',
											'posts_per_page'=>-1,
											'post_parent'=>$post->ID,
											'post_status'=>array('publish','pending','rejected'),
											'suppress_filters' => false
										));
										echo absint(count($applications));
										?>
										</span>
									</td>
									<td class="text-center">
										<span class="jm-status jm-status-<?php echo esc_attr($status_class) ?>">
										<?php echo esc_html($status_text)?>
										</span>
									</td>
									<td class="member-manage-actions text-center">
										<?php if(Noo_Member::can_change_job_state( $post->ID, get_current_user_id() )):?>
											<?php if($status == 'publish'):?>
												<a href="<?php echo wp_nonce_url( add_query_arg(array('action'=>'unpublish','job_id'=>get_the_ID())), 'job-manage-action' );?>" class="member-manage-action"  data-toggle="tooltip" title="<?php esc_attr_e('Unpublish Job','noo')?>"><i class="fa fa-toggle-on"></i></a>
											<?php else:?>
												<a href="<?php echo wp_nonce_url( add_query_arg(array('action'=>'publish','job_id'=>get_the_ID())), 'job-manage-action' );?>" class="member-manage-action" data-toggle="tooltip" title="<?php esc_attr_e('Publish Job','noo')?>"><i class="fa fa-toggle-off"></i></a>
											<?php endif;?>
										<?php endif;?>
										<?php if(Noo_Member::can_edit_job( $post->ID, get_current_user_id() )):?>
											<a href="<?php echo Noo_Member::get_edit_job_url(get_the_ID())?>" class="member-manage-action" data-toggle="tooltip" title="<?php esc_attr_e('Edit Job','noo')?>"><i class="fa fa-pencil"></i></a>
										<?php endif; ?>
										<?php if( $status == 'expired' ) : ?>
											<a href="#" class="member-manage-action" data-toggle="tooltip" title="<?php esc_attr_e('Expired Job','noo')?>"><i class="fa fa-clock-o"></i></a>
										<?php endif;?>
										<a href="<?php echo wp_nonce_url( add_query_arg(array('action'=>'delete','job_id'=>get_the_ID())), 'job-manage-action' );?>" class="member-manage-action action-delete" data-toggle="tooltip" title="<?php esc_attr_e('Delete Job','noo')?>"><i class="fa fa-trash-o"></i></a>
									</td>
								</tr>
							<?php endwhile;?>
						<?php else:?>
							<tr>
								<td><a href="<?php echo Noo_Member::get_post_job_url(); ?>" class="btn btn-primary"><?php _e('Post Job', 'noo')?></a></td>
							</tr>
						<?php endif;?>
					</tbody>
				</table>
			</div>
			<div class="member-manage-toolbar bottom-toolbar clearfix">
				<div class="member-manage-page pull-right clearfix">
					<?php noo_pagination(array(),$r)?>
				</div>
			</div>
		</form>
</div>
<?php
do_action('noo_member_manage_job_after');
wp_reset_query();