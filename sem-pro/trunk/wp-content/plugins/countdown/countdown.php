<?php
/*
Plugin Name: Events Widget
Plugin URI: http://redalt.com/wiki/Countdown
Description: Adds template tags to count down to a specified date. Browse Manage / Events to configure your events.
Version: 2.3 fork
Author: Owen Winkler &amp; Denis de Bernardy
Author URI: http://www.getsemiologic.com
License: MIT License - http://www.opensource.org/licenses/mit-license.php
*/

/*
Countdown - Adds template tags to count down to a specified date

This code is licensed under the MIT License.
http://www.opensource.org/licenses/mit-license.php
Copyright (c) 2006 Owen Winkler

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Fork since v.1.2, by Denis de Bernardy <http://www.semiologic.com>

- Widget support
- default options
- Mu compat
- Security fixes
- Revamp of admin interface
*/


function dtr_monthtonum($m)
{
	switch(strtolower(substr($m, 0, 3)))
	{
	case 'jan': return 1;
	case 'feb': return 2;
	case 'mar': return 3;
	case 'apr': return 4;
	case 'may': return 5;
	case 'jun': return 6;
	case 'jul': return 7;
	case 'aug': return 8;
	case 'sep': return 9;
	case 'oct': return 10;
	case 'nov': return 11;
	case 'dec': return 12;
	}
	return 0;
}

function dtr_weekdaytonum($m)
{
	switch(strtolower(substr($m, 0, 3)))
	{
	case 'mon': return 1;
	case 'tue': return 2;
	case 'wed': return 3;
	case 'thu': return 4;
	case 'fri': return 5;
	case 'sat': return 6;
	case 'sun': return 0;
	}
	return 0;
}

function dtr_xsttonum($x)
{
	switch(substr($x, 0, 1))
	{
	case '1': return 1;
	case '2': return 2;
	case '3': return 3;
	case '4': return 4;
	case '5': return 5;
	case 'l': return 6;
	}
}

function dtr_xst_weekday($index, $weekday, $month)
{
	$now = getdate();
	$year = $now['year'] + (($month<$now['mon'])? 1 : 0);

	$day = 1;
	$firstday = intval(date('w', mktime(0,0,0,$month, $day, $year)));

	$day += $weekday - $firstday;
	if($day <= 0) $day += 7;
	$index --;
	while($index > 0)
	{
		$day += 7;
		$index --;
		if(!checkdate($month, $day + 7, $year)) break;
	}
	return mktime(0, 0, 0, $month, $day, $year);
}

