<?php

/**
 * @package     JohnCMS
 * @link        http://johncms.com
 * @copyright   Copyright (C) 2008-2011 JohnCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://johncms.com/about
 * @dev			agssbuzz@catroxs.org
				http://www.catroxs.org
 */

define('_IN_JOHNCMS', 1);

require('../incfiles/core.php');
$lng_forum = core::load_lng('forum');
if (isset($_SESSION['ref']))
    unset($_SESSION['ref']);

/*
-----------------------------------------------------------------
Настройки форума
-----------------------------------------------------------------
*/
$set_forum = $user_id && !empty($datauser['set_forum']) ? unserialize($datauser['set_forum']) : array(
    'farea' => 0,
    'upfp' => 0,
    'preview' => 1,
    'postclip' => 1,
    'postcut' => 2
);

/*
-----------------------------------------------------------------
Список расширений файлов, разрешенных к выгрузке
-----------------------------------------------------------------
*/
// Файлы архивов
$ext_arch = array(
    'zip',
    'rar',
    '7z',
    'tar',
    'gz',
    'apk'
);
// Звуковые файлы
$ext_audio = array(
    'mp3',
    'amr'
);
// Файлы документов и тексты
$ext_doc = array(
    'txt',
    'pdf',
    'doc',
    'rtf',
    'djvu',
    'xls'
);
// Файлы Java
$ext_java = array(
    'jar',
    'jad'
);
// Файлы картинок
$ext_pic = array(
    'jpg',
    'jpeg',
    'gif',
    'png',
    'bmp'
);
// Файлы SIS
$ext_sis = array(
    'sis',
    'sisx'
);
// Файлы видео
$ext_video = array(
    '3gp',
    'avi',
    'flv',
    'mpeg',
    'mp4'
);
// Файлы Windows
$ext_win = array(
    'exe',
    'msi'
);
// Другие типы файлов (что не перечислены выше)
$ext_other = array('wmf');

/*
-----------------------------------------------------------------
Ограничиваем доступ к Форуму
-----------------------------------------------------------------
*/
$error = '';
if (!$set['mod_forum'] && $rights < 7)
    $error = $lng_forum['forum_closed'];
elseif ($set['mod_forum'] == 1 && !$user_id)
    $error = $lng['access_guest_forbidden'];
if ($error) {
    require('../incfiles/head_shout.php');
    echo '<div class="rmenu"><p>' . $error . '</p></div>';
    require('../incfiles/end.php');
    exit;
}

$headmod = $id ? 'forum,' . $id : 'forum';

/*
-----------------------------------------------------------------
Заголовки страниц форума
-----------------------------------------------------------------
*/
if (empty($id)) {
    $textl = '' . $lng['forum'] . '';
} else {
    $req = mysql_query("SELECT `text`,`tiento` FROM `forum` WHERE `id`= '" . $id . "'");
    $res = mysql_fetch_assoc($req);
    $hdr = strtr($res['text'], array(
        '&quot;' => '',
        '&amp;' => '',
        '&lt;' => '',
        '&gt;' => '',
        '&#039;' => ''
    ));
    $hdr = mb_substr($hdr, 0, 30);
    $hdr = functions::checkout($hdr);
	$titlex = mb_strlen($res['text']) > 30 ? $hdr . '...' : $hdr;
	switch ($res['tiento']){
	case 1 :
	$prefix='[Discuss]';
	break;
	case 2 :
	$prefix='[Share]';
	break;
	case 3 :
	$prefix='[Info]';
	break;
	case 4 :
	$prefix='[Tutorial]';
	break;
	case 5 :
	$prefix='[Help]';
	break;
	case 6 :
	$prefix='[Ask]';
	break;
	case 7 :
	$prefix='[Request]';
	break;
	case 8 :
	$prefix='[Movie]';
	break;
	case 9 :
	$prefix='[Ongoing]';
	break;
	case 10 :
	$prefix='[Completed]';
	break;
	default:
	$prefix='';
	break;
	}
    $textl=$prefix.' '.$titlex;
}

