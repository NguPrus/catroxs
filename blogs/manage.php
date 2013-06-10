<?php
/*
////////////////////////////////////////////////////////////////////////////////
      ''Script For Update Anime On Wap Forum JCMS''
Script For : JohnCMS v4.4 (http://johncms.com)
News Mod To Blogs By : Gobelz (http://sakahayang.se.gp)
Remodif For Wap Update Anime By : Farid_Ryuzetsu (http://cybersubs.tk)

Enjoy This bro...!!!
NB: Dilarang Memperjualbelikan Script ini Karena ini gratis
TTD : Farid_Ryuzetsu (Cyber Indonesia)

////////////////////////////////////////////////////////////////////////////////
*/
define('_IN_JOHNCMS', 1);
require_once('../incfiles/core.php');
require_once('../incfiles/head.php');
if ($rights >= 8)
if (!$user_id) {
echo functions::display_error('Hanya untuk member,masbrow!');
require('../incfiles/end.php');
exit;
}
$flood = functions::antiflood();
if ($flood) {
echo functions::display_error('Tidak diperbolehkan mengulang pesan berkali kali tunggu '.$flood.' detik <br />');
echo '<div class="menu"><a href="index.php">Back</a></div>';
require('../incfiles/end.php');
exit;
}
if ($rights == 8 || $rights >= 8) {

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
            return $lng_news['one_minute'];
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
if ($rights >= 8)
echo '<div class="phdr"><a href="index.php"><b>Blog</b></a> | Kategori Blogs </div>';
switch ($act) {
    case 'add' :
      if (isset ($_POST['submit'])) {
         
         $name = isset ($_POST['name']) ? trim($_POST['name']) : '';
         
         $error = array();
         
         if(empty($name))
            $error[] = 'Nama kategori Kosong!';
         
         else if (mb_strlen($name) < 2 || mb_strlen($name) > 50)
            $error[] = 'Kesalahan dalam panjang nama kategori!';
         
         if(empty($error)) {
            $q = mysql_query("SELECT * FROM `animes_cat` WHERE `name`='" . mysql_real_escape_string($name) . "' LIMIT 1");
                if (mysql_num_rows($q)) {
               $error[] =  'Kategori sudah ada!';
            }
         }
         if(empty($error)) {
            $req = mysql_query("SELECT `realid` FROM `animes_cat` ORDER BY `realid` DESC LIMIT 1");
                if (mysql_num_rows($req)) {
                    $res = mysql_fetch_assoc($req);
                    $sort_id = $res['realid'] + 1;
                } else {
                    $sort_id = 1;
                }
            
            mysql_query("INSERT INTO `animes_cat` SET
            `realid` = '$sort_id',
                `name` = '" . mysql_real_escape_string($name) . "'");
            $img_id = mysql_insert_id();
            
            require_once ('../incfiles/lib/class.upload.php');
            $handle = new upload($_FILES['imagefile']);
            if ($handle->uploaded) {
               // Обрабатываем фото
               $handle->file_new_name_body = 'ico_cat_' . $img_id;
               $handle->allowed = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png');
               $handle->file_max_size = 1024 * $set['flsz'];
               $handle->file_overwrite = true;
               $handle->image_resize = true;
               $handle->image_x = 16;
               $handle->image_y = 16;
               $handle->image_convert = 'jpg';
               $handle->process('../files/blogs/');
               if($handle->processed)
                  @ chmod('../files/blogs/ico_cat_' . $img_id . '.jpg', 0666);
               $handle->clean();
            }
            
            Header('Location: manage.php');
            
         } else {
            echo functions::display_error($error, '<a href="manage.php?act=add">Ulangi</a>');
         }
         
      } else {
         echo '<form action="manage.php?act=add" method="post" enctype="multipart/form-data">
         <div class="gmenu"><p>
         <b>Kategori:</b><br />
         <input type="text" name="name" /><br /><small>Maksimal 50 Karakter</small><br />
         <b>Ikon kategori:</b><br />
         <input type="file" name="imagefile"/><br />
         <small>Format yang diperbolehkan: GIF, JPEG, JPG, PNG, maksimal 300kb icon diperkecil menjadi 16X16px</small><br />
         <input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $set['flsz']) . '" />
         </p><p><input type="submit" value="Tambah" name="submit" />
         </p></div></form>';
      }
      echo '<div class="phdr"><a href="manage.php">Manage Blogs</a></div>';
      break;
   case 'up' :
      ////////////////////////////////////////////////////////////
        // Displacement on one position upwards                      //
        ////////////////////////////////////////////////////////////
      if ($id) {
         $req = mysql_query("SELECT * FROM `animes_cat` WHERE `id` = '$id' LIMIT 1");
            if (mysql_num_rows($req)) {
            $res1 = mysql_fetch_assoc($req);
            $sort = $res1['realid'];
            $req = mysql_query("SELECT * FROM `animes_cat` WHERE `realid` < '$sort' ORDER BY `realid` DESC LIMIT 1");
            if (mysql_num_rows($req)) {
               $res = mysql_fetch_assoc($req);
               $id2 = $res['id'];
               $sort2 = $res['realid'];
               mysql_query("UPDATE `animes_cat` SET `realid` = '$sort2' WHERE `id` = '$id'");
               mysql_query("UPDATE `animes_cat` SET `realid` = '$sort' WHERE `id` = '$id2'");
            }
         }
      }
        header('Location: manage.php');
      break;
   case 'down' :
      ////////////////////////////////////////////////////////////
        // Displacement on one position downwards                 //
        ////////////////////////////////////////////////////////////
        if ($id) {
            $req = mysql_query("SELECT `realid` FROM `animes_cat` WHERE `id` = '$id' LIMIT 1");
            if (mysql_num_rows($req)) {
                $res1 = mysql_fetch_assoc($req);
                $sort = $res1['realid'];
                $req = mysql_query("SELECT `id`, `realid` FROM `animes_cat` WHERE `realid` > '$sort' ORDER BY `realid` ASC LIMIT 1");
                if (mysql_num_rows($req)) {
                    $res = mysql_fetch_assoc($req);
                    $id2 = $res['id'];
                    $sort2 = $res['realid'];
                    mysql_query("UPDATE `animes_cat` SET `realid` = '$sort2' WHERE `id` = '$id'");
                    mysql_query("UPDATE `animes_cat` SET `realid` = '$sort' WHERE `id` = '$id2'");
                }
            }
        }
      header('Location: manage.php');
      break;
   case 'edit' :
      if($id) {
         $q = mysql_query("SELECT * FROM `animes_cat` WHERE `id`='$id' LIMIT 1");
         if (mysql_num_rows($q)) {
            
            $row = mysql_fetch_assoc($q);
            
            if (isset ($_POST['submit'])) {
               
               $name = isset ($_POST['name']) ? trim($_POST['name']) : '';
               
               $error = array();
               
               if(empty($name))
                  $error[] = 'Nama kategori kosong!';
               else if (mb_strlen($name) < 2 || mb_strlen($name) > 50)
                  $error[] = 'Kesalahan dalam panjang nama kategori!';
               
               if(empty($error)) {
                  if($name != $row['name']) {
                     $q = mysql_query("SELECT * FROM `animes_cat` WHERE `name`='" . mysql_real_escape_string($name) . "' LIMIT 1");
                     if (mysql_num_rows($q)) {
                        $error[] =  'Kategori sudah ada!';
                     }
                  }
               }
               if(empty($error)) {
                  mysql_query("UPDATE `animes_cat` SET
                  `name` = '" . mysql_real_escape_string($name) . "' WHERE `id`='$id' LIMIT 1");
                  
                  require_once ('../incfiles/lib/class.upload.php');
                  $handle = new upload($_FILES['imagefile']);
                  if ($handle->uploaded) {
                     // Обрабатываем фото
                     $handle->file_new_name_body = 'ico_cat_' . $id;
                     $handle->allowed = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png');
                     $handle->file_max_size = 1024 * $set['flsz'];
                     $handle->file_overwrite = true;
                     $handle->image_resize = true;
                     $handle->image_x = 16;
                     $handle->image_y = 16;
                     $handle->image_convert = 'jpg';
                     $handle->process('../files/blogs/');
                     if($handle->processed)
                        @ chmod('../files/blogs/ico_cat_' . $id . '.jpg', 0666);
                     $handle->clean();
                  }
                  
                  Header('Location: manage.php');
                  
               } else {
                  echo functions::display_error($error, '<a href="manage.php?act=edit&amp;id='.$id.'">Ulang</a>');
               }
               
            } else {
               echo '<form action="manage.php?act=edit&amp;id=' . $id . '" method="post" enctype="multipart/form-data">
               <div class="gmenu"><p>
               <b>Kategori:</b><br />
               <input type="text" name="name" value="' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '"/><br /><small>Min. 2, Max. 50 Character</small><br />
               <b>Kategori Icon:</b><br />
               <input type="file" name="imagefile"/><br />
               <small>Format yang diperbolehkan: GIF, JPEG, JPG, PNG, maksimal 300kb icon diperkecil menjadi 16X16px</small><br />
               <input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $set['flsz']) . '" />
               </p><p><input type="submit" value="Edit" name="submit" />
               </p></div></form>';
            }
         } else {
            echo functions::display_error('Kategori tidak ada');
         }
      } else {
         echo functions::display_error('Kategori tidak dipilih');
      }
      echo '<div class="phdr"><a href="manage.php">Manage Blogs</a></div>';
      break;
   case 'delete' :
      if($id) {
         $q = mysql_query("SELECT * FROM `animes_cat` WHERE `id`='$id' LIMIT 1");
         if (mysql_num_rows($q)) {
            if (isset ($_POST['submit'])) {
               $cn = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes` WHERE `refid` = '$id'"), 0);
               if($cn) {
                  $reqs = mysql_query("SELECT * FROM `animes` WHERE `refid`='$id'");
                  $massdel = array();
                  while (($row = mysql_fetch_assoc($reqs)) !== false) {
                     $massdel[] = $row['id'];
                     if(file_exists('../files/blogs/icon_' . $row['id'] . '.jpg') !== false) {
                        unlink('../files/blogs/icon_' . $row['id'] . '.jpg');
                        unlink('../files/blogs/anime_icon_' . $row['id'] . '.jpg');
                     }
                  }
                  if($massdel) {
                     $result = implode(',', $massdel);
                     mysql_query("DELETE FROM `animes` WHERE `id` IN (" . $result . ")");
                     mysql_query("DELETE FROM `animes_comments` WHERE `refid` IN (" . $result . ")");
                  }
               }
               mysql_query("DELETE FROM `animes_cat` WHERE `id` = '$id'");
               if(file_exists('../files/blogs/ico_cat_' . $id . '.jpg') !== false)
                  unlink('../files/blogs/ico_cat_' . $id . '.jpg');
               Header('Location: manage.php');
            } else {
if ($rights >= 8)               
echo '<form action="manage.php?act=delete&amp;id=' . $id . '" method="post">
               <div class="gmenu"><p>
               Anda yakin menghapus kategori ini
               </p><p><input type="submit" value="Hapus" name="submit" />
               </p></div></form>';
            }
         } else {
            echo functions::display_error('Kategori tidak ada');
         }
      } else {
         echo functions::display_error('Kategori tidak dipilih');
      }
      echo '<div class="phdr"><a href="manage.php">Manage Blogs</a></div>';
      break;
   case 'delnews' :
      if($id) {
         if(mysql_result(mysql_query("SELECT COUNT(*) FROM `animes` WHERE `id`='$id'"), 0) != 0) {
            if (isset ($_POST['submit'])) {
               if(file_exists('../files/blogs/icon_' . $id . '.jpg') !== false) {
                  unlink('../files/blogs/icon_' . $id . '.jpg');
                  unlink('../files/blogs/anime_icon_' . $id . '.jpg');
               }
               mysql_query("DELETE FROM `animes` WHERE `id`='$id'");
               mysql_query("DELETE FROM `animes_comments` WHERE `refid`='$id'");
               
               echo '<div class="rmenu">Blog dihapus</div>';
            } else {
               echo '<form action="manage.php?act=delnews&amp;id=' . $id . '" method="post">
               <div class="gmenu"><p>
               Anda yakin akan menghapus blog ini
               </p><p><input type="submit" value="Hapus" name="submit" />
               </p></div></form>';
            }
         } else {
            echo functions::display_error('Blog tidak ada');
         }
      } else {
         echo functions::display_error('Blog tidak dipilih');
      }
   break;
   case 'clear' :
      echo '<div class="phdr">Hapus Blog</div>';
            if (isset ($_POST['submit'])) {
                $cl = isset ($_POST['cl']) ? intval($_POST['cl']) : '';
                switch ($cl) {
                    case '1' :
                        $reqs = mysql_query("SELECT * FROM `animes` WHERE `time`<='" . ($realtime - 604800) . "'");
                  $massdel = array();
                  while (($row = mysql_fetch_assoc($reqs)) !== false) {
                     $massdel[] = $row['id'];
                     if(file_exists('../files/blogs/icon_' . $row['id'] . '.jpg') !== false) {
                        unlink('../files/blogs/icon_' . $row['id'] . '.jpg');
                        unlink('../files/blogs/anime_icon_' . $row['id'] . '.jpg');
                     }
                  }
                  if($massdel) {
                     $result = implode(',', $massdel);
                     mysql_query("DELETE FROM `animes` WHERE `id` IN (" . $result . ")");
                     mysql_query("DELETE FROM `animes_comments` WHERE `refid` IN (" . $result . ")");
                     mysql_query("OPTIMIZE TABLE `animes`, `animes_comments`;");
                  }
                        echo '<p class="rmenu">Anda yakin menghapus blogs selama 1 minggu</p>';
                        break;

                    case '2' :
                        // Проводим полную очистку
                  $reqs = mysql_query("SELECT * FROM `animes` WHERE `time`<='" . ($realtime - 604800) . "'");
                  while (($row = mysql_fetch_assoc($reqs)) !== false) {
                     if(file_exists('../files/blogs/icon_' . $row['id'] . '.jpg') !== false) {
                        unlink('../files/blogs/icon_' . $row['id'] . '.jpg');
                        unlink('../files/blogs/anime_icon_' . $row['id'] . '.jpg');
                     }
                  }
                  mysql_query("TRUNCATE TABLE `animes`");
                  mysql_query("TRUNCATE TABLE `animes_comments`");
                        
                        echo '<p class="rmenu">Anda yakin menghapus semua blog</p>';
                        break;

                    default :
                        // Чистим сообщения, старше 1 месяца
                  $reqs = mysql_query("SELECT * FROM `animes` WHERE `time`<='" . ($realtime - 2592000) . "'");
                  $massdel = array();
                  while (($row = mysql_fetch_assoc($reqs)) !== false) {
                     $massdel[] = $row['id'];
                     if(file_exists('../files/blogs/icon_' . $row['id'] . '.jpg') !== false) {
                        unlink('../files/blogs/icon_' . $row['id'] . '.jpg');
                        unlink('../files/blogs/anime_icon_' . $row['id'] . '.jpg');
                     }
                  }
                  if($massdel) {
                     $result = implode(',', $massdel);
                     mysql_query("DELETE FROM `animes` WHERE `id` IN (" . $result . ")");
                     mysql_query("DELETE FROM `animes_comments` WHERE `refid` IN (" . $result . ")");
                     mysql_query("OPTIMIZE TABLE `animes`, `animes_comments`;");
                  }
                        echo '<p class="rmenu">Blogs telah hapus dalam rentang waktu 1 bulan.</p>';
                }
            } else {
                echo '<div class="gmenu"><p><u>Metode penghapusan :</u>';
                echo '<form id="clean" method="post" action="manage.php?act=clear"><div>';
                echo '<input type="radio" name="cl" value="0" checked="checked" />1 Bulan<br />';
                echo '<input type="radio" name="cl" value="1" />1 Minggu<br />';
                echo '<input type="radio" name="cl" value="2" />Semuanya<br />';
                echo '<input type="submit" name="submit" value="Hapus" /></div>';
                echo '</form></p></div>';
            }
      echo '<div class="phdr"><a href="manage.php">Manage Blogs</a></div>';
      break;
      
   case 'ico' :
      if($id) {
         $q = mysql_query("SELECT * FROM `animes_cat` WHERE `id`='$id' LIMIT 1");
         if (mysql_num_rows($q)) {
            if (isset ($_POST['submit'])) {
               if(file_exists('../files/blogs/ico_cat_' . $id . '.jpg') !== false)
                  unlink('../files/blogs/ico_cat_' . $id . '.jpg');
               Header('Location: manage.php');
            } else {
               echo '<form action="manage.php?act=ico&amp;id=' . $id . '" method="post" enctype="multipart/form-data">
               <div class="gmenu"><p>
               Anda yakin menghapus icon ini
               </p><p><input type="submit" value="Hapus" name="submit" />
               </p></div></form>';
            }
         } else {
            echo functions::display_error('Kategori tidak ada');
         }
      } else {
         echo functions::display_error('Kategori tidak dipilih');
      }
      echo '<div class="phdr"><a href="manage.php?act=news">Manage Blogs</a></div>';
      break;
   
   case 'list' :
      $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes`"), 0);
      if($total) {
         $req = mysql_query("SELECT `id`, `name`, `text`, `time` FROM `animes` ORDER BY `time` DESC LIMIT "
                    . $start . "," . $kmess);
         $i = 1;
         while (($row = mysql_fetch_assoc($req)) !== false) {
            echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
            if(file_exists('../files/blogs/anime_icon_' . $row['id'] . '.jpg') !== false) {
               echo '<table cellpadding="0" cellspacing="0" width="100%"><tr><td width="32">';
               echo '<img style="margin: 0 0 -3px 0;border: 0px;" src="../files/blogs/anime_icon_' . $row['id'] . '.jpg" alt="" width="32" height="32"/>&#160;';
               echo '</td><td>';
               echo '<a href="../blogs/index.php?act=view&amp;id=' . $row['id'] . '">' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '</a> <br />(' . date('d.m.o / H:i', $row['time'] + $sdvigclock * 3600) . ')<br />';
               echo '</td></tr></table>';
            } else {
               echo '<a href="../blogs/index.php?act=view&amp;id=' . $row['id'] . '">' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '</a> (' . date('d.m.o / H:i', $row['time'] + $sdvigclock * 3600) . ')<br />';
            }
            echo '<div class="sub"></div>';
            $text = $row['text'];
            if(mb_strlen($text) > 100) {
               $str = mb_substr($text, 0, 100);
               $text = mb_substr($str, 0, mb_strrpos($str, ' ')) . '...';
            }
            echo functions::checkout($text, 2, 1);
            if($row['time'] > time())
               echo '<div class="sub func">Waktu tersisa untuk disembunyikan: ' . timer($row['time'] - time()) . '</div>';
            echo '</div>
            <div class="bmenu"><a href="manage.php?act=newsedit&amp;id=' . $row['id'] . '">Edit Blogs</a> | <a href="manage.php?act=delnews&amp;id=' . $row['id'] . '">Delete</a></div>';
            ++$i;
         }
         echo '<div class="phdr">Total Blogs: ' . $total . '</div>';
         if ($total > $kmess) {
            echo '<p>' . functions::display_pagination('index.php?act=animes&amp;mod=list&amp;', $start, $total, $kmess) . '</p>';
            echo '<p><form action="index.php" method="get">
            <input type="hidden" name="act" value="mod_news"/>
            <input type="hidden" name="mod" value="list"/>
            <input type="text" name="page" size="2"/>
            <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
         }
      } else {
         echo '<div class="rmenu">Blogs kosong</div>';
      }
      
      echo '<div class="phdr"><a href="manage.php">Manage Blogs</a></div>';
      break;

   
   
   
   case 'newsedit' :
      if($id) {
         $q = mysql_query("SELECT * FROM `animes` WHERE `id`='$id' LIMIT 1");
         if (mysql_num_rows($q)) {
            $row = mysql_fetch_assoc($q);
            
            $day = date('d', $row['time']);
            $year= date('o', $row['time']);
            $month = date('m', $row['time']);
            $minutes = date('i', $row['time']);
            $hour = date('H', $row['time']);
            
            if (isset ($_POST['submit'])) {
               $time = time();
               $timer = time();
               
               $date['day'] = date('d', $time);
               $date['year'] = date('o', $time);
               $date['month'] = date('m', $time);
               $date['i'] = date('i', $time);
               $date['h'] = date('H', $time);
               
               $name = isset ($_POST['name']) ? trim($_POST['name']) : '';
               $text = isset ($_POST['text']) ? trim($_POST['text']) : '';
               $cat = isset($_POST['cat']) ? abs(intval($_POST['cat'])) : 0;
               $day = isset($_POST['day']) && $_POST['day'] >= 1 && $_POST['day'] <= 31 ? abs(intval($_POST['day'])) : $date['day'];
               $month = isset($_POST['month']) && $_POST['month'] >= 1 && $_POST['month'] <= 12 ? abs(intval($_POST['month'])) : $date['month'];
               $year = isset($_POST['year']) && $_POST['year'] >= $date['year'] && $_POST['year'] <= ($date['year'] + 1) ? abs(intval($_POST['year'])) : $date['year'];
               
               $hour = isset($_POST['hour']) && $_POST['hour'] >= 0 && $_POST['hour'] <= 24 ? abs(intval($_POST['hour'])) : $date['h'];
               $minutes = isset($_POST['minutes']) && $_POST['minutes'] >= 0 && $_POST['minutes'] <= 60 ? abs(intval($_POST['minutes'])) : $date['i'];
               
               $error = array();
         
               $error = array();
               
               if(empty($name))
                  $error[] =  'Judul blogs tidak boleh dikosongkan!';
               else if (mb_strlen($name) < 2 || mb_strlen($name) > 150)
                  $error[] =  'Kesalahan dalam panjang judul blogs!';
               if(empty($text))
                  $error[] = 'Isi blogs tidak boleh dikosongkan!';
               else if (mb_strlen($text) < 2 || mb_strlen($text) > 5000)
                  $error[] = 'Kesalahan dalam panjang isi blogs!';
               if(!$cat)
                  $error[] = 'Kesalahan dalam memilih kategori blogs!';
                  
               if(empty($day) || empty($month) || empty($year))
                  $timer = 0;
               else 
                  $time = strtotime("$day.$month.$year.$hour.$minutes");
                  
               /*if(!empty($timer) && $time < time() && $time != $row['time']) {
                  $error[] = 'Не верная дата!';
               }*/
               if(!$error) {
                  $data = mysql_query("SELECT * FROM `animes_cat` WHERE `id`='$cat';");
                  if(!mysql_num_rows($data))
                     $error[] = 'Kategori tidak ada!';
               }
               
               if(empty($error)) {
                  if($name != $row['name']) {
                     $q = mysql_query("SELECT * FROM `animes` WHERE `name`='" . mysql_real_escape_string($name) . "' LIMIT 1");
                     if (mysql_num_rows($q)) {
                        $error[] =  'Blogs sudah ada!';
                     }
                  }
               }
               
               if(empty($error)) {
                  
                  mysql_query("UPDATE `animes` SET
                  `refid` = '$cat',
                  `name` = '" . mysql_real_escape_string($name) . "',
                  `text` = '" . mysql_real_escape_string($text) . "',
                  `user_id` = '" . $user_id . "',
                  `time` = '" . $time . "' WHERE `id`='$id'");
                  
                  //$img_id = mysql_insert_id();
                  
                  require_once ('../incfiles/lib/class.upload.php');
                  $handle = new upload($_FILES['imagefile']);
                  if ($handle->uploaded) {
                     
                     // Обрабатываем фото
                     $handle->file_new_name_body = 'icon_' . $id;
                     $handle->allowed = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png');
                     $handle->file_max_size = 1024 * $set['flsz'];
                     $handle->file_overwrite = true;
                     $handle->image_resize = true;
                     $handle->image_x = 100;
                     $handle->image_ratio_y = true;
                     $handle->image_convert = 'jpg';
                     $handle->process('../files/blogs/');
                     if($handle->processed) {
                        @ chmod('../files/blogs/icon_' . $id . '.jpg', 0666);
                     }
                     
                     $handle->file_new_name_body = 'anime_icon_' . $id;
                     $handle->allowed = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png');
                     $handle->file_max_size = 1024 * $set['flsz'];
                     $handle->file_overwrite = true;
                     $handle->image_resize = true;
                     $handle->image_x = 32;
                     $handle->image_y = 32;
                     $handle->image_convert = 'jpg';
                     $handle->process('../files/blogs/');
                     if($handle->processed) {
                        @ chmod('../files/blogs/anime_icon_' . $id . '.jpg', 0666);
                     }
                  }
                  $handle->clean();
                  
                  Header('Location: ../blogs/index.php?act=view&id='.$id);
                  
               } else {
                  echo functions::display_error($error, '<a href="manage.php?act=news">Ulangi</a>');
               }
               
            } else {
               
               echo '<form action="manage.php?act=newsedit&amp;id=' . $id . '" method="post" enctype="multipart/form-data">
               <div class="gmenu"><p>
               <b>Judul Blogs:</b><br />
               <input type="text" name="name" value="' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '" /><br />
               <small>Min. 2, max. 150 karakter</small><br />
               <b>Blogs text:</b><br />
               <textarea name="text" cols="24" rows="4">' . htmlentities($row['text'], ENT_QUOTES, 'UTF-8') . '</textarea><br />
               <small>Min. 2, max. 5000 character</small><br />
               <b>Pilih Kategori:</b><br />
               <select name="cat">';
               $req = mysql_query("SELECT * FROM `animes_cat` ORDER BY `realid` ASC");
               while (($rows = mysql_fetch_assoc($req)) !== false) {
                  echo '<option value="' . $rows['id'] . '"' . ($rows['id'] == $row['refid'] ? ' selected="selected"':'') . '>' . htmlentities($rows['name'], ENT_QUOTES, 'UTF-8') . '</option>';
               }
               echo '</select><br /><b>Tanggal:</b><br />
               <table><tr>
               <td><span style="text-decoration: underline;">Tanggal:</span><br />
               <input type="text" value="' . $day . '" size="2" maxlength="2" name="day" />.</td>
               <td><span style="text-decoration: underline;">Bulan</span><br />
               <input type="text" value="' . $month . '" size="2" maxlength="2" name="month" />.</td>
               <td><span style="text-decoration: underline;">Tahun</span><br />
               <input type="text" value="' . $year . '" size="4" maxlength="4" name="year" />-</td>
               <td><span style="text-decoration: underline;">Jam</span><br />
               <input type="text" value="' . $hour . '" size="2" maxlength="2" name="hour" />:</td>
               <td><span style="text-decoration: underline;">Menit</span><br />
               <input type="text" value="' . $minutes . '" size="2" maxlength="2" name="minutes" /></td>
               </tr></table>
               <small>Tangal di sistem  ' . date('d.m.o / H:i', time() + $sdvigclock * 3600) . '<br />
               Isi tanggal untuk menampilkan blog selama waktu yang di tentukan</small><br />
               <b>Gambar blogs::</b><br />
               <input type="file" name="imagefile"/><br />
               <small>Format yang diperbolehkan: GIF, JPEG, JPG, PNG max.data 1000 kb<br />
               </small><br />
               <input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $set['flsz']) . '" />
               </p><p><input type="submit" value="Edit" name="submit" />
               </p></div></form>';
            }
         } else {
            echo '<div class="rmenu">Blogs Tidak ada</div>';
         }
      } else {
         echo '<div class="rmenu">Blogs tidak dipilih</div>';
      }
      echo '<div class="phdr"><a href="manage.php">Manage Blogs</a></div>';
      break;
   
   
   
   default:
   $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_cat`"), 0);
   if($total) {
      $req = mysql_query("SELECT `id`, `name` FROM `animes_cat`
      ORDER BY `realid` ASC LIMIT "
                 . $start . "," . $kmess);
      $i = 1;
      while (($row = mysql_fetch_assoc($req)) !== false) {
         echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
         if(file_exists('../files/blogs/ico_cat_' . $row['id'] . '.jpg') !== false)
            echo '<a href="index.php?act=animes&amp;mod=ico&amp;id=' . $row['id'] . '"><img style="margin: 0 0 -3px 0;border: 0px;" src="../files/blogs/ico_cat_' . $row['id'] . '.jpg" alt="" width="16" height="16"/></a>&#160;';
         echo htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . ' <a href="../blogs/index.php?id=' . $row['id'] . '">&raquo;</a>';
         echo '<div class="sub">
         <a href="manage.php?act=up&amp;id=' . $row['id'] . '">Keatas</a> | <a href="manage.php?act=down&amp;id=' . $row['id'] . '">Kebawah</a> | <a href="manage.php?act=edit&amp;id=' . $row['id'] . '">Edit</a> | <a href="manage.php?act=delete&amp;id=' . $row['id'] . '">Hapus</a>
         </div>';
         echo '</div>';
         ++$i;
      }
      echo '<div class="phdr">Total Kategori: ' . $total . '</div>';
      if ($total > $kmess) {
         echo '<p>' . functions::display_pagination('manage.php?', $start, $total, $kmess) . '</p>';
         echo '<p><form action="index.php" method="get">
         <input type="hidden" name="act" value="mod_news"/>
         <input type="text" name="page" size="2"/>
         <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
      }
   } else {
      echo '<div class="rmenu">Tidak ada kategori</div>';
   }
   echo '<div class="gmenu"><form action="manage.php?act=add" method="post"><input type="submit" value="Tambah kategori" /></form></div>';
   if($total) {
      echo '<div class="gmenu"><form action="nulis.php" method="post"><input type="submit" value="Tambah Blogs" /></form></div>';
      echo '<div class="bmenu"><a href="manage.php?act=list">List Anime</a></div>';
      echo '<div class="bmenu"><a href="manage.php?act=clear">Hapus Blog</a></div>';
   }
   
}
}
else { echo '<div class="rmenu"><center><b>Maaf, akses hanya untuk admin!</b></center></div>';
}
echo '<p class="menu"><a href="../blogs/">Kembali</a></p>';
require_once('../incfiles/end.php');
?>