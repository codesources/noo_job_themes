
<?php
$sidebar = get_sidebar_id();
// echo $sidebar; die;
if( ! empty( $sidebar ) ) :
?>
<div class="<?php noo_sidebar_class(); ?> hidden-print">
	<div class="noo-sidebar-wrap">
		<?php // Dynamic Sidebar
		if ( ! function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( $sidebar ) ) : ?>
			<!-- Sidebar fallback content -->
	
		<?php endif; // End Dynamic Sidebar sidebar-main ?>
	</div>
</div>
<?php endif; // End sidebar ?> 
