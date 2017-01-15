<div class="noo-main noo-company" role="main">
    <div class="col-md-8">
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
    <div class="col-md-4">
        <?php Noo_Company::display_sidebar(get_the_ID());
        // wp_reset_postdata();
        // wp_reset_query();
        ?>
    </div>

</div>