<?php

/**
 * @package     JohnCMS
 * @link        http://johncms.com
 * @copyright   Copyright (C) 2008-2011 JohnCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://johncms.com/about
 */

defined('_IN_JOHNADM') or die('Error: restricted access');
if ($rights < 9) {
echo functions::display_error($lng['access_forbidden']);
require_once('../incfiles/end.php');
exit;
}
require('../incfiles/lib/class.upload.php');
$smiley = trim($_GET['smiley']);
$ext = array('gif', 'jpg', 'jpeg', 'png');
$lng_smileys = core::load_lng('smileys');
switch ($mod) {
default:
echo '<div class="mainblok"><div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['smileys'] . '</div>';
echo '<div class="omenu"><a href="?act=smileys&amp;mod=refresh">Update</a></div>';
$dir = glob('../images/smileys/user/*', GLOB_ONLYDIR);
$i = 0;
foreach ($dir as $val) {
$cat = explode('/', $val);
$cat = array_pop($cat);
$name_cat = $lng_smileys[$cat] ? $lng_smileys[$cat] : $cat;
echo $i % 2 ? '<div class="menu">' : '<div class="menu">';
echo '<a href="?act=smileys&amp;do='.$cat.'&amp;mod=show_cat">'.htmlspecialchars($name_cat).'</a>
('.count(glob('../images/smileys/user/'.$cat.'/*.{gif,jpg,png}', GLOB_BRACE)).')';
echo '</div>';
++$i;
}
echo '<div class="phdr">'.$lng['total'].': '.$i.'</div></div>';
break;
case 'rename':
if (!file_exists('../images/smileys/user/'.$do.'/'.$smiley)) {
echo functions::display_error($lng['error_wrong_data']);
echo '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';
require_once('../incfiles/end.php');
exit;
}
$name_cat = $lng_smileys[$do] ? $lng_smileys[$do] : $do;
echo '<div class="mainblok"><div class="phdr">
<a href="?act=smileys&amp;do='.$do.'&amp;mod=show_cat"><b>'.htmlspecialchars($name_cat).'</b></a>
| '.$lng['edit'].'</div>';
$format = functions::format($smiley);
$smiley_code = str_replace('.'.$format, '', $smiley);
if (isset($_POST['submit'])) {
$name = trim($_POST['smiley']);
$name = functions::rus_lat($name);
$name = preg_replace('/[^_a-z0-9]/i', '', $name);
$cat = trim($_POST['cat']);
$error = array();
if ($name != $smiley_code) {
$glob = glob('../images/smileys/user/*/*.{gif,jpg,png}', GLOB_BRACE);
foreach ($glob as $val) {
$val = explode('/', $val);
$val = array_pop($val);
$val = str_replace('.'.$format, '', $val);
if ($val == $name)
$i = 1;
}
if ($i)
$error[] = 'Smile with the same name already exists.';
}
if (!is_dir('../images/smileys/user/'.$cat))
$error[] = 'Category you have selected does not exist.';
if ($error)
echo functions::display_error($error);
else {
$name = $name.'.'.$format;
rename('../images/smileys/user/'.$do.'/'.$smiley, '../images/smileys/user/'.$cat.'/'.$name);
header('Location: ?act=smileys&mod=show_cat&do='.$do);
exit;
}
}
echo '<form method="POST"><div class="gmenu">';
echo 'Name:<br /><input name="smiley" value="'.htmlspecialchars($smiley_code).'"/> <b>.'.$format.'</b>';
echo '<br />Code:  <b>:'.htmlspecialchars($smiley_code).'</b><br />';
echo 'Category:<br />';
echo '<select name="cat">';
$dir = glob('../images/smileys/user/*', GLOB_ONLYDIR);
$i = 0;
foreach ($dir as $val) {
$cat = explode('/', $val);
$cat = array_pop($cat);
$name_cat = $lng_smileys[$cat] ? $lng_smileys[$cat] : $cat;
echo '<option value="'.$cat.'">'.htmlspecialchars($name_cat).'</option>';
}
echo '</select>';
echo '<br /><input type="submit" value="Change" name="submit"/>';
echo '</div></form></div>';
break;
case 'show_cat':
$c = '../images/smileys/user/'.$do.'/';
if (!is_dir($c)) {
echo functions::display_error($lng['error_wrong_data']);
echo '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';
require_once('../incfiles/end.php');
exit;
}
$name_cat = $lng_smileys[$do] ? $lng_smileys[$do] : $do;
echo '<div class="mainblok"><div class="phdr"><a href="index.php?act=smileys"><b>'.$lng['smileys'].'</b></a> | '.htmlspecialchars($name_cat).'</div>';
echo '<div class="omenu"><a href="?act=smileys&amp;do='.$do.'&amp;mod=upload">Upload</a></div>';
$dir = glob($c.'*.{gif,jpg,png}', GLOB_BRACE);
$total = count($dir);
for ($i = $start; $i < $page * $kmess && $i < $total; $i++) {
$smiley = explode('/', $dir[$i]);
$smiley = array_pop($smiley);
$format = functions::format($smiley);
$smiley_code = str_replace('.'.$format, '', strtolower($smiley));
echo $i % 2 ? '<div class="menu">' : '<div class="menu">';
echo functions::smileys(':'.$smiley_code.'').' :'.$smiley_code.'<br />
<a href="?act=smileys&amp;mod=unlink&amp;do='.$do.'&amp;smiley='.$smiley.'">'.$lng['delete'].'</a>
| <a href="?act=smileys&amp;mod=rename&amp;do='.$do.'&amp;smiley='.$smiley.'">'.$lng['edit'].'</a>';
echo '</div>';
}
echo '<div class="phdr">'.$lng['total'].': '.$total.'</div></div>';
if ($total > $kmess) {
echo '<div class="topmenu"><form action="?act=smileys&amp;do='.$do.'&amp;mod=show_cat" method="post">
'.functions::display_pagination('?act=smileys&amp;do='.$do.'&amp;mod=show_cat&amp;', $start, $total, $kmess).'
<input type="text" name="page" size="2"/><input type="submit" value="Go!"/></form></div>';
}
break;
case 'upload':
$c = '../images/smileys/user/'.$do.'/';
if (!is_dir($c)) {
echo functions::display_error($lng['error_wrong_data']);
echo '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';
require_once('../incfiles/end.php');
exit;
}
$name_cat = $lng_smileys[$do] ? $lng_smileys[$do] : $do;
echo '<div class="mainblok"><div class="phdr"><a href="?act=smileys&amp;do='.$do.'&amp;mod=show_cat"><b>'.htmlspecialchars($name_cat).'</b></a>
| Upload</div>';
if (isset($_POST['submit'])) {
$format = functions::format($_FILES['smiley']['name']);
$_FILES['smiley']['name'] = str_replace('.'.$format, '', strtolower($_FILES['smiley']['name']));
$name = $_POST['name'] ? $_POST['name'] : $_FILES['smiley']['name'];
$name = functions::rus_lat($name);
$name = preg_replace('/[^_a-z0-9]/i', '', $name);
$glob = glob('../images/smileys/user/*/*.{gif,jpg,png}', GLOB_BRACE);
foreach ($glob as $val) {
$val = explode('/', $val);
$val = array_pop($val);
$val = str_replace('.'.$format, '', $val);
if ($val == $name)
$i = 1;
}
if ($i)
$error[] = 'Smile with the same name already exists.';
if ($error)
echo functions::display_error($error);
else {
$handle = new upload($_FILES['smiley']);
if ($handle->uploaded) {
// Обрабатываем фото
$handle->file_new_name_body = $name;
$handle->allowed = array (
'image/jpeg',
'image/gif',
'image/png'
);
$handle->file_max_size = 1024 * $set['flsz'];
$handle->file_overwrite = true;
$handle->image_ratio_no_zoom_in = true;
$handle->image_x = 100;
$handle->image_y = 100;
$handle->process($c);
if ($handle->processed) {
echo '<div class="gmenu">Smile has been successfully uploaded!</div>';
echo '<div class="menu"><a href="?act=smileys&amp;mod=show_cat&amp;do='.$do.'">Proceed</a></div>';
} else {
echo functions::display_error($handle->error());
echo '<div class="menu"><a href="?act=smileys&amp;mod=upload&amp;do='.$do.'">'.$lng['repeat'].'</a></div>';
}
$handle->clean();
}
require_once('../incfiles/end.php');
exit;
}
}
echo '<form method="POST" enctype="multipart/form-data"><div class="gmenu">';
echo 'Allowed file ('.implode(', ', $ext).'):<br />
<input type="file" name="smiley"/>';
echo '<br />Name:<br /><input name="name" /><br />
<input type="submit" name="submit" value="Upload"/>';
echo '</div></form></div>';
break;
case 'unlink':
if (!file_exists('../images/smileys/user/'.$do.'/'.$smiley)) {
echo functions::display_error($lng['error_wrong_data']);
echo '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';
require_once('../incfiles/end.php');
exit;
}
$name_cat = $lng_smileys[$do] ? $lng_smileys[$do] : $do;
if (isset($_GET['yes'])) {
unlink('../images/smileys/user/'.$do.'/'.$smiley);
header('Location: ?act=smileys&mod=show_cat&do='.$do);
}
echo '<div class="mainblok"><div class="phdr"><a href="?act=smileys&amp;do='.$do.'&amp;mod=show_cat"><b>'.htmlspecialchars($name_cat).'</b></a>
| '.$lng['delete'].'</div>';
echo '<div class="omenu">';
echo 'Are you sure you want to remove this smile '.htmlspecialchars($smiley).'?<br />
<a href="?act=smileys&amp;mod=unlink&amp;do='.$do.'&amp;smiley='.$smiley.'&amp;yes">Yes</a>
| <a href="?act=smileys&amp;mod=show_cat&amp;do='.$do.'">No</a>';
echo '</div></div>';
break;
case 'refresh':
echo '<div class="mainblok"><div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['smileys'] . '</div>';
$smileys = array();

// Обрабатываем простые смайлы
foreach(glob($rootpath . 'images/smileys/simply/*') as $var){
    $file = basename($var);
    $name = explode(".", $file);
    if (in_array($name[1], $ext)) {
        $smileys['usr'][':' . $name[0]] = '<img src="' . $set['homeurl'] . '/images/smileys/simply/' . $file . '" alt="" />';
    }
}

// Обрабатываем Админские смайлы
foreach(glob($rootpath . 'images/smileys/admin/*') as $var){
    $file = basename($var);
    $name = explode(".", $file);
    if (in_array($name[1], $ext)) {
        //$smileys['adm'][':' . functions::trans($name[0]) . ':'] = '<img src="' . $set['homeurl'] . '/images/smileys/admin/' . $file . '" alt="" />';
        $smileys['adm'][':' . $name[0]] = '<img src="' . $set['homeurl'] . '/images/smileys/admin/' . $file . '" alt="" />';
    }
}

// Обрабатываем смайлы каталога
foreach(glob($rootpath . 'images/smileys/user/*/*') as $var){
    $file = basename($var);
    $name = explode(".", $file);
    if (in_array($name[1], $ext)) {
        $path = str_replace('..', $set['homeurl'], dirname($var));
        //$smileys['usr'][':' . functions::trans($name[0]) . ':'] = '<img src="' . $path . '/' . $file . '" alt="" />';
        $smileys['usr'][':' . $name[0]] = '<img src="' . $path . '/' . $file . '" alt="" />';
    }
}

// Записываем в файл Кэша
if (file_put_contents($rootpath . 'files/cache/smileys.dat', serialize($smileys))) {
    echo '<div class="gmenu"><p>' . $lng['smileys_updated'] . '</p></div>';
} else {
    echo '<div class="omenu"><p>' . $lng['smileys_error'] . '</p></div>';
}
$total = count($smileys['adm']) + count($smileys['usr']);
echo '<div class="phdr"><a href="?act=smileys&amp;">'.$lng['back'].'</a></div>';
echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div></div>';
break;
}
echo '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';

?>