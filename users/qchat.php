<?php

define('_IN_JOHNCMS', 1);

$headmod = 'quickchat';
require_once ("../incfiles/core.php");
$lng_profile = core::load_lng('profile');
$textl = $lng_mod['shout'];
require_once ("../incfiles/head.php");
echo '<div class="fmenu"><center><a href="qchat.php?id=' . $user['id'] .'&amp;start=' . $start . '&amp;ob=' . $refr . '">Refresh</a> | <a href="/pages/faq.php?act=smileys">Smileys</a> | <a href="/users/qchat.php">Shoutbox</a></center></div>';



$act = isset($_GET['act']) ? $_GET['act'] : '';
switch ($act)
{
case "del":
if ($rights>=7)
{
if (empty($_GET['id']))
{
echo "<div class='rmenu'>ERROR !!<br />&raquo; <a href='qchat.php?'>" . $lng['back'] . "</a></div>";
require_once ("../incfiles/end.php");
exit;
}
$id = intval($_GET['id']);
if (isset($_GET['yes']))
{
mysql_query("DELETE FROM `qchat` WHERE `id`='" . $id . "' LIMIT 1;");
header("Location: qchat.php");
} else
{
echo '<div class="rmenu"><p>' . $lng['delete_confirmation'] . '<br />';
echo "<a href='qchat.php?act=del&amp;id=" . $id . "&amp;yes'>" . $lng['delete'] . "</a> | <a href='qchat.php'>" . $lng['cancel'] . "</a></p></div>";
}
} else
{
echo "<div class='rmenu'>" . $lng['access_guest_forbidden'] . " !!!</div>";
}
break;

case "trans":
include ("../pages/trans.$ras_pages");
echo '&gt;<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . $lng['back'] . '</a><br />';
break;

case "say":
if (empty($user_id) && empty($_POST['name']))
{
echo "<div class='rmenu'>ERROR !!<br />" . $lng['access_forbidden'] . "<br />&laquo; <a href='qchat.php?'>" . $lng['back'] . "</a></div>";
require_once ("../incfiles/end.php");
exit;
}

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=NULL){

$balek=$_SERVER['HTTP_REFERER'];

} elseif (ereg("&pass=", $_SERVER['HTTP_REFERER'])){

$balek='/index.php';

} else {

$balek='qchat.php';

}


