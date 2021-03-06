<?php

/**
 * @package     JohnCMS
 * @link        http://johncms.com
 * @copyright   Copyright (C) 2008-2011 JohnCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://johncms.com/about
   @dev			agssbuzz@catroxs.org
				http://www.catroxs.org
 */

defined('_IN_JOHNCMS') or die('Error: restricted access');
/*
//Start PHP Firewall
$cpu = sys_getloadavg();
if($cpu[1] > 50) {
echo "Overloaded, Please try again later.");
exit;
}
*/

define('PHP_FIREWALL_REQUEST_URI', strip_tags( $_SERVER['REQUEST_URI'] ) );
define('PHP_FIREWALL_ACTIVATION', true );
if (file_exists($rootpath.'php-firewall/firewall.php')){
include_once($rootpath.'php-firewall/firewall.php' );
}

require_once ($rootpath . 'incfiles/ksantiddos.php');
$ksa = new ksantiddos();
// Substitute your mysql_login, mysql_pass and database name below:
$ksa->doit(10,20,'mysql_login','mysql_pass','database'); // allow 20 hits in 10 seconds
//End PHP Firewall


$headmod = isset($headmod) ? mysql_real_escape_string($headmod) : '';
$textl = isset($textl) ? $textl : $set['copyright'];

/*
-----------------------------------------------------------------
Keluaran judul halaman HTML, menghubungkan file CSS
-----------------------------------------------------------------
*/
if (stristr(core::$user_agent, "msie") && stristr(core::$user_agent, "windows")) {
    // Kami menyediakan keterangan untuk Internet Explorer
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header('Content-type: text/html; charset=UTF-8');
} else {
    // Kami memberikan judul untuk browser lain
    header("Cache-Control: public");
    header('Content-type: application/xhtml+xml; charset=UTF-8');
}
if($iki_web){
	//disini tampilan web
	header("Expires: " . date("r", time() + 60));
	echo'<?xml version="1.0" encoding="utf-8"?>' . "\n" .
		"\n" . '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">' .
		"\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">' .
		"\n" . '<head>' .
		"\n" . '<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>' .
		"\n" . '<meta http-equiv="Content-Style-Type" content="text/css" />' .
		"\n" . '<meta name="Generator" content="JohnCMS, http://johncms.com" />' . // ВНИМАНИЕ!!! Данный копирайт удалять нельзя
		(!empty($set['meta_key']) ? "\n" . '<meta name="keywords" content="' . $set['meta_key'] . '" />' : '') .
		(!empty($set['meta_desc']) ? "\n" . '<meta name="description" content="' . $set['meta_desc'] . '" />' : '') .
		"\n" . '<link rel="stylesheet" href="' . $set['homeurl'] . '/theme/web/style.css" type="text/css" />' .
		"\n" . '<link rel="shortcut icon" href="' . $set['homeurl'] . '/favicon.ico" />' .
		"\n" . '<link rel="alternate" type="application/rss+xml" title="RSS | ' . $lng['site_news'] . '" href="' . $set['homeurl'] . '/rss/rss.php" />' .
		"\n" . '<title>' . $textl . '</title>' .
		"\n" . '</head><body>' . core::display_core_errors();
}else{
	//disini tampilan wap
	header("Expires: " . date("r", time() + 60));
	echo'<?xml version="1.0" encoding="utf-8"?>' . "\n" .
		"\n" . '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">' .
		"\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">' .
		"\n" . '<head>' .
		"\n" . '<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>' .
		"\n" . '<meta http-equiv="Content-Style-Type" content="text/css" />' .
		"\n" . '<meta name="Generator" content="JohnCMS, http://johncms.com" />' . // ВНИМАНИЕ!!! Данный копирайт удалять нельзя
		(!empty($set['meta_key']) ? "\n" . '<meta name="keywords" content="' . $set['meta_key'] . '" />' : '') .
		(!empty($set['meta_desc']) ? "\n" . '<meta name="description" content="' . $set['meta_desc'] . '" />' : '') .
		"\n" . '<link rel="stylesheet" href="' . $set['homeurl'] . '/theme/wap/style.css" type="text/css" />' .
		"\n" . '<link rel="shortcut icon" href="' . $set['homeurl'] . '/favicon.ico" />' .
		"\n" . '<link rel="alternate" type="application/rss+xml" title="RSS | ' . $lng['site_news'] . '" href="' . $set['homeurl'] . '/rss/rss.php" />' .
		"\n" . '<title>' . $textl . '</title>' .
		"\n" . '</head><body>' . core::display_core_errors();
}

