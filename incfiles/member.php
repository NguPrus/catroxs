<?php
////////////////////////////////////
//                                //
// Coding    agssbuzz@catroxs.org //
// versi     johncms 4.4          //
//                                //
////////////////////////////////////


$online = array();

//menentukan query
      $users = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > '" . (time() - 600) . "'"), 0);
      $guests = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > '" . (time() - 600) . "'"), 0);
      if ($users >= 1)
         echo '' . $users . ' User On : ';
      else
         echo 'No User On';
      $hadir = @mysql_query("SELECT * FROM `users` WHERE `lastdate` >= '" . intval(time() - 600) . "' ORDER BY RAND() LIMIT 35 ;");
      $hitung = mysql_num_rows($hadir);
      while ($notal = mysql_fetch_array($hadir))
//lokasin user online
{
            $where = explode(",", $notal['place']);
            switch ($where[0]) {
                case 'forumfiles' :
                    $place = '<a href="../forum/index.php?act=files">Files Forum</a>';
                    break;
                case 'forumwho' :
                    $place = '<a href="../forum/index.php?act=who">Who in forum</a>';
                    break;
                case 'anketa' :
                    $place = '<a href="../users/profile.php">Profil</a>';
                    break;
                case 'userset' :
                    $place = '<a href="../str/my_set.php">Pengaturan</a>';
                    break;
                case 'trns' :
                    $place = '<a href="../translate/index.php">translate</a>';
                    break;
                case 'online' :
                    $place = '<a href="../str/online.php">List online</a>';
                    break;
                case 'privat' :
                case 'kujum' :
                    $place = '<a href="../index.php?act=cab">mainmenu</a>';
                    break;
                case 'grabermaker' :
                    $place = '<a href="../grabermaker/">Grabber Maker</a>';
                    break;
                case 'read' :
                    $place = '<a href="../read.php">FAQ</a>';
                    break;
                case 'load' :
                    $place = '<a href="../download/index.php">Download</a>';
                    break;
                case 'gallery' :
                    $place = '<a href="../gallery/index.php">Gallery</a>';
                    break;
                case 'quickchat' :
                    $place = '<a href="../str/qchat.php">Shout</a>';
                    break;
                case 'FAQ' :
                    $place = '<a href="../pages/faq.php">Smiles</a>';
                    break;
                case 'forum' :
                case 'forums' :
                    $place = '<a href="../forum/index.php">Forum</a>/<a href="../forum/index.php?act=who">&gt;&gt;</a>';
                    break;
                case 'chat' :
                    $place = '<a href="../chat/index.php">Chat</a>';
                    break;
                case 'guest' :
                    $place = '<a href="../guestbook/index.php">Bukutamu</a>';
                    break;
                case 'Blogs' :
                    $place = '<a href="../blogs/">Blogs</a>';
                    break;
                case 'Free Host' :
                    $place = '<a href="../freehost/index.php">Free Host</a>';
                    break;

                case 'Mobile FTP' :
                    $place = '<a href="../ftp/index.php">Mobile FTP</a>';
                    break;
 case 'lib' :
                    $place = '<a href="../library/index.php">Library</a>';
                    break;
                case 'mainpage' :
                default :
                    $place = '<a href="../index.php">Home</a>';
                    break;
         }
  //menampilkan user on  
  $online[]="<a href='/users/profile.php?user=" . $notal['id'] . "'>" . ($notal[name]) . "</a>[<span class='red'>$place</span>]";
}
         //coding dibawah akan membatasi tampilan jumlah ol di halaman
            if (($users >= 1) && ($users <= 35)){
            echo implode(', ',$online).'.';
            } elseif ($users >= 36){
            $minus = $users - 35;
            echo implode(', ',$users).'.';       
            echo ' <a href="' . core::$system_set['homeurl'] . '/users/index.php?act=online">' . $minus . ' more</a>';
            }
			
//menampilkan jumlah pengunjung ol
      echo '<br/>Guest: ';
      if ($guests >= 1)
         echo ' <a href="' . core::$system_set['homeurl'] . '/users/index.php?act=online&amp;mod=guest"> ' . $guests . '</a><br/>';
      else
      echo '0<br/>';
?>