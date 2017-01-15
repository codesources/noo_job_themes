<?php

if( !function_exists( 'jm_force_redirect' ) ) :
	function jm_force_redirect( $location, $status = 302 ) {
		wp_safe_redirect( $location, $status );
		exit;
	}
endif;
