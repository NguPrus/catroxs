<?php
/*
////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
*/
defined('_IN_JOHNCMS') or die('Error: restricted access');
if($id) {
   $query = mysql_query("SELECT * FROM `animes` WHERE `id`='$id' LIMIT 1;");
   if (mysql_num_rows($query)) {
      $res1 = mysql_fetch_assoc($query);
      if(empty($_SESSION['error']))
      $_SESSION['error'] = '';
      $textl =  'Anime | Komentar Anime "' . htmlentities($res1['name'], ENT_QUOTES, 'UTF-8') . '"';
      require_once('../incfiles/head.php');
require_once('../blogs/includes/komen.php');      
echo '<div class="phdr"><h3>Komentar : "<a href="index.php?act=view&amp;id=' . $id . '">' . htmlentities($res1['name'], ENT_QUOTES, 'UTF-8') . '</a>"</h3></div>';
      
      $com = isset($_REQUEST) ? abs(intval($_REQUEST['com'])) : '';
      
      switch($mod) {
         case 'replay':
            if($rights >= 7) {
               $q = mysql_query("SELECT * FROM `animes_comments` WHERE `id`='$com' LIMIT 1;");
               if (mysql_num_rows($q)) {
                  $r = mysql_fetch_assoc($q);
                  if(isset($_POST['submit'])) {
                     $text = isset($_POST['text']) ? trim($_POST['text']) : '';
                     $error = array();
                     if(!$text)
                        $error[] = 'Komentar tidak boleh kosong!';
                     elseif (mb_strlen($text) < 4 || mb_strlen($text) > 5000)
                        $error[] =  'Komentar terlalu panjang atau terlalu pendek!';
                     $flood = functions::antiflood();
                     if($flood)
                        $error[] = $lng['error_flood'] . ' ' . $flood . $lng['sec'];
                     
                     if(empty($error)) {
                        mysql_query("UPDATE `animes_comments` SET
                        `reply` = '[b]" . $login . "[/b]:" . mysql_real_escape_string($text) . "' WHERE `id`='$com'");
                        mysql_query("UPDATE `users` SET
                           `lastpost` = '" . time() . "'
                           WHERE `id` = '$user_id'
                        ");
                        $_SESSION['error'] = '<div class="gmenu">Komentar ditambahkan</div>';
                     } else {
                        $_SESSION['error'] = '<div class="rmenu">' . implode('<br />', $error) . '</div>';
                     }
                     Header('Location: index.php?act=comments&id=' . $id);
                     exit;
                  }
                  echo $_SESSION['error'] . '<div class="gmenu">
                  <form action="index.php?act=comments&amp;mod=replay&amp;id=' . $id . '&amp;com=' . $com . '" method="post"  enctype="multipart/form-data"><div>
                  <b>Jawab:</b><br/>
                  <textarea rows="3" name="text">' . htmlentities($r['reply'], ENT_QUOTES, 'UTF-8') . '</textarea>
                  <br /><span style="font-size: x-small;">Min 2. Max 5000 karakter</span><br />
                  <input type="submit" name="submit" value="jawab"/>
                  </div></form>
                  </div>';
               } else {
                  echo functions::display_error( 'Pesan tidak ada!');
               }
            } else {
               Header('Location: ../?err');
               exit;
            }
         break;
         
         case 'delete':
            if($rights >= 7) {
               $q = mysql_query("SELECT * FROM `animes_comments` WHERE `id`='$com' LIMIT 1;");
               if (mysql_num_rows($q)) {
                  if(isset($_POST['submit'])) {
                     mysql_query("DELETE FROM `animes_comments` WHERE `id`='$com'");
                     $_SESSION['error'] = '<div class="gmenu">Komentar di hapus</div>';
                     Header('Location: index.php?act=comments&amp;id=' . $id);
                     exit;
                  }
                  echo $_SESSION['error'] . '<div class="rmenu">
                  <form action="index.php?act=comments&amp;mod=delete&amp;id=' . $id . '&amp;com=' . $com . '" method="post"  enctype="multipart/form-data"><div>
                  Hapus komentar ini?<br />
                  <input type="submit" name="submit" value="Hapus"/>
                  </div></form>
                  </div>';
               } else {
                  echo functions::display_error( 'Komentar tidak ada!');
               }
            } else {
               Header('Location: ../?err');
               exit;
            }
         break;
         
         default:
         if($user_id && empty($ban[1]) ) {
            if(isset($_POST['submit'])) {
               $text = isset($_POST['text']) ? trim($_POST['text']) : '';
               $error = array();
               if(!$text)
                  $error[] = 'Komentar tidak boleh kosong!';
               elseif (mb_strlen($text) < 4 || mb_strlen($text) > 5000)
                  $error[] = 'Komentar terlalu panjang atau pendek!';
               $flood = functions::antiflood();
               if($flood)
                  $error[] = $lng['error_flood'] . ' ' . $flood . $lng['sec'];
               
               if(empty($error)) {
                  mysql_query("INSERT INTO `animes_comments` SET
                  `refid` = '$id',
                  `time` = '" . time() . "',
                  `user_id` = '" . $user_id . "',
                  `text` = '" . mysql_real_escape_string($text) . "';");
                  mysql_query("UPDATE `users` SET
                     `lastpost` = '" . time() . "'
                     WHERE `id` = '$user_id'
                  ");
                  $_SESSION['error'] = '<div class="gmenu">Komentar ditambahkan</div>';
               } else {
                  $_SESSION['error'] = '<div class="rmenu">' . implode('<br />', $error) . '</div>';
               }
               Header('Location: index.php?act=comments&id=' . $id);
               exit;
            }
            echo $_SESSION['error'] . '<div class="gmenu">
            <form action="index.php?act=comments&amp;id=' . $id . '" method="post"  enctype="multipart/form-data"><div>
            <b>Pesan komentar:</b><br/>
            <textarea rows="3" name="text">' . (!empty($_POST['text']) ? htmlentities($_POST['text'], ENT_QUOTES, 'UTF-8') : '') . '</textarea>
            <br /><span style="font-size: x-small;">Min 2. Max 5000 karakter</span><br />
            <input type="submit" name="submit" value="Kirim"/>
            </div></form>
            </div>';
         }
         echo '<div class="phdr"><h3>Komentar</h3></div>';
         $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_comments` WHERE `refid`='$id';"), 0);
         if($total) {
            if ($total > $kmess)
               echo '<div class="topmenu">' . functions::display_pagination('index.php?act=comments&amp;id=' . $id . '&amp;', $start, $total, $kmess) . '</div>';
            $i = 1;
            $req = mysql_query("SELECT `animes_comments`.*, `animes_comments`.`time` as `mtime`, `animes_comments`.`id` as `mid`, `users`.* FROM `animes_comments` LEFT JOIN `users` ON `animes_comments`.`user_id`=`users`.`id` WHERE `animes_comments`.`refid`='$id' ORDER BY `animes_comments`.`time` DESC LIMIT "
                  . $start . "," . $kmess);
            while (($row = mysql_fetch_assoc($req)) !== false) {
               echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
               $post = $row['text'];
               $post = functions::checkout($post, 1, 1);
               if ($set_user['smileys'])
                  $post = functions::smileys($post, $row['rights'] >= 1 ? 1 : 0);
               if($row['reply'])
                  $post .= '<div class="reply">' . functions::checkout($row['reply'], 1, 1) . '</div>';
               if($rights >= 7) $subtext = '<a href="index.php?act=comments&amp;mod=replay&amp;id=' . $id . '&amp;com=' . $row['mid'] . '">Jawab</a> | <a href="index.php?act=comments&amp;mod=delete&amp;id=' . $id . '&amp;com=' . $row['mid'] . '">Hapus</a>';
               else $subtext = '';
               $text = ' <span class="gray">(' . functions::display_date($row['mtime']) . ')</span>';
               $arg = array(
                  'header' => $text,
                  'body' => $post,
                  'sub' => $subtext
               );
               echo functions::display_user($row, $arg);
               echo '</div>';
               ++$i;
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
            if ($total > $kmess) {
               echo '<div class="topmenu">' . functions::display_pagination('index.php?act=comments&amp;id=' . $id . '&amp;', $start, $total, $kmess) . '</div>';
               echo '<p><form action="index.php" method="get">
               <input type="hidden" name="act" value="comments"/>
               <input type="hidden" name="id" value="' . $id . '"/>
               <input type="text" name="page" size="2"/>
               <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
            }
            
         } else {
            echo '<div class="rmenu">Belum ada komentar,jadilah yg pertamax !</div>';
         }
      }
      echo '<div class="bmenu"><a href="index.php?act=view&amp;id=' . $id . '">Kembali</a></div>';
   } else {
      $textl = 'Blogs';
      require_once('../incfiles/head.php');
      echo functions::display_error('Blogs tidak ada');
   }
} else {
   $textl = 'Blogs';
   require_once('../incfiles/head.php');
}