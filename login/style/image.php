<?php
	header('Cache-control: max-age=1209600');
	header('Expires: '.strftime('%a, %d %b %Y %T %Z', time()+1209600));
	ob_start('ob_gzhandler');

	$image_path = urldecode($_SERVER['QUERY_STRING']);
	if($image_path == '' || !is_file($image_path) || strpos($image_path, '../') !== false)
		die();

	if(substr($image_path, -4) == '.gif')
		header('Content-type: image/gif');
	elseif(substr($image_path, -4) == '.jpg' || substr($image_path, -5) == '.jpeg')
		header('Content-type: image/jpeg');
	elseif(substr($image_path, -4) == '.png')
		header('Content-type: image/png');
	else
		die();

	readfile($image_path);
?>