function dates_to_remember($showonly = -1, $timefrom = null, $startswith = '<li>', $endswith = '</li>', $paststartswith = '<li class="pastevent">', $pastendswith = '</li>')
{
	$options = get_option('dtr_options');
	if(!is_array($options)) {
		$options['listformat'] = '<b>%date%</b> (%until%)<br />' . "\n" . '%event%';
		$options['dateformat'] = 'M j';
		$options['timeoffset'] = 0;
		update_option('dtr_options', $options);
	}

	$datefile = get_option('countdown_datefile');

	if ( !$datefile )
	{
		$datefile = implode('', file(dirname(__FILE__) . '/default-dates.txt'));

		update_option('countdown_datefile', $datefile);
	}

	#echo '<pre>';
	#var_dump($datefile, get_option('countdown_datefile'));
	#echo '</pre>';

	$dates = explode("\n", $datefile);
	$dtr = array();
	$dtrflags = array();

	if($timefrom == null) $timefrom = strtotime(date('Y-m-d', time() + ($options['timeoffset'] * 3600)));

	foreach($dates as $entry)
	{
		$entry = trim($entry);

		if ( $entry == ''
			|| strpos($entry, '#') === 0
			|| strpos($entry, '*') === 0
			)
		{
			continue;
		}
		
		$flags = array();
		if ( preg_match('/every ?(2nd|other|3rd|4th)? week (starting|from) ([0-9]{4}-[0-9]{2}-[0-9]{2})( until ([0-9]{4}-[0-9]{2}-[0-9]{2}))?[\\s]+(.*)/i',
			$entry, $matches)
			)
		{
			switch($matches[1])
			{
			case '2nd':
			case 'other': $inc = 14; break;
			case '3rd': $inc = 21; break;
			case '4th': $inc = 28; break;
			default: $inc = 7;
			}
			$date_info = getdate(strtotime($matches[3]));
			$absday = ceil($date_info[0] / 86400);
			$today_info = getdate(time() + ($options['timeoffset'] * 3600));
			$todayday = ceil($today_info[0] / 86400);
			if($absday == $todayday)
			{
				$eventtime = $absday * 86400;
			}
			else
			{
				$chunk = ceil(($todayday - $absday) / $inc);
				$absday = $absday + ($chunk * $inc);
				$eventtime = $absday * 86400;
			}
			if($matches[5] != '')
			{
				$limit = strtotime($matches[5]);
				if($timefrom - 86400 > $limit) $eventtime = $limit;
			}
			$eventname = $matches[6];
		}
		elseif ( preg_match('/easter[\\s]+(.*)/i', $entry, $matches)
			&& function_exists('easter_date')
			) {
			$eventtime = easter_date(intval(date('Y')));
			if($eventtime < time()) $eventtime = easter_date(intval(date('Y')) + 1);
			$eventname = $matches[1];
		}
		elseif ( preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})[\\s]+(?:through|thru)[\\s+]([0-9]{4}-[0-9]{2}-[0-9]{2})[\\s]+(.*)/i',
			$entry, $matches)
			)
		{
			$eventtime = strtotime($matches[1]);
			$eventname = $matches[3];
		}
		elseif ( preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})[\\s]+(.*)/i', $entry, $matches) )
		{
			$eventtime = strtotime($matches[1]);
			$eventname = $matches[2];
		}
		elseif ( preg_match('/([0-9]{2}-[0-9]{2})[\\s]+(.*)/i', $entry, $matches) )
		{
			$eventtime = strtotime(date('Y', time() + ($options['timeoffset'] * 3600)).'-'.$matches[1]);
			if($timefrom > $eventtime) $eventtime = strtotime(date('Y', time() + 31536000).'-'.$matches[1]);
			$eventname = $matches[2];
		}
		elseif ( preg_match('/(1st|2nd|3rd|4th|5th|last)[\\s]+(mon(?:day)?|tue(?:sday)?|wed(?:nesday)?|thu(?:rsday)?|fri(?:day)?|sat(?:urday)?|sun(?:day)?)[\\s]+(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?|all)(.*)/i',
			$entry, $matches)
			)
		{
			$eventname = $matches[4];
			$xst = dtr_xsttonum($matches[1]);
			$day = dtr_weekdaytonum($matches[2]);
			if($matches[3] == 'all')
			{
				$month = dtr_monthtonum(date('M', $timefrom));
				$eventtime = dtr_xst_weekday($xst, $day, $month);
				if($eventtime < $timefrom)
				{
					$zero_hour = getdate($timefrom);
					$month = dtr_monthtonum(date('M', mktime(0,0,0, ($zero_hour['mon'] % 12) + 1, 1, $_zero_hour['year'])));
					$eventtime = dtr_xst_weekday($xst, $day, $month);
				}
			}
			else
			{
				$month = dtr_monthtonum($matches[3]);
				$eventtime = dtr_xst_weekday($xst, $day, $month);
			}
		}
		else
		{
			continue;
		}

		if ( preg_match('/^the[\\s]+(mon(?:day)?|tue(?:sday)?|wed(?:nesday)?|thu(?:rsday)?|fri(?:day)?|sat(?:urday)?|sun(?:day)?)[\\s]+(before|after)/i',
			$entry, $matches)
			) {
			switch($matches[2]) {
				case 'before': $direction = 'last'; break;
				case 'after': $direction = 'next'; break;
			}
			$eventtime = strtotime("{$direction} {$matches[1]}", $eventtime);
		}
		
		if ( preg_match('/^([0-9]+)[\\s]+(days?|weeks?|months?)[\\s]+(before|after)/i',
			$entry, $matches)
			) {
			switch($matches[3]) {
				case 'before': $direction = '-'; break;
				case 'after': $direction = '+'; break;
			}
			$amount = intval($matches[1]);
			switch($matches[2]) {
			case 'week':
			case 'weeks':
				$unit = "weeks";
				break;
			case 'day':
			case 'days':
				$unit = "days";
				break;
			case 'month':
			case 'months':
				$unit = "months";
				break;
			}
			$eventtime = strtotime("{$direction}{$amount} {$unit}", $eventtime);
		}

		$flags = array();
		$eventname = preg_replace('/%(.*?)%/e', '($flags[]="\\1")?"":""', $eventname);

		if($timefrom <= $eventtime)
		{
			while(isset($dtr[$eventtime]) && $dtr[$eventtime] != $eventname) $eventtime ++;
			$dtr[$eventtime] = $eventname;
			$dtrflags[$eventtime] = $flags;
		}
	}
	ksort($dtr);

	foreach($dtr as $eventtime => $event)
	{
		$do_daystil = !in_array('nocountdown', $dtrflags[$eventtime]);
		countdown_days($event, date('Y-m-d', $eventtime), $startswith, $endswith, $paststartswith, $pastendswith, $do_daystil);
		$showonly --;
		if($showonly == 0) break;
	}
}

