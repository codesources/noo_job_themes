<?php

/**
 * Class 
 *
 * @ignore
 */

if( class_exists( 'plugin_delete_me' ) ) :
	if( !function_exists( 'jm_delete_me_btn' ) ) {
		function jm_delete_me_btn() {
			?>
			<?php 
				echo do_shortcode( '[plugin_delete_me class="btn btn-black"/]' );
			?>
			<?php
		}
		add_action( 'noo_update_password_after', 'jm_delete_me_btn' );
	}

endif;