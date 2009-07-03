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


# show header
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head><title><?php do_action('display_page_title'); ?></title>
<?php
do_action('wp_head');
?>
</head>
<body class="<?php do_action('display_page_class'); ?>">
<?php
do_action('before_the_entries');

# show posts
if ( have_posts() )
{
	while ( have_posts() )
	{
		the_post();

?>
<div class="entry" id="entry-<?php the_ID(); ?>">
<?php
		do_action('before_the_entry');
		do_action('display_entry_header');
		do_action('display_entry_body');
		do_action('display_entry_spacer');
		do_action('display_entry_meta');
		do_action('display_entry_actions');
		do_action('after_the_entry');
		comments_template();
?>
</div>
<?php
	}

}
# or fallback
else
{
	do_action('display_404');
}

do_action('after_the_entries');

# show footer
?>
</div>
</div><!-- #main -->
</div><!-- #body -->

<?php
do_action('wp_footer');
?>
</body>
</html>