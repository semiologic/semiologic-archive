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


/*
Template Name: Links
*/

if ( isset($_GET['cat_id']) )
{
	global $wp_the_query;
	wp_redirect(apply_filters('the_permalink', get_permalink($wp_the_query->get_queried_object_id())), 301);
	die;
}

include sem_path . '/index.php';
?>