<?php

/**
 * @package     JohnCMS
 * @link        http://johncms.com
 * @copyright   Copyright (C) 2008-2011 JohnCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://johncms.com/about
 */

defined('_IN_JOHNCMS') or die('Error: restricted access');

$mp = new mainpage();

/*
-----------------------------------------------------------------
Блок информации
-----------------------------------------------------------------
*/
echo '<div class="phdr"><b>News Update</b></div>';
echo $mp->news;
echo '<div class="menu"><a href="news/index.php">' . $lng['news_archive'] . '</a> (' . $mp->newscount . ')</div>';

/*
-----------------------------------------------------------------
Блок общения
-----------------------------------------------------------------
*/
echo '<div class="phdr"><b>' . $lng['dialogue'] . '</b></div>';
// Ссылка на гостевую
echo '<div class="menu"><table width="100%"><tr><td width="40px"><img src="images/forum.png" width="30" height="30"/></td><td>';
if ($set['mod_guest'] || $rights >= 7)
    echo '&rsaquo;&nbsp;<a href="guestbook/index.php">' . $lng['guestbook'] . '</a> (' . counters::guestbook() . ')';	
// Mod forum
if ($set['mod_forum'] || $rights >= 7)
	if ($user_id || $set['active']) {
	echo '<p>&rsaquo;&nbsp;<a href="forum/">' . $lng['forum'] . '</a> (' . counters::forum() . ')</p>';
	} else {
	echo '<p>&rsaquo;&nbsp;' . $lng['forum'] . ' (' . counters::forum() . ')</p>';
	}
	echo '</td></tr></table></div>';
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//menampilkan last post
		if ($user_id || $set['active']) {
		require 'last_aktif.php';
		} else {
		require 'las_pasif.php';
		}
	require_once ($rootpath . 'pages/blogs.php');
		


/*
-----------------------------------------------------------------
Блок полезного
-----------------------------------------------------------------
*/    
// Ссылка на библиотеку
if ($user_id || $set['active']) {
    echo '<div class="phdr"><b>' . $lng['community'] . '</b></div>' .
        '<div class="menu"><a href="users/index.php">' . $lng['users'] . '</a> (' . counters::users() . ')</div>' .
        '<div class="menu"><a href="users/album.php">' . $lng['photo_albums'] . '</a> (' . counters::album() . ')</div>';
}
?>