<?php
#
# DO NOT EDIT THIS FILE
# ---------------------
# You would lose your changes when you upgrade your site. Use php widgets instead.
#


if ( $post->post_password !== '' && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password )
	return;

global $comments_captions;


#
# Extract pings
#

$pings = separate_comments($comments);
$comments = $pings['comment'];
$pings = $pings['pings'];


if ( $pings || $comments )
	echo '<div id="comments">' . "\n";
	
#
# Display pings
#

if ( $pings ) {
	$title = the_title('', '', false);

	$caption = $comments_captions['pings_on'];
	$caption = str_replace('%title%', $title, $caption);
	
	echo '<div class="pings_header">' . "\n"
		. '<div class="pings_header_top"><div class="hidden"></div></div>' . "\n"
		. '<div class="pad">' . "\n"
		. '<h2>' . $caption . '</h2>' . "\n"
		. '<div class="pings_header_bottom"><div class="hidden"></div></div>' . "\n"
		. '</div>' . "\n"
		. '</div>' . "\n";
	
	foreach ( $pings as $comment ) {
		$cur_date = get_comment_date();
		
		if ( !isset($prev_date) || $cur_date != $prev_date ) {
			if ( isset($prev_date) ) {
				echo '</ul>' . "\n";
				
				echo '</div>' . "\n"
					. '<div class="pings_list_bottom"><div class="hidden"></div></div>' . "\n"
					. '</div> <!-- pings_list -->' . "\n";
			}
			
			echo '<div class="pings_date">' . "\n"
				. '<div class="pad">' . "\n"
				. '<span>'
				. $cur_date
				. '</span>'
				. '</div>' . "\n"
				. '</div>' . "\n";
			
			echo '<div class="pings_list">' . "\n"
				. '<div class="pings_list_top"><div class="hidden"></div></div>' . "\n"
				. '<div class="pad">' . "\n";

			echo '<ul>' . "\n";
			
			$prev_date = $cur_date;
		}
		
		echo '<li id="comment-' . get_comment_ID() . '">'
			. get_comment_author_link()
			. '</li>' . "\n";
	}
	
	echo '</ul>' . "\n";
	
	echo '</div>' . "\n"
		. '<div class="pings_list_bottom"><div class="hidden"></div></div>' . "\n"
		. '</div> <!-- pings_list -->' . "\n";
	
	unset($prev_date);
} # if ( $pings )


#
# Display comments
#

