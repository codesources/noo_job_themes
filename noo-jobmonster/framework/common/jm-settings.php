<?php
if( !function_exists( 'jm_get_setting' ) ) :
	function jm_get_setting($group, $id = null ,$default = null){
		global $noo_job_setting_group;
		if(!isset($noo_job_setting_group[$group])){
			$noo_job_setting_group[$group] = get_option($group);
		}
		$group_setting_value = $noo_job_setting_group[$group];
		if(empty($id)) {
			return $group_setting_value;
		}

		if(isset($group_setting_value[$id])) {
			return $group_setting_value[$id];
		}

		return $default;
	}
endif;

if( !function_exists( 'jm_setting_page_url' ) ) :
	function jm_setting_page_url($tab = '' ) {
		return jm_dashboard_page_url( 'jm-setting', $tab );
	}
endif;
