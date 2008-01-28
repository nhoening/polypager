<?php
header("Content-type: text/css; charset=iso-8859-1");

// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
	define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
require('..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');
$sys_info = getSysInfo();
//backdoor hack to get all picswap colorsets
		if (utf8_strpos($sys_info['skin'],'picswap')>-1) {
			$skin = 'picswap';
			$css = 'picswap/'.$sys_info['skin'].'.css';
		}else {
			$skin = $sys_info["skin"];
			$css = $skin.'/skin.css';
		}
?>

@import url("../style/skins/<?=$css?>");	/* map */
body {
	padding: 4px 1em 4px 1em;
	color: #123;
	background-color: #fff !important;
    background-image: none !important;
}
