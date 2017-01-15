<div class="noo-main noo-company noo-company-style2" role="main">
    <div class="noo-company-profile" itemscope itemtype="http://schema.org/Organization">
        <div class="noo-company-sumary col-md-12">
            <?php
            $company_name		= get_post_field( 'post_title', get_the_ID() );
            $logo_company 		= Noo_Company::get_company_logo( get_the_ID() );
            ?>
            <div class="noo-company-avatar">
                <a href="<?php echo get_permalink(); ?>"><?php echo $logo_company;?></a>
            </div>
            <div class="noo-company-name">
                <h3 class="company-title" itemprop="name"><?php if( !is_singular( 'noo_company' ) ) : ?><a href="<?php echo get_permalink(); ?>"><?php endif; ?><?php echo esc_html( $company_name );?><?php if( !is_singular( 'noo_company' ) ) : ?></a><?php endif; ?></h3>
                <?php $post_view = noo_get_post_views($post->ID); ?>
                <div class="company-view-count">
                    <?php
                    if( $post_view > 0 ) {
                        echo '<span class="count">' . sprintf( _n( '%d view', '%d views', $post_view, 'noo' ), $post_view ) .'</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="<?php noo_main_class(); ?>">
            <div class="noo-company-content">
                <div class="noo-company-heading"><?php _e('Something About Company', 'noo'); ?></div>
                <?php
                $content = get_post_field( 'post_content', get_the_ID() );
                $content = apply_filters('the_content', $content);
                echo $content;
                ?>
            </div>
            <div class="clearfix"></div>
            <div class="noo-company-profile-line"></div>
            <div class="job-listing" data-agent-id="<?php the_ID() ?>">
                <?php
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $job_ids = Noo_Company::get_company_jobs(get_the_ID(), array(), -1);
                $args = array(
                    'paged' => $paged,
                    'post_type' => 'noo_job',
                    'post__in' => array_merge($job_ids, array(0)),
                    'post_status' => 'publish'
                );

                $r = new WP_Query($args);
                jm_job_loop(array(
                    'query' => $r,
                    'title' => sprintf( _n( '%s has posted %s job', '%s has posted %s jobs', $r->found_posts, 'noo' ), get_the_title(), '<span class="text-primary">' . $r->found_posts . '</span>' ),
                    'no_content' => __('This company has no active jobs', 'noo'),
                    'is_shortcode' => true
                ));
                ?>
            </div>
        </div>
        <div class="<?php noo_sidebar_class(); ?> hidden-print">
            <?php Noo_Company::display_sidebar_two(get_the_ID()); ?>
        </div>
    </div>
</div>