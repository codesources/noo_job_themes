<?php

function noo_upload()
{
	check_ajax_referer('aaiu_allow', 'nonce');

	$file = array(
		'name' => $_FILES['aaiu_upload_file']['name'],
		'type' => $_FILES['aaiu_upload_file']['type'],
		'tmp_name' => $_FILES['aaiu_upload_file']['tmp_name'],
		'error' => $_FILES['aaiu_upload_file']['error'],
		'size' => $_FILES['aaiu_upload_file']['size'],
		);
	$file = noo_fileupload_process($file);
}

function noo_fileupload_process($file)
{
	$attachment = noo_handle_file($file);
	if (is_array($attachment)) {
		$file = explode('/', $attachment['data']['file']);
		$file = array_slice($file, 0, count($file) - 1);
		$path = implode('/', $file);

		$dir = wp_upload_dir();
		$path = $dir['baseurl'] . '/' . $path;
		$thumbnail = '';
		$image = '';

		if( isset( $attachment['data']['sizes']['thumbnail'] ) ) {
			$thumbnail = $path . '/' . $attachment['data']['sizes']['thumbnail']['file'];
		} else {
			$thumbnail = $dir['baseurl'] . '/' . $attachment['data']['file'];
		}

		$image = $dir['baseurl'] . '/' . $attachment['data']['file'];

		$response = array(
			'success' => true,
			'image' => $image,
			'thumbnail' => $thumbnail,
			'image_id' => $attachment['id']
			);

		echo json_encode($response);
		exit;
	}

	$response = array('success' => false);
	echo json_encode($response);
	exit;
}

function noo_handle_file($upload_data)
{

	$return = false;
	$uploaded_file = wp_handle_upload($upload_data, array('test_form' => false));

	if (isset($uploaded_file['file'])) {
		$file_loc = $uploaded_file['file'];
		$file_name = basename($upload_data['name']);
		$file_type = wp_check_filetype($file_name);

		$attachment = array(
			'post_mime_type' => $file_type['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_name)),
			'post_content' => '',
			'post_status' => 'inherit'
			);

		$attach_id = wp_insert_attachment($attachment, $file_loc);
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_loc);
		wp_update_attachment_metadata($attach_id, $attach_data);

		$return = array('data' => $attach_data, 'id' => $attach_id);

		return $return;
	}

	return $return;
}

function noo_getHTML($attachment)
{
	$file = explode('/', $attachment['data']['file']);
	$file = array_slice($file, 0, count($file) - 1);
	$path = implode('/', $file);

	$dir = wp_upload_dir();
	$path = $dir['baseurl'] . '/' . $path;

	if( is_page_template('agent_dashboard_submit.php') ) {
		$image = $attachment['data']['sizes']['property-thumbnail']['file'] . 3;
	} else {
		$image = $attachment['data']['sizes']['property-image']['file'] . get_page_template();
	}

	$html = $path . '/' . $image;

	return $html;
}

function noo_delete_attachment()
{
	check_ajax_referer('aaiu_remove', 'nonce');

	$attach_id = $_POST['attach_id'];

	wp_delete_attachment($attach_id, true);
	exit;
}

add_action('wp_ajax_noo_upload', 'noo_upload');
add_action('wp_ajax_noo_delete_attachment', 'noo_delete_attachment');
add_action('wp_ajax_nopriv_noo_upload', 'noo_upload');
add_action('wp_ajax_nopriv_noo_delete_attachment', 'noo_delete_attachment');

function noo_plupload_form($field_name='',$extensions='jpg,gif,png',$value=''){
	$extensions = explode(',', $extensions);
	noo_file_upload_form_field( $field_name, $extensions, $value );
}

/* -------------------------------------------------------
 * Create functions noo_get_file_upload
 $dir * ------------------------------------------------------- */

if ( ! function_exists( 'noo_get_file_upload' ) ) :
	
	function noo_get_file_upload( $filename = null, $folder = 'jobmonster' ) {
		if ( $filename == null ) return;
		$dir = wp_upload_dir();
		$target_dir = $dir['baseurl'] . '/' . $folder . '/';

		wp_mkdir_p($target_dir);
		$file_path = $target_dir . $filename;

		return $file_path;
	}

endif;

/** ====== END noo_get_file_upload  $dir ===== **/

