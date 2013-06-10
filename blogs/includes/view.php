<?php
/*
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
*/
defined('_IN_JOHNCMS') or die('Error: restricted access');
if(empty($_SESSION['error']))
   $_SESSION['error'] = '';
//&#1060;&#1091;&#1085;&#1082;&#1094;&#1080;&#1103; &#1086;&#1090;&#1086;&#1073;&#1088;&#1072;&#1078;&#1077;&#1085;&#1080;&#1103; &#1088;&#1077;&#1081;&#1090;&#1080;&#1085;&#1075;&#1072;
function rating($id, $type = 0) {
   $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_rating` WHERE `news`='$id'"), 0); 
   if($total) {
      $query = mysql_query("SELECT `golos`, COUNT(*) as `count` FROM `animes_rating` WHERE `news`='$id' GROUP BY `golos`");
      $array['plus'] = 0;
      $array['minus'] = 0;
      while (($row = mysql_fetch_assoc($query)) !== false) {
         if(isset($row['golos']) && $row['golos'] == 1) $array['plus'] = $row['count'];
         else if(isset($row['golos']) && $row['golos'] == 2) $array['minus'] = $row['count'];
         else {
            $array['plus'] = 0;
            $array['minus'] = 0;
         }
      }
      if($array['plus'] > $array['minus']) {
         if($array['minus'] == 0) {
            if($array['plus'] == 1) $count = 60;
            else if($array['plus'] == 2) $count = 60;
            else if($array['plus'] == 3) $count = 60;
            else if($array['plus'] == 4) $count = 70;
            else if($array['plus'] == 5) $count = 70;
            else if($array['plus'] == 6) $count = 80;
            else if($array['plus'] == 7) $count = 80;
            else if($array['plus'] == 8) $count = 90;
            else if($array['plus'] == 9) $count = 90;
            else if($array['plus'] >= 10) $count = 100;
         } else {
            $count = round($array['minus'] / $array['plus'], 1) * 100;
            if($count == 0)   $count = 100;
            else $count = (100 - $count);
         }
      } else if($array['plus'] < $array['minus']) {
         if($array['plus'] == 0) {
            if($array['minus'] == 1) $count = 40;
            else if($array['minus'] == 2) $count = 40;
            else if($array['minus'] == 3) $count = 40;
            else if($array['minus'] == 4) $count = 30;
            else if($array['minus'] == 5) $count = 30;
            else if($array['minus'] == 6) $count = 20;
            else if($array['minus'] == 7) $count = 20;
            else if($array['minus'] == 8) $count = 10;
            else if($array['minus'] == 9) $count = 10;
            else if($array['minus'] >= 10) $count = 0;
         } else {
            $pr = (round($array['plus'] / $array['minus'], 1) * 100);
            $count = $pr;
         }
      } else
         $count = 50;
      $percent = $count;
      if($percent == 100)   $stars = 10;
      else if($percent < 100 && $percent >= 90) $stars = 9;
      else if($percent < 100 && $percent >= 90) $stars = 9;
      else if($percent < 90 && $percent >= 80) $stars = 8;
      else if($percent < 80 && $percent >= 70) $stars = 7;
      else if($percent < 70 && $percent >= 60) $stars = 6;
      else if($percent < 60 && $percent >= 50) $stars = 5;
      else if($percent < 50 && $percent >= 40) $stars = 4;
      else if($percent < 40 && $percent >= 30) $stars = 3;
      else if($percent < 30 && $percent >= 20) $stars = 2;
      else if($percent < 20 && $percent >= 10) $stars = 2;
      else if($percent < 10 && $percent > 0) $stars = 1;
      else if($percent == 0) $stars = 0;
      if($type == 0) return '<img class="ico" src="../blogs/stars/stars_'.$stars.'.gif" alt="&bull;" />';
      else return $percent;
   } else return '<img class="ico" src="../blogs/stars/stars_5.gif" alt="&bull;" />';
}
if($id) {
   $query = mysql_query("SELECT `animes`.*, `animes_cat`.`name` as `catname`, `animes_cat`.`id` as `catid` FROM `animes` LEFT JOIN `animes_cat` ON `animes`.`refid`=`animes_cat`.`id` WHERE `animes`.`id`='$id'".($rights < 7 ? " AND `animes`.`time`<='" . time() . "'":"")." LIMIT 1;");
   if (mysql_num_rows($query)) {
      //&#1055;&#1086;&#1082;&#1072;&#1079;&#1099;&#1074;&#1072;&#1077;&#1084; &#1085;&#1086;&#1074;&#1086;&#1089;&#1090;&#1100;
      $res1 = mysql_fetch_assoc($query);
      $textl =  'Blogs | ' . htmlentities($res1['name'], ENT_QUOTES, 'UTF-8');
      require_once('../incfiles/head.php');
      echo '<div class="phdr"><h3>' . htmlentities($res1['name'], ENT_QUOTES, 'UTF-8') . '</h3></div>';
      echo '<div class="list1">';
      //&#1042;&#1099;&#1074;&#1086;&#1076;&#1080;&#1084; &#1082;&#1072;&#1088;&#1090;&#1080;&#1085;&#1082;&#1091;
      if(file_exists('../files/blogs/anime_icon_' . $id . '.jpg') !== false)         
echo '<div align="center"><img style="float: center; margin: 5px 6px 2px 2px; border: 2px;" src="../files/blogs/anime_icon_' . $id . '.jpg" /></div>';
      $text = functions::checkout($res1['text'], 1, 1);
      if ($set_user['smileys'])
         $text = functions::smileys($text);
      echo '<div class="textx">';
	  echo $text;
	  echo '</div>';
      echo '<div style="clear:both;"></div></div>';
      //&#1054;&#1073;&#1088;&#1072;&#1073;&#1072;&#1090;&#1099;&#1074;&#1072;&#1077;&#1084; &#1075;&#1086;&#1083;&#1086;&#1089;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077;
      if(isset($_POST['plus_x']) || isset($_POST['plus_y'])) {
         if($res1['user_id'] == $user_id) {
            $_SESSION['error'] = '<div class="list1 red">Anda tidak boleh menilai blog sendiri!</div>';
         } else {
            $plus = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_rating` WHERE `news`='$id' AND `user_id`='$user_id' LIMIT 1;"), 0);
            if($plus) {
               $_SESSION['error'] = '<div class="list1 red">Penilaian sudah diterima!</div>';
            } else {
               mysql_query("INSERT INTO `animes_rating` SET
               `news`='$id',
               `user_id`='$user_id', `golos`='1';");
               $_SESSION['error'] = '<div class="list1 green">Penilaian diterima!</div>';
            }
         }
         Header('Location: index.php?act=view&id=' . $id);
         exit;
      } else if(isset($_POST['minus_x']) || isset($_POST['minus_y'])) {
         if($res1['user_id'] == $user_id) {
            $_SESSION['error'] = '<div class="list1 red">nda tidak boleh menilai blog sendiri!</div>';
         } else {
            $plus = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_rating` WHERE `news`='$id' AND `user_id`='$user_id' LIMIT 1;"), 0);
            if($plus) {
               $_SESSION['error'] = '<div class="list1 red">Penilaian sudah diterima!</div>';
            } else {
               mysql_query("INSERT INTO `animes_rating` SET
               `news`='$id',
               `user_id`='$user_id', `golos`='2';");
               $_SESSION['error'] = '<div class="list1 green">Penilaian diterima!</div>';
            }
         }
         Header('Location: index.php?act=view&id=' . $id);
         exit;
      }
      //&#1057;&#1086;&#1086;&#1073;&#1097;&#1077;&#1085;&#1080;&#1077; &#1086;&#1073; &#1086;&#1089;&#1090;&#1072;&#1074;&#1083;&#1077;&#1085;&#1085;&#1086;&#1084; &#1075;&#1086;&#1083;&#1086;&#1089;&#1077;
      echo $_SESSION['error'];
      //&#1042;&#1099;&#1074;&#1086;&#1076;&#1080;&#1084; &#1072;&#1074;&#1090;&#1086;&#1088;&#1072; &#1085;&#1086;&#1074;&#1086;&#1089;&#1090;&#1080;
      $us = mysql_query("SELECT `id`, `name` FROM `users` WHERE `id` = '{$res1['user_id']}'");
      if (mysql_num_rows($us)) {
         $rowuse = mysql_fetch_assoc($us);
         $name_use = $user_id ? '<a href="../users/profile.php?id=' . $rowuse['id'] . '">' . $rowuse['name'] . '</a>' : $rowuse['name'];
      } else {
         $name_use = $lng['guest'];
      }
      //
   echo '<div class="gmenu">
      Ditulis oleh: ' . $name_use . '<br />
      Pada: ' . date('d.m.o / H:i', $res1['time'] + $sdvigclock * 3600) . '<br />
      '.($res1['time'] > time()?'<div class="func">Waktu tersisa untuk diperlihatkan: ' . timer($res1['time'] - time()) . '</div>':'').'
      </div>
      ' . ($rights >= 7 ? '<div class="menu"><div class="menu">
      <a href="manage.php?act=newsedit&amp;id='.$id.'">' . $lng['edit'] . '</a><br />
      <a href="manage.php?act=delnews&amp;id=' . $id . '">' . $lng['delete'] . '</a><br />
      </div></div>':'') . '
      <div class="bmenu"></div>';
       
   } else {
      $textl = 'Blogs';
      require_once('../incfiles/head.php');
      echo functions::display_error('Blogs tidak ada');
   }
} else {
   $textl = 'Blogs';
   require_once('../incfiles/head.php');
   echo functions::display_error('Blogs tidak dipilih');
}
            echo '<div class="bmenu"><a href="index.php">Back to List</a></div>';
unset($_SESSION['error']);