<?php
#
# Generic Print Template
# ----------------------
#
# This file will be used if no print.php file is present in your theme's folder
#
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head><title><?php
if ( $title = trim(wp_title('&#8211;', false)) ) {
	if ( strpos($title, '&#8211;') === 0 )
		$title = trim(substr($title, strlen('&#8211;')));
	echo $title;
} else {
	bloginfo('description');
}
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
<meta name="robots" content="noindex,nofollow" />
<?php
wp_print_scripts('swfobject');
if ( method_exists('hitslink', 'header_scripts') )
	hitslink::header_scripts();
?>
<style type="text/css">
body {
	padding: 0px;
	margin: 0px;
}

.media {
	margin: 10px auto;
}
</style>
</head>
<body>
<div id="body">
<?php
echo mediacaster::video($args);
?>
</div>
<?php
if ( method_exists('google_analytics', 'footer_scripts') )
	google_analytics::footer_scripts();
?>
</body>
</html>