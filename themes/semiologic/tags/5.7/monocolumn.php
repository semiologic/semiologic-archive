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
Template Name: Mono Column
*/

add_filter('active_layout', 'strip_s');

include sem_path . '/index.php';
?>