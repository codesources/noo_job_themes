<?php
/**
 * @package Import demo
 * @version 1.0 [<description>]
 * 
 */

/** Display verbose errors */
if( !defined('IMPORT_DEBUG') ) define( 'IMPORT_DEBUG', false );

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require $class_wp_importer;
}

// include NOO file parsers
require dirname( __FILE__ ) . '/parsers.php';

class Noo_Import_Demo extends WP_Importer{
	var $max_NOO_version = 1.2; // max. supported NOO version

	var $id; // NOO attachment ID

	// information to import from NOO file
	var $version;
	var $authors = array();
	var $posts = array();
	var $terms = array();
	var $categories = array();
	var $tags = array();
	var $base_url = '';

	// mappings from old information to new
	var $processed_authors = array();
	var $author_mapping = array();
	var $processed_terms = array();
	var $processed_posts = array();
	var $post_orphans = array();
	var $processed_menu_items = array();
	var $menu_item_orphans = array();
	var $missing_menu_items = array();

	var $fetch_attachments = true;
	var $url_remap = array();
	var $featured_images = array();

	public function __construct() {

		// -- set ajax
			add_action( 'wp_ajax_process_data', array( $this, 'process_data' ) );

	}

	public function process_data() {
		// --- Check security request
			// check_ajax_referer( 'install-demo', 'security' );
			set_time_limit(0);
		// --- load file demo
			$file_demo = NOO_FRAMEWORK_ADMIN . "/import-demo/data/{$_POST['name']}/content.xml";
			$widget = NOO_FRAMEWORK_ADMIN . "/import-demo/data/{$_POST['name']}/widgets.wie";
			$option = NOO_FRAMEWORK_ADMIN . "/import-demo/data/{$_POST['name']}/option.json";
			$slider_file = NOO_FRAMEWORK_ADMIN . "/import-demo/data/{$_POST['name']}/slider.zip";

			$this->import( $file_demo );
			$this->_noo_process_import_file( $widget );
			$this->process_option( $option );
			$this->import_RevSlider( $slider_file );
			esc_html_e( 'Import Successfully!', 'noo' );
			wp_die();

	}

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the NOO file for importing
	 */
	function import( $file ) {
		add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
		add_filter( 'http_request_timeout', array( $this, 'http_request_timeout' ) );

		$this->import_start( $file );

		$this->get_author_mapping();

		wp_suspend_cache_invalidation( true );
		$this->process_categories();
		$this->process_tags();
		$this->process_terms();
		$this->process_posts();
		wp_suspend_cache_invalidation( false );

		// update incorrect/missing information in the DB
		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();

		$this->import_end();
	}

	function http_request_timeout( $timeout ) {
		return 60;
	}

	function import_RevSlider($slider_file)
	{
		if (class_exists('RevSlider')) {
			if (file_exists($slider_file)) {
				$slider = new RevSlider();
				$slider->importSliderFromPost(true, true, $slider_file);
			}
		}

	}

	/**
	 * Parses the NOO file and prepares us for the task of processing parsed data
	 *
	 * @param string $file Path to the NOO file for importing
	 */
	function import_start( $file ) {
		if ( ! is_file($file) ) {
			echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'noo' ) . '</strong><br />';
			echo esc_html__( 'The file does not exist, please try again.', 'noo' ) . '</p>';
			$this->footer();
			die();
		}

