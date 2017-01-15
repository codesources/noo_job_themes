<?php
if( !function_exists( 'jm_setting_menu' ) ) :
	function jm_setting_menu()
	{
		add_menu_page(
			__('JobMonster', 'noo'),
			__('JobMonster', 'noo'),
			'manage_job',
			'jm-dashboard',
			null,
			get_template_directory_uri() . '/assets/images/icon-jm.png',
            28
		);
		add_submenu_page(
			'jm-dashboard',
			__('Settings', 'noo'),
			__('Settings', 'noo'),
			'manage_options',
			'jm-setting',
			'jm_setting_page'
		);
        add_submenu_page(
			'jm-dashboard',
			__('Custom Fields', 'noo'),
			__('Custom Fields', 'noo'),
			'manage_options',
			'jm-cf-setting',
			'jm_cf_setting_page'
		);
		add_submenu_page (
			'jm-dashboard',
			__( 'Quick Setup', 'noo' ),
			__( 'Quick Setup', 'noo' ),
			'manage_options',
			'jm-basic-setup',
			'jm_basic_setup'
		);
	}
	add_action( 'admin_menu', 'jm_setting_menu', 99 );
endif;

if( !function_exists( 'jm_setting_page' ) ) :
    function jm_setting_page() {
        $tabs = apply_filters( 'noo_job_settings_tabs_array', array());
		$tab_keys = array_keys( $tabs );
		$current_tab = empty( $_GET['tab'] ) ? reset( $tab_keys ) : sanitize_title( $_GET['tab'] );
        ?>
        <div class="wrap">
            <form action="options.php" method="post">
                <h2 class="nav-tab-wrapper">
                    <?php
                        foreach ( $tabs as $name => $label )
                            echo '<a href="' . jm_setting_page_url($name) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
                    ?>
                </h2>
                <?php
                do_action( 'noo_job_setting_' . $current_tab );
                submit_button(__('Save Changes','noo'));
                ?>
            </form>
        </div>
        <?php
    }
endif;

if( !function_exists( 'jm_cf_setting_page' ) ) :
    function jm_cf_setting_page() {
        $tabs = apply_filters( 'jm_cf_settings_tabs_array', array());
		$tab_keys = array_keys( $tabs );
		$current_tab = empty( $_GET['tab'] ) ? reset( $tab_keys ) : sanitize_title( $_GET['tab'] );
        ?>
        <div class="wrap">
            <form action="options.php" method="post">
                <h2 class="nav-tab-wrapper">
                    <?php
                    foreach ( $tabs as $name => $label )
                        echo '<a href="' . jm_cf_setting_page_url($name) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
                    ?>
                </h2>
                <?php
                do_action( 'jm_cf_setting_' . $current_tab );
                submit_button(__('Save Changes','noo'));
                ?>
            </form>
        </div>
        <?php
    }
endif;

if( !function_exists( 'jm_basic_setup' ) ) :
	function jm_basic_setup() {
        if ( isset( $_GET['page'] ) == 'jm-basic-setup' ) :

            if ( isset( $_GET['tab'] ) ) :

                $tab = $_GET['tab'];

            else :

                $tab = 'general';

            endif;

            Noo_Notice_Install::noo_tab_menu( $tab );

            switch ( $tab ) {
                case 'general':
                    Noo_Notice_Install::noo_general_options();
                    break;
                case 'import_demo':
                    Noo_Notice_Install::noo_import_demo_options();
                    break;
                default:
                    Noo_Notice_Install::noo_general_options();
                    break;
            }

        endif;
    }
endif;

if( !function_exists( 'jm_dashboard_page_url' ) ) :
	function jm_dashboard_page_url( $page = '', $tab = '' ) {
		$args = array(
				'page' => $page,
				'tab' => $tab
			);
		return esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	}
endif;

if( !function_exists( 'jm_cf_setting_page_url' ) ) :
	function jm_cf_setting_page_url( $tab = '' ) {
		return jm_dashboard_page_url( 'jm-cf-setting', $tab );
	}
endif;