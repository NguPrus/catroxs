<?php
/*
dev		agssbuzz@catroxs.org
site 		http://catroxs.org
*/

define('_IN_JOHNCMS', 1);
$headmod = 'journal';
require ('../incfiles/core.php');
$lng_forum = core::load_lng('forum');
$textl = 'Forum Notifikasi';
require ('../incfiles/head.php');

if (!$user_id) {
    echo 'Anda belum login';
    require ('../incfiles/end_utama.php');
    exit;
}

echo '<div class="phdr"><b>Forum Notifikasi</b></div>';

if($datauser['journal_forum']) {	echo '<div class="topmenu">Notifikasi Baru : ' . $datauser['journal_forum'] . '</div>';
	mysql_query("UPDATE `users` SET `journal_forum`='0' WHERE `id` = '$user_id'");
}

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `id_user`='$user_id'" .
    ($rights >= 7 ? "" : " AND `close` != '1'")), 0);
if ($total) {
    $req = mysql_query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
     	FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
      	WHERE `forum`.`type` = 'm' AND `forum`.`id_user`='$user_id'" . ($rights >=
        7 ? "" : " AND `forum`.`close` != '1'") . " ORDER BY `forum`.`id` DESC LIMIT $start, $kmess");
    while (($res = mysql_fetch_assoc($req)) !== false) {
    	if ($res['close'])
        	echo '<div class="rmenu">';
        else
    		echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        $theme = mysql_fetch_assoc(mysql_query("SELECT `text` FROM `forum` WHERE `id` = '" . $res['refid'] . "' ORDER BY `id` ASC LIMIT 1"));
        echo 'Nick / User <a href="profile.php?user=' . $res['user_id'] . '"><b>' . $res['from'] . '</b></a> telah menjawab postingan anda pada Thread <a href="../forum/index.php?id=' . $res['refid'] . '">' . $theme['text'] . '</a>' .
        ' <span class="gray">(' . date("d.m.Y / H:i", $res['time'] + $set_user['sdvig'] * 3600) . ')</span><div class="menu"><small>' .
		'<a href="../forum/index.php?act=post&amp;id=' . $res['id'] . '">Baca Jawaban</a> ' .
        '<a href="../forum/index.php?act=say&amp;id=' . $res['id'] . '"> ' . $lng_forum['reply_btn'] . '</a> ' .
        '<a href="../forum/index.php?act=say&amp;id=' . $res['id'] . '&amp;cyt"> ' . $lng_forum['cytate_btn'] . '</a> </small></div><div class="menu"><small>';
		$text = bbcode::notags(functions::checkout(mb_substr($res['text'], 0, 150), 1, 1));
        if(mb_strlen($res['text']) > 300)
        	$text .= ' <span style="color:green;">...</span>';
        if ($res['kedit'])
            $text .= '<br /><span class="gray"><small>' . $lng_forum['edited'] . ' <b>' . $res['edit'] . '</b> (' . date("d.m /H:i", $res['tedit'] + $set_user['sdvig'] * 3600) . ') <b>[' . $res['kedit'] . ']</b></small></span>';
    	echo $text;
		$file_req = mysql_query("SELECT * FROM `cms_forum_files` WHERE `post` = '" . $res['id'] . "'");
        if (mysql_num_rows($file_req) > 0) {
            $file_res = mysql_fetch_assoc($file_req);
            $file_ile_size = round(@filesize('../files/forum/attach/' . $file_res['filename']) / 1024, 2);
            echo '<br /><span class="gray">' . $lng_forum['attached_file'] . ':';
            $att_ext = strtolower(functions::format('./files/forum/attach/' . $file_res['filename']));
            $pic_ext = array('gif', 'jpg', 'jpeg', 'png');
            if (in_array($att_ext, $pic_ext))
                echo '<div><a href="../forum/index.php?act=file&amp;id=' . $file_res['id'] . '"><img src="../forum/thumbinal.php?file=' .
                (urlencode($file_res['filename'])) . '" alt="' . $lng_forum['click_to_view'] . '" /></a></div>';
            else
                echo '<br /><a href="../forum/index.php?act=file&amp;id=' . $file_res['id'] . '">' . $file_res['filename'] . '</a>';
            echo ' (' . $file_ile_size . ' кб.)<br/>' . $lng_forum['downloads'] . ': ' . $file_res['dlcount'] . ' ' . $lng_forum['time'] . '</span>';
        }
		echo '</small></div></div>';
        ++$i;
    }
} else
	echo '<div class="menu"><p>Masih kosong brot :D</p></div>';
echo '<div class="phdr">Всего: ' . $total . '</div>';
if ($total > $kmess) {
	echo '<p>' . functions::display_pagination('journal.php?', $start, $total, $kmess) . '</p>' .
	'<p><form action="journal.php?" method="post"><input type="text" name="page" size="2"/><input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
}


require ('../incfiles/end.php');
?>