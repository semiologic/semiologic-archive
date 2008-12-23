<?php

include sem_path . '/header.php';


do_action('before_the_entries');
do_action('before_the_entry');

?><div class="entry now-reading">

		<?php if( have_books(intval($GLOBALS['nr_id'])) ) : ?>
			<?php while ( have_books(intval($GLOBALS['nr_id'])) ) : the_book(); ?>
			<?php if( can_now_reading_admin() ) : ?>			<p>Admin: &raquo; <a href="<?php manage_library_url() ?>">Manage Books</a> &raquo; <a href="<?php book_edit_url() ?>">Edit this book</a></p>
			<?php endif; ?>
			<?php library_search_form() ?>
			<p><a href="<?php library_url() ?>">&larr; Back to library</a></p>

			<h2><?php book_title() ?></h2>
			<p>By <a href="<?php book_author_permalink() ?>"><?php book_author() ?></a></p>

			<p>
				<a href="<?php book_url() ?>" class="noicon"><img src="<?php book_image() ?>" alt="<?php book_title() ?>" /></a>
			</p>

			<?php if( !is_custom_book() ): ?>				<p>You can view this book's Amazon detail page <a href="<?php book_url() ?>">here</a>.</p>
			<?php endif; ?>
			<?php if( book_has_post() ): ?>				<p>This book is linked with the post <a href="<?php book_post_url() ?>">&ldquo;<?php book_post_title() ?>&rdquo;</a>.</p>
			<?php endif; ?>
			<p>Tags: <?php print_book_tags() ?></p>

			<dl>
				<dt>Started reading:</dt>
				<dd><?php book_started() ?></dd>

				<dt>Finished reading:</dt>
				<dd><?php book_finished() ?></dd>

				<?php print_book_meta(0); ?>			</dl>

			<div class="review">

				<h3>Review</h3>

				<p><strong>Rating:</strong> <?php book_rating() ?></p>

				<?php book_review() ?>
			</div>

			<?php endwhile; ?>
		<?php else : ?>
			<p>That book doesn't exist!</p>

		<?php endif; ?></div>
<?php

do_action('after_the_entry');
do_action('after_the_entries');

include sem_path . '/footer.php';
?>