/*
-----------------------------------------------------------------
Переключаем режимы работы
-----------------------------------------------------------------
*/
$mods = array(
    'addfile',
    'addvote',
    'close',
    'deltema',
    'delvote',
    'editpost',
    'editvote',
    'file',
    'files',
    'filter',
    'loadtem',
    'massdel',
    'new',
    'nt',
    'per',
    'post',
    'ren',
    'restore',
    'say',
    'tema',
    'users',
    'vip',
    'vote',
    'who',
    'curators'
);
if ($act && ($key = array_search($act, $mods)) !== false && file_exists('includes/' . $mods[$key] . '.php')) {
    require('includes/' . $mods[$key] . '.php');
} else {
    require('../incfiles/head_shout.php');

    /*
    -----------------------------------------------------------------
    Если форум закрыт, то для Админов выводим напоминание
    -----------------------------------------------------------------
    */
    if (!$set['mod_forum']) echo '<div class="alarm">' . $lng_forum['forum_closed'] . '</div>';
    elseif ($set['mod_forum'] == 3) echo '<div class="rmenu">' . $lng['read_only'] . '</div>';
    if (!$user_id) {
        if (isset($_GET['newup']))
            $_SESSION['uppost'] = 1;
        if (isset($_GET['newdown']))
            $_SESSION['uppost'] = 0;
    }
    if ($id) {
        /*
        -----------------------------------------------------------------
        Определяем тип запроса (каталог, или тема)
        -----------------------------------------------------------------
        */
        $type = mysql_query("SELECT * FROM `forum` WHERE `id`= '$id'");
        if (!mysql_num_rows($type)) {
            // Если темы не существует, показываем ошибку
            echo functions::display_error($lng_forum['error_topic_deleted'], '<a href="index.php">' . $lng['to_forum'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        $type1 = mysql_fetch_assoc($type);

        /*
        -----------------------------------------------------------------
        Фиксация факта прочтения Топика
        -----------------------------------------------------------------
        */
        if ($user_id && $type1['type'] == 't') {
            $req_r = mysql_query("SELECT * FROM `cms_forum_rdm` WHERE `topic_id` = '$id' AND `user_id` = '$user_id' LIMIT 1");
            if (mysql_num_rows($req_r)) {
                $res_r = mysql_fetch_assoc($req_r);
                if ($type1['time'] > $res_r['time'])
                    mysql_query("UPDATE `cms_forum_rdm` SET `time` = '" . time() . "' WHERE `topic_id` = '$id' AND `user_id` = '$user_id' LIMIT 1");
            } else {
                mysql_query("INSERT INTO `cms_forum_rdm` SET `topic_id` = '$id', `user_id` = '$user_id', `time` = '" . time() . "'");
            }
        }

        /*
        -----------------------------------------------------------------
        Mendapatkan struktur Forum
        -----------------------------------------------------------------
        */
        $res = true;
        $parent = $type1['refid'];
        while ($parent != '0' && $res != false) {
            $req = mysql_query("SELECT * FROM `forum` WHERE `id` = '$parent' LIMIT 1");
            $res = mysql_fetch_assoc($req);
            if ($res['type'] == 'f' || $res['type'] == 'r')
                // seo
				// $tree[] = '<a href="index.php?id=' . $parent . '">' . $res['text'] . '</a>';
				$tree[] = '<span typeof="v:Breadcrumb"><b><a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $parent . '.html" rel="v:url" property="v:title">' . $res['text'] . '</a></b></span>';
            $parent = $res['refid'];
        }
        $tree[] = '<span typeof="v:Breadcrumb"><a href="index.php" rel="v:url" property="v:title">' . $lng['forum'] . '</a></span>';
        krsort($tree);
        if ($type1['type'] != 't' && $type1['type'] != 'm')
            $tree[] = '<span typeof="v:Breadcrumb"><b property="v:title">' . $type1['text'] . '</b></span>';

        /*
        -----------------------------------------------------------------
        Счетчик файлов и ссылка на них
        -----------------------------------------------------------------
        */
        $sql = ($rights == 9) ? "" : " AND `del` != '1'";
        if ($type1['type'] == 'f') {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `cat` = '$id'" . $sql), 0);
            if ($count > 0)
                $filelink = '<a href="index.php?act=files&amp;c=' . $id . '">' . $lng_forum['files_category'] . '</a>';
        } elseif ($type1['type'] == 'r') {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `subcat` = '$id'" . $sql), 0);
            if ($count > 0)
                $filelink = '<a href="index.php?act=files&amp;s=' . $id . '">' . $lng_forum['files_section'] . '</a>';
        } elseif ($type1['type'] == 't') {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `topic` = '$id'" . $sql), 0);
            if ($count > 0)
                $filelink = '<a href="index.php?act=files&amp;t=' . $id . '">' . $lng_forum['files_topic'] . '</a>';
        }
        $filelink = isset($filelink) ? $filelink . '&#160;<span class="red">(' . $count . ')</span>' : false;

        /*
        -----------------------------------------------------------------
        Счетчик "Кто в теме?"
        -----------------------------------------------------------------
        */
        $wholink = false;
        if ($user_id && $type1['type'] == 't') {
            $online_u = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 300) . " AND `place` = 'forum,$id'"), 0);
            $online_g = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300) . " AND `place` = 'forum,$id'"), 0);
            $wholink = '<a href="index.php?act=who&amp;id=' . $id . '">' . $lng_forum['who_here'] . '?</a>&#160;<span class="red">(' . $online_u . '&#160;/&#160;' . $online_g . ')</span><br/>';
        }

        /*
        -----------------------------------------------------------------
        Выводим верхнюю панель навигации
        -----------------------------------------------------------------
        */
		echo '<p>' . counters::forum_new(1) . '</p>' .
			 '<div class="phdr" xmlns:v="http://rdf.data-vocabulary.org/#"><span typeof="v:Breadcrumb"><a href="'.$home.'" rel="v:url" property="v:title">Home</a></span> | ' . functions::display_menu($tree) . '</div>' .
			 '<div class="topmenu"><a href="search.php?id=' . $id . '">' . $lng['search'] . '</a>' . ($filelink ? ' | ' . $filelink : '') . ($wholink ? ' | ' . $wholink : '') . '</div>';

        /*
        -----------------------------------------------------------------
        Отрбражаем содержимое форума
        -----------------------------------------------------------------
        */
        switch ($type1['type']) {
            case 'f':
                /*
                -----------------------------------------------------------------
                Список разделов форума
                -----------------------------------------------------------------
                */
                $req = mysql_query("SELECT `id`, `text`, `soft` FROM `forum` WHERE `type`='r' AND `refid`='$id' ORDER BY `realid`");
                $total = mysql_num_rows($req);
                if ($total) {
                    $i = 0;
                    while (($res = mysql_fetch_assoc($req)) !== false) {
                        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                        $coltem = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `refid` = '" . $res['id'] . "'"), 0);
                        //seo
						//echo '<a href="?id=' . $res['id'] . '">' . $res['text'] . '</a>';
						echo '<b><a href="'.$home.'/forum/' . functions::seo($res['text']) . '_'.$res['id'].'.html">' . $res['text'] . '</a></b>';
                        if ($coltem)
                            echo " [$coltem]";
                        if (!empty($res['soft']))
                            echo '<div class="sub"><span class="gray">' . $res['soft'] . '</span></div>';
                        echo '</div>';
                        ++$i;
                    }
                    unset($_SESSION['fsort_id']);
                    unset($_SESSION['fsort_users']);
                } else {
                    echo '<div class="menu"><p>' . $lng_forum['section_list_empty'] . '</p></div>';
                }
                echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
                break;

            case 'r':
                /*
                -----------------------------------------------------------------
                Daftar topik / thread
                -----------------------------------------------------------------
                */
                $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `refid`='$id'" . ($rights >= 7 ? '' : " AND `close`!='1'")), 0);
                if (($user_id && !isset($ban['1']) && !isset($ban['11']) && $set['mod_forum'] != 3) || core::$user_rights) {
                    // Tombol membuat topik baru
                    echo '<div class="gmenu"><form action="index.php?act=nt&amp;id=' . $id . '" method="post"><input type="submit" value="' . $lng_forum['new_topic'] . '" /></form></div>';
                }
                if ($total) {
                    $req = mysql_query("SELECT * FROM `forum` WHERE `type`='t'" . ($rights >= 7 ? '' : " AND `close`!='1'") . " AND `refid`='$id' ORDER BY `vip` DESC, `time` DESC LIMIT $start, $kmess");
                    $i = 0;
                    while (($res = mysql_fetch_assoc($req)) !== false) {
                        if ($res['close'])
                            echo '<div class="rmenu">';
                        else
                            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                        $nikuser = mysql_query("SELECT `from` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "' ORDER BY `time` DESC LIMIT 1");
                        $nam = mysql_fetch_assoc($nikuser);
                        $colmes = mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='m' AND `refid`='" . $res['id'] . "'" . ($rights >= 7 ? '' : " AND `close` != '1'"));
                        $colmes1 = mysql_result($colmes, 0);
                        $cpg = ceil($colmes1 / $kmess);
                        $np = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_rdm` WHERE `time` >= '" . $res['time'] . "' AND `topic_id` = '" . $res['id'] . "' AND `user_id`='$user_id'"), 0);
                        // icon
                        $icons = array(
                            ($np ? (!$res['vip'] ? '<img src="../theme/' . $set_user['skin'] . '/images/op.gif" alt=""/>' : '') : '<img src="../theme/' . $set_user['skin'] . '/images/np.gif" alt=""/>'),
                            ($res['vip'] ? '<img src="../theme/' . $set_user['skin'] . '/images/pt.gif" alt=""/>' : ''),
                            ($res['realid'] ? '<img src="../theme/' . $set_user['skin'] . '/images/rate.gif" alt=""/>' : ''),
                            ($res['edit'] ? '<img src="../theme/' . $set_user['skin'] . '/images/tz.gif" alt=""/>' : '')
                        ); 
                       echo functions::display_menu($icons, '&#160;', '&#160;');
						switch ($res['tiento']){
						case 1 :
						echo ' <span style="color: #29aee7">[Discuss]</span>';
						break;
						case 2 :
						echo ' <span style="color: #29aee7">[Share]</span>';
						break;
						case 3 :
						echo ' <span style="color: #29aee7">[Info]</span>';
						break;
						case 4 :
						echo' <span style="color: #29aee7">[Tutorial]</span>';
						break;
						case 5 :
						echo' <span style="color: #29aee7">[Help]</span>';
						break;
						case 6 :
						echo' <span style="color: #29aee7">[Ask]</span>';
						break;
						case 7 :
						echo' <span style="color: #29aee7">[Request]</span>';
						break;
						case 8 :
						echo' <span style="color: #33FF00">[Movie]</span>';
						break;
						case 9 :
						echo' <span style="color: #FFFF00">[Ongoing]</span>';
						break;
						case 10 :
						echo' <span style="color: #FF0000">[Completed]</span>';
						break;
						default:
						break;
						}
					   //seo
                        //echo '<a href="index.php?id=' . $res['id'] . '">' . $res['text'] . '</a> [' . $colmes1 . ']';
                        echo '<b><a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $res['id'] . '.html">' . $res['text'] . '</a> [' . $colmes1 . '] </b>';
						if ($cpg > 1) {
                            //echo '<a href="index.php?id=' . $res['id'] . '&amp;page=' . $cpg . '">&#160;&gt;&gt;</a>';
							echo '<b><a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $res['id'] . '_p' . $cpg . '.html">&#160;&gt;&gt;</a></b>';
                        }
                        echo '<div class="sub">';
                        echo $res['from'];
                        if (!empty($nam['from'])) {
                            echo '&#160;/&#160;' . $nam['from'];
                        }
                        echo ' <span class="gray">(' . functions::display_date($res['time']) . ')</span></div></div>';
                        ++$i;
                    }
                    unset($_SESSION['fsort_id']);
                    unset($_SESSION['fsort_users']);
                } else {
                    echo '<div class="menu"><p>' . $lng_forum['topic_list_empty'] . '</p></div>';
                }
                echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
                if ($total > $kmess) {
                    echo '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&amp;', $start, $total, $kmess) . '</div>' .
                         '<p><form action="index.php?id=' . $id . '" method="post">' .
                         '<input type="text" name="page" size="2"/>' .
                         '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
                         '</form></p>';
                }
                break;

            case 't':
                /*
                -----------------------------------------------------------------
                Читаем топик
                -----------------------------------------------------------------
                */
                $filter = isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id ? 1 : 0;
                $sql = '';
                if ($filter && !empty($_SESSION['fsort_users'])) {
                    // Подготавливаем запрос на фильтрацию юзеров
                    $sw = 0;
                    $sql = ' AND (';
                    $fsort_users = unserialize($_SESSION['fsort_users']);
                    foreach ($fsort_users as $val) {
                        if ($sw)
                            $sql .= ' OR ';
                        $sortid = intval($val);
                        $sql .= "`forum`.`user_id` = '$sortid'";
                        $sw = 1;
                    }
                    $sql .= ')';
                }

                // Если тема помечена для удаления, разрешаем доступ только администрации
                if ($rights < 6 && $type1['close'] == 1) {
                    echo '<div class="rmenu"><p>' . $lng_forum['topic_deleted'] . '<br/><a href="?id=' . $type1['refid'] . '">' . $lng_forum['to_section'] . '</a></p></div>';
                    require('../incfiles/end.php');
                    exit;
                }

                // Счетчик постов темы
                $colmes = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='m'$sql AND `refid`='$id'" . ($rights >= 7 ? '' : " AND `close` != '1'")), 0);
                if ($start >= $colmes) {
                    // Исправляем запрос на несуществующую страницу
                    $start = max(0, $colmes - (($colmes % $kmess) == 0 ? $kmess : ($colmes % $kmess)));
                }

                // Cetak nama topik
               echo '<div class="phdr"><a name="up" id="up"></a><a href="#down"><img src="../theme/' . $set_user['skin'] . '/images/down.png" alt="" width="20px" height="10px" border="0"/></a>&#160;&#160;<b>';
				switch ($type1['tiento']){
						case 1 :
						echo ' <span style="color: #29aee7">[Discuss]</span>';
						break;
						case 2 :
						echo ' <span style="color: #29aee7">[Share]</span>';
						break;
						case 3 :
						echo ' <span style="color: #29aee7">[Info]</span>';
						break;
						case 4 :
						echo' <span style="color: #29aee7">[Tutorial]</span>';
						break;
						case 5 :
						echo' <span style="color: #29aee7">[Help]</span>';
						break;
						case 6 :
						echo' <span style="color: #29aee7">[Ask]</span>';
						break;
						case 7 :
						echo' <span style="color: #29aee7">[Request]</span>';
						break;
						case 8 :
						echo' <span style="color: #33FF00">[Movie]</span>';
						break;
						case 9 :
						echo' <span style="color: #FFFF00">[Ongoing]</span>';
						break;
						case 10 :
						echo' <span style="color: #FF0000">[Completed]</span>';
						break;
				default:
				break;
				}
				echo ' '.$type1['text'] . '</b></div>';
					if ($colmes > $kmess) {
                    echo '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&amp;', $start, $colmes, $kmess) . '</div>';
                }

                // Метка удаления темы
                if ($type1['close']) {
                    echo '<div class="rmenu">' . $lng_forum['topic_delete_who'] . ': <b>' . $type1['close_who'] . '</b></div>';
                } elseif (!empty($type1['close_who']) && $rights >= 7) {
                    echo '<div class="gmenu"><small>' . $lng_forum['topic_delete_whocancel'] . ': <b>' . $type1['close_who'] . '</b></small></div>';
                }

                //Setting VIP Forum
                if ($rights == 7 || $rights >= 9) {
				echo '<div class="mainblok"><div class="phdr">Post Required</div><div class="menu"><form method="post">
				<input type="text" value="'.$type1['min_post'].'" name="totalpost"/><br/>
				<input type="submit" name="minpost" value="Oke"/>
				</form></div></div>';
				}
					$req = mysql_query("SELECT COUNT(*) FROM `forum` WHERE `id` = '".($type1['id'])."' AND `type` = 't'");
				if(isset($_POST['minpost'])){
					if (mysql_result($req, 0) > 0) {
						mysql_query("UPDATE `forum` SET  `min_post` = '" . ($_POST['totalpost']) . "' WHERE `id` = '".($type1['id'])."'");
						header('Location: index.php?id='.($type1['id']).'');
					} else {
						require('../incfiles/head_shout.php');
						echo functions::display_error($lng['error_wrong_data']);
						require('../incfiles/end.php');
						exit;
					}
				}
				if(!$user_id){
				$post_vip=1;
				}else{
					$post_vip=$datauser['postforum'];
				}
				if ($rights == 3 || $rights >= 6 || $rights >= 7 || $rights >= 9){
				} else
				if ($ban['1'] || $ban['12'] || $ban['11'] || $post_vip < $type1['min_post']){
					echo '<div class="omenu">Min '.$type1['min_post'].' Total Post to Unlocked this Topic</div>';
					require_once("../incfiles/end.php");
					exit;
				}
				
                // Метка закрытия темы
                if ($type1['edit']) {
                    echo '<div class="rmenu">' . $lng_forum['topic_closed'] . '</div>';
                }

                /*
                -----------------------------------------------------------------
                Блок голосований
                -----------------------------------------------------------------
                */
                if ($type1['realid']) {
                    $clip_forum = isset($_GET['clip']) ? '&amp;clip' : '';
                    $vote_user = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `user`='$user_id' AND `topic`='$id'"), 0);
                    $topic_vote = mysql_fetch_assoc(mysql_query("SELECT `name`, `time`, `count` FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id' LIMIT 1"));
                    echo '<div  class="gmenu"><b>' . functions::checkout($topic_vote['name']) . '</b><br />';
                    $vote_result = mysql_query("SELECT `id`, `name`, `count` FROM `cms_forum_vote` WHERE `type`='2' AND `topic`='" . $id . "' ORDER BY `id` ASC");
                    if (!$type1['edit'] && !isset($_GET['vote_result']) && $user_id && $vote_user == 0) {
                        // Выводим форму с опросами
                        echo '<form action="index.php?act=vote&amp;id=' . $id . '" method="post">';
                        while (($vote = mysql_fetch_assoc($vote_result)) !== false) {
                            echo '<input type="radio" value="' . $vote['id'] . '" name="vote"/> ' . functions::checkout($vote['name'], 0, 1) . '<br />';
                        }
                        echo '<p><input type="submit" name="submit" value="' . $lng['vote'] . '"/><br /><a href="index.php?id=' . $id . '&amp;start=' . $start . '&amp;vote_result' . $clip_forum .
                             '">' . $lng_forum['results'] . '</a></p></form></div>';
                    } else {
                        // Выводим результаты голосования
                        echo '<small>';
                        while (($vote = mysql_fetch_assoc($vote_result)) !== false) {
                            $count_vote = $topic_vote['count'] ? round(100 / $topic_vote['count'] * $vote['count']) : 0;
                            echo functions::checkout($vote['name'], 0, 1) . ' [' . $vote['count'] . ']<br />';
                            echo '<img src="vote_img.php?img=' . $count_vote . '" alt="' . $lng_forum['rating'] . ': ' . $count_vote . '%" /><br />';
                        }
                        echo '</small></div><div class="bmenu">' . $lng_forum['total_votes'] . ': ';
                        if ($user_id && core::$user_data['rights'] > 6)
                            echo '<a href="index.php?act=users&amp;id=' . $id . '">' . $topic_vote['count'] . '</a>';
                        else
                            echo $topic_vote['count'];
                        echo '</div>';
                        if ($user_id && $vote_user == 0)
                            echo '<div class="bmenu"><a href="index.php?id=' . $id . '&amp;start=' . $start . $clip_forum . '">' . $lng['vote'] . '</a></div>';
                    }
                }
                $curators = !empty($type1['curators']) ? unserialize($type1['curators']) : array();
                $curator = false;
                if ($rights < 6 && $rights != 3 && $user_id) {
                    if (array_key_exists($user_id, $curators)) $curator = true;
                }
                /*
                -----------------------------------------------------------------
                Fiksasi pesan pertama pada topik
                -----------------------------------------------------------------
                */
                if (($set_forum['postclip'] == 2 && ($set_forum['upfp'] ? $start < (ceil($colmes - $kmess)) : $start > 0)) || isset($_GET['clip'])) {
                    $postreq = mysql_query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
                    FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
                    WHERE `forum`.`type` = 'm' AND `forum`.`refid` = '$id'" . ($rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "
                    ORDER BY `forum`.`id` LIMIT 1");
                    $postres = mysql_fetch_assoc($postreq);
                    echo '<div class="topmenu"><p>';
                    if ($postres['sex'])
                        echo '<img src="../theme/' . $set_user['skin'] . '/images/' . ($postres['sex'] == 'm' ? 'm' : 'w') . ($postres['datereg'] > time() - 86400 ? '_new.png" width="14"' : '.png" width="10"') . ' height="10"/>&#160;';
                    else
                        echo '<img src="../images/del.png" width="10" height="10" alt=""/>&#160;';
                    if ($user_id && $user_id != $postres['user_id']) {
                        echo '<a href="../users/profile.php?user=' . $postres['user_id'] . '&amp;fid=' . $postres['id'] . '"><b>' . $postres['from'] . '</b></a> ' .
                             '<a href="index.php?act=say&amp;id=' . $postres['id'] . '&amp;start=' . $start . '"> ' . $lng_forum['reply_btn'] . '</a> ' .
                             '<a href="index.php?act=say&amp;id=' . $postres['id'] . '&amp;start=' . $start . '&amp;cyt"> ' . $lng_forum['cytate_btn'] . '</a> ';
                    } else {
                        echo '<b>' . $postres['from'] . '</b> ';
                    }
                    $user_rights = array(
                        1 => 'Kil',
                        3 => 'Mod',
                        6 => 'Smd',
                        7 => 'Adm',
                        8 => 'SV'
                    );
                    echo @$user_rights[$postres['rights']];
                    echo (time() > $postres['lastdate'] + 300 ? '<span class="red"> [Off]</span>' : '<span class="green"> [ON]</span>');
                    echo ' <span class="gray">(' . functions::display_date($postres['time']) . ')</span><br/>';
                    if ($postres['close']) {
                        echo '<span class="red">' . $lng_forum['post_deleted'] . '</span><br/>';
                    }
                    echo functions::checkout(mb_substr($postres['text'], 0, 500), 0, 2);
                    if (mb_strlen($postres['text']) > 500)
                        echo '...<a href="index.php?act=post&amp;id=' . $postres['id'] . '">' . $lng_forum['read_all'] . '</a>';
                    echo '</p></div>';
                }
                if ($filter)
                    echo '<div class="rmenu">' . $lng_forum['filter_on'] . '</div>';
                // Задаем правила сортировки (новые внизу / вверху)
                if ($user_id)
                    $order = $set_forum['upfp'] ? 'DESC' : 'ASC';
                else
                    $order = ((empty($_SESSION['uppost'])) || ($_SESSION['uppost'] == 0)) ? 'ASC' : 'DESC';
                // Запрос в базу
                $req = mysql_query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`, `users`.`pangkat`, `users`.`postforum`
                FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
                WHERE `forum`.`type` = 'm' AND `forum`.`refid` = '$id'"
                                   . ($rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "$sql ORDER BY `forum`.`id` $order LIMIT $start, $kmess");
                // Верхнее поле "Написать"
                if (($user_id && !$type1['edit'] && $set_forum['upfp'] && $set['mod_forum'] != 3) || ($rights >= 7 && $set_forum['upfp'])) {
                    echo '<div class="gmenu"><form name="form1" action="index.php?act=say&amp;id=' . $id . '" method="post">';
                    if ($set_forum['farea']) {
                        $token = mt_rand(1000, 100000);
                        $_SESSION['token'] = $token;
                        echo'<p>' .
                            (!$is_mobile ? bbcode::auto_bb('form1', 'msg') : '') .
                            '<textarea rows="' . $set_user['field_h'] . '" name="msg"></textarea></p>' .
                            '<p><input type="checkbox" name="addfiles" value="1" /> ' . $lng_forum['add_file'] .
                            ($set_user['translit'] ? '<br /><input type="checkbox" name="msgtrans" value="1" /> ' . $lng['translit'] : '') .
                            '</p><p><input type="submit" name="submit" value="' . $lng['write'] . '" style="width: 107px; cursor: pointer;"/> ' .
                            ($set_forum['preview'] ? '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' : '') .
                            '<input type="hidden" name="token" value="' . $token . '"/>' .
                            '</p></form></div>';
                    } else {
                        echo '<p><input type="submit" name="submit" value="' . $lng['write'] . '"/></p></form></div>';
                    }
                }
                if ($rights == 3 || $rights >= 6)
                    echo '<form action="index.php?act=massdel" method="post">';
				
				/*				
				------------------------------------------------------------------------
				menampilkan header postingan
				- User
				- Pangkat
				- Jumlah post
				- Waktu
				- Avatar
				------------------------------------------------------------------------
                */
			//tampilan web pada forum
			if($iki_web){				
				$i = 1;
				$nomer = $page ? $page * 10 + 1 : 1;
                while (($res = mysql_fetch_assoc($req)) !== false) {
                    if ($res['close'])
                        echo '<div class="rmenu">';
                    else
                        echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
						//header post : waktu, jam dan jumlah posting
						echo '<table width="100%" cellpadding="0" cellspacing="0" class="phdr"><tr>' .
			                 '<td width="auto"><img src="' . $home . '/images/file.png"> </img> (' . functions::display_date($res['time']) . ')</td>' .
			                 '<td width="auto" align="right"><a href="'.$home.'/forum/' . functions::seo($type1['text']) . '_p' . $res['id'] . '.html"># '.($nomer - 10).'</a></td></tr></table>';
						echo '<table width="100%" cellpadding="0" cellspacing="0"><tr><td class="newsx" width="17%" align="left" valign="top">';
                    if ($set_user['avatar']) {
						echo '<table width="100%" cellpadding="0" cellspacing="0" ><tr><div class="info"><div class="avatar">';
                        if (file_exists(('../files/users/avatar/' . $res['user_id'] . '.png')))
                            echo '<center><img src="../files/users/avatar/' . $res['user_id'] . '.png" width="40" height="40" alt="' . $res['from'] . '" /></center>';
                        else
                            echo '<center><img src="../images/empty.png" width="40" height="40" alt="' . $res['from'] . '" /></center>';
                        echo '</div>';
						}

					// Link ke profile
                    if ($user_id && $user_id != $res['user_id']) {
                        echo '<b><a href="../users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a></b>';
                    } else {
                        echo '<b>' . $res['from'] . '</b> ';
                    }
					
					// Menampilkan indikator on off
					echo (time() > $res['lastdate'] + 600 ? '<span class="red"> &bull;</span> ' : '<span class="green"> &bull;</span> ');
					echo '<br />';
					
                    // Pangkat
					if ($res['postforum'] != 0) { 
					$arank = $res['postforum']; 
					if ($arank <= 75) 
					$arank = 'Newbie'; 
					elseif ($arank <= 200) 
					$arank = 'Catroxs User'; 
					elseif ($arank <= 400) 
					$arank = 'Aktivis Catroxs'; 
					elseif ($arank <= 600) 
					$arank = 'Catroxs Holic'; 
					elseif ($arank <= 850) 
					$arank = 'Catroxs Addict'; 
					elseif ($arank <= 1200) 
					$arank = 'Catroxs Maniak'; 
					elseif ($arank <= 3200) 
					$arank = 'Catroxs Geek'; 
					elseif ($arank <= 4500) 
					$arank = 'Catroxs Freak';
					elseif ($arank >= 5000) 
					$arank = 'Made in Catroxs'; 
					} 
					if (!empty($res['pangkat'])){ 
					$jenenge = '' . bbcode::tags($res['pangkat']). ''; 
					}else{ 
					$jenenge = ''.$arank.''; 
					}
                    $user_rights = array(
						0 => '' . $jenenge . '',
                        3 => 'Moderator',
                        6 => 'Senior Moderator',
                        7 => 'Admin',
                        9 => 'Super Admin'
                    );
                    echo @$user_rights[$res['rights']];

                    // Menampilkan Status
                    if (!empty($res['status']))
                        echo '<div class="status">' . $res['status'] . '</div>';
					// total post
					echo '<div>Post : <a href="' .  $home .'/users/profile.php?act=activity&amp;user=' . $res['user_id'] . '">' . $res['postforum'] . '</a></div>';
                        echo '</div></tr></table></td><div class="textx"><td width="80%" valign="top">';                    /*
                    -----------------------------------------------------------------
                    Вывод текста поста
                    -----------------------------------------------------------------
                    */
                    $text = $res['text'];
                    if ($set_forum['postcut']) {
                        // Если текст длинный, обрезаем и даем ссылку на полный вариант
                        switch ($set_forum['postcut']) {
                            case 2:
                                $cut = 1000;
                                break;

                            case 3:
                                $cut = 3000;
                                break;
                            default :
                                $cut = 500;
                        }
                    }
                    if ($set_forum['postcut'] && mb_strlen($text) > $cut) {
                        $text = mb_substr($text, 0, $cut);
                        $text = functions::checkout($text, 1, 1);
                        $text = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $text);
                        if ($set_user['smileys'])
                            $text = functions::smileys($text, $res['rights'] ? 1 : 0);
                        //seo
						//echo bbcode::notags($text) . '...<br /><a href="index.php?act=post&amp;id=' . $res['id'] . '">' . $lng_forum['read_all'] . ' &gt;&gt;</a>';
						echo bbcode::notags($text) . '...<br /><a href="'.$home.'/forum/' . functions::seo($type1['text']) . '_p' . $res['id'] . '.html">' . $lng_forum['read_all'] . ' &gt;&gt;</a>';
                    } else {
                        // Или, обрабатываем тэги и выводим весь текст
                        $text = functions::checkout($text, 1, 1);
                        if ($set_user['smileys'])
                            $text = functions::smileys($text, $res['rights'] ? 1 : 0);
                        echo $text;
                    }
                    if ($res['kedit']) {
                        // Если пост редактировался, показываем кем и когда
                        echo '<br /><span class="gray"><small>' . $lng_forum['edited'] . ' <b>' . $res['edit'] . '</b> (' . functions::display_date($res['tedit']) . ') <b>[' . $res['kedit'] . ']</b></small></span>';
                    }
                    // Если есть прикрепленный файл, выводим его описание
                    $freq = mysql_query("SELECT * FROM `cms_forum_files` WHERE `post` = '" . $res['id'] . "'");
                    if (mysql_num_rows($freq) > 0) {
                        $fres = mysql_fetch_assoc($freq);
                        $fls = round(@filesize('../files/forum/attach/' . $fres['filename']) / 1024, 2);
                        echo '<br /><span class="gray">' . $lng_forum['attached_file'] . ':';
                        // Предпросмотр изображений
                        $att_ext = strtolower(functions::format('./files/forum/attach/' . $fres['filename']));
                        $pic_ext = array(
                            'gif',
                            'jpg',
                            'jpeg',
                            'png'
                        );
                        if (in_array($att_ext, $pic_ext)) {
                            echo '<div><a href="index.php?act=file&amp;id=' . $fres['id'] . '">';
                            echo '<img src="thumbinal.php?file=' . (urlencode($fres['filename'])) . '" alt="' . $lng_forum['click_to_view'] . '" /></a></div>';
                        } else {
                            echo '<br /><a href="index.php?act=file&amp;id=' . $fres['id'] . '">' . $fres['filename'] . '</a>';
                        }
                        echo ' (' . $fls . ' кб.)<br/>';
                        echo $lng_forum['downloads'] . ': ' . $fres['dlcount'] . ' ' . $lng_forum['time'] . '</span>';
                        $file_id = $fres['id'];
                    }
					// Quote dan Reply post
					if ($user_id){
					echo '<br />';
					}
					echo '</td></div></tr></table>';
					echo '<hr /><div class="subquote"><table class="forumb" width="100%">';
					echo '<td align="left" valign="top">';
					if ($user_id && $res['ip_via_proxy']) {
                                echo 'IP 1 : ' . long2ip($res['ip']) . '<br /> ' .
                                     'IP 2 : ' . long2ip($res['ip_via_proxy']) .
									 '<br />UA : ' . $res['soft'] . '';
                            } else {
							if ($user_id){
                                echo 'IP : ' . long2ip($res['ip']) . '<br />UA : ' . $res['soft'] . '';
                            }
							}
					echo '</td>';
					if ($user_id && $user_id != $res['user_id']) {
					echo '<td align="right" valign="top">';
                    echo '<a href="index.php?act=say&amp;id=' . $res['id'] . '&amp;start=' . $start . '&amp;cyt"><div id="quote_post_1"><div class="quote_post_2">Quote</div></div></a>' ;
					echo '</td>';							 
                    }
					echo '</table></div>';
					
					
                    if ((($rights == 3 || $rights >= 6 || $curator) && $rights >= $res['rights']) || ($res['user_id'] == $user_id && !$set_forum['upfp'] && ($start + $i) == $colmes && $res['time'] > time() - 600) || ($res['user_id'] == $user_id && $set_forum['upfp'] && $start == 0 && $i == 1 && $res['time'] > time() - 600)) {
                        // Link untuk mengedit / menghapus posting
                        $menu = array(
                            '<a href="index.php?act=editpost&amp;id=' . $res['id'] . '">' . $lng['edit'] . '</a>',
                            ($rights >= 7 && $res['close'] == 1 ? '<a href="index.php?act=editpost&amp;do=restore&amp;id=' . $res['id'] . '">' . $lng_forum['restore'] . '</a>' : ''),
                            ($res['close'] == 1 ? '' : '<a href="index.php?act=editpost&amp;do=del&amp;id=' . $res['id'] . '">' . $lng['delete'] . '</a>')
                        );
                        echo '<div class="sub">';
                        if ($rights == 3 || $rights >= 6)
                            echo '<input type="checkbox" name="delch[]" value="' . $res['id'] . '"/>&#160;';
                        echo functions::display_menu($menu);
                        if ($res['close']) {
                            echo '<div class="red">' . $lng_forum['who_delete_post'] . ': <b>' . $res['close_who'] . '</b></div>';
                        } elseif (!empty($res['close_who'])) {
                            echo '<div class="green">' . $lng_forum['who_restore_post'] . ': <b>' . $res['close_who'] . '</b></div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';					
					++$nomer;
                    ++$i;
				}
				
			//tampilan wap pada forum
			} else {
                $i = 1;
				$nomer = $page ? $page * 10 + 1 : 1;
                while (($res = mysql_fetch_assoc($req)) !== false) {                 
                    if ($res['close'])
                        echo '<div class="rmenu">';
                    else
                        echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
						//header post : waktu, jam dan jumlah posting
						echo '<table width="100%" cellpadding="0" cellspacing="0" class="phdr"><tr>' .
			                 '<td width="auto"><img src="' . $home . '/images/file.png"> </img> (' . functions::display_date($res['time']) . ')</td>' .
			                 '<td width="auto" align="right"><a href="'.$home.'/forum/' . functions::seo($type1['text']) . '_p' . $res['id'] . '.html"># '.($nomer - 10).'</a></td></tr></table>';
						echo '<div class="newsx">';
                    if ($set_user['avatar']) {
						echo '<table width="100%" cellpadding="0" cellspacing="0"><tr><td width="40" align="left" valign="top"><div class="avatar">';
                        if (file_exists(('../files/users/avatar/' . $res['user_id'] . '.png')))
                            echo '<img src="../files/users/avatar/' . $res['user_id'] . '.png" width="40" height="40" alt="' . $res['from'] . '" />';
                        else
                            echo '<img src="../images/empty.png" width="40" height="40" alt="' . $res['from'] . '" />';
                        echo '</div></td>';
						}

					// Link ke profile
					echo '<td width="auto" align="left" valign="top">';
                    if ($user_id && $user_id != $res['user_id']) {
                        echo '<a href="../users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a> ';
                    } else {
                        echo '<b>' . $res['from'] . '</b> ';
                    }
					
					// Menampilkan indikator on off
					echo (time() > $res['lastdate'] + 600 ? '<span class="red"> &bull;</span> ' : '<span class="green"> &bull;</span> ');
					echo '<br />';
					
                    // Pangkat
					if ($res['postforum'] != 0) { 
					$arank = $res['postforum']; 
					if ($arank <= 75) 
					$arank = 'Newbie'; 
					elseif ($arank <= 200) 
					$arank = 'Catroxs User'; 
					elseif ($arank <= 400) 
					$arank = 'Aktivis Catroxs'; 
					elseif ($arank <= 600) 
					$arank = 'Catroxs Holic'; 
					elseif ($arank <= 850) 
					$arank = 'Catroxs Addict'; 
					elseif ($arank <= 1200) 
					$arank = 'Catroxs Maniak'; 
					elseif ($arank <= 3200) 
					$arank = 'Catroxs Geek'; 
					elseif ($arank <= 4500) 
					$arank = 'Catroxs Freak';
					elseif ($arank >= 5000) 
					$arank = 'Made in Catroxs'; 
					} 
					if (!empty($res['pangkat'])){ 
					$jenenge = '' . bbcode::tags($res['pangkat']). ''; 
					}else{ 
					$jenenge = ''.$arank.''; 
					}
                    $user_rights = array(
						0 => '' . $jenenge . '',
                        3 => 'Moderator',
                        6 => 'Senior Moderator',
                        7 => 'Admin',
                        9 => 'Super Admin'
                    );
                    echo @$user_rights[$res['rights']];

                    // Menampilkan Status
                    if (!empty($res['status']))
                        echo '<div class="status">' . $res['status'] . '</div>';
					echo '</td>';
					// total post
					echo '<td align="right" valign="top">';
					echo '&#160;Post : <a href="' .  $home .'/users/profile.php?act=activity&amp;user=' . $res['user_id'] . '">' . $res['postforum'] . '</a>';
                        echo '</td></tr></table></div><div class="textx">';                    /*
                    -----------------------------------------------------------------
                    Вывод текста поста
                    -----------------------------------------------------------------
                    */
                    $text = $res['text'];
                    if ($set_forum['postcut']) {
                        // Если текст длинный, обрезаем и даем ссылку на полный вариант
                        switch ($set_forum['postcut']) {
                            case 2:
                                $cut = 1000;
                                break;

                            case 3:
                                $cut = 3000;
                                break;
                            default :
                                $cut = 500;
                        }
                    }
                    if ($set_forum['postcut'] && mb_strlen($text) > $cut) {
                        $text = mb_substr($text, 0, $cut);
                        $text = functions::checkout($text, 1, 1);
                        $text = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $text);
                        if ($set_user['smileys'])
                            $text = functions::smileys($text, $res['rights'] ? 1 : 0);
                        //seo
						//echo bbcode::notags($text) . '...<br /><a href="index.php?act=post&amp;id=' . $res['id'] . '">' . $lng_forum['read_all'] . ' &gt;&gt;</a>';
						echo bbcode::notags($text) . '...<br /><a href="'.$home.'/forum/' . functions::seo($type1['text']) . '_p' . $res['id'] . '.html">' . $lng_forum['read_all'] . ' &gt;&gt;</a>';
                    } else {
                        // Или, обрабатываем тэги и выводим весь текст
                        $text = functions::checkout($text, 1, 1);
                        if ($set_user['smileys'])
                            $text = functions::smileys($text, $res['rights'] ? 1 : 0);
                        echo $text;
                    }
                    if ($res['kedit']) {
                        // Если пост редактировался, показываем кем и когда
                        echo '<br /><span class="gray"><small>' . $lng_forum['edited'] . ' <b>' . $res['edit'] . '</b> (' . functions::display_date($res['tedit']) . ') <b>[' . $res['kedit'] . ']</b></small></span>';
                    }
                    // Если есть прикрепленный файл, выводим его описание
                    $freq = mysql_query("SELECT * FROM `cms_forum_files` WHERE `post` = '" . $res['id'] . "'");
                    if (mysql_num_rows($freq) > 0) {
                        $fres = mysql_fetch_assoc($freq);
                        $fls = round(@filesize('../files/forum/attach/' . $fres['filename']) / 1024, 2);
                        echo '<br /><span class="gray">' . $lng_forum['attached_file'] . ':';
                        // Предпросмотр изображений
                        $att_ext = strtolower(functions::format('./files/forum/attach/' . $fres['filename']));
                        $pic_ext = array(
                            'gif',
                            'jpg',
                            'jpeg',
                            'png'
                        );
                        if (in_array($att_ext, $pic_ext)) {
                            echo '<div><a href="index.php?act=file&amp;id=' . $fres['id'] . '">';
                            echo '<img src="thumbinal.php?file=' . (urlencode($fres['filename'])) . '" alt="' . $lng_forum['click_to_view'] . '" /></a></div>';
                        } else {
                            echo '<br /><a href="index.php?act=file&amp;id=' . $fres['id'] . '">' . $fres['filename'] . '</a>';
                        }
                        echo ' (' . $fls . ' кб.)<br/>';
                        echo $lng_forum['downloads'] . ': ' . $fres['dlcount'] . ' ' . $lng_forum['time'] . '</span>';
                        $file_id = $fres['id'];
                    }
					// Quote dan Reply post
					if ($user_id){
					echo '<br /><hr />';
					}
					echo '<table class="forumb" width="100%">';
					echo '<td align="left" valign="top">';
					if ($user_id && $res['ip_via_proxy']) {
                                echo 'IP 1 : ' . long2ip($res['ip']) . '<br /> ' .
                                     'IP 2 : ' . long2ip($res['ip_via_proxy']) .
									 '<br />UA : ' . $res['soft'] . '';
                            } else {
							if ($user_id){
                                echo 'IP : ' . long2ip($res['ip']) . '<br />UA : ' . $res['soft'] . '';
                            }
							}
					echo '</td>';
					if ($user_id && $user_id != $res['user_id']) {
					echo '<td align="right" valign="top">';
                    echo '<a href="index.php?act=say&amp;id=' . $res['id'] . '&amp;start=' . $start . '&amp;cyt"><div id="quote_post_1"><div class="quote_post_2">Quote</div></div></a>' ;
					echo '</td>';							 
                    }
					echo '</table>';
					echo '</div>';
					
					
                    if ((($rights == 3 || $rights >= 6 || $curator) && $rights >= $res['rights']) || ($res['user_id'] == $user_id && !$set_forum['upfp'] && ($start + $i) == $colmes && $res['time'] > time() - 600) || ($res['user_id'] == $user_id && $set_forum['upfp'] && $start == 0 && $i == 1 && $res['time'] > time() - 600)) {
                        // Link untuk mengedit / menghapus posting
                        $menu = array(
                            '<a href="index.php?act=editpost&amp;id=' . $res['id'] . '">' . $lng['edit'] . '</a>',
                            ($rights >= 7 && $res['close'] == 1 ? '<a href="index.php?act=editpost&amp;do=restore&amp;id=' . $res['id'] . '">' . $lng_forum['restore'] . '</a>' : ''),
                            ($res['close'] == 1 ? '' : '<a href="index.php?act=editpost&amp;do=del&amp;id=' . $res['id'] . '">' . $lng['delete'] . '</a>')
                        );
                        echo '<div class="sub">';
                        if ($rights == 3 || $rights >= 6)
                            echo '<input type="checkbox" name="delch[]" value="' . $res['id'] . '"/>&#160;';
                        echo functions::display_menu($menu);
                        if ($res['close']) {
                            echo '<div class="red">' . $lng_forum['who_delete_post'] . ': <b>' . $res['close_who'] . '</b></div>';
                        } elseif (!empty($res['close_who'])) {
                            echo '<div class="green">' . $lng_forum['who_restore_post'] . ': <b>' . $res['close_who'] . '</b></div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
					++$nomer;
                    ++$i;
                }				
			}
				
                if ($rights == 3 || $rights >= 6) {
                    echo '<div class="rmenu"><input type="submit" value=" ' . $lng['delete'] . ' "/></div>';
                    echo '</form>';
                }
                // Replay Thread
                if (($user_id && !$type1['edit'] && !$set_forum['upfp'] && $set['mod_forum'] != 3) || ($rights >= 7 && !$set_forum['upfp'])) {
                    echo '<div class="gmenu"><form name="form2" action="index.php?act=say&amp;id=' . $id . '" method="post">';
                    if ($set_forum['farea']) {
                        $token = mt_rand(1000, 100000);
                        $_SESSION['token'] = $token;
                        echo '<p>';
                        if (!$is_mobile)
                            echo bbcode::auto_bb('form2', 'msg');
                        echo '<textarea rows="' . $set_user['field_h'] . '" name="msg"></textarea><br/></p>' .
                             '<p><input type="checkbox" name="addfiles" value="1" /> ' . $lng_forum['add_file'];
                        if ($set_user['translit'])
                            echo '<br /><input type="checkbox" name="msgtrans" value="1" /> ' . $lng['translit'];
                        echo'</p><p><input type="submit" name="submit" value="' . $lng['write'] . '" style="width: 107px; cursor: pointer;"/> ' .
                            ($set_forum['preview'] ? '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' : '') .
                            '<input type="hidden" name="token" value="' . $token . '"/>' .
                            '</p></form></div>';
                    } else {
                        $token = mt_rand(1000, 100000);
                        $_SESSION['token'] = $token;
                        echo '<p>';
                        if (!$is_mobile)
                            echo bbcode::auto_bb('form2', 'msg');
                        echo '<textarea rows="' . $set_user['field_h'] . '" name="msg"></textarea><br/></p>' .
                             '<p><input type="checkbox" name="addfiles" value="1" /> ' . $lng_forum['add_file'];
                        if ($set_user['translit'])
                            echo '<br /><input type="checkbox" name="msgtrans" value="1" /> ' . $lng['translit'];
                        echo'</p><p><input type="submit" name="submit" value="' . $lng['write'] . '" style="width: 107px; cursor: pointer;"/> ' .
                            ($set_forum['preview'] ? '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' : '') .
                            '<input type="hidden" name="token" value="' . $token . '"/>' .
                            '</p></form></div>';
                    }
                }
                echo '<div class="phdr"><a name="down" id="down"></a><a href="#up">' .
                     '<img src="../theme/' . $set_user['skin'] . '/images/up.png" alt="' . $lng['up'] . '" width="20" height="10" border="0"/></a>' .
                     '&#160;&#160;' . $lng['total'] . ': ' . $colmes . '</div>';
                if ($colmes > $kmess) {
                    echo '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&amp;', $start, $colmes, $kmess) . '</div>' .
                         '<p><form action="index.php?id=' . $id . '" method="post">' .
                         '<input type="text" name="page" size="2"/>' .
                         '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
                         '</form></p>';
                } else {
                    echo '<br />';
                }
				
				/*
				--------------------------------------------------------------
				Menampilkan Topik Yang Populer
				--------------------------------------------------------------
				*/
				
				echo '<div class="list1"><div class="phdr"><b>Popular Topic</b></div>';
				$colmes = mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
				$colmes1 = mysql_num_rows($colmes);
				if (!$is_mobile) {
					$req = mysql_query("SELECT * FROM `forum` WHERE `type`='t' AND `close` != '1' ORDER BY `refid` DESC LIMIT 5");
				}else{
					$req = mysql_query("SELECT * FROM `forum` WHERE `type`='t' AND `close` != '1' ORDER BY `refid` DESC LIMIT 3");
				}
				$i = 0;
				while($res = mysql_fetch_array($req)) {
					echo $i % 2 ? '<div class="menu">' : '<div class="menu">';
					echo '<div class="submenu">';
					$q3 = mysql_query("SELECT `id`, `refid`, `text` FROM `forum` WHERE `type`='r' AND `id`='" . $res['refid'] . "'");
					$razd = mysql_fetch_array($q3);
					$q4 = mysql_query("SELECT `text` FROM `forum` WHERE `type`='f' AND `id`='" . $razd['refid'] . "'");
					$frm = mysql_fetch_array($q4);
					$colmes = mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
					$colmes1 = mysql_num_rows($colmes);
					$cpg = ceil($colmes1 / $kmess);
					$nick = mysql_fetch_array($colmes);
					echo '&#160;<a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $res['id'] . '.html'. ($cpg > 1 && $_SESSION['uppost'] ? '&amp;clip&amp;page=' . $cpg : '') . '">' . $res['text'] . '</a>&#160;[' . $colmes1 . ']';
					if ($cpg > 1)
					echo '&#160;<a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $res['id'] . '_p' . $cpg . '.html' . ($_SESSION['uppost']) . '">&rarr;</a>';
					echo '</div></div>';
					++$i;
				}
				echo '</div>';
				
                /*
                -----------------------------------------------------------------
                Ссылки на модераторские функции
                -----------------------------------------------------------------
                */
                if ($curators) {
                    $array = array();
                    foreach ($curators as $key => $value)
                        $array[] = '<a href="../users/profile.php?user=' . $key . '">' . $value . '</a>';
                    echo '<p><div class="func">' . $lng_forum['curators'] . ': ' . implode(', ', $array) . '</div></p>';
                }
                if ($rights == 3 || $rights >= 6) {
                    echo '<p><div class="func">';
                    if ($rights >= 7)
                        echo '<a href="index.php?act=curators&amp;id=' . $id . '&amp;start=' . $start . '">' . $lng_forum['curators_of_the_topic'] . '</a><br />';
                    echo isset($topic_vote) && $topic_vote > 0
                            ? '<a href="index.php?act=editvote&amp;id=' . $id . '">' . $lng_forum['edit_vote'] . '</a><br/><a href="index.php?act=delvote&amp;id=' . $id . '">' . $lng_forum['delete_vote'] . '</a><br/>'
                            : '<a href="index.php?act=addvote&amp;id=' . $id . '">' . $lng_forum['add_vote'] . '</a><br/>';
                    echo '<a href="index.php?act=ren&amp;id=' . $id . '">' . $lng_forum['topic_rename'] . '</a><br/>';
                    // Закрыть - открыть тему
                    if ($type1['edit'] == 1)
                        echo '<a href="index.php?act=close&amp;id=' . $id . '">' . $lng_forum['topic_open'] . '</a><br/>';
                    else
                        echo '<a href="index.php?act=close&amp;id=' . $id . '&amp;closed">' . $lng_forum['topic_close'] . '</a><br/>';
                    // Удалить - восстановить тему
                    if ($type1['close'] == 1)
                        echo '<a href="index.php?act=restore&amp;id=' . $id . '">' . $lng_forum['topic_restore'] . '</a><br/>';
                    echo '<a href="index.php?act=deltema&amp;id=' . $id . '">' . $lng_forum['topic_delete'] . '</a><br/>';
                    if ($type1['vip'] == 1)
                        echo '<a href="index.php?act=vip&amp;id=' . $id . '">' . $lng_forum['topic_unfix'] . '</a>';
                    else
                        echo '<a href="index.php?act=vip&amp;id=' . $id . '&amp;vip">' . $lng_forum['topic_fix'] . '</a>';
                    echo '<br/><a href="index.php?act=per&amp;id=' . $id . '">' . $lng_forum['topic_move'] . '</a></div></p>';
                }
                if ($wholink)
                    echo '<div>' . $wholink . '</div>';
                if ($filter)
                    echo '<div><a href="index.php?act=filter&amp;id=' . $id . '&amp;do=unset">' . $lng_forum['filter_cancel'] . '</a></div>';
                else
                    echo '<div><a href="index.php?act=filter&amp;id=' . $id . '&amp;start=' . $start . '">' . $lng_forum['filter_on_author'] . '</a></div>';
                echo '<a href="index.php?act=tema&amp;id=' . $id . '">' . $lng_forum['download_topic'] . '</a>';
                break;

            default:
                /*
                -----------------------------------------------------------------
                Если неверные данные, показываем ошибку
                -----------------------------------------------------------------
                */
                echo functions::display_error($lng['error_wrong_data']);
                break;
        }
    } else {
        /*
        -----------------------------------------------------------------
        Список Категорий форума
        -----------------------------------------------------------------
        */
        $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files`" . ($rights >= 7 ? '' : " WHERE `del` != '1'")), 0);
        echo '<p>' . counters::forum_new(1) . '</p>' .
             '<div class="phdr"><b>' . $lng['forum'] . '</b></div>' .
             '<div class="topmenu"><a href="search.php">' . $lng['search'] . '</a> | <a href="index.php?act=files">' . $lng_forum['files_forum'] . '</a> <span class="red">(' . $count . ')</span></div>';
        $req = mysql_query("SELECT `id`, `text`, `soft` FROM `forum` WHERE `type`='f' ORDER BY `realid`");
        $i = 0;
        while (($res = mysql_fetch_array($req)) !== false) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='r' and `refid`='" . $res['id'] . "'"), 0);
			//seo
            //echo '<a href="index.php?id=' . $res['id'] . '">' . $res['text'] . '</a> [' . $count . ']';
            echo '<b><a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $res['id'] . '.html">' . $res['text'] . '</a> [' . $count . ']</b>';
			if (!empty($res['soft']))
                echo '<div class="sub"><span class="gray">' . $res['soft'] . '</span></div>';
            echo '</div>';
            ++$i;
        }
        $online_u = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 300) . " AND `place` LIKE 'forum%'"), 0);
        $online_g = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300) . " AND `place` LIKE 'forum%'"), 0);
        echo '<div class="phdr">' . ($user_id ? '<a href="index.php?act=who">' . $lng_forum['who_in_forum'] . '</a>' : $lng_forum['who_in_forum']) . '&#160;(' . $online_u . '&#160;/&#160;' . $online_g . ')</div>';
        unset($_SESSION['fsort_id']);
        unset($_SESSION['fsort_users']);
    }

    // Навигация внизу страницы
    echo '<p>' . ($id ? '<a href="index.php">' . $lng['to_forum'] . '</a><br />' : '');
    if (!$id) {
        echo '<a href="../pages/faq.php?act=forum">' . $lng_forum['forum_rules'] . '</a>';
    }
    echo '</p>';
    if (!$user_id) {
        if ((empty($_SESSION['uppost'])) || ($_SESSION['uppost'] == 0)) {
            echo '<a href="index.php?id=' . $id . '&amp;page=' . $page . '&amp;newup">' . $lng_forum['new_on_top'] . '</a>';
        } else {
            echo '<a href="index.php?id=' . $id . '&amp;page=' . $page . '&amp;newdown">' . $lng_forum['new_on_bottom'] . '</a>';
        }
    }
}

require_once('../incfiles/end.php');