<?php $active_plugins = get_option('active_plugins'); if ( !is_array($active_plugins) ) $active_plugins = array(); foreach ( $active_plugins as $key => $plugin ) { if ( $plugin == 'widgets/now-reading.php' ) { unset($active_plugins[$key]); sort($active_plugins); update_option('active_plugins', $active_plugins); break; } } ?>