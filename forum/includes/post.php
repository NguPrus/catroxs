<?php

/**
* @package     JohnCMS
* @link        http://johncms.com
* @copyright   Copyright (C) 2008-2011 JohnCMS Community
* @license     LICENSE.txt (see attached file)
* @version     VERSION.txt (see attached file)
* @author      http://johncms.com/about
* @dev 			agssbuzz@catroxs.org
*/

defined('_IN_JOHNCMS') or die('Error: restricted access');

require('../incfiles/head.php');
if (empty($_GET['id'])) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}

// pesan permintaan
$req = mysql_query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
WHERE `forum`.`type` = 'm' AND `forum`.`id` = '$id'" . ($rights >= 7 ? "" : " AND `forum`.`close` != '1'") . " LIMIT 1");
$res = mysql_fetch_array($req);

// Query Alat
$them = mysql_fetch_array(mysql_query("SELECT * FROM `forum` WHERE `type` = 't' AND `id` = '" . $res['refid'] . "'"));
echo '<div class="phdr"><b>' . $lng_forum['topic'] . ':</b> ' . $them['text'] . '</div><div class="list1">';
echo '<table width="100%" cellpadding="0" cellspacing="0" class="phdr"><tr>' .
'<td width="auto"><img src="' . $home . '/images/file.png"> </img> (' . functions::display_date($res['time']) . ')</td>' .
'</tr></table><div class="newsx">';
if ($set_user['avatar']) {
echo '<table width="100%"><tr><td width="40px" align="left" valign="top">';
if (file_exists(('../files/users/avatar/' . $res['user_id'] . '.png')))
    echo '<img src="../files/users/avatar/' . $res['user_id'] . '.png" width="32" height="32" alt="' . $res['from'] . '" />&#160;';
else
    echo '<img src="../images/empty.png" width="32" height="32" alt="' . $res['from'] . '" />&#160;';
echo '</td>';
}
// jenis kelamin
if ($res['sex'])
    echo '<img src="../theme/' . $set_user['skin'] . '/images/' . ($res['sex'] == 'm' ? 'm' : 'w') . '.png" alt=""  width="16" height="16"/>&#160;';
else
    echo '<img src="../images/del.png" width="12" height="12" />&#160;';
// Nick nick dan link ke profilnya
if ($user_id && $user_id != $res['user_id']) {
    echo '<a href="../users/profile.php?user=' . $res['user_id'] . '&amp;fid=' . $res['id'] . '"><b>' . $res['from'] . '</b></a> ';
	
} else {
    echo '<b>' . $res['from'] . '</b>';
}
// label Posisi
switch ($res['rights']) {
    case 7:
        echo " Adm ";
        break;

    case 6:
        echo " Smd ";
        break;

    case 3:
        echo " Mod ";
        break;

    case 1:
        echo " Kil ";
        break;
}
// Label Online / Offline
echo (time() > $res['lastdate'] + 300 ? '<span class="red"> [Off]</span>' : '<span class="green"> [ON]</span>');
echo '</tr>';
echo '</table>';
echo '</div>';
echo '<div class="textx">';
// pengguna Status
if (!empty($res['status']))
    echo '<div class="status"><img src="../images/star.gif" alt=""/>&#160;' . $res['status'] . '</div>';
$text = htmlentities($res['text'], ENT_QUOTES, 'UTF-8');
$text = nl2br($text);
$text = bbcode::tags($text);
if ($set_user['smileys'])
    $text = functions::smileys($text, ($res['rights'] >= 1) ? 1 : 0);
echo $text . '</div></div>';
// Quote dan Reply post
					echo '<hr />';
					echo '<table class="forumb" width="100%">';
					echo '<td align="left" valign="top">';
					if ($res['ip_via_proxy']) {
                                echo 'IP 1 : ' . long2ip($res['ip']) . '<br /> ' .
                                     'IP 2 : ' . long2ip($res['ip_via_proxy']) .
									 '<br />UA : ' . $res['soft'] . '';
                            } else {
                                echo 'IP : ' . long2ip($res['ip']) . '<br />UA : ' . $res['soft'] . '';
                            }
					echo '</td>';
					if ($user_id && $user_id != $res['user_id']) {
					echo '<td align="right" valign="top">';
                    echo '<a href="index.php?act=say&amp;id=' . $res['id'] . '&amp;start=' . $start . '&amp;cyt">Quote</a>' ;
					echo '</td>';							 
                    }
					echo '</table>';
// Hitung halaman berapa?
$page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '$id'"), 0) / $kmess);
echo '<div class="phdr"><a href="index.php?id=' . $res['refid'] . '&amp;page=' . $page . '">' . $lng_forum['back_to_topic'] . '</a></div>';
echo '<p><a href="index.php">' . $lng['to_forum'] . '</a></p>';