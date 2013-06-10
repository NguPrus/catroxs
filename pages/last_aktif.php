<?php
/*
-----------------------------------------------------------------
Last post
-----------------------------------------------------------------
*/

echo '<div class="phdr"><b>Latest Topic</b></div>';
$req = mysql_query("SELECT * FROM `forum` WHERE `type`='t' AND `close` != '1' ORDER BY `time` DESC LIMIT 5");
$i = 0;
while($res = mysql_fetch_array($req)) {
	echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
	echo '<div class="submenu">';
	$q3 = mysql_query("SELECT `id`, `refid`, `text` FROM `forum` WHERE `type`='r' AND `id`='" . $res['refid'] . "'");
	$razd = mysql_fetch_array($q3);
	$q4 = mysql_query("SELECT `text` FROM `forum` WHERE `type`='f' AND `id`='" . $razd['refid'] . "'");
	$frm = mysql_fetch_array($q4);
	$colmes = mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
	$colmes1 = mysql_num_rows($colmes);
	$cpg = ceil($colmes1 / $kmess);
	$nick = mysql_fetch_array($colmes);
	if ($res['edit'])
		echo '<img src="images/tz.gif" alt=""/>';
	elseif ($res['close'])
		echo '<img src="images/dl.gif" alt=""/>';
	else
		echo '<img src="images/np.gif" alt=""/>';
	if ($res['realid'] == 1)
		echo '&#160;<img src="images/rate.gif" alt=""/>';
	else
	//prefix forum
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
	//Judul thread
	echo '&#160;<a href="' . $set['homeurl'] . '/forum/' . functions::seo($res['text']) . '_' . $res['id'] . ($cpg > 1 && $_SESSION['uppost'] ? '_p' . $cpg : '') . '.html">' . $res['text'] .	'</a>&#160;[' . $colmes1 . ']';
	if ($cpg > 1)
		echo '&#160;<a href="' . $set['homeurl'] . '/forum/' . functions::seo($res['text']) . '_' . $res['id'] . ($_SESSION['uppost'] ? '' : '_p' . $cpg) . '.html">Lihat</a>';
		//echo '<a href="index.php?id=' . $res['id'] . '&amp;page=' . $cpg . '">&#160;&gt;&gt;</a>';
		//edit seo 4
		//echo '<a href="'.$home.'/forum/' . functions::seo($res['text']) . '_' . $res['id'] . '_p' . $cpg . '.html">&#160;&gt;&gt;</a>';
		echo '<br /><div class="sub"><div class="textx">Last Post ';
                        echo '<span class="green">';
		if ($colmes1 > 1) {
			echo '' . $nick['from'];
		}else 
			echo $res['from'];
                        echo '</span>';
			echo '</div></div></div></div>';
	++$i;
}