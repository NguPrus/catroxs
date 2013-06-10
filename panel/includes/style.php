<?

defined('_IN_JOHNADM') or die('Error: restricted access');

if ($rights < 9) {
    header('Location: /?err');
    exit;
}
echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | Editor CSS</div>';
if (isset($_POST['back'])) header("location: index.php?act=style");
$sk = isset($_GET['sk']) ? trim($_GET['sk']) : '';
switch ($mod) {
	case 'edit':
		if (isset($_POST['submit'])) {
			$style = '../theme/'.$sk.'/style.css';
			file_put_contents($style, trim($_POST['msg']));
			echo '<div class="rmenu">Изменено&#160;<b>'.$sk.'</b></div>';
		} 
		$style = '../theme/'.$sk.'/style.css';
		chmod($style, 0777);
		$file = file_get_contents($style);
		echo '<div class="gmenu">'.
			 'Editor css: <b>'.$sk.'</b>'.
			 '<form name="form" action="index.php?act=style&amp;mod=edit&amp;sk='.$sk.'" method="post">'.
			 '<textarea name="msg" rows="10" style="width: 100%; background-color: #f5f5f5;">'.$file.'</textarea><br />'.
			 '<input type="submit" name="submit" value="Ubah" /><input name="back" type="submit" value="Batal" />'.
			 '</form></div>';		
	break;
default:
	$dir = opendir('../theme');
	while ($sk = readdir($dir)) {
		if (($sk != '.') && ($sk != '..') && ($sk != '.svn')){
			echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
			echo '<a href="index.php?act=style&amp;mod=edit&amp;sk='.$sk.'">&#160;'.$sk.'</a>';
			echo '</div>';
			$i++;
		}
	}
}