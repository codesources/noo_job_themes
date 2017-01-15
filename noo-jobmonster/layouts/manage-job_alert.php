<?php

if( is_front_page() || is_home()) {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
} else {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
}

$current_user = wp_get_current_user();

$args = array(
	'post_type'=>'noo_job_alert',
	'paged' => $paged,
	'post_status'=>array('publish','pending'),
	'author'=>$current_user->ID
);
$r = new WP_Query($args);

do_action('noo_member_manage_job_alert_before');

$title_text = $r->found_posts ? sprintf( _n( "You've set up %s job alert", "You've set up %s job alerts", $r->found_posts, 'noo'), $r->found_posts, $current_user->user_email ) : __( 'You have no job alert', 'noo');
?>
<div class="member-manage">
	<h3><?php echo $title_text; ?></h3>
	<em><?php echo sprintf( __( 'The emails will be sent to "%s"', 'noo' ), $current_user->user_email ); ?></em>
	<form method="post">
		<div class="member-manage-toolbar top-toolbar hidden-xs clearfix">
		</div>
		<div style="display: none">
			<?php noo_form_nonce('job-alert-manage-action')?>
		</div>
		<div class="member-manage-table">
			<table class="table">
				<thead>
					<tr>
						<th><?php _e('Alert Name','noo')?></th>
						<th class="hidden-xs"><?php _e('keywords','noo')?></th>
						<th class="hidden-xs hidden-sm"><?php _e('Job Location','noo')?></th>
						<th class="hidden-xs"><?php _e('Job Category','noo')?></th>
						<th class="hidden-xs hidden-sm"><?php _e('Job Type','noo')?></th>
						<th class="hidden-xs hidden-sm"><?php _e('Frequency','noo')?></th>
						<th class="text-center hidden-xs"><?php _e('Action','noo')?></th>
					</tr>
				</thead>
				<tbody>
					<?php if($r->have_posts()):
					?>
						<?php while ($r->have_posts()): $r->the_post();global $post;
							$job_location = noo_get_post_meta(get_the_ID(),'_job_location');
							$job_locations = array();
							if( !empty( $job_location ) ) {
								$job_location = noo_json_decode($job_location);
								$job_locations = empty( $job_location ) ? array() : get_terms( 'job_location', array('include' => array_merge( $job_location, array(-1) ), 'hide_empty' => 0, 'fields' => 'names') );	
							}
							$job_category = noo_get_post_meta(get_the_ID(),'_job_category','');
							$job_categories = array();
							if( !empty( $job_category ) ) {
								$job_category = noo_json_decode($job_category);
								$job_categories = empty( $job_category ) ? array() : get_terms( 'job_category', array('include' => array_merge( $job_category, array(-1) ), 'hide_empty' => 0, 'fields' => 'names') );
							}
							
							$job_type = noo_get_post_meta(get_the_ID(),'_job_type');
							$job_type_term = !empty($job_type) ? get_term_by( 'id', $job_type, 'job_type' ) : null;
							$job_type_color = !empty( $job_type_term ) && isset( $job_type_term->term_id ) ? jm_get_job_type_color( $job_type_term->term_id ) : '';
						?>
							<tr>
								<td><strong><?php the_title()?></strong></td>
								<td class="hidden-xs"><em><?php echo noo_get_post_meta(get_the_ID(),'_keywords')?></em></td>
								<td class="hidden-xs hidden-sm"><em><?php echo implode(', ', $job_locations ); ?></em></td>
								<td class="hidden-xs"><em><?php echo implode(', ', $job_categories ); ?></em></td>
								<td class="hidden-xs hidden-sm">
									<?php if( !empty($job_type_term) ) : ?>
									<span class="job-type"><a href="<?php echo get_term_link($job_type_term,'job_type'); ?>" <?php echo !empty($job_type_color) ? 'style="color: ' . $job_type_color . ';"' : ''; ?>><i class="fa fa-bookmark"></i>&nbsp;<em><?php echo esc_html($job_type_term->name); ?></em></a></span>
									<?php endif; ?>
								</td>
								<td class="hidden-xs hidden-sm"><em>
								<?php
								$frequency_arr = Noo_Job_Alert::get_frequency();
								$frequency =  noo_get_post_meta(get_the_ID(),'_frequency');
								echo $frequency && isset($frequency_arr[$frequency]) ? $frequency_arr[$frequency]:'';
								?>
								</em></td>
								<td class="member-manage-actions hidden-xs text-center">
									<a href="<?php echo Noo_Member::get_edit_job_alert_url(get_the_ID())?>" class="member-manage-action" data-toggle="tooltip" title="<?php esc_attr_e('Edit Job Alert','noo')?>"><i class="fa fa-pencil"></i></a>
									<a href="<?php echo wp_nonce_url( add_query_arg(array('action'=>'delete_job_alert','job_alert_id'=>get_the_ID())), 'edit-job-alert' );?>" class="member-manage-action action-delete" data-toggle="tooltip" title="<?php esc_attr_e('Delete Job Alert','noo')?>"><i class="fa fa-trash-o"></i></a>
								</td>
							</tr>
						<?php endwhile;?>
					<?php else:?>
					<tr>
						<td colspan="7"><h3><?php _e('No saved job alerts','noo')?></h3></td>
					</tr>
					<?php endif;?>
				</tbody>
			</table>
		</div>
		<div class="member-manage-toolbar bottom-toolbar clearfix">
			<div class="member-manage-page pull-left">
				<a href="<?php echo Noo_Member::get_endpoint_url('add-job-alert'); ?>" class="btn btn-primary"><?php _e('Create New', 'noo'); ?></a>
			</div>
			<div class="member-manage-page pull-right">
				<?php noo_pagination(array(),$r)?>
			</div>
		</div>
	</form>
</div>
<?php
do_action('noo_member_manage_job_alert_after');
wp_reset_query();