if ( $comments ) {
	$title = the_title('', '', false);

	$caption = $comments_captions['comments_on'];
	$caption = str_replace('%title%', $title, $caption);

	if ( comments_open() && !( isset($_GET['action']) && $_GET['action'] == 'print' ) ) {

		$comment_form_link = ' <span class="comment_entry">'
			. '<a href="#respond" title="' . esc_attr($comments_captions['leave_comment']) . '" class="no_icon">'
			. '<img src="' . sem_url . '/icons/pixel.gif" height="16" width="16" alt="' . esc_attr($comments_captions['leave_comment']) . '" />'
			. '</a>'
			. '</span>';
	} else {
		$comment_form_link = false;
	}
	
	echo '<div class="comments_header">' . "\n"
		. '<div class="comments_header_top"><div class="hidden"></div></div>' . "\n"
		. '<div class="pad">' . "\n"
		. '<h2>' . $caption . $comment_form_link . '</h2>' . "\n"
		. '<div class="comments_header_bottom"><div class="hidden"></div></div>' . "\n"
		. '</div>' . "\n"
		. '</div>' . "\n";
	
	foreach ( (array) $comments as $comment ) {
		$cur_date = get_comment_date();
		
		if ( !isset($prev_date) || $cur_date != $prev_date ) {
			echo '<div class="comment_date">' . "\n"
				. '<div class="pad">' . "\n"
				. '<span>'
				. $cur_date
				. '</span>'
				. '</div>' . "\n"
				. '</div>' . "\n";
			
			$prev_date = $cur_date;
		}
		
		echo '<div class="spacer"></div>' . "\n";
		
		echo '<div id="comment-' . get_comment_ID() . '">' . "\n";
		
		echo '<div class="comment'
				. ( $comment->user_id == $post->post_author
					? ' comment_entry_author'
					: ''
					)
				. '">' . "\n"
			. '<div class="comment_top"><div class="hidden"></div></div>' . "\n"
			. '<div class="comment_pad">' . "\n";

		echo '<div class="comment_header">' . "\n"
			. '<div class="comment_header_top"><div class="hidden"></div></div>' . "\n"
			. '<div class="pad">' . "\n";
		
		echo '<h3>'
			. '<span class="comment_author" id="comment_author-' . get_comment_ID() . '">'
				. get_avatar($comment, 48)
				. ( $comment->user_id == $post->post_author
					? ( '<em>' . get_comment_author_link() . '</em>' )
					: get_comment_author_link()
					)
				. '</span>'
			. '<br/>' . "\n"
			. '<span class="comment_time">'
			. get_comment_date('g:i a')
			. '</span>' . "\n"
			. '<span class="link_comment">'
			. '<a href="#comment-' . get_comment_ID() . '" title="#">'
			. '<img src="' . sem_url . '/icons/pixel.gif' . '" height="12" width="12" class="no_icon" alt="#" />'
			. '</a>'
			. '</span>' . "\n"
			. '</h3>' . "\n";

		echo '</div>' . "\n"
			. '<div class="comment_header_bottom"><div class="hidden"></div></div>' . "\n"
			. '</div>' . "\n";


		echo '<div class="comment_content">' . "\n"
			. '<div class="comment_content_top"><div class="hidden"></div></div>' . "\n"
			. '<div class="pad">' . "\n";
		
		if ( !( isset($_GET['action']) && $_GET['action'] == 'print' ) ) {
			echo '<div class="comment_actions">' . "\n";

			edit_comment_link(__('Edit'), '<span class="edit_comment">', '</span>' . "\n");
			
			if ( comments_open() && $comment->comment_approved ) {
				
				echo '<span class="reply_comment">'
				. '<a href="#respond"'
					. ' onclick="'
						. "jQuery('#comment').val("
							. "'@&lt;a href=&quot;#comment-$comment->comment_ID&quot;&gt;'"
							. " + jQuery('#comment_author-$comment->comment_ID').text()"
							. " + '&lt;/a&gt;: '"
							. ");"
						. ' return addComment.moveForm('
							. "'comment-$comment->comment_ID', '$comment->comment_ID',"
							. " 'respond', '$post->ID'"
							. ');"'
					. '>'
				. $comments_captions['reply_link']
				. '</a>'
				. '</span>' . "\n";
			}
			
			echo '</div>' . "\n";
		}
		
		echo apply_filters('comment_text', get_comment_text());
		
		if ( $comment->comment_approved == '0' ) {
			echo '<p>'
				. '<em>' . __('Your comment is awaiting moderation.') . '</em>'
				. '</p>' . "\n";
		}
		
		echo '</div>' . "\n"
			. '<div class="comment_content_bottom"><div class="hidden"></div></div>' . "\n"
			. '</div>' . "\n";


		echo '<div class="spacer"></div>' . "\n";

		echo '</div>' . "\n"
			. '<div class="comment_bottom"><div class="hidden"></div></div>' . "\n"
			. '</div> <!-- comment -->' . "\n";
		
		echo '<div class="spacer"></div>' . "\n";
		
		echo '</div> <!-- comment-id -->' . "\n";
	} # foreach $comments as $comment
} # if $comments


if ( $pings || $comments )
	echo '</div><!-- #comments -->' . "\n";
	
#
# Display comment form
#

