<?php
if ($wp_query->have_posts()):
    if (empty($title)) {
        if (is_post_type_archive('noo_job')
            || is_tax('job_category')
            || is_tax('job_type')
            || is_tax('job_tag')
            || is_tax('job_location')
        ) {
            $title = __('Latest Jobs', 'noo');
        }
        if (is_search() || $title_type == 'job_count') {
            $title = sprintf(_n('We found %s available job for you', 'We found %s available jobs for you', $wp_query->found_posts, 'noo'), '<span class="text-primary">' . number_format_i18n($wp_query->found_posts) . '</span>');
        }
    }
    ?>
    <?php if (!empty($title)): ?>
    <div class="posts-loop-title noo-job-list-column-heading <?php if (is_singular('noo_job')) echo ' single_jobs' ?>">
        <h3><?php echo $title; ?></h3>
    </div>
<?php endif; ?>
    <?php
    $list_column = !empty($list_column) ?  $list_column :  '3';
    ?>
    <div class="posts-loop-content row noo-job-list-column noo-job-list-column-<?php echo $list_column;  ?>">
        <?php ?>
        <?php do_action('job_list_before', $loop_args, $wp_query); ?>

        <?php while ($wp_query->have_posts()) : $wp_query->the_post();
            global $post; ?>
            <?php
            $company_id = jm_get_job_company($post);

            if (!empty($company_id)) {
                $company_name = get_the_title($company_id);
                $company_link = get_the_permalink($company_id);
            }
            ?>
            <?php do_action('job_list_single_before', $loop_args, $wp_query); ?>
            <div class="noo_job-list-column-item col-lg-<?php echo 12/$list_column; ?> col-md-<?php echo 12/$list_column; ?> col-sm-6 col-xs-12" <?php post_class($item_class); ?>
                 data-url="<?php the_permalink(); ?>">
                <h2 class="loop-item-title">
                    <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                </h2>
                <a class="loop-item-company" href="<?php echo esc_url($company_link); ?>">
                    <?php echo esc_html($company_name); ?>
                </a>
                <?php
                $type = jm_get_job_type(get_the_ID());
                if (!empty($type)) : ?>
                    <div class="loop-item-type">
                        <a href="<?php echo get_term_link($type, 'job_type'); ?>"
                           style="color: <?php echo $type->color; ?>">
                            <i class="fa fa-bookmark"></i>
                            <span><?php echo $type->name; ?></span>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
            <?php do_action('job_list_single_after', $loop_args, $wp_query); ?>

        <?php endwhile; ?>
        <?php do_action('job_list_after', $loop_args, $wp_query); ?>
    </div>
    <?php
    if (isset($btn_link) && !empty($btn_link)) {
        $link = vc_build_link($btn_link);
        ?>
        <div class="noo-job-list-btn">
            <a class="btn btn-primary" href="<?php echo esc_url($link['url']) ?>"
               <?php if (isset($link['target']) && !empty($link['target'])): ?>target="_blank" <?php endif; ?>><span><?php echo esc_html($link['title']) ?></span></a>
        </div>
        <?php
    }
    ?>
<?php else: ?>
    <div class="jobs posts-loop ">
        <?php
        if ($no_content == 'text' || empty($no_content)) {
            noo_get_layout('no-content');
        } elseif ($no_content != 'none') {
            echo '<h3>' . $no_content . '</h3>';
        }
        ?>
    </div>
<?php endif; ?>