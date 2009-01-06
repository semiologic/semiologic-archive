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
include_once sem_path . '/header.php';

do_action('before_the_entries');

# show posts
if ( have_posts() )
{
	while ( have_posts() )
	{
		the_post();

		echo '<div class="entry" id="entry-' . get_the_ID() . '">' . "\n";

		do_action('the_entry');

		echo '</div>' . "\n";
	}

}
# or fallback
elseif ( is_404() || is_search() )
{
	echo '<div class="entry">' . "\n";

	do_action('the_404');

	echo '</div>' . "\n";
}

do_action('after_the_entries');

# show footer
include_once sem_path . '/footer.php';
?>