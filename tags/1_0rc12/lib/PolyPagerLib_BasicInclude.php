<?php
// you can use this to add custom PHP scripts
// include this file, then write your output in a function called writeData() and call
// useTemplate($path_to_root_dir);

if ( !defined('FILE_SEPARATOR') ) {
     define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}

require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_Utils.php");
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_HTMLFraming.php");
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_Sidepane.php");
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_Showing.php");

header( 'Content-Type: text/html; charset=utf-8');

$path_to_root_dir = ".";
$area = ""; 
?>