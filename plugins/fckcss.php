<?php
header("Content-type: text/css; charset=iso-8859-1");

// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
	define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
require('..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');
?>

body {
	font:14px/17px verdana, sans-serif;
	padding: 4px 1em 4px 1em;
	color: #123;
	background-color: #fff;
}

p { 
	margin: 0px; padding: 0px; 
}


blockquote {
    border-left: 2px solid grey;
    margin-left: 1em;
    padding-left: 1.5em;
    color: #555;
}


.code
{
	border: #8b4513 1px solid;
	padding-right: 5px;
	padding-left: 5px;
	color: #000066;
	font-family: 'Courier New' , Monospace;
	background-color: #ff9933;
}