/*
-----------------------------------------------------------------
Modul Iklan
-----------------------------------------------------------------
*/
$cms_ads = array();
if (!isset($_GET['err']) && $act != '404' && $headmod != 'admin') {
    $view = $user_id ? 2 : 1;
    $layout = ($headmod == 'mainpage' && !$act) ? 1 : 2;
    $req = mysql_query("SELECT * FROM `cms_ads` WHERE `to` = '0' AND (`layout` = '$layout' or `layout` = '0') AND (`view` = '$view' or `view` = '0') ORDER BY  `mesto` ASC");
    if (mysql_num_rows($req)) {
        while (($res = mysql_fetch_assoc($req)) !== FALSE) {
            $name = explode("|", $res['name']);
            $name = htmlentities($name[mt_rand(0, (count($name) - 1))], ENT_QUOTES, 'UTF-8');
            if (!empty($res['color'])) $name = '<span style="color:#' . $res['color'] . '">' . $name . '</span>';
            // Jika Anda mau mengatur font, dibawah settingannya..
            $font = $res['bold'] ? 'font-weight: bold;' : FALSE;
            $font .= $res['italic'] ? ' font-style:italic;' : FALSE;
            $font .= $res['underline'] ? ' text-decoration:underline;' : FALSE;
            if ($font) $name = '<span style="' . $font . '">' . $name . '</span>';
            @$cms_ads[$res['type']] .= '<a href="' . ($res['show'] ? functions::checkout($res['link']) : $set['homeurl'] . '/go.php?id=' . $res['id']) . '">' . $name . '</a><br/>';
            if (($res['day'] != 0 && time() >= ($res['time'] + $res['day'] * 3600 * 24)) || ($res['count_link'] != 0 && $res['count'] >= $res['count_link']))
                mysql_query("UPDATE `cms_ads` SET `to` = '1'  WHERE `id` = '" . $res['id'] . "'");
        }
    }
}

/*
-----------------------------------------------------------------
Blok Iklan
-----------------------------------------------------------------
*/
if (isset($cms_ads[0])) echo $cms_ads[0];

/*
-----------------------------------------------------------------
Ucapan selamat kepada users
-----------------------------------------------------------------
*/
echo '<div class="meta-header"><div class="header"><div class="head_line"> ' . $lng['hi'] . ', ' . ($user_id ? '<b>' . $login . '</b>!' : $lng['guest'] . '!') . '</div></div>';

/*
-----------------------------------------------------------------
Menu utama users
-----------------------------------------------------------------
*/
echo '<div class="tmn"><div class="head_line">' .
    (isset($_GET['err']) || $headmod != "mainpage" || ($headmod == 'mainpage' && $act) ? '<a href=\'' . $set['homeurl'] . '\'>' . $lng['homepage'] . '</a> | ' : '') .
    ($user_id ? '<a href="' . $set['homeurl'] . '/users/profile.php?act=office">' . $lng['personal'] . '</a> | ' : '') .
    ($user_id ? '<a href="' . $set['homeurl'] . '/exit.php">' . $lng['exit'] . '</a>' : '<a href="' . $set['homeurl'] . '/login.php">' . $lng['login'] . '</a> | <a href="' . $set['homeurl'] . '/registration.php">' . $lng['registration'] . '</a>') .
    '</div></div></div><div class="maintxt">';