function noo_plupload(){
	check_ajax_referer('noo-plupload', 'nonce');
	header('Content-Type: text/html; charset=' . get_option('blog_charset'));
	send_nosniff_header();
	nocache_headers();
	status_header(200);
	
	$dir = wp_upload_dir();
	
	$target_dir = $dir['basedir'].'/jobmonster/';
	
	wp_mkdir_p($target_dir);
	
	$cleanup_target_dir = true; // Remove old files
	$maxFileAge         = 5 * 3600; // Temp file age in seconds
	
	$chunk  = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
	$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
	
	$file_name = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
	$file_name = sanitize_file_name($file_name);
	
	$tmp_file_name = $file_name;
	$file_path = $target_dir . $tmp_file_name;
	
	if (file_exists($file_path)) {
		$count = 1;
		$new_path = $file_path;
		while (file_exists($new_path)) {
			$new_filename = $count++ . '_' . $file_name;
			$new_path = $target_dir . $new_filename;
		}
	
		$tmp_file_name = $new_filename;
	}
	
	$file_path = $target_dir . $tmp_file_name;
	
	// Remove old temp files
	if ($cleanup_target_dir) {
		if (is_dir($target_dir) && ($dir = opendir($target_dir))) {
			while (($file = readdir($dir)) !== false) {
				$tmp_file_path = $target_dir . $file;
	
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmp_file_path) < time() - $maxFileAge) && ($tmp_file_path != "{$file_path}.part")) {
					@unlink($tmp_file_path);
				}
			}
			closedir($dir);
		} else {
			die('{"status" : "error", "error" : {"code": 100, "message": "' . __("Failed to open temp directory.", 'noo') . '"}}');
		}
	}
	
	// Look for the content type header
	if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	
	if (isset($_SERVER["CONTENT_TYPE"]))
		$contentType = $_SERVER["CONTENT_TYPE"];
	
	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if (strpos($contentType, "multipart") !== false) {
		if (isset($_FILES["file"]['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			// Open temp file
			$out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = @fopen($_FILES["file"]['tmp_name'], "rb");
	
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else {
					die('{"status" : "error", "error" : {"code": 101, "message": "' . __("Failed to open input stream.",'noo') . '"}}');
				}
	
				@fclose($in);
				@fclose($out);
				@unlink($_FILES["file"]['tmp_name']);
			} else {
				die('{"status" : "error", "error" : {"code": 102, "message": "' . __("Failed to open output stream.", 'noo') . '"}}');
			}
	
		} else {
			die('{"status" : "error", "error" : {"code": 103, "message": "' . __("Failed to move uploaded file.", 'noo') . '"}}');
		}
	
	} else {
		// Open temp file
		$out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = @fopen("php://input", "rb");
	
			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else {
				die('{"status" : "error", "error" : {"code": 101, "message": "' . __("Failed to open input stream.", 'noo') . '"}}');
			}
	
			@fclose($in);
			@fclose($out);
		} else {
			die('{"status" : "error", "error" : {"code": 102, "message": "' . __("Failed to open output stream.", 'noo') . '"}}');
		}
	
	}
	
	// Check if file has been uploaded
	if (!$chunks || $chunk == $chunks - 1) {
		// Strip the temp .part suffix off
		rename("{$file_path}.part", $file_path);
	}
	$uploaded_filename = $_FILES["file"]["name"];
	$output = array("status"    => "ok",
			"data" => array(
				"filename"     => $tmp_file_name ,
				"upload_filename" => str_replace("\\'", "'", urldecode($uploaded_filename)) //Decoding filename to prevent file name mismatch.
			)
	);
	wp_send_json($output);
}

function noo_plupload_delete_file(){
	check_ajax_referer('noo-plupload-remove', 'nonce');
	
	$file_name = isset($_POST['filename']) ? $_POST['filename'] : '';
	$dir = wp_upload_dir();
	$target_dir = $dir['basedir'].'/jobmonster/';
	if(!empty($file_name) && file_exists($target_dir.$file_name)){
		@unlink($target_dir.$file_name);
	}
	die(1);
}
add_action('wp_ajax_noo_plupload', 'noo_plupload');
add_action('wp_ajax_noo_plupload_delete_file', 'noo_plupload_delete_file');
add_action('wp_ajax_nopriv_noo_plupload', 'noo_plupload');
add_action('wp_ajax_nopriv_noo_plupload_delete_file', 'noo_plupload_delete_file');

