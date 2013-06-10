<?php

/**
* @package     JohnCMS
* @link        http://johncms.com
* @copyright   Copyright (C) 2008-2011 JohnCMS Community
* @license     LICENSE.txt (see attached file)
* @version     VERSION.txt (see attached file)
* @author      http://johncms.com/about
*/

defined('_IN_JOHNCMS') or die('Restricted access');

class bbcode extends core
{
	/*
	-----------------------------------------------------------------
	Pengolahan tag dan link
	-----------------------------------------------------------------
	*/
	public static function tags($var)
	{
	$var = preg_replace ( '#@([\w\d]{2,})#si' , '@<a href="../user_$1">$1</a>' ,  $var );
	$var = preg_replace_callback('#\[img\](.*?)\[/img\]#si', 'bbimg', $var);  	
	$var = preg_replace(array ('#\[gt\](.*?)\[\/gt\]#se'), array ("''.rainbow('$1').''"), str_replace("]\n", "]", $var));
	$var = preg_replace('#\[copas\](.*?)\[/copas\]#si', '<div class="phpcode"><span style="color:#105FCE;">$1</span><br/><div class="code"><b>COPY CODE:</b><textarea>$1</textarea></div></div>', $var);$var = preg_replace('#@([\w\d]{2,})#si', '@<a href="../$1">$1</a>', $var);
	$var = self::highlight_code($var);                                     
	$var = self::highlight_codes($var);
	$var = self::highlight_images($var);
	//$var = self::highlight_img($var);
	$var = self::sembunyi($var);
	$var = self::parse_time($var);
	$var = self::OLD_highlight_url($var);        									
	$var = self::highlight_bb($var);                                      
	$var = self::highlight_url($var);                                      
	return $var;
	}
	
	/*
	private static function highlight_img($var) {
	if (!function_exists('process_img')) {
		function process_img($img) {
				global $user_id;
				global $home;
				if(!$user_id) {
				return '<a href="' . core::$system_set['homeurl'] . '"><img src="' . $img[1]. '" /></a>';
			} else {
				return '<a href="' . core::$system_set['homeurl'] . '"><img src="' . $img[1]. '" /></a>';
			}
		}
	}
	return preg_replace_callback('#\[img\](.+?)\[/img\]#si', 'process_img', $var);
	}
	*/
	
	/*
	-----------------------------------------------------------------
	PHP Code
	-----------------------------------------------------------------
	*/
	private static function highlight_code($var)
	{
		if (!function_exists('process_code')) {
		function process_code($php)
			{
				$php = strtr($php, array('<br />' => '', '\\' => 'slash_JOHNCMS'));
				$php = html_entity_decode(trim($php), ENT_QUOTES, 'UTF-8');
				$php = substr($php, 0, 2) != "<?" ? "<?php\n" . $php . "\n?>" : $php;
				$php = highlight_string(stripslashes($php), true);
				$php = strtr($php, array('slash_JOHNCMS' => '&#92;', ':' => '&#58;', '[' => '&#91;'));
				return '<div class="phpcode">' . $php . '</div>';
			}
		}
		return preg_replace(array('#\[php\](.+?)\[\/php\]#se'), array("''.process_code('$1').''"), str_replace("]\n", "]", $var));
	}
	
	/*
	-----------------------------------------------------------------
	Code General
	-----------------------------------------------------------------
	*/	
	private static function highlight_codes($var)
	{
		if (!function_exists('process_codes')) {
			function process_codes($code)
			{
				$code = strtr($code, array('<br />' => '', '\\' => 'slash_JOHNCMS'));
				$code = html_entity_decode(trim($code), ENT_QUOTES, 'UTF-8');
				$code = substr($code, 0, 2) != "" ? "" . $code . "" : $code;
				$code = highlight_string(stripslashes($code), true);
				$code = strtr($code, array('slash_JOHNCMS' => '&#92;', ':' => '&#58;', '[' => '&#91;'));
				return 'Code:<div class="code">' . $code . '</div>';
			}
		}
		return preg_replace(array('#\[code\](.+?)\[\/code\]#se'), array("''.process_codes('$1').''"), str_replace("]\n", "]", $var));
	}
	