if ( comments_open() && !( isset($_GET['action']) && $_GET['action'] == 'print' ) ) {
	echo '<div id="respond">' . "\n";
	
	echo '<div class="comments_header">' . "\n"
		. '<div class="comments_header_top"><div class="hidden"></div></div>' . "\n"
		. '<div class="pad">' . "\n"
		. '<h2>' . $comments_captions['leave_comment'] . '</h2>' . "\n";
	
	echo '<p class="cancel_comment_reply">'
		. '<a id="cancel-comment-reply-link" href="#respond" style="display:none;">'
		. __('Click here to cancel reply.')
		. '</a>'
		. '</p>' . "\n";
	
	echo '</div>' . "\n"
		. '<div class="comments_header_bottom"><div class="hidden"></div></div>' . "\n"
		. '</div>' . "\n";

	if ( get_option('comment_registration') && !$user_ID ) {
		$login_url = trailingslashit(site_url('login'))
			. 'wp-login.php?redirect_to='
			. urlencode(get_permalink());
		
		$login_url = '<span class="login">'
			. apply_filters('loginout',
				'<a href="' . $login_url . '">' . __('Logout') . '</a>'
				)
			. '</span>';
			
		echo '<div class="comments_login">' . "\n"
			. '<div class="pad">' . "\n"
			. '<p>'
			. str_replace('%login_url%', $login_url, $comments_captions['login_required'])
			. '</p>' . "\n"
			. '</div>' . "\n"
			. '</div>' . "\n";
	} else {
		echo '<form method="post" id="commentform"'
			. ' action="' . trailingslashit(site_url()) . 'wp-comments-post.php"'
			. '>' . "\n"
			. '<div class="pad">' . "\n";

		if ( $user_ID ) {
			$logout_url = '<span class="logout">'
				. apply_filters('loginout',
					'<a href="' . wp_logout_url() . '">' . __('Logout') . '</a>'
					)
				. '</span>';

			$identity = '<span class="signed_in_author">'
				. '<a href="' . trailingslashit(site_url()) . 'wp-admin/profile.php">'
				. $user_identity
				. '</a>'
				. '</span>';

			echo '<p>'
				. str_replace(
					array('%identity%', '%logout_url%'),
					array($identity, $logout_url),
					$comments_captions['logged_in_as']
					)
				. '</p>' . "\n";
		} else {
			echo '<p class="comment_label name_label">'
				. '<label for="author">'
				. $comments_captions['name_field']
				. ( $req
					? ' (*)'
					: ''
					)
				. '</label>'
				. '</p>' . "\n";
			
			echo '<p class="comment_field name_field">'
				. '<input type="text" name="author" id="author"'
					. ' value="' . esc_attr($comment_author) . '" />'
				. '</p>' . "\n";
			
			echo '<div class="spacer"></div>' . "\n";
			
			echo '<p class="comment_label email_label">'
				. '<label for="email">'
				. $comments_captions['email_field']
				. ( $req
					? ' (*)'
					: ''
					)
				. '</label>'
				. '</p>' . "\n";
			
			echo '<p class="comment_field email_field">'
				. '<input type="text" name="email" id="email"'
					. ' value="' . esc_attr($comment_author_email) . '" />'
				. '</p>' . "\n";
			
			echo '<div class="spacer"></div>' . "\n";
			
			echo '<p class="comment_label url_label">'
				. '<label for="url">'
				. $comments_captions['url_field']
				. '</label>'
				. '</p>' . "\n";
			
			echo '<p class="comment_field url_field">'
				. '<input type="text" name="url" id="url"'
					. ' value="' . esc_attr($comment_author_url) . '" />'
				. '</p>' . "\n";
			
			echo '<div class="spacer"></div>' . "\n";
		} # if ( $user_ID )
		
		
		echo '<textarea name="comment" id="comment" cols="48" rows="10"></textarea>' . "\n";
		
		if ( !$user_ID && $req )
			echo '<p>'
				.  $comments_captions['required_fields']
				. '</p>' . "\n";
		
		echo '<p class="submit">'
			. '<input name="submit" type="submit" id="submit" class="button"'
				. ' value="' . esc_attr($comments_captions['submit_field']) . '"'
				. ' />'
			. '</p>' . "\n";

		comment_id_fields();
		
		do_action('comment_form', $post->ID);
		
		echo '</div>' . "\n"
			. '</form>' . "\n";

		if ( function_exists('show_manual_subscription_form') ) {
			show_manual_subscription_form();
		}
	} # get_option('comment_registration') && !$user_ID
	
	echo '</div><!-- #respond -->' . "\n";
} # comments_open()
?>