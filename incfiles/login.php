<?php

$error = array();
$captcha = false;
$display_form = 1;

if ($display_form) {
    if ($error)
        echo functions::display_error($error);
    echo '<div class="header"><form action=" ' . $home . '/login.php" method="post">' .
	
		'<p width="auto" cellpadding="0" cellspacing="0" align="right">' .
		'<td width="10px">' . $lng['login_name'] . ':<br/>' .
		'<input type="text" name="n" value="' . htmlentities($user_login, ENT_QUOTES, 'UTF-8') . '" maxlength="20" size="8" /></td>' .
         
		 '<td width="10px">' . $lng['password'] . ':<br/>' .
         '<input type="password" name="p" maxlength="20" size="8" /></td>' .
		 '</p>' .
		 
		 '<p width="auto" cellpadding="0" cellspacing="0" align="right"><input type="checkbox" name="mem" value="1" checked="checked"/>' . $lng['remember'] .
         '<input type="submit" value="' . $lng['login'] . '"/></p>' .
		 '</form></div>';
}