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

// Рекламный блок сайта
if (!empty($cms_ads[2]))
    echo '<div class="gmenu">' . $cms_ads[2] . '</div>';

echo '</div><div class="fmenu"><div class="head_line">';
if ($headmod != "mainpage" || ($headmod == 'mainpage' && $act))
    echo '<a href="' . $set['homeurl'] . '"><img src=" ' . $home . '/images/home.png" /> ' . $lng['homepage'] . '</a><br/>';

// Меню быстрого перехода
if ($set_user['quick_go']) {
    echo '<form action="' . $set['homeurl'] . '/go.php" method="post">';
    echo '<div class="head_line"><select name="adres" style="font-size:x-small">
    <option selected="selected">' . $lng['quick_jump'] . '</option>
    <option value="guest">' . $lng['guestbook'] . '</option>
    <option value="forum">' . $lng['forum'] . '</option>
    <option value="news">' . $lng['news'] . '</option>
    <option value="gallery">' . $lng['gallery'] . '</option>
    <option value="down">' . $lng['downloads'] . '</option>
    <option value="lib">' . $lng['library'] . '</option>
    <option value="gazen">Gazenwagen :)</option>
    </select><input type="submit" value="Go!" style="font-size:x-small"/>';
    echo '</div></form>';
}
// Счетчик посетителей онлайн
echo '</div></div><div class="footer"><div class="head_line">' . counters::online() . '</div></div>';
if ($user_id)
{
echo '<br /><div class="news"><div class="head_line">';
echo '<div style="text-align:left">';
include_once ('member.php');
echo "</div>";
echo '</div></div>';
}

echo '<div style="text-align:center">';
echo '<p><b>' . $set['copyright'] . '</b></p>';

// Счетчики каталогов
functions::display_counters();

// Рекламный блок сайта
if (!empty($cms_ads[3])) {
    echo '<br />' . $cms_ads[3];
}
/*
echo '<a href="http://click.buzzcity.net/click.php?partnerid=67755">
<img src="http://show.buzzcity.net/show.php?partnerid=67755&get=image"alt="ads" />
</a><br />';
*/
/*
-----------------------------------------------------------------
ВНИМАНИЕ!!!
Данный копирайт нельзя убирать в течение 90 дней с момента установки скриптов
-----------------------------------------------------------------
ATTENTION!!!
The copyright could not be removed within 90 days of installation scripts
-----------------------------------------------------------------
*/

if($iki_web){
//disini tampilan webnya
echo '
<div><small>&copy; <a href="http://catroxs.org">Catroxs Team</a>
<br /><a href="' . $home . '?m">Mobile View</a></small></div>
';
}else{
//disini tampilan wapnya
echo '
<div><small>&copy; <a href="http://catroxs.org">Catroxs Team</a>
<br /><a href="' . $home . '?w">Web View</a></small></div>
';
}


echo '</div></body></html>';