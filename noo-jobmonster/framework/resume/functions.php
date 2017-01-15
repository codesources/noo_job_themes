<?php
if( !function_exists( 'jm_get_resume_setting' ) ) :
	function jm_get_resume_setting($id = null ,$default = null){
		return jm_get_setting('noo_resume_general', $id, $default);
	}
endif;

if( !function_exists( 'jm_resume_enabled' ) ) :
	function jm_resume_enabled(){
		return (bool) jm_get_resume_setting('enable_resume', 1);
	}
endif;

if( !function_exists( 'jm_get_resume_status' ) ) :
	function jm_get_resume_status() {
		return apply_filters('noo_resume_status', array(
			'draft'           => _x( 'Draft', 'Job status', 'noo' ),
			// 'preview'         => _x( 'Preview', 'Job status', 'noo' ),
			'pending'         => _x( 'Pending Approval', 'Job status', 'noo' ),
			'pending_payment' => _x( 'Pending Payment', 'Job status', 'noo' ),
			'publish'         => _x( 'Published', 'Job status', 'noo' ),
		));
	}
endif;

if( !function_exists( 'jm_get_allowed_file_types' ) ) :
	function jm_get_allowed_file_types( $is_display = false ) {
		$allowed_file_types = jm_get_resume_setting('extensions_upload_resume', 'doc,docx,pdf');
		$allowed_file_types = !empty( $allowed_file_types ) ? explode(',', $allowed_file_types ) : array();
		$allowed_exts = array();
		foreach ($allowed_file_types as $type) {
			$type = trim($type);
			if( empty( $type ) ) continue;

			if( $type[0] == '.' && !$is_display ) {
				$type = substr( $type, 1 );
			} elseif( $is_display ) {
				$type = '.' . $type;
			}

			$allowed_exts[] = $type;
		}

		return apply_filters( 'jm_allowed_upload_file_types', $allowed_exts );
	}
endif;
