<?php

/*
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
*/

define('_IN_JOHNCMS', 1);
require_once('../incfiles/core.php');
$headmod = 'Blogs';

//function timer
function timer($var = '') {
   global $waktu;
   if($var <= 0)
      return;
   if(86400 > $var) {
      if(3600 > $var) {
         if(60 > $var) {
            $time = $var;
            return $time . ' detik.';
         } else if(60 <= $var && (60 * 2) > $var) {
            return $time . ' menit.';
         }
         $hours = (60 - ceil((3600 - $var) / 60));
         return $hours . ' jam.';
      } else if(3600 <= $var && (3600 * 2) > $var) {
         return $waktu . 'satu jam.';
      }
      $days = (24 - ceil((86400 - $var) / 3600));
      return $days . ' jam.';
   } else if(86400 <= $var && (86400 * 2) > $var) {
      return $waktu . 'satu hari.';
   }
   $days = ceil($var / 86400);
   return $days . 'day left.';
}

$mods = array (
'view',
'comments'
);
//includes file (array)
if ($act && ($key = array_search($act, $mods)) !== false && file_exists('includes/' . $mods[$key] . '.php')) {
    require('includes/' . $mods[$key] . '.php');
} else {
   if($id) {
      $query = mysql_query("SELECT * FROM `animes_cat` WHERE `id`='$id' LIMIT 1;");
      if (mysql_num_rows($query)) {
         $req1 = mysql_fetch_assoc($query);
         
         $textl =  'Update | ' . htmlentities($req1['name'], ENT_QUOTES, 'UTF-8');
         require_once('../incfiles/head.php');
         echo '<div class="phdr"><h3><a href="./">Update</a> | ' . htmlentities($req1['name'], ENT_QUOTES, 'UTF-8') . '</h3></div>';
         
         $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes` WHERE `refid`='$id'".($rights < 7 ? " AND `time`<='" . time() . "'":"")), 0);
         if($total) {
            if ($total > $kmess) 
               echo '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&amp;', $start, $total, $kmess) . '</div>';
            $req = mysql_query("SELECT `id`, `name`, `text`, `time` FROM `animes` WHERE `refid`='$id'".($rights < 7 ? " AND `time`<='" . time() . "'":"")."
            ORDER BY `time` DESC LIMIT "
                       . $start . "," . $kmess);
            $i = 1;
            while (($row = mysql_fetch_assoc($req)) !== false) {
               echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
               if(file_exists('../files/blogs/anime_icon_' . $row['id'] . '.jpg') !== false) {
                  echo '<table cellpadding="0" cellspacing="0" width="100%"><tr><td width="32">';
                  echo '<img style="margin: 0 0 -3px 0;border: 0px;" src="../files/blogs/anime_icon_' . $row['id'] . '.jpg" alt="" width="32" height="32"/>&#160;';
                  echo '</td><td>';
                  echo '<div class="phdr">(' . date('d.m.o / H:i', $row['time'] + $sdvigclock * 3600) . ')</div><a href="'.$home.'/blogs/' . functions::seo(htmlentities($row['name'], ENT_QUOTES, 'UTF-8')) . '_p' . $row['id'] . '.html"><div align="center">' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '</div></a>';
                  echo '</td></tr></table>';
               } else {
                  echo '<div class="phdr">(' . date('d.m.o / H:i', $row['time'] + $sdvigclock * 3600) . ')</div><a href="'.$home.'/blogs/' . functions::seo(htmlentities($row['name'], ENT_QUOTES, 'UTF-8')) . '_p' . $row['id'] . '.html"><div align="center">' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '</div></a>';
               }
               echo '<div class="sub"></div>';
               $text = $row['text'];
               if(mb_strlen($text) > 100) {
                  $str = mb_substr($text, 0, 100);
                  $text = mb_substr($str, 0, mb_strrpos($str, ' ')) . '...';
               }
               echo functions::checkout($text, 2, 1);
               if($row['time'] > time())
                  echo '<div class="sub func">Waktu tersisa untuk tampil: ' . timer($row['time'] - time()) . '</div>';
               echo '</div>';
               ++$i;
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
            if ($total > $kmess) {
               echo '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&amp;', $start, $total, $kmess) . '</div>';
               echo '<p><form action="index.php" method="get">
               <input type="hidden" name="id" value="' . $id . '"/>
               <input type="text" name="page" size="2"/>
               <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
            }
         } else {
            echo '<div class="rmenu">Empty blogs</div>';
         }
      } else {
         echo '<div class="rmenu">Category does not exist</div>';
      }
   } else {
      $textl = 'Blogs';
      require_once('../incfiles/head.php');
      echo '<div class="phdr"><h3>Blogs</h3></div>';

      $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_cat`"), 0);
      if($total) {
         if ($total > $kmess) 
            echo '<div class="topmenu">' . functions::display_pagination('index.php?', $start, $total, $kmess) . '</div>';
         $req = mysql_query("SELECT `animes_cat`.`name`, `animes_cat`.`id`
         FROM `animes_cat`
         ORDER BY `animes_cat`.`realid` ASC LIMIT "  . $start . "," . $kmess);
         $i = 1;
         while (($row = mysql_fetch_assoc($req)) !== false) {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes` WHERE ".($rights < 7 ? "`time`<='" . time() . "' AND ":"")."`refid`='{$row['id']}'"), 0);
            echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
            if(file_exists('../files/blogs/ico_cat_' . $row['id'] . '.jpg') !== false)
               echo '<img style="margin: 0 0 -3px 0;border: 0px;" src="../files/blogs/ico_cat_' . $row['id'] . '.jpg" alt="" width="16" height="16"/>&#160;';
			echo '<a href="'.$home.'/blogs/' . functions::seo(htmlentities($row['name'], ENT_QUOTES, 'UTF-8')) . '_' . $row['id'] . '.html">' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '</a>(' . $count . ')';
            echo '</div>';
            ++$i;
         }
         echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
         if ($total > $kmess) {
            echo '<div class="topmenu">' . functions::display_pagination('index.php?', $start, $total, $kmess) . '</div>';
            echo '<p><form action="index.php" method="get">
            <input type="text" name="page" size="2"/>
            <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
         }
      } else {
         echo '<div class="rmenu">Empty Category</div>';
      }
   }
   if ($user_id)
   echo '<div class="gmenu"><div class="func"><a href="../blogs/nulis.php">Tambah Blogs</a></div></div>';
   if ($rights >= 7)
   echo '<div class="rmenu"><div class="func"><a href="../blogs/manage.php">Manage Blogs</a></div></div>';

}
require_once("../incfiles/end.php");