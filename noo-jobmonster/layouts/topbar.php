<div class="noo-topbar">
    <div class="container-boxed max">
        <?php
        $topbar_layout = noo_get_option('noo_top_bar_layout', 'right');
        $topbar_text = noo_get_option('noo_top_bar_text', '');
        if (!empty($topbar_text)): ?>
            <div class="noo-topbar-text pull-<?php echo ($topbar_layout == 'right' ? 'left' : 'right' ); ?>"><?php echo $topbar_text; ?></div>
        <?php endif; ?>
        <div class="pull-<?php echo $topbar_layout; ?>">
            <?php if (noo_get_option('noo_top_bar_social', 1)): ?>
                <div class="noo-topbar-social">
                    <?php
                    $facebook = noo_get_option('noo_header_top_facebook_url', '');
                    $google = noo_get_option('noo_header_top_google_plus_url', '');
                    $twitter = noo_get_option('noo_header_top_twitter_url', '');
                    $linked = noo_get_option('noo_header_top_linked_url', '');
                    ?>
                    <ul>

                        <?php if (!empty($facebook)) : ?>
                            <li>
                                <a href="<?php echo esc_url($facebook); ?>" class="fa fa-facebook"></a>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($google)) : ?>
                            <li>
                                <a href="<?php echo esc_url($google); ?>" class="fa fa-google-plus"></a>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($twitter)) : ?>
                            <li>
                                <a href="<?php echo esc_url($twitter); ?>" class="fa fa-twitter"></a>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($linked)) : ?>
                            <li>
                                <a href="<?php echo esc_url($linked); ?>" class="fa fa-linkedin"></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>
    </div>
</div>