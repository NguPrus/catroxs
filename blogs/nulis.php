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
if ($rights >= 6)
      $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_cat`"), 0);
      if($total) {
         if (isset ($_POST['submit'])) {
            $time = time();
            $timer = time();
            $date['day'] = date('d', $time);
            $date['year'] = date('o', $time);
            $date['month'] = date('m', $time);
            $date['i'] = date('i', $time);
            $date['h'] = date('h', $time);
            
            $name = isset ($_POST['name']) ? trim($_POST['name']) : '';
            $text = isset ($_POST['text']) ? trim($_POST['text']) : '';
            //$cat = isset($_POST['cat']) ? abs(intval($_POST['cat'])) : 0;
            $day = isset($_POST['day']) && $_POST['day'] >= 1 && $_POST['day'] <= 31 ? abs(intval($_POST['day'])) : 0;
            $month = isset($_POST['month']) && $_POST['month'] >= 1 && $_POST['month'] <= 12 ? abs(intval($_POST['month'])) : 0;
            $year = isset($_POST['year']) && $_POST['year'] >= $date['year'] && $_POST['year'] <= ($date['year'] + 1) ? abs(intval($_POST['year'])) : 0;
            
            $hour = isset($_POST['hour']) && $_POST['hour'] >= 0 && $_POST['hour'] <= 24 ? abs(intval($_POST['hour'])) : 0;
            $minutes = isset($_POST['minutes']) && $_POST['minutes'] >= 0 && $_POST['minutes'] <= 60 ? abs(intval($_POST['minutes'])) : 0;
            
            $error = array();
      
            $error = array();
   if ($rights >= 8)         
            if(empty($name))
               $error[] = 'Judul blogs tidak boleh dikosongkan!';
            else if (mb_strlen($name) < 2 || mb_strlen($name) > 150)
               $error[] = 'Kesalahan dalam panjang judul blogs!';
            if(empty($text))
               $error[] =  'Blogs text tidak boleh kosong!';
            else if (mb_strlen($text) < 2 || mb_strlen($text) > 5000)
               $error[] = 'Kesalahan dalam panjang isi blogs!';
            /*
			if(!$cat)
               $error[] =  'Tidak memilih kategori!';
            */ 
            if(empty($day) || empty($month) || empty($year))
               $timer = 0;
            else 
               $time = strtotime("$day.$month.$year.$hour.$minutes");
            /*
            if(!empty($timer) && $time < time())
               $error[] = 'He ?p??A?!';
            */
			/*
            if(!$error) {
               $data = mysql_query("SELECT * FROM `animes_cat` WHERE `id`='$cat';");
               if(!mysql_num_rows($data))
                  $error[] =  'Kategori tidak ada!';
            }
            */
            if(empty($error)) {
               $q = mysql_query("SELECT * FROM `animes` WHERE `name`='" . mysql_real_escape_string($name) . "' LIMIT 1");
               if (mysql_num_rows($q)) {
                  $error[] = 'Blogs sudah ada!';
               }
            }
            
            if(empty($error)) {
                    $rid = 0;
                    if (!empty($_POST['pf']) && ($_POST['pf'] != '0')) {
                        $pf = intval($_POST['pf']);
                        $rz = $_POST['rz'];
                        $pr = mysql_query("SELECT * FROM `forum` WHERE `refid` = '$pf' AND `type` = 'r'");
                        while ($pr1 = mysql_fetch_array($pr)) {
                            $arr[] = $pr1['id'];
                        }
                        foreach ($rz as $v) {
                            if (in_array($v, $arr)) {
                                mysql_query("INSERT INTO `forum` SET
                                    `refid` = '$v',
                                    `type` = 't',
                                    `time` = '" . time() . "',
                                    `user_id` = '$user_id',
                                    `from` = '$login',
                                    `text` = '$name'
                                ");
                                $rid = mysql_insert_id();
                                mysql_query("INSERT INTO `forum` SET
                                    `refid` = '$rid',
                                    `type` = 'm',
                                    `time` = '" . time() . "',
                                    `user_id` = '$user_id',
                                    `from` = '$login',
                                    `ip` = '" . long2ip($ip) . "',
                                    `soft` = '" . mysql_real_escape_string($agn) . "',
                                    `text` = '" . mysql_real_escape_string($text) . "'
                                ");
								if ($tiento>0) {
								mysql_query("UPDATE `forum` SET
								`tiento` = '$tiento'
								WHERE `id` = '$rid'");
								}
                            }
                        }
                    }
               
               mysql_query("INSERT INTO `animes` SET
               `refid` = '$cat',
               `name` = '" . mysql_real_escape_string($name) . "',
               `text` = '" . mysql_real_escape_string($text) . "',
               `user_id` = '" . $user_id . "',
               `time` = '" . $time . "'");
               
               $img_id = mysql_insert_id();
               
               require_once ('../incfiles/lib/class.upload.php');
               $handle = new upload($_FILES['imagefile']);
               if ($handle->uploaded) {
                  // O@a@??e???
                  $handle->file_new_name_body = 'icon_' . $img_id;
                  $handle->allowed = array('image/jpeg', 'image/jpg', 'image/gif', 'image/png');
                  $handle->file_max_size = 1024 * $set['flsz'];
                  $handle->file_overwrite = true;
                  $handle->image_resize = true;
                  $handle->image_x = 100;
                  $handle->image_ratio_y = true;
                  $handle->image_convert = 'jpg';
                  $handle->process('../files/blogs/');
                  if($handle->processed) {
                     @ chmod('../files/blogs/icon_' . $img_id . '.jpg', 0666);
                  }
                  
                  $handle->file_new_name_body = 'anime_icon_' . $img_id;
                  $handle->image_x = 32;
                  $handle->image_y = 32;
                  $handle->image_convert = 'jpg';
                  $handle->process('../files/blogs/');
                  if($handle->processed) {
                     @ chmod('../files/blogs/anime_icon_' . $img_id . '.jpg', 0666);
                  }
                  
               }
               $handle->clean();
               
               Header('Location: ../blogs/index.php?act=view&id='.$img_id);
               
            } else {
               echo functions::display_error($error, '<a href="nulis.php">Ulang</a>');
            }
            
         } else {
            echo '<form action="nulis.php" method="post" enctype="multipart/form-data">
            <div class="gmenu"><p>
            <b>Judul blogs:</b><br />
            <input type="text" name="name" /><br />
            <small>Min. 2, max. 150 karakter</small><br />
            <b>Blogs text:</b><br />
            <textarea name="text" cols="24" rows="4"></textarea><br />
            <small>Min. 2, max. 5000 character</small><br />';
			// post ke forum
			$fr = mysql_query("SELECT * FROM `forum` WHERE `type` = 'f'");
                echo '<input type="radio" name="pf" value="0" checked="checked" />' . $lng_news['discuss_off'] . '<br />';
                while ($fr1 = mysql_fetch_array($fr)) {
                    echo '<input type="radio" name="pf" value="' . $fr1['id'] . '"/>' . $fr1['text'] . '<select name="rz[]">';
                    $pr = mysql_query("SELECT * FROM `forum` WHERE `type` = 'r' AND `refid` = '" . $fr1['id'] . "'");
                    while ($pr1 = mysql_fetch_array($pr)) {
                        echo '<option value="' . $pr1['id'] . '">' . $pr1['text'] . '</option>';
                    }
                    echo '</select><br/>';
                }
            echo '<br /><b>Tanggal (kosong untuk tanggal saat ini):</b><br />
            <table><tr>
            <td><span style="text-decoration: underline;">Tanggal</span><br />
            <input type="text" value="" size="2" maxlength="2" name="day" />.</td>
            <td><span style="text-decoration: underline;">Bulan</span><br />
            <input type="text" value="" size="2" maxlength="2" name="month" />.</td>
            <td><span style="text-decoration: underline;">Tahun</span><br />
            <input type="text" value="" size="4" maxlength="4" name="year" />-</td>
            <td><span style="text-decoration: underline;">Jam</span><br />
            <input type="text" value="" size="2" maxlength="2" name="hour" />:</td>
            <td><span style="text-decoration: underline;">Menit</span><br />
            <input type="text" value="" size="2" maxlength="2" name="minutes" /></td>
            </tr></table>
            <small>Tangal di sistem ' . date('d.m.o / H:i', time() + $sdvigclock * 3600) . '<br />
            Isi tanggal untuk menampilkan blog selama waktu yang di tentukan</small><br />
            <b>Gambar blogs:</b><br />
            <input type="file" name="imagefile"/><br />
            <small>Format yang diperbolehkan: GIF, JPEG, JPG, PNG max.data 1000 kb<br />
            </small>
            <input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $set['flsz']) . '" />
            </p><p><input type="submit" value="Tambah" name="submit" />
            </p></div></form>';
			
			/*
			echo '<form action="index.php?do=add" method="post"><div class="menu"><div class="textx">' .
                     '<p><h3>' . $lng_news['article_title'] . '</h3>' .
					 '<select name="tiento">' .
					'<option value="0">No Prefix</option>' .
					'<option value="1">Sharing</option>' .
					'<option value="2">Help</option>' .
					'<option value="3">Info</option>' .
					'<option value="4">Discuss</option>' .
					'<option value="5">Ask</option>' .
					'</select>' .
                     '<input type="text" name="name"/></p>' .
                     '<p><h3>' . $lng['text'] . '</h3>' .
                     '<textarea rows="' . $set_user['field_h'] . '" name="text"></textarea></p>' .
                     '<p><h3>' . $lng_news['discuss'] . '</h3>';
                $fr = mysql_query("SELECT * FROM `forum` WHERE `type` = 'f'");
                echo '<input type="radio" name="pf" value="0" checked="checked" />' . $lng_news['discuss_off'] . '<br />';
                while ($fr1 = mysql_fetch_array($fr)) {
                    echo '<input type="radio" name="pf" value="' . $fr1['id'] . '"/>' . $fr1['text'] . '<select name="rz[]">';
                    $pr = mysql_query("SELECT * FROM `forum` WHERE `type` = 'r' AND `refid` = '" . $fr1['id'] . "'");
                    while ($pr1 = mysql_fetch_array($pr)) {
                        echo '<option value="' . $pr1['id'] . '">' . $pr1['text'] . '</option>';
                    }
                    echo '</select><br/>';
                }
                echo '</p></div></div><div class="bmenu">' .
                     '<input type="submit" name="submit" value="' . $lng['save'] . '"/>' .
                     '</div></form>' .
                     '<p><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
			*/
            
         }
      } else {
         echo '<div class="rmenu"><center>Maaf Kamu tidak memiliki hak akses ke laman ini, akses hanya untuk admin utama (SV)</center></div>';
         
      }
      echo '<div class="phdr"><a href="index.php">Kembali ke Blogs</a></div>';
   
      
require_once('../incfiles/end.php');
?>