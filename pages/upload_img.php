<?php
/**
 * @package     JohnCMS
 * @link        http://johncms.com
 * @copyright   Copyright (C) 2008-2011 JohnCMS Community
 * @license     LICENSE.txt (see attached file)
 * @version     VERSION.txt (see attached file)
 * @author      http://johncms.com/about
 */
define('_IN_JOHNCMS', 1);
require('../incfiles/core.php');
$textl = 'Upload Gambar';
$headmod = 'upload_img';
require('../incfiles/head.php');
$upload_file_size = 1024;


if ($user_id) {
    if (isset($_POST['submit'])) {
        require_once ('../incfiles/lib/class.upload.php');
        $handle = new upload($_FILES['imagefile']);
        if ($handle->uploaded) {
            $name_file = time() . '_' . mt_rand(100, 999);
            $handle->file_new_name_body = $name_file;
            $handle->allowed = array('image/jpeg', 'image/gif', 'image/png');
            $handle->file_max_size = 1024 * $upload_file_size;
            $handle->file_overwrite = true;
            $handle->image_convert = 'jpg';
            $handle->process('../files/images/');
            if ($handle->processed) {
            	$handle->file_new_name_body =$name_file . '_preview';
            	$GetImageSize = GetImageSize('../files/images/' . $name_file . '.jpg');
                $handle->file_overwrite = true;
                $handle->image_resize = true;
               	$handle->image_ratio_crop = true;
               	$x_ratio = 120 / $GetImageSize[0];
            	$y_ratio = 120 / $GetImageSize[1];
				if (($GetImageSize[0] <= 120) && ($GetImageSize[1] <= 120)) {
                	$handle->image_x = $GetImageSize[0];
                	$handle->image_y = $GetImageSize[1];
            	} else if (($x_ratio * $GetImageSize[1]) < 120) {
                	$handle->image_y = ceil($x_ratio * $GetImageSize[1]);
                	$handle->image_x = 120;
            	} else {
                	$handle->image_x = ceil($y_ratio * $GetImageSize[0]);
                	$handle->image_y = 120;
            	}
				$handle->image_convert = 'jpg';
				$handle->process('../files/images/');
				if ($handle->processed) {
        			echo '<div class="phdr"><a href="upload_img.php">Kembali</a> | <b>Upload Gambar</b></div>' .
        			'<div class="gmenu">Gambar diupload! Sekarang Anda dapat menyisipkannya ke pesan apapun di situs</div>' .
        			'<div class="menu">BBcode: <input type="text" value="[img=' . $name_file . ']" /></div>' .
        			'<div class="phdr"><a href="upload_img.php">Kembali</a></div>';
				}else
					echo functions::display_error($handle->error, '<a href="upload_img.php">Kembali</a>');
			} else
                echo functions::display_error($handle->error, '<a href="upload_img.php">Kembali</a>');
			$handle->clean();
		} else {
            echo functions::display_error('Tidak ada file yang dipilih', '<a href="upload_img.php">Kembali</a>');
        }
    } else {
        echo '<div class="phdr"> <a href="faq.php?act=tags">ВВcode</a> | <b>Upload Gambar</b></div>' .
        '<form enctype="multipart/form-data" method="post" action="upload_img.php?img"><div class="list1">' .
        'Pilih gambar:<br /><input type="file" name="imagefile" value="" />' .
        '<input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $upload_file_size) . '" />' . '<br />' .
        '<p><input type="submit" name="submit" value="Upload" /></p></div></form>' .
        '<div class="list2"><small>Type file yang diperbolehkan adalah JPG, JPEG, PNG, dan GIF<br />' .
        'Ukuran file tidak boleh melebihi ' . $upload_file_size . 'kb.</small></div>' .
        '<div class="phdr"><b>Gambar</b></div>';
		$array = glob($rootpath . 'files/images/*preview.jpg');
        $total = count($array);
        $end = $start + $kmess;
        if ($end > $total)
            $end = $total;
        if ($start >= $total) {
            $start = 0;
            $end = $total > $kmess ? $kmess : $total;
        }
        if ($total > 0) {
            for ($i = $start; $i < $end; $i++) {
            	$code = preg_replace('#../files/images/(.+?)_preview.jpg#is', '\1', $array[$i]);
            	echo ($i  % 2) ? '<div class="list2">' : '<div class="list1">';
				echo'<table width="100%" cellspacing="0" cellpadding="0"><tr valign="top"><td>' .
				'<a href="' . str_replace('_preview', '', $array[$i]) . '"><img src="' . $array[$i] . '" alt="+"  /></a></td>' .
    			'<td align="right"><input type="text" value="[img=' . $code . ']" /></td></tr></table></div>';
			}
        } else
            echo '<div class="menu">Daftar Kosong!</div>';
        echo '<div class="phdr">Total' . $total .  '</div>';
        if ($total > $kmess) {
            echo '<div class="topmenu">' . functions::display_pagination('upload_img.php?', $start, $total, $kmess) . '</div>';
            echo '<p><form action="upload_img.php" method="post"><input type="submit" value="pergi ke halaman &gt;&gt;"/></form></p>';
        }

		echo '<p><a href="faq.php?act=tags">ВВcode</a></p>';
    }
} else {
    header('location: /login.php');
}
require('../incfiles/end.php');

?>