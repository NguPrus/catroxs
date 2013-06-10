<?php
echo '<div class="phdr" align="center"><b>Update Movies</b></div>';

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM `animes`"), 0);
      if($total) {
         $req = mysql_query("SELECT `id`,`user_id`, `name`, `count`, `text`, `time` FROM `animes` ORDER BY `id` DESC LIMIT 4 ");
         $i = 1;
			while (($row = mysql_fetch_assoc($req)) !== false) {
            echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
            if(file_exists('files/blogs/anime_icon_' . $row['id'] . '.jpg') !== false) {                 
			echo '<a href="../blogs/index.php?act=view&amp;id=' . $row['id'] . '">' . htmlentities($row['name'], ENT_QUOTES, 
			'UTF-8') . '</a> <br />(' . date('d.m.o / H:i', $row['time'] + $sdvigclock * 3600) . ')<br />';
            } else {
               echo '<div class="phdr">(' . date('d.m.o / H:i', $row['time'] + $sdvigclock * 3600) . ')</div>
			   <a href="'.$home.'/blogs/' . functions::seo(htmlentities($row['name'], ENT_QUOTES, 'UTF-8')) . '_p' . $row['id'] . '.html"><div align="center"><font size="3">' . htmlentities($row['name'], ENT_QUOTES, 'UTF-8') . '</font></div></a><br/>';
            }
            $text = $row['text'];
            if(mb_strlen($text) > 100) {
               $str = mb_substr($text, 0, 100);
               $text = mb_substr($str, 0, mb_strrpos($str, ' ')) . '...'; 
            }
           
		   echo '<div class="textx">';
            echo functions::checkout($text, 2, 1);
		   echo '</div>';
            $us = mysql_query("SELECT `id`, `name` FROM `users` WHERE `id` = '{$req['user_id']}'");
              if (mysql_num_rows($us)) {
             $rowuse = mysql_fetch_assoc($us);
             $name_use = $user_id ? '<a href="../users/profile.php?id=' . $rowuse['id'] . '">' . $rowuse['name'] . '</a>' : $rowuse['name'];
              }            
				//echo '<br/>[<a href="../blogs/index.php?act=view&amp;id=' . $row['id'] . '">Komentar : ' . mysql_result(mysql_query("SELECT COUNT(*) FROM `animes_comments` WHERE `refid`= '".$row['id']."' "), 0) . '</a>]';
				echo '</div>';            
            ++$i;
			}
         echo '<div>' . $lng['total'] . ': ' . $total . '</div>';
         }
?>