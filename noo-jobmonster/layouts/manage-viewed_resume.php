<?php
if( is_front_page() || is_home()) {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
} else {
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
}

$viewed_resumes = jm_get_viewed_resumes();
$args = array(
	'post_type'=>'noo_resume',
	'paged' => $paged,
	'post_status'=>array('publish'),
	'post__in'=>array_merge($viewed_resumes, array(0)),
);
$r = new WP_Query($args);
ob_start();

$package = jm_get_job_posting_info();
$can_view_resume = isset($package['can_view_resume']) ? $package['can_view_resume'] === '1' : false;
$resume_view_limit = isset($package['resume_view_limit']) ? intval( $package['resume_view_limit'] ) : 0;
$resume_remain = jm_get_resume_view_remain();
$resume_view_expired = jm_is_resume_view_expired();

do_action('noo_member_manage_viewed_resume_before');

?>
<div class="member-manage">
	<?php if($r->have_posts()):?>
		<h3><?php echo sprintf(__("You've viewed %s resumes",'noo'),'<span class="text-primary">'.$r->found_posts.'</span>');?></h3>
		<?php if( $can_view_resume ) : ?>
			<?php if( $resume_remain == 0 || $resume_view_expired ) : ?>
				<?php $package_page_id = Noo_Job_Package::get_setting( 'package_page_id' ); ?>
				<em><?php echo __('You can\'t view new resume anymore. <a href="%s"></a>','noo'); ?></em><br/>
				<a href="<?php echo get_permalink( $package_page_id ); ?>"><i class="fa fa-long-arrow-right"></i>&nbsp;<?php _e('Upgrade your membership', 'noo'); ?></a>
			<?php else : ?>
				<?php if( $resume_view_limit > 0 ) : ?>
					<em><?php echo sprintf( __('You can view %d more resumes','noo'), $resume_remain ); ?></em><br/>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
		<form method="post">
			<div class="member-manage-table">
				<table class="table">
					<thead>
						<tr>
							<th><?php _e('Title','noo')?></th>
							<th class="hidden-xs"><?php _e('Category','noo')?></th>
							<th class="hidden-xs hidden-sm"><?php _e('Location','noo')?></th>
							<th class="hidden-xs hidden-sm"><?php _e('Date Modified','noo')?></th>
						</tr>
					</thead>
					<tbody>
						<?php while ($r->have_posts()): $r->the_post();global $post;?>
							<tr>
								<td class="title-col">
									<a href="<?php the_permalink()?>"><strong><?php the_title()?></strong></a>
								</td>
								<td class="hidden-xs category-col"><em><?php
									$job_category = noo_get_post_meta($post->ID,'_job_category','');
									$job_categories = array();
									if( !empty( $job_category ) ) {
										$job_category = noo_json_decode($job_category);
										$job_categories = empty( $job_category ) ? array() : get_terms( 'job_category', array('include' => array_merge( $job_category, array(-1) ), 'hide_empty' => 0, 'fields' => 'names') );
										echo implode(', ', $job_categories );
									}
								?></em></td>
								<td class="hidden-xs hidden-sm location-col">
									<?php
									$job_location = noo_get_post_meta($post->ID,'_job_location','');
									$job_locations = array();
									if( !empty( $job_location ) ) :
										$job_location = noo_json_decode($job_location);
										$job_locations = empty( $job_location ) ? array() : get_terms( 'job_location', array('include' => array_merge( $job_location, array(-1) ), 'hide_empty' => 0, 'fields' => 'names') );
									?>
									<i class="fa fa-map-marker"></i>&nbsp;<em><?php echo implode(', ', $job_locations ); ?></em>
									<?php endif; ?>
								</td>
								<td class="hidden-xs hidden-sm date-col"><span><i class="fa fa-calendar"></i>&nbsp;<em><?php the_modified_date(); ?></em></span></td>
							</tr>
						<?php endwhile;?>
					</tbody>
				</table>
			</div>
			<div class="member-manage-toolbar bottom-toolbar clearfix">
				<div class="member-manage-page pull-right">
					<?php noo_pagination(array(),$r)?>
				</div>
			</div>
		</form>
	<?php else:?>
		<h3><?php echo __("You haven't viewed any resumes yet.",'noo')?></h3>
		<p>
			<a href="<?php echo get_post_type_archive_link( 'noo_resume' ); ?>" class="btn btn-primary"><?php _e('Go to Resumes', 'noo')?></a>
		</p>
	<?php endif;?>
</div>
<?php
do_action('noo_member_manage_viewed_resume_after');
wp_reset_query();