	/*
	-----------------------------------------------------------------
	Images Local
	-----------------------------------------------------------------
	*/
	private static function highlight_images($var) {
		if (!function_exists('process_images')) {
			function process_images($name) {
				global $rootpath;
				$file = $rootpath . 'files/images/' . $name . '_preview.jpg';
				if(file_exists($file))
				$image = '<a href="' . core::$system_set['homeurl'] . '/files/images/' . $name . '.jpg"><img src="' . $file . '" alt="' . $name . '" /></a>';
				else
				$image = '<b>[<span style="color:red">image removed</span>]</b>';
				return $image;
			}
		}
		return preg_replace('#\[img=([0-9]+)_([0-9]{3})\]#se', "process_images('$1_$2')", $var);
	}
	/*
	------------
	Sembunyikan Teks Untuk Guest
	------------
	*/
	private static function sembunyi($var)
	{
		if (!function_exists('ndelik')) {
			function ndelik($hide)
			{
				global $user_id;
				if(!$user_id) {
				return '
					<div class="rmenu">
						<span class="red">
							<b>
								<blink>Akses Dibatasi</blink>
							</b>
						</span>
					</div>
					<div class="rmenu" align="center">
						<b>
							Hanya member, silahkan login or register :)
						</b>
					</div>';
				} else {
					return '' . $hide[1] . '';
				}
			}
		}
		return preg_replace_callback('#\[hide\](.*?)\[/hide\]#si', 'ndelik', $var);
	}
	
    /*
    -----------------------------------------------------------------
	waktu pemrosesan
    -----------------------------------------------------------------
    */
    private static function parse_time($var)
    {
        if (!function_exists('process_time')) {
            function process_time($time)
            {
                $shift = (core::$system_set['timeshift'] + core::$user_set['timeshift']) * 3600;
                if($out = strtotime($time)){
                    return date("d.m.Y / H:i", $out + $shift);
                } else {
                    return false;
                }
            }
        }
        return preg_replace(array('#\[time\](.+?)\[\/time\]#se'), array("''.process_time('$1').''"), $var);
    }
	
	    /*
    -----------------------------------------------------------------
    URL penanganan di BBcode tag
    -----------------------------------------------------------------
    */
    private static function OLD_highlight_url($var)
    {
        if (!function_exists('process_url')) {
            function process_url($url)
            {
                    $tmp = parse_url($url[1]);
                    if ('http://' . $tmp['host'] == core::$system_set['homeurl'] || isset(core::$user_set['direct_url']) && core::$user_set['direct_url']) {
                        return '<a href="' . $url[1] . '">' . $url[2] . '</a>';
                    } else {
                        return '<a href="' . core::$system_set['homeurl'] . '/go.php?url=' . rawurlencode($url[1]) . '">' . $url[2] . '</a>';
                    }
            }
        }
        return preg_replace_callback('~\\[url=(https?://.+?)\\](.+?)\\[/url\\]~', 'process_url', $var);
    }
	/*
	-----------------------------------------------------------------
	Icon BBcode
	-----------------------------------------------------------------
	*/
	private static function highlight_bb($var)
	{
		$var = preg_replace_callback('#\[size=([0-9]{1,2})\](.*?)\[/size\]#si',create_function('$data', 'if(intval($data[1])>48) {$data[1] = 48; } elseif (intval($data[1])<3) {$data[1] = 3; }return "<span style=\"font-size:{$data[1]}px\">{$data[2]}</span>";'), $var);
		// C?co????
		$search = array(
		'#\[b](.+?)\[/b]#is',                                              // ?p??
		'#\[i](.+?)\[/i]#is',                                              // Kypc?
		'#\[u](.+?)\[/u]#is',                                              // ?Aep?y??
		'#\[s](.+?)\[/s]#is',                                              // ??p?y??
		'#\[small](.+?)\[/small]#is',                                      // Ma???????
		'#\[big](.+?)\[/big]#is',                                          // @??????
		'#\[red](.+?)\[/red]#is',                                          // Kpac??
		'#\[green](.+?)\[/green]#is',                                      // ????
		'#\[blue](.+?)\[/blue]#is',                                        // C??
		'!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/color]!is', // ?e????
		'!\[bg=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/bg]!is',       // ?e???
		'#\[(quote|c)](.+?)\[/(quote|c)]#is',                              // ???
		'#\[\*](.+?)\[/\*]#is',                                            // C?co?
		"#\[spoiler=(?:&quot;|\"|')?(.*?)[\"']?(?:&quot;|\"|')?\](.*?)\[\/spoiler\](\r\n?|\n?)#si", // Spoiler for
		'#\[spoiler\](.*?)\[\/spoiler\](\r\n?|\n?)#si',                  // Spoiler
		'#\[area\](.+?)\[/area\]#si',
		'#\[sub\](.*?)\[/sub\]#si',
		'#\[sup\](.*?)\[/sup\]#si',
		'#\[left\](.*?)\[/left\]#si',
		'#\[right\](.*?)\[/right\]#si',
		'#\[justify\](.*?)\[/justify\]#si',
		'#\[center\](.*?)\[/center\]#si',
		'!\[align=(left|center|right|justify)\](.+?)\[/align]!is',
		'!\[size=(xx-small|x-small|small|medium|large|x-large|xx-large)\](.+?)\[/size\]!is',
		'#\[youtube\](.+?)\[/youtube\]#si',      
		'#\[rmenu](.+?)\[/rmenu]#is',
		'#\[gmenu](.+?)\[/gmenu]#is', '#\[lmenu](.+?)\[/lmenu]#is',
		'#\[tmenu](.+?)\[/tmenu]#is'                                          
		);
		// C?co????
		$replace = array(
		'<span style="font-weight: bold">$1</span>',                       // ?p??
		'<span style="font-style:italic">$1</span>',                       // Kypc?
		'<span style="text-decoration:underline">$1</span>',               // ?Aep?y??
		'<span style="text-decoration:line-through">$1</span>',            // ??p?y??
		'<span style="font-size:x-small">$1</span>',                       // Ma???????
		'<span style="font-size:large">$1</span>',                         // @??????
		'<span style="color:red">$1</span>',                               // Kpac??
		'<span style="color:green">$1</span>',                             // ????
		'<font color="blue">$1</font>',                              // C??
		'<span style="color:$1">$2</span>',                                // ?e????
		'<span style="background-color:$1">$2</span>',                     // ?e???
		'<div class="bbcode_container"><div class="bbcode_quote"><div class="quote_container"><div class="bbcode_quote_container"></div>$2</div></div></div>',
		'<span class="bblist">$1</span>',                           // C?co?
				 "<div style=\"margin: 5px 5px 5px 5px;\">
				 <div class=\"smallfont\" style=\"margin-bottom:2px\">
				 <b>Spoiler</b> for <i>$1</i>: 
				 <input type=\"button\" style=\"margin: 0px; padding: 0px; width: 45px; font-size: 10px;\" onClick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerText = ''; this.value = 'Hide'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'Show'; }\" value=\"Show\"></input>
				 <div style=\"border: 1px inset; background-color: whitesmoke; margin: 0px; padding: 2px;\">
				 <div style=\"display: none;\">$2</div>
				 </div>
				 </div>
				 </div>", // Spoiler for
				 "<div style=\"margin: 5px 5px 5px 5px;\">
				 <div class=\"smallfont\" style=\"margin-bottom:2px\">
				 <b>Spoiler</b>: 
				 <input type=\"button\" style=\"margin: 0px; padding: 0px; width: 45px; font-size: 10px;\" onClick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerText = ''; this.value = 'Hide'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'Show'; }\" value=\"Show\"></input>
				 <div style=\"border: 1px inset; background-color: whitesmoke; margin: 0px; padding: 2px;\">
				 <div style=\"display: none;\">$1</div>
				 </div>
				 </div>
				 </div>", // Spoiler
		'<textarea>' . htmlentities('\1') . '</textarea>',
		'<span style="vertical-align:sub">\1</span>',
		'<span style="vertical-align:super">\1</span>',
		'<div style="text-align:left">\1</div>',
		'<div style="text-align:right">\1</div>',
		'<div style="text-align:justify">\1</div>',
		'<div style="text-align:center">\1</div>',
		'<div style="text-align: $1">$2</div>',
		'<span style="font-size:$1">$2</span>',
		'<center><iframe width="auto" height="auto" src="http://www.youtube.com/embed/$1?rel=0" frameborder="0" allowfullscreen="true"></iframe></center>',
		'<center><div style="max-width:90%" class="rmenu">$1</div></center>',
		'<center><div style="max-width:90%" class="gmenu">$1</div></center>',
		'<center><div style="max-width:90%" class="lmenu">$1</div></center>',
		'<center><div style="max-width:90%" class="tmenu">$1</div></center>'
		);
		return preg_replace($search, $replace, $var);
	}
	
	/*
    -----------------------------------------------------------------
    Parse Link
    -----------------------------------------------------------------
    Hal ini didasarkan pada fitur modifikasi dari versi 3.xx forum phpBB
    -----------------------------------------------------------------
    */
    public static function highlight_url($text)
    {
        if (!function_exists('url_callback')) {
            function url_callback($type, $whitespace, $url, $relative_url)
            {
                $orig_url = $url;
                $orig_relative = $relative_url;
                $url = htmlspecialchars_decode($url);
                $relative_url = htmlspecialchars_decode($relative_url);
                $text = '';
                $chars = array('<', '>', '"');
                $split = false;
                foreach ($chars as $char) {
                    $next_split = strpos($url, $char);
                    if ($next_split !== false) {
                        $split = ($split !== false) ? min($split, $next_split) : $next_split;
                    }
                }
                if ($split !== false) {
                    $url = substr($url, 0, $split);
                    $relative_url = '';
                } else if ($relative_url) {
                    $split = false;
                    foreach ($chars as $char) {
                        $next_split = strpos($relative_url, $char);
                        if ($next_split !== false) {
                            $split = ($split !== false) ? min($split, $next_split) : $next_split;
                        }
                    }
                    if ($split !== false) {
                        $relative_url = substr($relative_url, 0, $split);
                    }
                }
                $last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];
                switch ($last_char)
                {
                    case '.':
                    case '?':
                    case '!':
                    case ':':
                    case ',':
                        $append = $last_char;
                        if ($relative_url) $relative_url = substr($relative_url, 0, -1);
                        else $url = substr($url, 0, -1);
                        break;

                    default:
                        $append = '';
                        break;
                }
                $short_url = (mb_strlen($url) > 40) ? mb_substr($url, 0, 30) . ' ... ' . mb_substr($url, -5) : $url;
                switch ($type)
                {
                    case 1:
                        $relative_url = preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
                        $url = $url . '/' . $relative_url;
                        $text = $relative_url;
                        if (!$relative_url) {
                            return $whitespace . $orig_url . '/' . $orig_relative;
                        }
                        break;

                    case 2:
                        $text = $short_url;
                        if (!isset(core::$user_set['direct_url']) || !core::$user_set['direct_url']) {
                            $url = core::$system_set['homeurl'] . '/go.php?url=' . rawurlencode($url);
                        }
                        break;

                    case 3:
                        $url = 'http://' . $url;
                        $text = $short_url;
                        if (!isset(core::$user_set['direct_url']) || !core::$user_set['direct_url']) {
                            $url = core::$system_set['homeurl'] . '/go.php?url=' . rawurlencode($url);
                        }
                        break;

                    case 4:
                        $text = $short_url;
                        $url = 'mailto:' . $url;
                        break;
                }
                $url = htmlspecialchars($url);
                $text = htmlspecialchars($text);
                $append = htmlspecialchars($append);
                return $whitespace . '<a href="' . $url . '">' . $text . '</a>' . $append;
            }
        }

        static $url_match;
        static $url_replace;

        if (!is_array($url_match)) {
            $url_match = $url_replace = array();

            // Обработка внутренние ссылки
            $url_match[] = '#(^|[\n\t (>.])(' . preg_quote(core::$system_set['homeurl'], '#') . ')/((?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#ieu';
            $url_replace[] = "url_callback(1, '\$1', '\$2', '\$3')";

            // Обработка обычных ссылок типа xxxx://aaaaa.bbb.cccc. ...
            $url_match[] = '#(^|[\n\t (>.])([a-z][a-z\d+]*:/{2}(?:(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-zа-яё0-9.]+:[a-zа-яё0-9.]+:[a-zа-яё0-9.:]+\])(?::\d*)?(?:/(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#ieu';
            $url_replace[] = "url_callback(2, '\$1', '\$2', '')";

            // Обработка сокращенных ссылок, без указания протокола "www.xxxx.yyyy[/zzzz]"
            $url_match[] = '#(^|[\n\t (>])(www\.(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#ieu';
            $url_replace[] = "url_callback(3, '\$1', '\$2', '')";

            // Обработка адресов E-mail
            $url_match[] = '/(^|[\n\t (>])(([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&amp;)+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?))/ie';
            $url_replace[] = "url_callback(4, '\$1', '\$2', '')";
        }
        return preg_replace($url_match, $url_replace, $text);
    }
	
	/*
	-----------------------------------------------------------------
	Notags
	-----------------------------------------------------------------
	*/
	static function notags($var = '')
	{
		$var = preg_replace ( '#@([\w\d]{2,})#si' , '@<a href="../user_$1">$1</a>' ,  $var );
		$var = preg_replace('#\[color=(.+?)\](.+?)\[/color]#si', '$2', $var);
		$var = preg_replace("#\[spoiler=(?:&quot;|\"|')?(.*?)[\"']?(?:&quot;|\"|')?\](.*?)\[\/spoiler\](\r\n?|\n?)#si", 
		"<div style=\"margin: 5px 5px 5px 5px;\">
		<div class=\"smallfont\" style=\"margin-bottom:2px\">
		<b>Spoiler</b> for <i>$1</i>: 
		<input type=\"button\" style=\"margin: 0px; padding: 0px; width: 45px; font-size: 10px;\" onClick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerText = ''; this.value = 'Hide'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'Show'; }\" value=\"Show\"></input>
		<div style=\"border: 1px inset; background-color: whitesmoke; margin: 0px; padding: 2px;\">
		<div style=\"display: none;\">$2</div>
		</div>
		</div>
		</div>", $var);
		$var = preg_replace("#\[spoiler\](.*?)\[\/spoiler\](\r\n?|\n?)#si", 
		"<div style=\"margin: 5px 5px 5px 5px;\">
		<div class=\"smallfont\" style=\"margin-bottom:2px\">
		<b>Spoiler</b>: 
		<input type=\"button\" style=\"margin: 0px; padding: 0px; width: 45px; font-size: 10px;\" onClick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerText = ''; this.value = 'Hide'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'Show'; }\" value=\"Show\"></input>
		<div style=\"border: 1px inset; background-color: whitesmoke; margin: 0px; padding: 2px;\">
		<div style=\"display: none;\">$1</div>
		</div>
		</div>
		</div>", $var);
		$var = preg_replace('#\[align=(.+?)\](.+?)\[/align]#si', '$2', $var);
		$var = preg_replace('#\[size=(.+?)\](.+?)\[/size]#si', '$2', $var);
		$var = preg_replace('!\[bg=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/bg]!is', '$2', $var);
		$replace = array(
		'[small]' => '',
		'[/small]' => '',
		'[spoiler]' => '',
		'[/spoiler]' => '',
		'[copas]' => '',
		'[/copas]' => '',
		'[big]' => '',
		'[/big]' => '',
		'[green]' => '',
		'[/green]' => '',
		'[red]' => '',
		'[/red]' => '',
		'[blue]' => '',
		'[/blue]' => '',
		'[b]' => '',
		'[/b]' => '',
		'[i]' => '',
		'[/i]' => '',
		'[u]' => '',
		'[/u]' => '',
		'[s]' => '',
		'[/s]' => '',
		'[quote]' => '',
		'[/quote]' => '',
		'[c]' => '',
		'[/c]' => '',
		'[*]' => '',
		'[/*]' => '',
		'[youtube]' => '',
		'[/youtube]' => '',
		'[img]' => '',
		'[/img]' => '',
		'[code]' => '',
		'[/code]' => '',
		);
		return strtr($var, $replace);
	}

	/*
	-----------------------------------------------------------------
	Warna
	-----------------------------------------------------------------
	*/
	public static function auto_bb($form, $field)
	{
		if (self::$is_mobile) {
			return false;
		}
		$colors = array(
			'ffffff', 'bcbcbc', '708090', '6c6c6c', '454545',
			'fcc9c9', 'fe8c8c', 'fe5e5e', 'fd5b36', 'f82e00',
			'ffe1c6', 'ffc998', 'fcad66', 'ff9331', 'ff810f',
			'd8ffe0', '92f9a7', '34ff5d', 'b2fb82', '89f641',
			'b7e9ec', '56e5ed', '21cad3', '03939b', '039b80',
			'cac8e9', '9690ea', '6a60ec', '4866e7', '173bd3',
			'f3cafb', 'e287f4', 'c238dd', 'a476af', 'b53dd2'
		);
		$i = 1;
		$font_color = '<table><tr>';
		$bg_color = '<table><tr>';
		foreach ($colors as $value) {
			$font_color .= '<a href="javascript:tag(\'[color=#' . $value . ']\', \'[/color]\', \'\');" style="background-color:#' . $value . ';"></a>';
			$bg_color .= '<a href="javascript:tag(\'[bg=#' . $value . ']\', \'[/bg]\', \'\');" style="background-color:#' . $value . ';"></a>';
			if (!($i % sqrt(count($colors)))){
				$font_color .= '</tr><tr>';
				$bg_color .= '</tr><tr>';
			}
			++$i;
		}
		$font_color .= '</tr></table>';
		$bg_color .= '</tr></table>';
		$smileys = !empty(self::$user_data['smileys']) ? unserialize(self::$user_data['smileys']) : '';
		if (!empty($smileys)) {
			$res_sm = '';
			$bb_smileys = '<small><a href="' . self::$system_set['homeurl'] . '/pages/faq.php?act=my_smileys" class="omenu">' . self::$lng['edit_list'] . '</a></small><br />';
			foreach ($smileys as $value)
			$res_sm .= '<a href="javascript:tag(\':\', \'' . $value . '\', \'\');">:' . $value . '</a> ';
			$bb_smileys .= functions::smileys($res_sm, self::$user_data['rights'] >= 1 ? 1 : 0);
		} else {
			$bb_smileys = '<small><a href="' . self::$system_set['homeurl'] . '/pages/faq.php?act=smileys">' . self::$lng['add_smileys'] . '</a></small>';
		}
		$out = '<style>
		.bb_hide{background-color: rgba(178,178,178,0.5); padding: 5px; border-radius: 3px; border: 1px solid #708090; display: none; overflow: auto; max-width: 300px; max-height: 150px; position: absolute;}
		.bb_opt:hover .bb_hide{display: block;}
		.bb_color a {float:left;  width:9px; height:9px; margin:1px; border: 1px solid black;}
		</style>
		<script language="JavaScript" type="text/javascript">
		function tag(text1, text2, text3) {
		if ((document.selection)) {
		document.' . $form . '.' . $field . '.focus();
		document.' . $form . '.document.selection.createRange().text = text3+text1+document.' . $form . '.document.selection.createRange().text+text2+text3;
		} else if(document.forms[\'' . $form . '\'].elements[\'' . $field . '\'].selectionStart!=undefined) {
		var element = document.forms[\'' . $form . '\'].elements[\'' . $field . '\'];
		var str = element.value;
		var start = element.selectionStart;
		var length = element.selectionEnd - element.selectionStart;
		element.value = str.substr(0, start) + text3 + text1 + str.substr(start, length) + text2 + text3 + str.substr(start + length);
		} else document.' . $form . '.' . $field . '.value += text3+text1+text2+text3;}</script>
		<a href="javascript:tag(\'[b]\', \'[/b]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/bold.gif" alt="b" title="' . self::$lng['tag_bold'] . '" border="0"/></a>
		<a href="javascript:tag(\'[i]\', \'[/i]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/italics.gif" alt="i" title="' . self::$lng['tag_italic'] . '" border="0"/></a>
		<a href="javascript:tag(\'[u]\', \'[/u]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/underline.gif" alt="u" title="' . self::$lng['tag_underline'] . '" border="0"/></a> | 
		<a href="javascript:tag(\'[left]\', \'[/left]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/text_align_left.png" alt="url" title="text align left" border="0"/></a>
		<a href="javascript:tag(\'[center]\', \'[/center]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/text_align_center.png" alt="url" title="text align center" border="0"/></a>
		<a href="javascript:tag(\'[right]\', \'[/right]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/text_align_right.png" alt="url" title="text align right" border="0"/></a> | 
		<a href="javascript:tag(\'[s]\', \'[/s]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/strike.gif" alt="s" title="' . self::$lng['tag_strike'] . '" border="0"/></a>
		<a href="javascript:tag(\'[*]\', \'[/*]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/list.gif" alt="s" title="' . self::$lng['tag_list'] . '" border="0"/></a> | 
		<a href="javascript:tag(\'[c]\', \'[/c]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/quote.gif" alt="quote" title="' . self::$lng['tag_quote'] . '" border="0"/></a>
		<a href="javascript:tag(\'[php]\', \'[/php]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/php.gif" alt="cod" title="' . self::$lng['tag_code'] . '" border="0"/></a>
		<a href="javascript:tag(\'[code]\', \'[/code]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/code.png" alt="url" title="code" border="0"/></a> | 
		<a href="javascript:tag(\'[img]\', \'[/img]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/img.gif" alt="img" title="images" border="0"/></a>
		<a href="javascript:tag(\'[hide]\', \'[/hide]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/hide.gif" alt="cod" title="Hide untuk member saja" border="0"/></a>
		<a href="javascript:tag(\'[url=]\', \'[/url]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/link.gif" alt="url" title="' . self::$lng['tag_link'] . '" border="0"/></a>
		<a href="javascript:tag(\'[youtube]\', \'[/youtube]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/youtube.png" alt="url" title="youtube video" border="0"/></a>
		<a href="javascript:tag(\'[spoiler]\', \'[/spoiler]\', \'\')"><img src="' . self::$system_set['homeurl'] . '/images/bb/sp.png" alt="b" title="Spoiler" border="0"/></a>
		<span class="bb_opt" style="display: inline-block; cursor:pointer">
		<img src="' . self::$system_set['homeurl'] . '/images/bb/color.gif" onmouseover="this.src=\'' . self::$system_set['homeurl'] . '/images/bb/color_on.gif\'" onmouseout="this.src=\'' . self::$system_set['homeurl'] . '/images/bb/color.gif\'" alt="color" title="' . self::$lng['color_bg'] . '" border="0"/>
		<div class="bb_hide bb_color">' . $font_color . '</div></span>
		<span class="bb_opt" style="display: inline-block; cursor:pointer">
		<img src="' . self::$system_set['homeurl'] . '/images/bb/color_bg.gif" onmouseover="this.src=\'' . self::$system_set['homeurl'] . '/images/bb/color_bg_on.gif\'" onmouseout="this.src=\'' . self::$system_set['homeurl'] . '/images/bb/color_bg.gif\'" alt="color" title="' . self::$lng['color_text'] . '" border="0"/>
		<div class="bb_hide bb_color">' . $bg_color . '</div></span>';
		if (self::$user_id) {
			$out .= ' <span class="bb_opt" style="display: inline-block; cursor:pointer"><img src="' . self::$system_set['homeurl'] . '/images/bb/smileys.gif" alt="sm" title="' . self::$lng['smileys'] . '" border="0"/>
			<div class="bb_hide">' . $bb_smileys . '</div></span>';
		}
		$out .= ' <span class="bb_opt" style="display: inline-block; cursor:pointer"> <a href="' . self::$system_set['homeurl'] . '/pages/img.php">
		<img src="' . self::$system_set['homeurl'] . '/images/bb/im.gif" alt="url" title="' . self::$lng['tag_link'] . '" border="0"/></a>
		<div class="bb_hide">To insert an image in a message, you necessary upload it to our server, to do this, use
		<a href="' . self::$system_set['homeurl'] . '/pages/upload_img.php" class="green"><b>image uploader</b></a>.
		After uploading image you get a code image, with which will be able to insert it into the message</div></span>';
		return $out . '';
	}
}

function rainbow($text) {
	$text = trim(stripslashes($text));
	$result="";
	$font=0;
	$turn=0;
	while(
	$font<=strlen($text)) { 
		$font_color = mb_substr($text,$font,1,'UTF-8');
		$font++;
		if($turn==0){ $turn=1; $result.= "<FONT color=\"#ff00ff\">".$font_color."</FONT>"; }
		else if($turn==1){ $turn=2; $result.= "<FONT color=\"#ff00cc\">".$font_color."</FONT>"; }
		else if($turn==2){ $turn=3; $result.= "<FONT color=\"#ff0099\">".$font_color."</FONT>"; }
		else if($turn==3){$turn=4; $result.= "<FONT color=\"#ff0066\">".$font_color."</FONT>"; } 
		else if($turn==4){$turn=5; $result.= "<FONT color=\"#ff0033\">".$font_color."</FONT>"; } 
		else if($turn==5){$turn=6; $result.= "<FONT color=\"#ff0000\">".$font_color."</FONT>"; } 
		else if($turn==6){$turn=7; $result.= "<FONT color=\"#ff3300\">".$font_color."</FONT>"; } 
		else if($turn==7){$turn=8; $result.= "<FONT color=\"#ff6600\">".$font_color."</FONT>"; }
		else if($turn==8){$turn=9; $result.= "<FONT color=\"#ff9900\">".$font_color."</FONT>"; }
		else if($turn==9){$turn=10; $result.= "<FONT color=\"#ffcc00\">".$font_color."</FONT>"; } 
		else if($turn==10){$turn=11; $result.= "<FONT color=\"#ffff00\">".$font_color."</FONT>"; } 
		else if($turn==11){$turn=12; $result.= "<FONT color=\"#ccff00\">".$font_color."</FONT>"; } 
		else if($turn==12){$turn=13; $result.= "<FONT color=\"#99ff00\">".$font_color."</FONT>"; }
		else if($turn==13){$turn=14; $result .= "<FONT color=\"#66ff00\">".$font_color."</FONT>"; } 
		else if($turn==14){$turn=15; $result .= "<FONT color=\"#33ff00\">".$font_color."</FONT>"; } 
		else if($turn==15){$turn=16; $result .= "<FONT color=\"#00ff00\">".$font_color."</FONT>"; } 
		else if($turn==16){$turn=17; $result .= "<FONT color=\"#00ff33\">".$font_color."</FONT>"; } 
		else if($turn==17){$turn=18; $result .= "<FONT color=\"#00ff66\">".$font_color."</FONT>"; } 
		else if($turn==18){$turn=19; $result .= "<FONT color=\"#00ff99\">".$font_color."</FONT>"; } 
		else if($turn==19){$turn=20; $result .= "<FONT color=\"#00ffcc\">".$font_color."</FONT>"; } 
		else if($turn==20){$turn=21; $result .= "<FONT color=\"#00ffff\">".$font_color."</FONT>"; }
		else if($turn==21){$turn=22; $result .= "<FONT color=\"#00ccff\">".$font_color."</FONT>"; } 
		else if($turn==22){$turn=23; $result .= "<FONT color=\"#0099ff\">".$font_color."</FONT>"; } 
		else if($turn==23){$turn=24; $result .= "<FONT color=\"#0066ff\">".$font_color."</FONT>"; } 
		else if($turn==24){$turn=25; $result .= "<FONT color=\"#0033ff\">".$font_color."</FONT>"; } 
		else if($turn==25){$turn=26; $result .= "<FONT color=\"#0000ff\">".$font_color."</FONT>"; } 
		else if($turn==26){$turn=27; $result .= "<FONT color=\"#3300ff\">".$font_color."</FONT>"; } 
		else if($turn==27){$turn=28; $result .= "<FONT color=\"#6600ff\">".$font_color."</FONT>"; } 
		else if($turn==28){$turn=29; $result .= "<FONT color=\"#9900ff\">".$font_color."</FONT>"; } 
		else if($turn==29){$turn=0; $result .= "<FONT color=\"#cc00ff\">".$font_color."</FONT>"; } 
	}
	$result = html_entity_decode($result ,ENT_QUOTES,'UTF-8');
	return $result; 
}

function bbimg($var)
{ 
	/*
	-----------------------------------------------------------------
	menampilkan gambar BBcode
	-----------------------------------------------------------------
	*/
	if (is_array($var[1])){
	foreach($var as $mass){
	@$img = getimagesize($mass[1]); 
	if ($img) return '<img src="' . $mass[1] . '" alt="img" height="' . intval($img[1] / 3 * 2) . '" width="' . intval($img[0] / 3 * 2) . '"/>';
	}}else{
	@$img = getimagesize($var[1]); 
	if ($img) return '<img src="' . $var[1] . '" alt="img" height="' . intval($img[1] / 3 * 2) . '" width="' . intval($img[0] / 3 * 2) . '"/>';
	}
}
?>