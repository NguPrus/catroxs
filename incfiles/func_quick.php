<?php
{
	$roq = mysql_query("SELECT `qchat`.*, `users`.`name`, `users`.`rights`, `users`.`lastdate`, `users`.`sex`, `users`.`status`, `users`.`datereg`, `users`.`ip` , `users`.`browser`   FROM `qchat` LEFT JOIN `users` ON `qchat`.`user_id` = `users`.`id` ORDER BY `time` DESC LIMIT 3;");;
	while ($res = mysql_fetch_array($roq))
{
echo ceil(ceil($i / 2) - ($i / 2)) == 0 ? '<div class="menu">' : '<div class="menu">';
// icon seks
global $set_user, $realtime, $user_id, $admp, $home;

if (!empty($user_id) && ($user_id != $res['user_id'])) {
	echo '<a href="' . $set['homeurl'] . '/users/profile.php?user=' . $res['user_id'] . '"><b>' . $res['name'] . '</b></a> ';
}
else {
	echo '<b>' . $res['name'] . '</b>';
}
$ontimes = $res['lastdate'] + 600;
if (time() > $ontimes)
{
	echo '<span style="color: red"> <img src="' . $home . '/images/off.png" alt="[OFF]"></img></span>';
}
else
{
	echo '<span style="color: green"> <img src="' . $home . '/images/on.png" alt="[ON]"></img></span>';
}
echo ' ';
$post = functions::antilink(functions::checkout($res['text'], 0, 10));
$post = functions::smileys($post, $res['rights'] >= 1 ? 1 : 0);
 
// text
if (mb_strlen($post) >= 100)
echo $post.' ';
else
echo $post;
echo '</div>';
++$i;
}
	$refr = rand(0, 999);
}
?>
