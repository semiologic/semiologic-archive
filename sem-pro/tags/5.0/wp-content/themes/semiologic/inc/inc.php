<?php
#
# DO NOT EDIT THIS FILE
# ---------------------
# The Semiologic theme features a custom.php feature. This allows to hook into
# the template without editing its php files. That way, you won't need to worry
# about losing your changes when you upgrade your site.
#
# You'll find detailed sample files in the custom-samples folder
#


#
# include utils
#

foreach ( (array) glob(sem_path . '/utils/*.php') as $inc_file )
{
	include_once $inc_file;
}

foreach ( (array) glob(sem_pro_path . '/utils/*.php') as $inc_file )
{
	include_once $inc_file;
}


#
# include admin screens
#

if ( strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false )
{
	foreach ( (array) glob(sem_path . '/admin/*.php') as $inc_file )
	{
		include_once $inc_file;
	}

	foreach ( (array) glob(sem_pro_path . '/admin/*.php') as $inc_file )
	{
		include_once $inc_file;
	}
}


#
# include custom.php and skin.php files
#

foreach (
	array(
		sem_path . '/skins/' . get_active_skin() . '/skin.php',
		sem_path . '/custom.php',
		sem_path . '/skins/' . get_active_skin() . '/custom.php'
		) as $inc_file
	)
{
	if ( file_exists($inc_file) )
	{
		include_once $inc_file;
	}
}


#
# autocorrect wp-cache
#

if ( defined('WP_CACHE') && !function_exists('wp_cache_add_pages') )
{
	include_once ABSPATH . PLUGINDIR . '/wp-cache/wp-cache.php';
}

?>