/*
-----------------------------------------------------------------
Peralihan bahasa dan logo situs
-----------------------------------------------------------------
*/
echo '<div class="top_head"><table style="width: 100%;"><tr>' .
    '<td valign="bottom"><a href="' . $set['homeurl'] . '"><img src="' . $set['homeurl'] . '/theme/web/images/logo.gif" alt=""/></a></td>' .
    ($headmod == 'mainpage' && count(core::$lng_list) > 1 ? '<td align="right"><a href="' . $set['homeurl'] . '/go.php?lng"><b>' . strtoupper(core::$lng_iso) . '</b></a>&#160;<img src="' . $set['homeurl'] . '/images/flags/' . core::$lng_iso . '.gif" alt=""/>&#160;</td>' : '') .
    '</tr></table></div>';
	
/*
-----------------------------------------------------------------
Blok iklan
-----------------------------------------------------------------
*/
if (!empty($cms_ads[1])) echo '<div class="gmenu">' . $cms_ads[1] . '</div>';

/*
-----------------------------------------------------------------
Scan Identitas Users
-----------------------------------------------------------------
*/
$sql = '';
$set_karma = unserialize($set['karma']);
if ($user_id) {
    // Memperbaiki info resmi users
    if (!$datauser['karma_off'] && $set_karma['on'] && $datauser['karma_time'] <= (time() - 86400)) {
        $sql .= " `karma_time` = '" . time() . "', ";
    }
    $movings = $datauser['movings'];
    if ($datauser['lastdate'] < (time() - 300)) {
        $movings = 0;
        $sql .= " `sestime` = '" . time() . "', ";
    }
    if ($datauser['place'] != $headmod) {
        ++$movings;
        $sql .= " `place` = '" . mysql_real_escape_string($headmod) . "', ";
    }
    if ($datauser['browser'] != $agn)
        $sql .= " `browser` = '" . mysql_real_escape_string($agn) . "', ";
    $totalonsite = $datauser['total_on_site'];
    if ($datauser['lastdate'] > (time() - 300))
        $totalonsite = $totalonsite + time() - $datauser['lastdate'];
    mysql_query("UPDATE `users` SET $sql
        `movings` = '$movings',
        `total_on_site` = '$totalonsite',
        `lastdate` = '" . time() . "'
        WHERE `id` = '$user_id'
    ");
} else {
    // Memperbaiki info resmi pengunjung
    $movings = 0;
    $session = md5(core::$ip . core::$ip_via_proxy . core::$user_agent);
    $req = mysql_query("SELECT * FROM `cms_sessions` WHERE `session_id` = '$session' LIMIT 1");
    if (mysql_num_rows($req)) {
        // Jika ada dalam database maka akan diperbaharui databasenya
        $res = mysql_fetch_assoc($req);
        $movings = ++$res['movings'];
        if ($res['sestime'] < (time() - 300)) {
            $movings = 1;
            $sql .= " `sestime` = '" . time() . "', ";
        }
        if ($res['place'] != $headmod) {
            $sql .= " `place` = '" . mysql_real_escape_string($headmod) . "', ";
        }
        mysql_query("UPDATE `cms_sessions` SET $sql
            `movings` = '$movings',
            `lastdate` = '" . time() . "'
            WHERE `session_id` = '$session'
        ");
    } else {
        // Jika belum berada di database, maka catatan akan ditambahkan
        mysql_query("INSERT INTO `cms_sessions` SET
            `session_id` = '" . $session . "',
            `ip` = '" . core::$ip . "',
            `ip_via_proxy` = '" . core::$ip_via_proxy . "',
            `browser` = '" . mysql_real_escape_string($agn) . "',
            `lastdate` = '" . time() . "',
            `sestime` = '" . time() . "',
            `place` = '" . mysql_real_escape_string($headmod) . "'
        ");
    }
}

/*
-----------------------------------------------------------------
Menampilkan pesan pada users yang di BAN
-----------------------------------------------------------------
*/
if (!empty($ban)) echo '<div class="alarm">' . $lng['ban'] . '&#160;<a href="' . $set['homeurl'] . '/users/profile.php?act=ban">' . $lng['in_detail'] . '</a></div>';

/*
-----------------------------------------------------------------
Link Yang belum dibaca
-----------------------------------------------------------------
*/
if ($user_id) {
    $list = array();
    $new_sys_mail = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='$user_id' AND `read`='0' AND `sys`='1' AND `delete`!='$user_id';"), 0);
	if ($new_sys_mail) $list[] = '<a href="' . $home . '/mail/index.php?act=systems">Система</a> (+' . $new_sys_mail . ')';
	$new_mail = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` LEFT JOIN `cms_contact` ON `cms_mail`.`user_id`=`cms_contact`.`from_id` AND `cms_contact`.`user_id`='$user_id' WHERE `cms_mail`.`from_id`='$user_id' AND `cms_mail`.`sys`='0' AND `cms_mail`.`read`='0' AND `cms_mail`.`delete`!='$user_id' AND `cms_contact`.`ban`!='1' AND `cms_mail`.`spam`='0'"), 0);
	if ($new_mail) $list[] = '<a href="' . $home . '/mail/index.php?act=new">' . $lng['mail'] . '</a> (+' . $new_mail . ')';
    if ($datauser['comm_count'] > $datauser['comm_old']) $list[] = '<a href="' . core::$system_set['homeurl'] . '/users/profile.php?act=guestbook&amp;user=' . $user_id . '">' . $lng['guestbook'] . '</a> (' . ($datauser['comm_count'] - $datauser['comm_old']) . ')';
    $new_album_comm = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_files` WHERE `user_id` = '" . core::$user_id . "' AND `unread_comments` = 1"), 0);
    if ($new_album_comm) $list[] = '<a href="' . core::$system_set['homeurl'] . '/users/album.php?act=top&amp;mod=my_new_comm">' . $lng['albums_comments'] . '</a>';
	//menampilkan notifikasi forum
	if ($datauser['journal_forum']) $list[] = '<a href="' . core::$system_set['homeurl'] . '/users/journal.php"> Forum</a>&#160;(' . $datauser['journal_forum'] . ')';
    if (!empty($list)) echo '<div class="rmenu">' . $lng['unread'] . ': ' . functions::display_menu($list, ', ') . '</div>';
}

/*
-----------------------------------------------------------------
Qchat
-----------------------------------------------------------------
*/
/*
if (($user_id) && !$ban['1'] && !$ban['12']){
$php_self=$_SERVER['PHP_SELF'];
*/
if ($user_id)
if ($headmod != "users" || ($headmod == 'users' && $act))
if ($headmod != "guestbook" || ($headmod == 'guestbook' && $act))
if ($headmod != "quickchat" || ($headmod == 'quickchat' && $act))
if ($headmod != "load" || ($headmod == 'load' && $act))
if ($headmod != "admin" || ($headmod == 'admin' && $act))
if ($headmod != "online" || ($headmod == 'online' && $act))
if ($headmod != "library" || ($headmod == 'library' && $act))
if ($headmod != "gallery" || ($headmod == 'gallery' && $act))
if ($headmod != "journal" || ($headmod == 'journal' && $act))

{
	echo '<div class="phdr"><span class="text"><b>Quick Chat</b></span></div>';
	echo '<form action="' . $home . '/users/qchat.php?act=say" method="post" id="date">';
	echo '<textarea name="msg"></textarea>';
	echo "<input type='submit' title='mengirim pesan' name='submit' value='Shout'/></form>"; 
	echo '';require_once ($rootpath . 'incfiles/func_quick.php');
	echo '<div class="fmenu"><center><a href="' . $home .'/index.php?id=' . $user['id'] .'&amp;start=' . $start . '&amp;ob=' . $refr . '">Refresh</a> | <a href="' . $home . '/pages/faq.php?act=smileys">Smileys</a> | <a href="' . $home . '/users/qchat.php">Shoutbox</a></center></div>';
}