<?php
session_start();
	noo_create_image( isset( $_GET['code'] ) ? $_GET['code'] : null );
exit(); 

function noo_create_image( $security_code ) {

    $_SESSION["security_code"] = $security_code;
	$width  = 100; 
	$height = 40;  
	$image  = ImageCreate($width, $height);  
	$white  = ImageColorAllocate($image, 255, 255, 255); 
	$black  = ImageColorAllocate($image, 0, 0, 0); 
    ImageFill($image, 0, 0, $black); 
    ImageString($image, 16, 30, 12, $security_code, $white); 
    header("Content-Type: image/jpeg"); 
    ImageJpeg($image); 
    ImageDestroy($image); 
} 
?>