if (empty($user_id))
{
echo "<div class='rmenu'>ERROR!!<br />&laquo; <a href='qchat.php?'>" . $lng['back'] . "</a></div>";
require_once ("../incfiles/end.php");
exit;
}
if (isset($_POST['submit']))
{
if (empty($_POST['msg']))
{
echo "<div class='rmenu'><b>ERROR !!</b><br />" . $lng['error_empty_message'] . "<br />&laquo; <a href='$balek'>" . $lng['back'] . "</a></div>";
require_once ("../incfiles/end.php");
exit;
}
// anti spam, cek pada frekuensi menambahkan pesan

$old = ($rights > 1) ? 10 : 30;
$spam = $lastpost > (time() - $old) ? 1 : false;
if ($spam)
{
echo "<div class='rmenu'><b>ERROR !!</b><br />" . $lng['error_flood'] . " $old " . $lng['seconds'] . "<br />&laquo; <a href='$balek'>" . $lng['back'] . "</a></div>";
require_once ("../incfiles/end.php");
exit;
}
$msg = trim($_POST['msg']);
$msg = mb_substr($msg, 0, 600);
if ($_POST['msgtrans'] == 1)
{
$msg = trans($msg);
}
// masukan pesan ke dalam database
mysql_query("INSERT INTO `qchat` SET
`time`='" . time() . "',
`user_id`='" . $user_id . "',
`text`='" . mysql_real_escape_string($msg) . "';");
// memperbaiki posting terakhir (antispam)

mysql_query("UPDATE `users` SET `lastpost` = '" . time() . "' WHERE `id` = '" . $user_id . "'");
header("location: $balek");
}
else
{
echo '<form action="qchat.php?act=say" method="post">';
echo 'Tek pesan (max. 100):<br /><textarea cols="21" rows="4" name="msg"></textarea><br />';
if ($offtr != 1)
{
echo "<input type='checkbox' name='msgtrans' value='1' /> Translate<br />";
}
echo "<input type='submit' title='Klik posting untuk memposting' name='submit' value='" . $lng['sent'] . "'/><br /></form>";
echo "&gt;<a href='$balek'>" . $lng['back'] . "</a><br />";
}
break;

case 'clean':
if ($rights>=8)
{
if (isset($_POST['submit']))
{
$cl = isset($_POST['cl']) ? intval($_POST['cl']) : '';
switch ($cl)
{
case '1':
// bersihkan pesan yang sudah lebih dari 1 hari

mysql_query("DELETE FROM `qchat` WHERE `time`<='" . (time() - 86400) . "';");

mysql_query("OPTIMIZE TABLE `qchat`;");
echo 'bersihkan pesan yang sudah lebih dari 1 hari<br />&gt;<a href="qchat.php">Kembali</a><br />';
break;

case '2':
// melakukan pembersihan lengkap
mysql_query("DELETE FROM `qchat`;");
mysql_query("OPTIMIZE TABLE `qchat`;");
echo 'Bersihkan semua pesan.<br />&gt;<a href="qchat.php">Kembali</a><br />';
break;

default:
// membersihkan pesan yang udah lebih dari 1 minggu
mysql_query("DELETE FROM `qchat` WHERE `time`<='" . (time() - 604800) . "';");
mysql_query("OPTIMIZE TABLE `qchat`;");
echo 'Bersihkan pesan yang udah lebih dari 1 minggu.<br />&gt;<a href="qchat.php">Ke daftar</a><br />';
}
} else
{
echo '<b>membersihkan  komunikasi</b><br />';
echo 'Anda yakin akan Bersihkan pesan ?';
echo '<form id="clean" method="post" action="qchat.php?act=clean">';
echo '<input type="radio" name="cl" value="0" checked="checked" /> lebih lama dari 1 minggu<br />';
echo '<input type="radio" name="cl" value="1" /> lebih lama dari 1 hari<br />';
echo '<input type="radio" name="cl" value="2" /> Semua pesan<br />';
echo '<input type="submit" name="submit" value="Bersihkan" />';
echo '<br /></form>';
echo '<a href="qchat.php">Batal</a><br />';
}
} else
{
header("location: qchat.php");
}
break;

default:
////////////////////////////////////////////////////////////
// display Quick chat                                 //
////////////////////////////////////////////////////////////
// bentuk input pesan baru
if ($user_id || $set['gb'] != 0)
{
echo '<div class="phdr"><a name="up" id="up"></a><a href="#down"><img src="../theme/default/images/down.png" alt="down" /></a>&nbsp;&nbsp;&nbsp;<b>' . $lng_mod['shout'] . '</b></div>';
 echo '<div class="b"><b>' . $lng['message'] . '</b> <small>(max 100)</small><br />';
echo bbcode::auto_bb('form', 'msg');
echo '<form name="form" action="qchat.php?act=say" method="post">';
echo '<textarea name="msg" width="50%"></textarea><br /><br />';
if ($offtr != 1)
echo "<input type='submit' title='mengirim pesan' name='submit' value='" . $lng['sent'] . "'/></form></div>";
}
$req = mysql_query("SELECT COUNT(*) FROM `qchat`");
$colmes = mysql_result($req, 0); // jumlah pesan
echo '<div class="topmenu">' . functions::display_pagination('qchat.php?', $start, $colmes, $kmess) . '</div>';
if ($colmes > 0)
{
$req = mysql_query("SELECT `qchat`.*, `users`.`name`, `users`.`rights`, `users`.`lastdate`, `users`.`sex`, `users`.`status`, `users`.`datereg`, `users`.`ip` , `users`.`browser`   FROM `qchat` LEFT JOIN `users` ON `qchat`.`user_id` = `users`.`id` ORDER BY `time` DESC LIMIT " . $start . "," . $kmess . ";");

while ($res = mysql_fetch_array($req))
{
echo ceil(ceil($i / 2) - ($i / 2)) == 0 ? '<div class="list1">' : '<div class="list2">';

// icon seks
global $set_user, $realtime, $user_id, $admp, $home;
if ($set_user['avatar']) {
echo '<div class="newx"><table width="100%"><tr><td width="40px" align="left" valign="top">';
if (file_exists(('../files/users/avatar/' . $res['user_id'] . '.png')))
echo '<img src="../files/users/avatar/' . $res['user_id'] . '.png" width="32" height="32" alt="' . $user['name'] . '" /> ';
else
echo '<img src="../images/empty.png" width="32" height="32" alt="' . $user['name'] . '" /> ';
}
echo '</td><td>';
// icon nick baru
if (!empty($user_id) && ($user_id != $res['user_id']))
{
echo '<img src="' . $set['homeurl'] . '/theme/default/images/m.png" alt="" />';
echo $res['datereg'] > time() - 86400 ? '+' : '';
echo '<a href="' . $set['homeurl'] . '/users/profile.php?user=' . $res['user_id'] . '"><b>' . $res['name'] . '</b></a> ';
} else
{
echo '<b>' . $res['name'] . '</b>';
}
// jabatan
switch ($res['user_id'])
{
case 1:
echo ' [SV] ';
break;
}
// Online / Offline
$ontime = $res['lastdate'] + 600;
if (time() > $ontime)
{
echo '<span style="color: red"> <img src="../images/off.png" alt="[OFF]"></img></span><br/>';
} else
{
echo '<span style="color: green"> <img src="../images/on.png" alt="[ON]"></img></span><br />';
}
$vrp = $res['time'] + $sdvig * 3600;
$vr = date("d.m.y / H:i", $vrp);
echo ' <span style="color: #999999">(' . $vr . ')</span><br />';
echo '</td></tr></table></div>';
if ($res['user_id']) {
                    // A??peAc??o??? ????e?cc?? ?c???
                    $text = functions::checkout($res['text'], 1, 1);
                    if ($set_user['smileys'])
                        $text = functions::smileys($text, $res['rights'] >= 1 ? 1 : 0);
                } else {
                    // A?Ac??o@a@??e???????ye?cc??
                    $res['name'] = functions::checkout($res['name']);
                    $text = functions::antilink(functions::checkout($res['text'], 0, 2));
                }

// Tampilkan text posting
echo '<div class="textx">' . $text . '</div>';
echo '<font color="lime">';
//echo functions::update_time($res['time']);
echo '</font>';

// link ke pungsi moderskie
if ($rights>=7)
{
echo '<div class="maintxt"><a href="qchat.php?act=del&amp;id=' . $res['id'] . '">' . $lng['delete'] . '</a></div>';
}
echo "</div>";
++$i;
}
echo "<div class='phdr'><a name='down' id='down'></a><a href='#up'><img src='../theme/default/images/up.png' alt='up' /></a>&nbsp;&nbsp;&nbsp;Total : $colmes</div>";
if ($colmes > $kmess)
{
echo '<div class="topmenu">' . functions::display_pagination('qchat.php?', $start, $colmes, $kmess) . '</div>';
echo '<form action="qchat.php" method="get"><input type="text" name="page" size="2"/><input type="submit" value="' . $lng['to_page'] . '"/><br /></form>';
}
// untuk admin menyediakan link untuk pembersihan
if ($rights>=8)

echo '<a href="qchat.php?act=clean">' . $lng['clear_all'] . '</a><br />';
} else
{
echo '<div class="gmenu">' . $lng['guestbook_empty'] . ' </div>';
}
break;
}

require_once ("../incfiles/end.php");

?>