		$import_data = $this->parse( $file );

		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'noo' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			$this->footer();
			die();
		}

		$this->version = $import_data['version'];
		$this->get_authors_from_import( $import_data );
		$this->posts = $import_data['posts'];
		$this->terms = $import_data['terms'];
		$this->categories = $import_data['categories'];
		$this->tags = $import_data['tags'];
		$this->base_url = esc_url( $import_data['base_url'] );

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		do_action( 'import_start' );
	}

	/**
	 * Performs post-import cleanup of files and the cache
	 */
	function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		esc_html_e( 'All done. Have fun! ', 'noo' );

		do_action( 'import_end' );
	}

	/**
	 * Handles the NOO upload and initial parsing of the file to prepare for
	 * displaying author import options
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'noo' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'noo' ) . '</strong><br />';
			printf( esc_html__( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'noo' ), esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}

		$this->id = (int) $file['id'];
		$import_data = $this->parse( $file['file'] );
		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . esc_html__( 'Sorry, there has been an error.', 'noo' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			return false;
		}

		$this->version = $import_data['version'];
		if ( $this->version > $this->max_NOO_version ) {
			echo '<div class="error"><p><strong>';
			printf( esc_html__( 'This NOO file (version %s) may not be supported by this version of the importer. Please consider updating.', 'noo' ), esc_html($import_data['version']) );
			echo '</strong></p></div>';
		}

		$this->get_authors_from_import( $import_data );

		return true;
	}

	/**
	 * Retrieve authors from parsed NOO data
	 *
	 * Uses the provided author information from NOO 1.1 files
	 * or extracts info from each post for NOO 1.0 files
	 *
	 * @param array $import_data Data returned by a NOO parser
	 */
	function get_authors_from_import( $import_data ) {
		if ( ! empty( $import_data['authors'] ) ) {
			$this->authors = $import_data['authors'];
		// no author information, grab it from the posts
		} else {
			foreach ( $import_data['posts'] as $post ) {
				$login = sanitize_user( $post['post_author'], true );
				if ( empty( $login ) ) {
					printf( esc_html__( 'Failed to import author %s. Their posts will be attributed to the current user.', 'noo' ), esc_html( $post['post_author'] ) );
					echo '<br />';
					continue;
				}

				if ( ! isset($this->authors[$login]) )
					$this->authors[$login] = array(
						'author_login' => $login,
						'author_display_name' => $post['post_author']
					);
			}
		}
	}

	/**
	 * Display import options for an individual author. That is, either create
	 * a new user based on import info or map to an existing user
	 *
	 * @param int $n Index for each author in the form
	 * @param array $author Author information, e.g. login, display name, email
	 */
	function author_select( $n, $author ) {
		esc_html_e( 'Import author:', 'noo' );
		echo ' <strong>' . esc_html( $author['author_display_name'] );
		if ( $this->version != '1.0' ) echo ' (' . esc_html( $author['author_login'] ) . ')';
		echo '</strong><br />';

		if ( $this->version != '1.0' )
			echo '<div style="margin-left:18px">';

		$create_users = $this->allow_create_users();
		if ( $create_users ) {
			if ( $this->version != '1.0' ) {
				esc_html_e( 'or create new user with login name:', 'noo' );
				$value = '';
			} else {
				esc_html_e( 'as a new user:', 'noo' );
				$value = esc_attr( sanitize_user( $author['author_login'], true ) );
			}

			echo ' <input type="text" name="user_new['.$n.']" value="'. $value .'" /><br />';
		}

		if ( ! $create_users && $this->version == '1.0' )
			esc_html_e( 'assign posts to an existing user:', 'noo' );
		else
			esc_html_e( 'or assign posts to an existing user:', 'noo' );
		wp_dropdown_users( array( 'name' => "user_map[$n]", 'multi' => true, 'show_option_all' => esc_html__( '- Select -', 'noo' ) ) );
		echo '<input type="hidden" name="imported_authors['.$n.']" value="' . esc_attr( $author['author_login'] ) . '" />';

		if ( $this->version != '1.0' )
			echo '</div>';
	}

	/**
	 * Map old author logins to local user IDs based on decisions made
	 * in import options form. Can map to an existing user, create a new user
	 * or falls back to the current user in case of error with either of the previous
	 */
	function get_author_mapping() {
		if ( ! isset( $_POST['imported_authors'] ) )
			return;

		$create_users = $this->allow_create_users();

		foreach ( (array) $_POST['imported_authors'] as $i => $old_login ) {
			// Multisite adds strtolower to sanitize_user. Need to sanitize here to stop breakage in process_posts.
			$santized_old_login = sanitize_user( $old_login, true );
			$old_id = isset( $this->authors[$old_login]['author_id'] ) ? intval($this->authors[$old_login]['author_id']) : false;

			if ( ! empty( $_POST['user_map'][$i] ) ) {
				$user = get_userdata( intval($_POST['user_map'][$i]) );
				if ( isset( $user->ID ) ) {
					if ( $old_id )
						$this->processed_authors[$old_id] = $user->ID;
					$this->author_mapping[$santized_old_login] = $user->ID;
				}
			} else if ( $create_users ) {
				if ( ! empty($_POST['user_new'][$i]) ) {
					$user_id = wp_create_user( $_POST['user_new'][$i], wp_generate_password() );
				} else if ( $this->version != '1.0' ) {
					$user_data = array(
						'user_login' => $old_login,
						'user_pass' => wp_generate_password(),
						'user_email' => isset( $this->authors[$old_login]['author_email'] ) ? $this->authors[$old_login]['author_email'] : '',
						'display_name' => $this->authors[$old_login]['author_display_name'],
						'first_name' => isset( $this->authors[$old_login]['author_first_name'] ) ? $this->authors[$old_login]['author_first_name'] : '',
						'last_name' => isset( $this->authors[$old_login]['author_last_name'] ) ? $this->authors[$old_login]['author_last_name'] : '',
					);
					$user_id = wp_insert_user( $user_data );
				}

				if ( ! is_wp_error( $user_id ) ) {
					if ( $old_id )
						$this->processed_authors[$old_id] = $user_id;
					$this->author_mapping[$santized_old_login] = $user_id;
				} else {
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ){
						printf( esc_html__( 'Failed to create new user for %s. Their posts will be attributed to the current user.', 'noo' ), esc_html($this->authors[$old_login]['author_display_name']) );
						echo ' ' . $user_id->get_error_message();
						echo '<br />';
					}
				}
			}

			// failsafe: if the user_id was invalid, default to the current user
			if ( ! isset( $this->author_mapping[$santized_old_login] ) ) {
				if ( $old_id )
					$this->processed_authors[$old_id] = (int) get_current_user_id();
				$this->author_mapping[$santized_old_login] = (int) get_current_user_id();
			}
		}
	}

	/**
	 * Create new categories based on import information
	 *
	 * Doesn't create a new category if its slug already exists
	 */
	function process_categories() {
		$this->categories = apply_filters( 'wp_import_categories', $this->categories );

		if ( empty( $this->categories ) )
			return;

		foreach ( $this->categories as $cat ) {
			// if the category already exists leave it alone
			$term_id = term_exists( $cat['category_nicename'], 'category' );
			if ( $term_id ) {
				if ( is_array($term_id) ) $term_id = $term_id['term_id'];
				if ( isset($cat['term_id']) )
					$this->processed_terms[intval($cat['term_id'])] = (int) $term_id;
				continue;
			}

			$category_parent = empty( $cat['category_parent'] ) ? 0 : category_exists( $cat['category_parent'] );
			$category_description = isset( $cat['category_description'] ) ? $cat['category_description'] : '';
			$catarr = array(
				'category_nicename' => $cat['category_nicename'],
				'category_parent' => $category_parent,
				'cat_name' => $cat['cat_name'],
				'category_description' => $category_description
			);

			$id = wp_insert_category( $catarr );
			if ( ! is_wp_error( $id ) ) {
				if ( isset($cat['term_id']) )
					$this->processed_terms[intval($cat['term_id'])] = $id;
			} else {				
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ){
					printf( esc_html__( 'Failed to import category %s', 'noo' ), esc_html($cat['category_nicename']) );
					echo ': ' . $id->get_error_message();
					echo '<br />';
				}
				continue;
			}
		}

		unset( $this->categories );
	}

	/**
	 * Create new post tags based on import information
	 *
	 * Doesn't create a tag if its slug already exists
	 */
	function process_tags() {
		$this->tags = apply_filters( 'wp_import_tags', $this->tags );

		if ( empty( $this->tags ) )
			return;

		foreach ( $this->tags as $tag ) {
			// if the tag already exists leave it alone
			$term_id = term_exists( $tag['tag_slug'], 'post_tag' );
			if ( $term_id ) {
				if ( is_array($term_id) ) $term_id = $term_id['term_id'];
				if ( isset($tag['term_id']) )
					$this->processed_terms[intval($tag['term_id'])] = (int) $term_id;
				continue;
			}

			$tag_desc = isset( $tag['tag_description'] ) ? $tag['tag_description'] : '';
			$tagarr = array( 'slug' => $tag['tag_slug'], 'description' => $tag_desc );

			$id = wp_insert_term( $tag['tag_name'], 'post_tag', $tagarr );
			if ( ! is_wp_error( $id ) ) {
				if ( isset($tag['term_id']) )
					$this->processed_terms[intval($tag['term_id'])] = $id['term_id'];
			} else {
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ){
					printf( esc_html__( 'Failed to import post tag %s', 'noo' ), esc_html($tag['tag_name']) );
					echo ': ' . $id->get_error_message();
					echo '<br />';
				}
				continue;
			}
		}

		unset( $this->tags );
	}

	/**
	 * Create new terms based on import information
	 *
	 * Doesn't create a term its slug already exists
	 */
	function process_terms() {
		$this->terms = apply_filters( 'wp_import_terms', $this->terms );

		if ( empty( $this->terms ) )
			return;

		foreach ( $this->terms as $term ) {
			// if the term already exists in the correct taxonomy leave it alone
			$term_id = term_exists( $term['slug'], $term['term_taxonomy'] );
			if ( $term_id ) {
				if ( is_array($term_id) ) $term_id = $term_id['term_id'];
				if ( isset($term['term_id']) )
					$this->processed_terms[intval($term['term_id'])] = (int) $term_id;
				continue;
			}

			if ( empty( $term['term_parent'] ) ) {
				$parent = 0;
			} else {
				$parent = term_exists( $term['term_parent'], $term['term_taxonomy'] );
				if ( is_array( $parent ) ) $parent = $parent['term_id'];
			}
			$description = isset( $term['term_description'] ) ? $term['term_description'] : '';
			$termarr = array( 'slug' => $term['slug'], 'description' => $description, 'parent' => intval($parent) );

			$id = wp_insert_term( $term['term_name'], $term['term_taxonomy'], $termarr );
			if ( ! is_wp_error( $id ) ) {
				if ( isset($term['term_id']) )
					$this->processed_terms[intval($term['term_id'])] = $id['term_id'];
			} else {
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ){
					printf( esc_html__( 'Failed to import %s %s', 'noo' ), esc_html($term['term_taxonomy']), esc_html($term['term_name']) );
					echo ': ' . $id->get_error_message();
					echo '<br />';
				}
				continue;
			}
		}

		unset( $this->terms );
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	function process_posts() {
		$this->posts = apply_filters( 'wp_import_posts', $this->posts );

		foreach ( $this->posts as $post ) {
			$post = apply_filters( 'wp_import_post_data_raw', $post );

			if ( ! post_type_exists( $post['post_type'] ) ) {
				printf( esc_html__( 'Failed to import &#8220;%s&#8221;: Invalid post type %s', 'noo' ),
					esc_html($post['post_title']), esc_html($post['post_type']) );
				echo '<br />';
				do_action( 'wp_import_post_exists', $post );
				continue;
			}

			if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
				continue;

			if ( $post['status'] == 'auto-draft' )
				continue;

			if ( 'nav_menu_item' == $post['post_type'] && $_POST['import_nav'] == 'true' ) {
				$this->process_menu_item( $post );
				continue;
			}

			$post_type_object = get_post_type_object( $post['post_type'] );

			$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
			if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
				// printf( esc_html__('%s &#8220;%s&#8221; already exists.', 'noo' ), $post_type_object->labels->singular_name, esc_html($post['post_title']) );
				// echo '<br />';
				$comment_post_ID = $post_id = $post_exists;
			} else {
				$post_parent = (int) $post['post_parent'];
				if ( $post_parent ) {
					// if we already know the parent, map it to the new local ID
					if ( isset( $this->processed_posts[$post_parent] ) ) {
						$post_parent = $this->processed_posts[$post_parent];
					// otherwise record the parent for later
					} else {
						$this->post_orphans[intval($post['post_id'])] = $post_parent;
						$post_parent = 0;
					}
				}

				// map the post author
				$author = sanitize_user( $post['post_author'], true );
				if ( isset( $this->author_mapping[$author] ) )
					$author = $this->author_mapping[$author];
				else
					$author = (int) get_current_user_id();

				$postdata = array(
					'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
					'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
					'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
					'post_status' => $post['status'], 'post_name' => $post['post_name'],
					'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
					'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
					'post_type' => $post['post_type'], 'post_password' => $post['post_password']
				);

				$original_post_ID = $post['post_id'];
				$postdata = apply_filters( 'wp_import_post_data_processed', $postdata, $post );

				if ( 'attachment' == $postdata['post_type'] ) {
					$remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];
					// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
					// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
					$postdata['upload_date'] = $post['post_date'];
					if ( isset( $post['postmeta'] ) ) {
						foreach( $post['postmeta'] as $meta ) {
							if ( $meta['key'] == '_wp_attached_file' ) {
								if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
									$postdata['upload_date'] = $matches[0];
								break;
							}
						}
					}

					$comment_post_ID = $post_id = $this->process_attachment( $postdata, $remote_url );
				} else {
					$comment_post_ID = $post_id = wp_insert_post( $postdata, true );
					do_action( 'wp_import_insert_post', $post_id, $original_post_ID, $postdata, $post );
				}

				if ( is_wp_error( $post_id ) ) {
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ){
						printf( esc_html__( 'Failed to import %s &#8220;%s&#8221;', 'noo' ), $post_type_object->labels->singular_name, esc_html($post['post_title']) );
						echo ': ' . $post_id->get_error_message();
						echo '<br />';
					}
					continue;
				}

				if ( $post['is_sticky'] == 1 )
					stick_post( $post_id );
			}

			// map pre-import ID to local ID
			$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

			if ( ! isset( $post['terms'] ) )
				$post['terms'] = array();

			$post['terms'] = apply_filters( 'wp_import_post_terms', $post['terms'], $post_id, $post );

			// add categories, tags and other terms
			if ( ! empty( $post['terms'] ) && $_POST['import_post'] == 'true' ) {
				$terms_to_set = array();
				foreach ( $post['terms'] as $term ) {
					// back compat with NOO 1.0 map 'tag' to 'post_tag'
					$taxonomy = ( 'tag' == $term['domain'] ) ? 'post_tag' : $term['domain'];
					$term_exists = term_exists( $term['slug'], $taxonomy );
					$term_id = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
					if ( ! $term_id ) {
						$t = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'] ) );
						if ( ! is_wp_error( $t ) ) {
							$term_id = $t['term_id'];
							do_action( 'wp_import_insert_term', $t, $term, $post_id, $post );
						} else {
							if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG ){
								printf( esc_html__( 'Failed to import %s %s', 'noo' ), esc_html($taxonomy), esc_html($term['name']) );
								echo ': ' . $t->get_error_message();
								echo '<br />';
							}
							do_action( 'wp_import_insert_term_failed', $t, $term, $post_id, $post );
							continue;
						}
					}
					$terms_to_set[$taxonomy][] = intval( $term_id );
				}

				foreach ( $terms_to_set as $tax => $ids ) {
					$tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
					do_action( 'wp_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post );
				}
				unset( $post['terms'], $terms_to_set );
			}

			if ( ! isset( $post['comments'] ) )
				$post['comments'] = array();

			$post['comments'] = apply_filters( 'wp_import_post_comments', $post['comments'], $post_id, $post );

			// add/update comments
			if ( ! empty( $post['comments'] ) && $_POST['import_comment'] == 'true' ) {
				$num_comments = 0;
				$inserted_comments = array();
				foreach ( $post['comments'] as $comment ) {
					$comment_id	= $comment['comment_id'];
					$newcomments[$comment_id]['comment_post_ID']      = $comment_post_ID;
					$newcomments[$comment_id]['comment_author']       = $comment['comment_author'];
					$newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
					$newcomments[$comment_id]['comment_author_IP']    = $comment['comment_author_IP'];
					$newcomments[$comment_id]['comment_author_url']   = $comment['comment_author_url'];
					$newcomments[$comment_id]['comment_date']         = $comment['comment_date'];
					$newcomments[$comment_id]['comment_date_gmt']     = $comment['comment_date_gmt'];
					$newcomments[$comment_id]['comment_content']      = $comment['comment_content'];
					$newcomments[$comment_id]['comment_approved']     = $comment['comment_approved'];
					$newcomments[$comment_id]['comment_type']         = $comment['comment_type'];
					$newcomments[$comment_id]['comment_parent'] 	  = $comment['comment_parent'];
					$newcomments[$comment_id]['commentmeta']          = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : array();
					if ( isset( $this->processed_authors[$comment['comment_user_id']] ) )
						$newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
				}
				ksort( $newcomments );

				foreach ( $newcomments as $key => $comment ) {
					// if this is a new post we can skip the comment_exists() check
					if ( ! $post_exists || ! comment_exists( $comment['comment_author'], $comment['comment_date'] ) ) {
						if ( isset( $inserted_comments[$comment['comment_parent']] ) )
							$comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
						$comment = wp_filter_comment( $comment );
						$inserted_comments[$key] = wp_insert_comment( $comment );
						do_action( 'wp_import_insert_comment', $inserted_comments[$key], $comment, $comment_post_ID, $post );

						foreach( $comment['commentmeta'] as $meta ) {
							$value = maybe_unserialize( $meta['value'] );
							add_comment_meta( $inserted_comments[$key], $meta['key'], $value );
						}

						$num_comments++;
					}
				}
				unset( $newcomments, $inserted_comments, $post['comments'] );
			}

			if ( ! isset( $post['postmeta'] ) )
				$post['postmeta'] = array();

			$post['postmeta'] = apply_filters( 'wp_import_post_meta', $post['postmeta'], $post_id, $post );

			// add/update post meta
			if ( ! empty( $post['postmeta'] ) && $_POST['import_post'] == 'true' ) {
				foreach ( $post['postmeta'] as $meta ) {
					$key = apply_filters( 'import_post_meta_key', $meta['key'], $post_id, $post );
					$value = false;

					if ( '_edit_last' == $key ) {
						if ( isset( $this->processed_authors[intval($meta['value'])] ) )
							$value = $this->processed_authors[intval($meta['value'])];
						else
							$key = false;
					}

					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string
						if ( ! $value )
							$value = maybe_unserialize( $meta['value'] );

						add_post_meta( $post_id, $key, $value );
						do_action( 'import_post_meta', $post_id, $key, $value );

						// if the post has a featured image, take note of this in case of remap
						if ( '_thumbnail_id' == $key )
							$this->featured_images[$post_id] = (int) $value;
					}
				}
			}
		}

		unset( $this->posts );
	}

	/**
	 * Attempt to create a new menu item from import data
	 *
	 * Fails for draft, orphaned menu items and those without an associated nav_menu
	 * or an invalid nav_menu term. If the post type or term object which the menu item
	 * represents doesn't exist then the menu item will not be imported (waits until the
	 * end of the import to retry again before discarding).
	 *
	 * @param array $item Menu item details from NOO file
	 */
	function process_menu_item( $item ) {
		// skip draft, orphaned menu items
		if ( 'draft' == $item['status'] )
			return;

		$menu_slug = false;
		if ( isset($item['terms']) ) {
			// loop through terms, assume first nav_menu term is correct menu
			foreach ( $item['terms'] as $term ) {
				if ( 'nav_menu' == $term['domain'] ) {
					$menu_slug = $term['slug'];
					break;
				}
			}
		}

		// no nav_menu term associated with this menu item
		if ( ! $menu_slug ) {
			esc_html_e( 'Menu item skipped due to missing menu slug', 'noo' );
			echo '<br />';
			return;
		}

		$menu_id = term_exists( $menu_slug, 'nav_menu' );
		if ( ! $menu_id ) {
			printf( esc_html__( 'Menu item skipped due to invalid menu slug: %s', 'noo' ), esc_html( $menu_slug ) );
			echo '<br />';
			return;
		} else {
			$menu_id = is_array( $menu_id ) ? $menu_id['term_id'] : $menu_id;
		}

		foreach ( $item['postmeta'] as $meta )
			$$meta['key'] = $meta['value'];

		if ( 'taxonomy' == $_menu_item_type && isset( $this->processed_terms[intval($_menu_item_object_id)] ) ) {
			$_menu_item_object_id = $this->processed_terms[intval($_menu_item_object_id)];
		} else if ( 'post_type' == $_menu_item_type && isset( $this->processed_posts[intval($_menu_item_object_id)] ) ) {
			$_menu_item_object_id = $this->processed_posts[intval($_menu_item_object_id)];
		} else if ( 'custom' != $_menu_item_type ) {
			// associated object is missing or not imported yet, we'll retry later
			$this->missing_menu_items[] = $item;
			return;
		}

		if ( isset( $this->processed_menu_items[intval($_menu_item_menu_item_parent)] ) ) {
			$_menu_item_menu_item_parent = $this->processed_menu_items[intval($_menu_item_menu_item_parent)];
		} else if ( $_menu_item_menu_item_parent ) {
			$this->menu_item_orphans[intval($item['post_id'])] = (int) $_menu_item_menu_item_parent;
			$_menu_item_menu_item_parent = 0;
		}

		// wp_update_nav_menu_item expects CSS classes as a space separated string
		$_menu_item_classes = maybe_unserialize( $_menu_item_classes );
		if ( is_array( $_menu_item_classes ) )
			$_menu_item_classes = implode( ' ', $_menu_item_classes );
		$args = array(
			'menu-item-object-id'               => $_menu_item_object_id,
			'menu-item-object'                  => $_menu_item_object,
			'menu-item-parent-id'               => $_menu_item_menu_item_parent,
			'menu-item-position'                => intval( $item['menu_order'] ),
			'menu-item-type'                    => $_menu_item_type,
			'menu-item-title'                   => $item['post_title'],
			'menu-item-url'                     => $_menu_item_url,
			'menu-item-description'             => $item['post_content'],
			'menu-item-attr-title'              => $item['post_excerpt'],
			'menu-item-target'                  => $_menu_item_target,
			'menu-item-classes'                 => $_menu_item_classes,
			'menu-item-xfn'                     => $_menu_item_xfn,
			'menu-item-status'                  => $item['status']
		);
		if ( isset( $_menu_item_megamenu ) ) $args['menu-item-megamenu'] = $_menu_item_megamenu;
		if ( isset( $_menu_item_megamenu_col ) ) $args['menu-item-megamenu_columns'] = $_menu_item_megamenu_col;
		if ( isset( $_menu_item_megamenu_heading ) ) $args['menu-item-megamenu_heading'] = $_menu_item_megamenu_heading;
		if ( isset( $_menu_item_megamenu_widgetarea ) ) $args['menu-item-megamenu_widgetarea'] = $_menu_item_megamenu_widgetarea;
		if ( isset( $_menu_item_megamenu_icon ) ) $args['menu-item-megamenu_icon'] = $_menu_item_megamenu_icon;
		if ( isset( $_menu_item_megamenu_icon_color ) ) $args['menu-item-megamenu_icon_color'] = $_menu_item_megamenu_icon_color;
		if ( isset( $_menu_item_megamenu_icon_size ) ) $args['menu-item-megamenu_icon_size'] = $_menu_item_megamenu_icon_size;
		if ( isset( $_menu_item_megamenu_icon_alignment ) ) $args['menu-item-megamenu_icon_alignment'] = $_menu_item_megamenu_icon_alignment;

		$id = $this->noo_update_nav_menu_item( $menu_id, 0, $args );
		if ( $id && ! is_wp_error( $id ) )
			$this->processed_menu_items[intval($item['post_id'])] = (int) $id;
	}

	function noo_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0, $menu_item_data = array() ) {
	
	    $menu_id = (int) $menu_id;
	    $menu_item_db_id = (int) $menu_item_db_id;
	 
	    // make sure that we don't convert non-nav_menu_item objects into nav_menu_item objects
	    if ( ! empty( $menu_item_db_id ) && ! is_nav_menu_item( $menu_item_db_id ) )
	        return new WP_Error( 'update_nav_menu_item_failed', esc_html__( 'The given object ID is not that of a menu item.', 'noo' ) );
	 
	    $menu = wp_get_nav_menu_object( $menu_id );
	 
	    if ( ! $menu && 0 !== $menu_id ) {
	        return new WP_Error( 'invalid_menu_id', esc_html__( 'Invalid menu ID.', 'noo' ) );
	    }
	 
	    if ( is_wp_error( $menu ) ) {
	        return $menu;
	    }
	 
	    $defaults = array(
			'menu-item-db-id'                   => $menu_item_db_id,
			'menu-item-object-id'               => 0,
			'menu-item-object'                  => '',
			'menu-item-parent-id'               => 0,
			'menu-item-position'                => 0,
			'menu-item-type'                    => 'custom',
			'menu-item-title'                   => '',
			'menu-item-url'                     => '',
			'menu-item-description'             => '',
			'menu-item-attr-title'              => '',
			'menu-item-target'                  => '',
			'menu-item-classes'                 => '',
			'menu-item-xfn'                     => '',
			'menu-item-status'                  => '',
			'menu-item-megamenu'                => '',
			'menu-item-megamenu_columns'        => '',
			'menu-item-megamenu_heading'        => '',
			'menu-item-megamenu_widgetarea'     => '',
			'menu-item-megamenu_icon'           => '',
			'menu-item-megamenu_icon_color'     => '',
			'menu-item-megamenu_icon_size'      => '',
			'menu-item-megamenu_icon_alignment' => '',
	    );
	 
	    $args = wp_parse_args( $menu_item_data, $defaults );
	 
	    if ( 0 == $menu_id ) {
	        $args['menu-item-position'] = 1;
	    } elseif ( 0 == (int) $args['menu-item-position'] ) {
	        $menu_items = 0 == $menu_id ? array() : (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish,draft' ) );
	        $last_item = array_pop( $menu_items );
	        $args['menu-item-position'] = ( $last_item && isset( $last_item->menu_order ) ) ? 1 + $last_item->menu_order : count( $menu_items );
	    }
	 
	    $original_parent = 0 < $menu_item_db_id ? get_post_field( 'post_parent', $menu_item_db_id ) : 0;
	 
	    if ( 'custom' != $args['menu-item-type'] ) {
	        /* if non-custom menu item, then:
	            * use original object's URL
	            * blank default title to sync with original object's
	        */
	 
	        $args['menu-item-url'] = '';
	 
	        $original_title = '';
	        if ( 'taxonomy' == $args['menu-item-type'] ) {
	            $original_parent = get_term_field( 'parent', $args['menu-item-object-id'], $args['menu-item-object'], 'raw' );
	            $original_title = get_term_field( 'name', $args['menu-item-object-id'], $args['menu-item-object'], 'raw' );
	        } elseif ( 'post_type' == $args['menu-item-type'] ) {
	 
	            $original_object = get_post( $args['menu-item-object-id'] );
	            $original_parent = (int) $original_object->post_parent;
	            $original_title = $original_object->post_title;
	        }
	 
	        if ( $args['menu-item-title'] == $original_title )
	            $args['menu-item-title'] = '';
	 
	        // hack to get wp to create a post object when too many properties are empty
	        if ( '' ==  $args['menu-item-title'] && '' == $args['menu-item-description'] )
	            $args['menu-item-description'] = ' ';
	    }
	 
	    // Populate the menu item object
	    $post = array(
	        'menu_order' => $args['menu-item-position'],
	        'ping_status' => 0,
	        'post_content' => $args['menu-item-description'],
	        'post_excerpt' => $args['menu-item-attr-title'],
	        'post_parent' => $original_parent,
	        'post_title' => $args['menu-item-title'],
	        'post_type' => 'nav_menu_item',
	    );
	 
	    $update = 0 != $menu_item_db_id;
	 
	    // New menu item. Default is draft status
	    if ( ! $update ) {
	        $post['ID'] = 0;
	        $post['post_status'] = 'publish' == $args['menu-item-status'] ? 'publish' : 'draft';
	        $menu_item_db_id = wp_insert_post( $post );
	        if ( ! $menu_item_db_id || is_wp_error( $menu_item_db_id ) )
	            return $menu_item_db_id;
	    }
	 
	    // Associate the menu item with the menu term
	    // Only set the menu term if it isn't set to avoid unnecessary wp_get_object_terms()
	     if ( $menu_id && ( ! $update || ! is_object_in_term( $menu_item_db_id, 'nav_menu', (int) $menu->term_id ) ) ) {
	        wp_set_object_terms( $menu_item_db_id, array( $menu->term_id ), 'nav_menu' );
	    }
	 
	    if ( 'custom' == $args['menu-item-type'] ) {
	        $args['menu-item-object-id'] = $menu_item_db_id;
	        $args['menu-item-object'] = 'custom';
	    }
	 
	    $menu_item_db_id = (int) $menu_item_db_id;
	 	
	    update_post_meta( $menu_item_db_id, '_menu_item_type', sanitize_key($args['menu-item-type']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_menu_item_parent', strval( (int) $args['menu-item-parent-id'] ) );
	    update_post_meta( $menu_item_db_id, '_menu_item_object_id', strval( (int) $args['menu-item-object-id'] ) );
	    update_post_meta( $menu_item_db_id, '_menu_item_object', sanitize_key($args['menu-item-object']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_target', sanitize_key($args['menu-item-target']) );
	 
	    $args['menu-item-classes'] = array_map( 'sanitize_html_class', explode( ' ', $args['menu-item-classes'] ) );
	    $args['menu-item-xfn'] = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['menu-item-xfn'] ) ) );
	    update_post_meta( $menu_item_db_id, '_menu_item_classes', $args['menu-item-classes'] );
	    update_post_meta( $menu_item_db_id, '_menu_item_xfn', $args['menu-item-xfn'] );
	    update_post_meta( $menu_item_db_id, '_menu_item_url', esc_url_raw($args['menu-item-url']) );
		
		update_post_meta( $menu_item_db_id, '_menu_item_megamenu', sanitize_key($args['menu-item-megamenu']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_col', sanitize_key($args['menu-item-megamenu_columns']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_heading', sanitize_key($args['menu-item-megamenu_heading']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_widgetarea', sanitize_key($args['menu-item-megamenu_widgetarea']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_icon', sanitize_key($args['menu-item-megamenu_icon']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_icon_color', sanitize_key($args['menu-item-megamenu_icon_color']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_icon_size', sanitize_key($args['menu-item-megamenu_icon_size']) );
	    update_post_meta( $menu_item_db_id, '_menu_item_megamenu_icon_alignment', sanitize_key($args['menu-item-megamenu_icon_alignment']) );

	    if ( 0 == $menu_id )
	        update_post_meta( $menu_item_db_id, '_menu_item_orphaned', (string) time() );
	    elseif ( get_post_meta( $menu_item_db_id, '_menu_item_orphaned' ) )
	        delete_post_meta( $menu_item_db_id, '_menu_item_orphaned' );
	 
	    // Update existing menu item. Default is publish status
	    if ( $update ) {
	        $post['ID'] = $menu_item_db_id;
	        $post['post_status'] = 'draft' == $args['menu-item-status'] ? 'draft' : 'publish';
	        wp_update_post( $post );
	    }
	 
	    return $menu_item_db_id;
	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array $post Attachment post details from NOO
	 * @param string $url URL to fetch attachment from
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	function process_attachment( $post, $url ) {
		if ( ! $this->fetch_attachments )
			return new WP_Error( 'attachment_processing_error',
				esc_html__( 'Fetching attachments is not enabled', 'noo' ) );

		// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
		if ( preg_match( '|^/[\w\W]+$|', $url ) )
			$url = rtrim( $this->base_url, '/' ) . $url;

		$upload = $this->fetch_remote_file( $url, $post );
		if ( is_wp_error( $upload ) )
			return $upload;

		if ( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
		else
			return new WP_Error( 'attachment_processing_error', esc_html__('Invalid file type', 'noo' ) );

		$post['guid'] = $upload['url'];

		// as per wp-admin/includes/upload.php
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		// remap resized image URLs, works by stripping the extension and remapping the URL stub.
		if ( preg_match( '!^image/!', $info['type'] ) ) {
			$parts = pathinfo( $url );
			$name = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

			$parts_new = pathinfo( $upload['url'] );
			$name_new = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

			$this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
		}

		return $post_id;
	}

	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	function fetch_remote_file( $url, $post ) {
		// extract the file name and extension from the url
		$file_name = basename( $url );

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		if ( $upload['error'] )
			return new WP_Error( 'upload_dir_error', $upload['error'] );

		// fetch the remote url and write it to the placeholder file
		$headers = wp_get_http( $url, $upload['file'] );

		// request failed
		if ( ! $headers ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', esc_html__('Remote server did not respond', 'noo' ) );
		}

		// make sure the fetch was successful
		if ( $headers['response'] != '200' ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf( esc_html__('Remote server returned error response %1$d %2$s', 'noo' ), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
		}

		$filesize = filesize( $upload['file'] );

		if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', esc_html__('Remote file is incorrect size', 'noo' ) );
		}

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', esc_html__('Zero size file downloaded', 'noo' ) );
		}

		$max_size = (int) $this->max_attachment_size();
		if ( ! empty( $max_size ) && $filesize > $max_size ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf(esc_html__('Remote file is too large, limit is %s', 'noo' ), size_format($max_size) ) );
		}

		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url] = $upload['url'];
		$this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
		// keep track of the destination if the remote url is redirected somewhere else
		if ( isset($headers['x-final-location']) && $headers['x-final-location'] != $url )
			$this->url_remap[$headers['x-final-location']] = $upload['url'];

		return $upload;
	}

	/**
	 * Attempt to associate posts and menu items with previously missing parents
	 *
	 * An imported post's parent may not have been imported when it was first created
	 * so try again. Similarly for child menu items and menu items which were missing
	 * the object (e.g. post) they represent in the menu
	 */
	function backfill_parents() {
		global $wpdb;

		// find parents for post orphans
		foreach ( $this->post_orphans as $child_id => $parent_id ) {
			$local_child_id = $local_parent_id = false;
			if ( isset( $this->processed_posts[$child_id] ) )
				$local_child_id = $this->processed_posts[$child_id];
			if ( isset( $this->processed_posts[$parent_id] ) )
				$local_parent_id = $this->processed_posts[$parent_id];

			if ( $local_child_id && $local_parent_id )
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $local_parent_id ), array( 'ID' => $local_child_id ), '%d', '%d' );
		}

		// all other posts/terms are imported, retry menu items with missing associated object
		$missing_menu_items = $this->missing_menu_items;
		foreach ( $missing_menu_items as $item )
			$this->process_menu_item( $item );

		// find parents for menu item orphans
		foreach ( $this->menu_item_orphans as $child_id => $parent_id ) {
			$local_child_id = $local_parent_id = 0;
			if ( isset( $this->processed_menu_items[$child_id] ) )
				$local_child_id = $this->processed_menu_items[$child_id];
			if ( isset( $this->processed_menu_items[$parent_id] ) )
				$local_parent_id = $this->processed_menu_items[$parent_id];

			if ( $local_child_id && $local_parent_id )
				update_post_meta( $local_child_id, '_menu_item_menu_item_parent', (int) $local_parent_id );
		}
	}

	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	function backfill_attachment_urls() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort( $this->url_remap, array(&$this, 'cmpr_strlen') );

		foreach ( $this->url_remap as $from_url => $to_url ) {
			// remap urls in post_content
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url) );
			// remap enclosure urls
			$result = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url) );
		}
	}

	/**
	 * Update _thumbnail_id meta to new, imported attachment IDs
	 */
	function remap_featured_images() {
		// cycle through posts that have a featured image
		foreach ( $this->featured_images as $post_id => $value ) {
			if ( isset( $this->processed_posts[$value] ) ) {
				$new_id = $this->processed_posts[$value];
				// only update if there's a difference
				if ( $new_id != $value )
					update_post_meta( $post_id, '_thumbnail_id', $new_id );
			}
		}
	}

	/**
	 * Parse a NOO file
	 *
	 * @param string $file Path to NOO file for parsing
	 * @return array Information gathered from the NOO file
	 */
	function parse( $file ) {
		$parser = new NOO_Parser();
		return $parser->parse( $file );
	}

	// Display import page title
	function header() {
		echo '<div class="wrap">';
		// screen_icon();
		echo '<h2>' . esc_html__( 'Import WordPress', 'noo' ) . '</h2>';

		$updates = get_plugin_updates();
		$basename = plugin_basename(__FILE__);
		if ( isset( $updates[$basename] ) ) {
			$update = $updates[$basename];
			echo '<div class="error"><p><strong>';
			printf( esc_html__( 'A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'noo' ), $update->update->new_version );
			echo '</strong></p></div>';
		}
	}

	// Close div.wrap
	function footer() {
		echo '</div>';
	}

	/**
	 * Display introductory text and file upload form
	 */
	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.esc_html__( 'Howdy! Upload your WordPress eXtended RSS (NOO) file and we&#8217;ll import the posts, pages, comments, custom fields, categories, and tags into this site.', 'noo' ).'</p>';
		echo '<p>'.esc_html__( 'Choose a NOO (.xml) file to upload, then click Upload file and import.', 'noo' ).'</p>';
		wp_import_upload_form( 'admin.php?import=wordpress&amp;step=1' );
		echo '</div>';
	}

	/**
	 * Decide if the given meta key maps to information we will want to import
	 *
	 * @param string $key The meta key to check
	 * @return string|bool The key if we do want to import, false if not
	 */
	function is_valid_meta_key( $key ) {
		// skip attachment metadata since we'll regenerate it from scratch
		// skip _edit_lock as not relevant for import
		if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ) ) )
			return false;
		return $key;
	}

	/**
	 * Decide whether or not the importer is allowed to create users.
	 * Default is true, can be filtered via import_allow_create_users
	 *
	 * @return bool True if creating users is allowed
	 */
	function allow_create_users() {
		return apply_filters( 'import_allow_create_users', true );
	}

	/**
	 * Decide whether or not the importer should attempt to download attachment files.
	 * Default is true, can be filtered via import_allow_fetch_attachments. The choice
	 * made at the import options screen must also be true, false here hides that checkbox.
	 *
	 * @return bool True if downloading attachments is allowed
	 */
	function allow_fetch_attachments() {
		return apply_filters( 'import_allow_fetch_attachments', true );
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	function max_attachment_size() {
		return apply_filters( 'import_attachment_size_limit', 0 );
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 60
	 */
	

	// return the difference in length between two strings
	function cmpr_strlen( $a, $b ) {
		return strlen($b) - strlen($a);
	}

	// --- [ WIDGET ]

	function _noo_available_widgets() {

		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;

		$available_widgets = array();

		foreach ( $widget_controls as $widget ) {

			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[$widget['id_base']] ) ) { // no dupes

				$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
				$available_widgets[$widget['id_base']]['name'] = $widget['name'];

			}

		}

		return apply_filters( '_noo_available_widgets', $available_widgets );

	}
	/**
	 * Process import file
	 *
	 * This parses a file and triggers importation of its widgets.
	 *
	 * @since 0.3
	 * @param string $file Path to ._noo file uploaded
	 * @global string $_noo_import_results
	 */
	function _noo_process_import_file( $file ) {

		global $_noo_import_results;

		// File exists?
		// if ( ! file_exists( $file ) ) {
		// 	wp_die(
		// 		esc_html__( 'Import file could not be found. Please try again.', 'widget-importer-exporter' ),
		// 		'',
		// 		array( 'back_link' => true )
		// 	);
		// }

		// Get file contents and decode
		$data = file_get_contents( $file );
		$data = json_decode( $data );

		// Delete import file
		// unlink( $file );

		// Import the widget data
		// Make results available for display on import/export page
		$_noo_import_results = $this->_noo_import_data( $data );

	}

	/**
	 * Import widget JSON data
	 *
	 * @since 0.4
	 * @global array $wp_registered_sidebars
	 * @param object $data JSON widget data from ._noo file
	 * @return array Results array
	 */
	function _noo_import_data( $data ) {

		global $wp_registered_sidebars;

		// Have valid data?
		// If no data or could not decode
		if ( empty( $data ) || ! is_object( $data ) ) {
			wp_die(
				esc_html__( 'Imported data could not be read. Please try a different file..', 'widget-importer-exporter' ),
				'',
				array( 'back_link' => true )
			);
		}

		// Hook before import
		do_action( '_noo_before_import' );
		$data = apply_filters( '_noo_import_data', $data );

		// Get all available widgets site supports
		$available_widgets = $this->_noo_available_widgets();

		// Get all existing widget instances
		$widget_instances = array();
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[$widget_data['id_base']] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		// Begin results
		$results = array();

		// Loop import data's sidebars
		foreach ( $data as $sidebar_id => $widgets ) {

			// Skip inactive widgets
			// (should not be in export file)
			if ( 'wp_inactive_widgets' == $sidebar_id ) {
				continue;
			}

			// Check if sidebar is available on this site
			// Otherwise add widgets to inactive, and say so
			if ( isset( $wp_registered_sidebars[$sidebar_id] ) ) {
				$sidebar_available = true;
				$use_sidebar_id = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message = '';
			} else {
				$sidebar_available = false;
				$use_sidebar_id = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
				$sidebar_message_type = 'error';
				$sidebar_message = esc_html__( 'Sidebar does not exist in theme (using Inactive)', 'widget-importer-exporter' );
			}

			// Result for sidebar
			$results[$sidebar_id]['name'] = ! empty( $wp_registered_sidebars[$sidebar_id]['name'] ) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
			$results[$sidebar_id]['message_type'] = $sidebar_message_type;
			$results[$sidebar_id]['message'] = $sidebar_message;
			$results[$sidebar_id]['widgets'] = array();

			// Loop widgets
			foreach ( $widgets as $widget_instance_id => $widget ) {

				$fail = false;

				// Get id_base (remove -# from end) and instance ID number
				$id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

				// Does site support this widget?
				if ( ! $fail && ! isset( $available_widgets[$id_base] ) ) {
					$fail = true;
					$widget_message_type = 'error';
					$widget_message = esc_html__( 'Site does not support widget', 'widget-importer-exporter' ); // explain why widget not imported
				}

				// Filter to modify settings object before conversion to array and import
				// Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below)
				// Ideally the newer _noo_widget_settings_array below will be used instead of this
				$widget = apply_filters( '_noo_widget_settings', $widget ); // object

				// Convert multidimensional objects to multidimensional arrays
				// Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
				// Without this, they are imported as objects and cause fatal error on Widgets page
				// If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
				// It is probably much more likely that arrays are used than objects, however
				$widget = json_decode( json_encode( $widget ), true );

				// Filter to modify settings array
				// This is preferred over the older _noo_widget_settings filter above
				// Do before identical check because changes may make it identical to end result (such as URL replacements)
				$widget = apply_filters( '_noo_widget_settings_array', $widget );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[$id_base] ) ) {

					// Get existing widgets in this sidebar
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets = isset( $sidebars_widgets[$use_sidebar_id] ) ? $sidebars_widgets[$use_sidebar_id] : array(); // check Inactive if that's where will go

					// Loop widgets with ID base
					$single_widget_instances = ! empty( $widget_instances[$id_base] ) ? $widget_instances[$id_base] : array();
					foreach ( $single_widget_instances as $check_id => $check_widget ) {

						// Is widget in same sidebar and has identical settings?
						if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {

							$fail = true;
							$widget_message_type = 'warning';
							$widget_message = esc_html__( 'Widget already exists', 'widget-importer-exporter' ); // explain why widget not imported

							break;

						}

					}

				}

				// No failure
				if ( ! $fail ) {

					// Add widget instance
					$single_widget_instances = get_option( 'widget_' . $id_base ); // all instances for that widget ID base, get fresh every time
					$single_widget_instances = ! empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 ); // start fresh if have to
					$single_widget_instances[] = $widget; // add it

						// Get the key it was given
						end( $single_widget_instances );
						$new_instance_id_number = key( $single_widget_instances );

						// If key is 0, make it 1
						// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it)
						if ( '0' === strval( $new_instance_id_number ) ) {
							$new_instance_id_number = 1;
							$single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
							unset( $single_widget_instances[0] );
						}

						// Move _multiwidget to end of array for uniformity
						if ( isset( $single_widget_instances['_multiwidget'] ) ) {
							$multiwidget = $single_widget_instances['_multiwidget'];
							unset( $single_widget_instances['_multiwidget'] );
							$single_widget_instances['_multiwidget'] = $multiwidget;
						}

						// Update option with new widget
						update_option( 'widget_' . $id_base, $single_widget_instances );

					// Assign widget instance to sidebar
					$sidebars_widgets = get_option( 'sidebars_widgets' ); // which sidebars have which widgets, get fresh every time
					$new_instance_id = $id_base . '-' . $new_instance_id_number; // use ID number from new widget instance
					$sidebars_widgets[$use_sidebar_id][] = $new_instance_id; // add new instance to sidebar
					update_option( 'sidebars_widgets', $sidebars_widgets ); // save the amended data

					// After widget import action
					$after_widget_import = array(
						'sidebar'           => $use_sidebar_id,
						'sidebar_old'       => $sidebar_id,
						'widget'            => $widget,
						'widget_type'       => $id_base,
						'widget_id'         => $new_instance_id,
						'widget_id_old'     => $widget_instance_id,
						'widget_id_num'     => $new_instance_id_number,
						'widget_id_num_old' => $instance_id_number
					);
					do_action( '_noo_after_widget_import', $after_widget_import );

					// Success message
					if ( $sidebar_available ) {
						$widget_message_type = 'success';
						$widget_message = esc_html__( 'Imported', 'widget-importer-exporter' );
					} else {
						$widget_message_type = 'warning';
						$widget_message = esc_html__( 'Imported to Inactive', 'widget-importer-exporter' );
					}

				}

				// Result for widget instance
				$results[$sidebar_id]['widgets'][$widget_instance_id]['name'] = isset( $available_widgets[$id_base]['name'] ) ? $available_widgets[$id_base]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
				$results[$sidebar_id]['widgets'][$widget_instance_id]['title'] = ! empty( $widget['title'] ) ? $widget['title'] : esc_html__( 'No Title', 'widget-importer-exporter' ); // show "No Title" if widget instance is untitled
				$results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
				$results[$sidebar_id]['widgets'][$widget_instance_id]['message'] = $widget_message;

			}

		}

		// Hook after import
		do_action( '_noo_after_import' );

		// Return results
		return apply_filters( '_noo_import_results', $results );

	}

	// --- [ OPTION ]

	/**
	 * Load import option
	 */
	
	function process_option( $file ) {
		$file_contents = file_get_contents( $file );
		$data = json_decode( $file_contents, true );
		$options_to_import = $this->get_whitelist_options();

		$hash = '048f8580e913efe41ca7d402cc51e848';

		// Allow others to prevent their options from importing
			// $blacklist = $this->get_blacklist_options();

		foreach ( (array) $options_to_import as $option_name ) {
			if ( isset( $data['options'][ $option_name ] ) ) {
				
				// we're going to use a random hash as our default, to know if something is set or not
				$old_value = get_option( $option_name, $hash );

				$option_value = maybe_unserialize( $data['options'][ $option_name ] );
				if ( in_array( $option_name, $data['no_autoload'] ) ) {
					delete_option( $option_name );
					add_option( $option_name, $option_value, '', 'no' );
				} else {
					update_option( $option_name, $option_value );
				}
			}
		}

		// Re-construct the permalink
		flush_rewrite_rules();

		$nav_menu_locations = get_theme_mod( 'nav_menu_locations', array() );
		if( $primary_menu = wp_get_nav_menu_object( 'Primary Menu' ) ) {
			$nav_menu_locations['primary'] = $primary_menu->term_id;
		}

		set_theme_mod( 'nav_menu_locations', $nav_menu_locations );
	} 

	/**
	 * Get an array of blacklisted options which we never want to import.
	 *
	 * @return array
	 */
	private function get_blacklist_options() {
		return apply_filters( 'options_import_blacklist', array() );
	}

	/**
	 * Get an array of known options which we would want checked by default when importing.
	 *
	 * @return array
	 */
	private function get_whitelist_options() {
		return apply_filters( 'options_import_whitelist', array(
			'nav_menu_options',
			'page_for_posts',
			'page_on_front',
			'show_on_front',
			'users_can_register',
			'permalink_structure',
			'noo_member',
			'job_package',
			'theme_mods_noo-jobmonster',
			'noo_job_custom_field',
			'noo_candidate_custom_field',
			'noo_company_custom_field',
			'noo_resume_custom_field',
		) );
	}


}

new Noo_Import_Demo();
