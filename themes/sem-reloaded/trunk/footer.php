<?php
#
# DO NOT EDIT THIS FILE
# ---------------------
# You would lose your changes when you upgrade your site. Use php widgets instead.
#

			
		# end content
		
		echo '</div>' . "\n";
		
		echo '<div id="main_bottom"><div class="hidden"></div></div>' . "\n";
		
		echo '</div><!-- main -->' . "\n";
		
		
		# sidebars
		switch ( $active_layout ) :


		case 'mts' :
		case 'tsm' :

			
			# sidebars wrapper
		
			echo '<div id="sidebars">' . "\n";

			echo '<div id="sidebars_top"><div class="hidden"></div></div>' . "\n";

		
			# top sidebar
			
			echo '<div id="top_sidebar" class="sidebar wide_sidebar">' . "\n";

			echo '<div id="top_sidebar_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('top_sidebar');

			echo '<div id="top_sidebar_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- top sidebar -->' . "\n";

			
			# spacer

			echo '<div class="spacer"></div>' . "\n";
			
			
			# split
			
			echo '<div id="top_sidebar_split" class="sidebars_split"><div class="hidden"></div></div>' . "\n";
			
			
			# left sidebar
			
			echo '<div id="sidebar" class="sidebar">' . "\n";
			
			echo '<div id="sidebar_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('left_sidebar');

			echo '<div id="sidebar_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- left sidebar -->' . "\n";

			
			# right sidebar
			
			echo '<div id="sidebar2" class="sidebar">' . "\n";
			
			echo '<div id="sidebar2_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('right_sidebar');

			echo '<div id="sidebar2_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- right sidebar -->' . "\n";
			
			
			# split
			
			echo '<div id="bottom_sidebar_split" class="sidebars_split"><div class="hidden"></div></div>' . "\n";
			
			
			# spacer

			echo '<div class="spacer"></div>' . "\n";
			
			
			# bottom sidebar
			
			echo '<div id="bottom_sidebar" class="sidebar wide_sidebar">' . "\n";

			echo '<div id="bottom_sidebar_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('bottom_sidebar');

			echo '<div id="bottom_sidebar_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- bottom sidebar -->' . "\n";

			
			# spacer

			echo '<div class="spacer"></div>' . "\n";
			
			
			# end sidebars wrapper

			echo '<div id="sidebars_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- sidebars -->' . "\n";

			
			# spacer
			
			echo '<div class="spacer"></div>' . "\n";

			break;


		case 'sms' :

			
			# left sidebar
			
			echo '<div id="sidebar" class="sidebar">' . "\n";

			echo '<div id="sidebar_top" class="sidebar_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('left_sidebar');

			echo '<div id="sidebar_bottom" class="sidebar_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- left sidebar -->' . "\n";

			
			# spacer
			
			echo '<div class="spacer"></div>' . "\n";

			
			# end sidebar wrapper
			
			echo '</div><!-- sidebar wrapper -->' . "\n";

			
			# right sidebar
			
			echo '<div id="sidebar2" class="sidebar">' . "\n";

			echo '<div id="sidebar2_top" class="sidebar_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('right_sidebar');

			echo '<div id="sidebar2_bottom" class="sidebar_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- right sidebar -->' . "\n";

			
			# spacer
			
			echo '<div class="spacer"></div>' . "\n";

			break;


		case 'mms' :
		case 'smm' :
		case 'ms' :
		case 'sm' :

			
			# left sidebar
			
			echo '<div id="sidebar" class="sidebar">' . "\n";

			echo '<div id="sidebar_top" class="sidebar_top"><div class="hidden"></div></div>' . "\n";

			sem_panels::display('left_sidebar');

			echo '<div id="sidebar_bottom" class="sidebar_bottom"><div class="hidden"></div></div>' . "\n";

			echo '</div><!-- left sidebar -->' . "\n";
			
			
			# spacer
			
			echo '<div class="spacer"></div>' . "\n";

			break;

		endswitch;

	
	# end body
	
	echo '</div>' . "\n";
	
	echo '</div>' . "\n";
	
	echo '<div id="body_bottom"><div class="hidden"></div></div>' . "\n";

	echo '</div><!-- body -->' . "\n";
	
	# footer
	
	if ( $active_layout != 'letter' ) :
		
		echo '<div id="footer_wrapper">' . "\n";
		
		sem_panels::display('the_footer');
		
		echo '</div>' . "\n";
		
	endif;

# end wrapper

echo '</div>' . "\n";

echo '<div id="wrapper_bottom"><div class="hidden"></div></div>' . "\n";

echo '</div><!-- wrapper -->' . "\n";


do_action('after_the_canvas');

do_action('wp_footer');
?>
</body>
</html>