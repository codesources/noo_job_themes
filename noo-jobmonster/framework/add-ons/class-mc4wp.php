<?php

/**
 * Class NOO_JobMonster_MC4WP_Integration
 *
 * @ignore
 */

if( defined( 'MC4WP_VERSION' ) && defined( 'MC4WP_PLUGIN_DIR' ) ) :
	if( !class_exists( 'MC4WP_User_Integration' ) ) {
		require_once MC4WP_PLUGIN_DIR . '/includes/integrations/class-user-integration.php';
	}

	if( !class_exists( 'NOO_JobMonster_MC4WP_Integration') ) :
		class NOO_JobMonster_MC4WP_Integration extends MC4WP_User_Integration {

			/**
			 * @var string
			 */
			public $name = "JobMonster theme";

			/**
			 * @var string
			 */
			public $description = "Subscribes users from JobMonster registration forms.";


			/**
			 * Add hooks
			 */
			public function add_hooks() {

				if( ! $this->options['implicit'] ) {
					add_action( 'noo_register_form', array( $this, 'output_checkbox' ), 20 );
				}

				add_action( 'user_register', array( $this, 'subscribe_from_jobmonster' ), 90, 1 );
			}

			/**
			 * Subscribes from JobMonster Registration Form
			 * @param int $user_id
			 * @return bool
			 */
			public function subscribe_from_jobmonster( $user_id ) {

				// was sign-up checkbox checked?
				if ( ! $this->triggered() ) {
					return false;
				}

				// gather emailadress from user who WordPress registered
				$user = get_userdata( $user_id );

				// was a user found with the given ID?
				if ( ! $user instanceof WP_User ) {
					return false;
				}

				$merge_vars = $this->user_merge_vars( $user );

				return $this->subscribe($merge_vars, $user_id );
			}

			/**
			 * Get HTML for the checkbox
			 *
			 * @return string
			 */
			public function get_checkbox_html() {

				ob_start();
				?>
				<!-- MailChimp for WordPress v<?php echo MC4WP_VERSION; ?> - https://mc4wp.com/ -->

				<?php do_action( 'mc4wp_integration_before_checkbox_wrapper', $this ); ?>
				<?php do_action( 'mc4wp_integration_'. $this->slug .'_before_checkbox_wrapper', $this ); ?>

				<!-- <p class="mc4wp-checkbox mc4wp-checkbox-<?php echo esc_attr( $this->slug ); ?>">
					<label>
						<?php // Hidden field to make sure "0" is sent to server ?>
						<input type="hidden" name="<?php echo esc_attr( $this->checkbox_name ); ?>" value="0" />
						<input type="checkbox" name="<?php echo esc_attr( $this->checkbox_name ); ?>" value="1" <?php echo $this->get_checkbox_attributes(); ?> />
						<span><?php echo $this->get_label_text(); ?></span>
					</label>
				</p> -->
				<div class="form-group text-center">
					<div class="checkbox">
						<div class="form-control-flat mc4wp-checkbox">
							<label class="checkbox">
								<input type="hidden" name="<?php echo esc_attr( $this->checkbox_name ); ?>" value="0" />
								<input type="checkbox" name="<?php echo esc_attr( $this->checkbox_name ); ?>" value="1" <?php echo $this->get_checkbox_attributes(); ?> /><i></i>
								<span><?php echo $this->get_label_text(); ?></span>
							</label>
						</div>
					</div>
				</div>

				<?php do_action( 'mc4wp_integration_after_checkbox_wrapper', $this ); ?>
				<?php do_action( 'mc4wp_integration_'. $this->slug .'_after_checkbox_wrapper', $this ); ?>

				<!-- / MailChimp for WordPress -->
				<?php
				$html = ob_get_clean();
				return $html;
			}

			/**
			 * @return bool
			 */
			public function is_installed() {
				return true;
			}

		}
	endif;
endif;