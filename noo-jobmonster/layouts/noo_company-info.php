<?php

$company_name		= get_post_field( 'post_title', $company_id );
$logo_company 		= Noo_Company::get_company_logo( $company_id );
$all_socials		= noo_get_social_fields();
?>
<div class="company-desc" itemscope itemtype="http://schema.org/Organization">
	<div class="company-header">
		<div class="company-featured"><a href="<?php echo get_permalink( $company_id ); ?>"><?php echo $logo_company;?></a></div>
		<h3 class="company-title" itemprop="name"><?php if( !is_singular( 'noo_company' ) ) : ?><a href="<?php echo get_permalink( $company_id ); ?>"><?php endif; ?><?php echo esc_html( $company_name );?><?php if( !is_singular( 'noo_company' ) ) : ?></a><?php endif; ?></h3>
	</div>
	<div class="company-info">
		<div class="company-info-content">
			<?php
			wp_enqueue_script('noo-readmore');
			echo get_post_field( 'post_content', $company_id );
			?>
		</div>
		<?php
			// Custom Fields
			$fields = jm_get_company_custom_fields();
			$html = array();

			foreach ($fields as $field) {
				$id = jm_company_custom_fields_name($field['name'],$field);
				$value = noo_get_post_meta($company_id, $id, '');
				if( $field['name'] == '_address' ) {
					$location_term = !empty( $value ) ? get_term_by( 'id', $value, 'job_location' ) : '';
					$value = !empty( $location_term ) ? $location_term->name : '';
				}

				if( !empty( $value ) ) {
					$html[] = '<li>' . noo_display_field( $field, $id, $value, array( 'label_tag' => 'strong', 'label_class' => 'company-cf', 'value_tag' => 'span' ), false) . '</li>';
				}
			}
			if( !empty( $html ) && count( $html ) > 0 ) : ?>
				<div class="company-custom-fields">
					<strong class="company-cf-title"><?php _e('Information','noo');?></strong>
					<ul>
						<?php echo implode("\n", $html); ?>
					</ul>
				</div>
			<?php endif; ?>
		<?php 
			// Job's social info
			$socials = jm_get_company_socials();
			$html = array();

			foreach ($socials as $social) {
				if( !isset( $all_socials[$social] ) ) continue;
				$data = $all_socials[$social];
				$value = get_post_meta( $company_id, "_{$social}", true );
				if( !empty( $value ) ) {
					$url = $social == 'email' ? 'mailto:' . $value : esc_url( $value );
					$html[] = '<a title="' . sprintf( esc_attr__( 'Connect with us on %s' ,'noo' ), $data['label'] ) . '" class="noo-icon fa ' . $data['icon'] . '" href="' . $url . '" target="_blank"></a>';
				}
			}

			if( !empty( $html ) && count( $html ) > 0 ) : ?>
				<div class="job-social clearfix">
					<span class="noo-social-title"><?php _e('Connect with us','noo');?></span>
					<?php echo implode("\n", $html); ?>
				</div>
			<?php endif; ?>
			<?php if( $show_more_job ) :
				$exclude_this_job =  array();
				if( is_singular( 'noo_job' ) ) {
					$post_object = get_queried_object();
					$exclude_this_job  = get_queried_object_id();
				}

				$more_jobs = Noo_Company::get_company_jobs($company_id, (array) $exclude_this_job, 5 );
				if( !empty( $more_jobs ) ) :
			?>
					<div class="more-jobs clearfix">
						<strong><?php echo sprintf(__('More jobs from %s', 'noo'), $company_name); ?></strong>
						<ul>
							<?php foreach ($more_jobs as $job) : ?>
								<li><a class="more-job-title" href="<?php echo get_permalink( $job );?>"><?php echo get_the_title( $job );?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php endif; ?>
	</div>
</div>