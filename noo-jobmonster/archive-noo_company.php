<?php get_header(); ?>
<div class="container-wrap">
	<div class="main-content container-boxed max offset">
		<div class="row">
			<div class="<?php noo_main_class(); ?>" role="main">
			<?php
				$company_list_style = noo_get_option('noo_companies_style', '');
				$args = array(
					'title' => __('Employer', 'noo'),
					'style' => $company_list_style,
					'archive' => 'yes'
				);
				Noo_Company::loop_display( $args );
			?>
			</div> <!-- /.main -->
			<?php get_sidebar(); ?>
		</div><!--/.row-->
	</div><!--/.container-boxed-->
</div><!--/.container-wrap-->
	
<?php get_footer(); ?>