function countdown_days($event, $date, $startswith = '', $endswith = '', $paststartswith = '', $pastendswith = '', $do_daystil = true) {
	$options = get_option('dtr_options');

	$until = intval((strtotime($date) - strtotime(date('Y-m-d', time() + ($options['timeoffset'] * 3600)))) / 86400);
	$remaining = '';
	if($until >= 0) {
 		echo $startswith;
		$date_format = $options['dateformat'];
		$date_str = date($date_format, strtotime($date));
		if($do_daystil)
		{
			switch($until)
			{
			case 0: $remaining = 'Today'; break;
			case 1: $remaining = '1 day'; break;
			default: $remaining = "{$until} days"; break;
			}
		}
		echo str_replace(array('%date%', '%event%', '%until%', '%untilnum%'), array($date_str, $event, $remaining, $until), $options['listformat']);
		echo $endswith;
	}
	else
	{
 		echo $paststartswith;
		$date_format = $options['dateformat'];
		$date_str = date($date_format, strtotime($date));
		if($do_daystil)
		{
			switch(abs($until))
			{
			case 1: $remaining = '1 day ago'; break;
			default: $remaining = "{$until} days ago"; break;
			}
		}
		echo str_replace(array('%date%', '%event%', '%until%', '%untilnum%'), array($date_str, $event, $remaining, $until), $options['listformat']);
		echo $pastendswith;
	}
}


function countdown_widget($args)
{
	extract($args);
	$options = get_option('countdown_widget');


	$options['number'] = $options['number'] ? $options['number'] : 5;

	echo $before_widget
		. $before_title
		. ( ( isset($options['title']) )
			? $options['title']
			: __('Upcoming Events')
			)
		. $after_title
		. '<ul>';

		dates_to_remember($options['number']);
	echo '</ul>'
		. $after_widget;
}

function countdown_widget_control()
{
	$options = get_option('countdown_widget');
	
	if ( $options === false )
	{
		$options = array(
			'title' => 'Upcoming Events',
			'number' => 5
			);
		
		update_option('countdown_widget', $options);
	}

	if ( $_POST["countdown_widget_update"] )
	{
		$new_options = $options;

		$new_options['title'] = strip_tags(stripslashes($_POST["countdown_widget_title"]));
		$new_options['number'] = intval($_POST["countdown_widget_number"]);

		if ( $options != $new_options )
		{
			$options = $new_options;

			update_option('countdown_widget', $options);
		}
	}

	$title = attribute_escape($options['title']);
	$number = $options['number'] ? $options['number'] : '';

	echo '<input type="hidden" name="countdown_widget_update" value="1" />';

	echo '<div style="margin-bottom: 6px;">'
		. '<label>'
		. 'Title' . '<br />'
		. '<input type="text" size="45"'
		. ' name="countdown_widget_title"'
		. ' value="' . $title . '"'
		. ' />'
		. '</label>'
		. '</div>';
	
	echo '<div style="margin-bottom: 6px;">'
		. '<label>'
		. 'Number of events' . '<br />'
		. '<input type="text" size="45"'
		. ' name="countdown_widget_number"'
		. ' value="' . $number . '"'
		. ' />'
		. '</label>'
		. '</div>';
}


function countdown_widget_init()
{
	$widget_options = array('classname' => 'countdown', 'description' => __( "Displays upcoming events") );
	$control_options = array('width' => 460);
	
	wp_register_sidebar_widget('countdown', 'Events Widget', 'countdown_widget', $widget_options );
	wp_register_widget_control('countdown', 'Events Widget', 'countdown_widget_control', $control_options );
}

add_action('widgets_init', 'countdown_widget_init');


if ( is_admin() )
{
	include dirname(__FILE__) . '/countdown-admin.php';
	include dirname(__FILE__) . '/countdown-manage.php';
}
?>