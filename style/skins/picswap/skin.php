<?php


//this can be one of 'aqua', 'fall', 'saarpreme' or 'uptight'
$colorset = $_GET['colorset'];
if ($colorset == "") $colorset = "aqua";

/* gets the color you need for the actual colorset
	for colors, you might want to look here for more:
	http://graphicdesign.about.com/library/color/blweb.htm
	$code can be 'dark', 'light', 'contrast' or 'data_bg'
*/
function getColor($code) {
	global $colorset;

	switch($code) {
		case 'dark':
			switch($colorset) {
				case 'aqua': return '003399'; break;
				case 'fall': return 'cc3333'; break;
				case 'uptight': return '330000'; break;
				case 'saarpreme': return '36264a'; break;
			}
		case 'light':
			switch($colorset) {
				case 'aqua': return '486cb5'; break;
				case 'fall': return 'ff9900'; break;
				case 'uptight': return 'fff6f6'; break;
				case 'saarpreme': return '70677c'; break;
			}
		case 'contrast':
			switch($colorset) {
				case 'aqua': return '8fc88f'; break;
				case 'fall': return 'ffcc00'; break;
				case 'uptight': return '6ec9ac'; break;
				case 'saarpreme': return 'e06056'; break;
			}
		case 'data_bg':
			switch($colorset) {
				case 'aqua': return 'ecf0f9'; break;
				case 'fall': return 'fbfae1'; break;
				case 'uptight': return 'fff6f6'; break;
				case 'saarpreme': return 'fcfbfd'; break;
			}
		default: return '';
	}
}


header("Content-type: text/css; charset=iso-8859-1");
set_include_path(get_include_path() . PATH_SEPARATOR . '../../..');
require_once("lib/PolyPagerLib_Utils.php");

?>

body {
	font-family: 'Georgia', verdana, sans-serif, arial, helvetica;
	color: #123; /*a lighter black for text...*/
	background-color: #<?=getColor('light');?>;

	<?if($colorset == 'uptight') {
		echo('	background-image: url(pics/uptight/bg.jpg);'."\n");
		echo('	background-repeat: repeat-x;'."\n");
	}
	?>
}

#container {
	<?if($colorset != 'uptight') {
		echo('/*jpg runs with better performance than a gif!*/'."\n");
		echo('	background-image: url(pics/'.$colorset.'/bg.jpg);'."\n");
		echo('	padding-left: 10px;'."\n");
		echo('	padding-right: 10px;'."\n");
	}?>
}
#header {
	height: 130px !important;
	<?
	if ($pic == "") {
		$pictures = scandir_n("pics/bg",0,true,false);
		$i = count($pictures); //index of bg pic
		if ($i > 1) {
			mt_srand((double)microtime()*1000000); //initate mt_rand
			$i = mt_rand(1, $i-1);	//length of $pictures may vary
			echo("	/*randomly picked pic nr. ($i-1) as background*/\n");
		}
		$pic = $pictures[$i-1];
	}
	echo("	background:url(pics/bg/".$pic.") top left no-repeat; /* 800x130 */\n");
	?>
	border-top-width: 5px;
	border-left-width: 1px;
	border-right-width: 1px;
	border-top-style: solid;
	border-top-color: #<?=getColor('dark'); ?>;
	border-left-style: solid;
	border-left-color: #<?=getColor('dark'); ?>;
	border-right-style: solid;
	border-right-color: #<?=getColor('dark'); ?>;
}

#title {
	background: url(../../pics/fill_dark.gif);
	text-align: right;
	font-weight: 800;
	color: #<?=getColor('contrast'); ?>;
}
#title a {
	text-decoration:none;
}

#title a:hover {
	color: #<?=getColor('contrast'); ?>;
}

#impressum {
	background-color: #<?=getColor('dark'); ?>;
	color: #<?=getColor('contrast'); ?>;
}
#impressum a{
	text-decoration:none;
	color: #<?=getColor('contrast'); ?>;
}

#data, #data_admin, #data_gallery {
	border-width: 1px;
	border-style: solid;
	border-color: #<?=getColor('dark'); ?>;
	border-top-width: 0px !important;
	border-top-style: solid;
	border-top-color: #<?=getColor('dark'); ?>;
	background-image: url(pics/<?echo($colorset);?>/heading_s.gif);
	background-repeat:repeat-x;
	background-color: #<?=getColor('data_bg'); ?>;
	padding-top: 25px;
}

/* ------------------- sidepane ------------------------------- */
#sidepane * {
	color: #<?=getColor('data_bg'); ?>;
	padding: 1px;
}

#sidepane #feeds .entry, #sidepane #intro .entry, #searchbox{
	background-color: #<?=getColor('dark'); ?>;
}

#intro .description {
	font-weight: bold;
}

#sidepane a:hover {
	color: #<?=getColor('contrast'); ?>;
}

#feeds div.entry, #feeds div.description, #intro div.description{
	border-bottom-style: solid;
	border-bottom-color: #<?=getColor('data_bg'); ?>;
}

#feeds div.entry {
	border-bottom-width: 1px;
}

#feeds div.description, #intro div.description {
	color: #<?=getColor('contrast'); ?>;
	height: 23px;
	border-bottom-width: 5px;
	margin-bottom: 5px;
	background: transparent url(pics/<?echo($colorset);?>/heading.gif) top right no-repeat;
}

#feeds div.description {
	padding-right: 10px;
	padding-top: 2px;
}

#feeds div.description a{
	/*font-weight: 800;*/
	background: transparent url(../../pics/rss.gif) top right no-repeat;
	color: #<?=getColor('contrast'); ?> !important;
}

#feeds div.entry a{
	color: #<?=getColor('data_bg'); ?> !important;
}


/* ----------------end sidepane ------------------------------- */

/* ------------------- show - divs ------------------------- */

/*.show_entry {
	border-width: 3px;
	border-color: #<?=getColor('contrast'); ?>;
	border-style: solid;
	border-radius: 2em;
	-khtml-border-radius: 2em;
	-moz-border-radius: 2em;
	-o-border-radius: 2em;
	opera-border-radius: 2em;
}*/

.show_entry_with_options {
	background: transparent url(pics/<?=$colorset;?>/corner_s.gif) bottom left no-repeat;
}

.list_entry {
	border-left: 5px solid #<?=getColor('dark'); ?>;
	margin-bottom: 3px;
}

.adop {
	padding-left: 5px;
	border-right: 5px solid #<?=getColor('dark'); ?>;
	border-top: 2px solid #<?=getColor('dark'); ?>;
	width: 60px;
}

.title {
	background: transparent url(pics/<?echo($colorset);?>/heading.gif) top left repeat-y;
	padding-left: 3px;
	color: #<?=getColor('contrast'); ?>;
}

.title a.entry_title_link {text-decoration: none;}
.title a.entry_title_link:hover {color:#<?=getColor('contrast'); ?>;}

.label{
    text-align:left;
	color: #<?=getColor('dark'); ?>;
	display: block;
	width: 100%;
}

div.show .options {
	border-left: 3px solid #d0ccc6;
	/*border-bottom: 3px solid #d0ccc6;*/
	padding: 0;
	background: transparent url(pics/<?echo($colorset);?>/corner_b.gif) bottom left no-repeat;
}
div.show .options span {
	 font-size: 0.85em;
	 color: #d0ccc6;

}
.edit {
	display:block;

}

div.show .options span.whole_link{
	 text-align: right;
	 font-size: 0.85em;
}
/* ----------------end show - divs ------------------------- */

/* ------------------- menue------------------------------- */
#menu {
	background:transparent;
}
#menu ul{
	text-align:right;
}

#main_menu {
	position: relative; top: 94px; left: 0px !important;
	background: url(../../pics/fill_dark.gif);
}
ul#main_menu li, #sub_menus ul li  {
	background:transparent;
}

ul#main_menu li {
	position:relative; top: 1px;
}

ul#main_menu li a:link, #sub_menus li a:link {
	padding-right: 4px;
	display:block; 
}

ul#main_menu li a:hover, #sub_menus li a:hover {
	height: 19px !important;
}

ul#main_menu li a:link, #sub_menus li a:link {
	color: #<?=getColor('contrast'); ?> ! important;
	text-decoration: none;
}
ul#main_menu li a:visited, #sub_menus li a:visited {
	color: #<?=getColor('contrast'); ?> ! important;
	text-decoration: none;
}
/* hovering and here: sliding doors */
ul#main_menu li:hover, ul#main_menu li.here {
	background:transparent url(pics/<?echo($colorset);?>/tab_left.gif) no-repeat top left !important;
}
ul#main_menu li:hover a, ul#main_menu li.here a {
	background:transparent url(pics/<?echo($colorset);?>/tab_right.gif) no-repeat top right !important;
}
ul#main_menu li a:active, #sub_menus li a:active {
	color: #<?=getColor('contrast'); ?> ! important;
	text-decoration: none;
}
/* these are the sublists */
#sub_menus {position: relative; top: 83px; left: 0px;}
#sub_menus ul {}

/* hovering and here: sliding doors */
#sub_menus li:hover, #sub_menus li.here {
	background:transparent url(pics/<?echo($colorset);?>/tab_left_sub.gif) no-repeat top left !important;
}
#sub_menus li:hover a, #sub_menus li.here a {
	background:transparent url(pics/<?echo($colorset);?>/tab_right_sub.gif) no-repeat top right !important;
	color: #<?=getColor('dark'); ?> !important;
}

/* "here" must be visible a little*/
ul#main_menu li.here a, #sub_menus li.here a {
	text-decoration: underline;
}

/* -----------------end menue ------------------------------ */

/* ----------------------- typography ---------------------- */
/* --  1. Links -- */
a {
	text-decoration: none;
	font-weight: bold;
	color:#<?=getColor('contrast'); ?>;
}

a:visited {
	text-decoration: none;
	font-weight: bold;
	color:#<?=getColor('contrast'); ?>;
}

a:hover {
	text-decoration: underline;
	color:#<?=getColor('dark'); ?>;
}

a:active {
	text-decoration: none;
	color:#<?=getColor('contrast'); ?>;
}

/* links in text : small as the text */
.show_entry_with_options a, .show_entry a, #feeds a {
	font: 600 14px/17px palatino, georgia !important;
}

/* --  2. Headlines -- */
h1 {
	font-weight: bold;
	color: #<?=getColor('dark'); ?>;
	width: 550px;
}
h2 {
	font-weight: normal;
	color: #<?=getColor('contrast'); ?>;
	margin-top: 5px;
	width: 550px;
	}
h3 {
	font-weight: normal;
	color: #<?=getColor('dark'); ?>;
	width: 550px;
}
h4 {
	font-weight: bold;
	width: 550px;
}
h5 {
	font-weight: bold;
	width: 550px;
	}
h6 {
	font-weight: bold;
	width: 550px;
}

/* --------------------end typography ---------------------- */

/* ---------------------system styles ---------------------- */
.high {
	background-color: #6ee;
}
.sys_msg {
	 color: #ff5107 !important;
	 background-color: #eee !important;
	 border: 2px solid #999 !important;
}
.sys_msg a{
	 color: #ff5107 !important;
}
#admin_options {
	 color: #ff5107;
	 background-color: #eee;
	 border: 2px solid #999;
	 float: right;
}
.debug {
	
	display:block;
	background-color: red;
}

blockquote {
    border-left: 2px solid grey;
    margin-left: 1em;
    padding-left: 1.5em;
    color: #555;
}

/* -----------------end system styles ---------------------- */

/* ------------------- predefined divs --------------------- */

/* in editing */
table tr.edit td.last_edited_label {
	border-width: 1px;
	border-style: solid;
	border-color: #aaa;
	text-align: center;
}

.state_label {
	background-color: #040;
	border-width: 1px;
	border-style: solid;
	border-color: #123;
}


/* ----------------end predefined divs --------------------- */

/* ------------------- search + toc ----------------------------- */

.clicked #toc_content_link, .clicked #search_content_link{
	background:transparent url(pics/opt_right.gif) no-repeat top right !important;
}

.clicked{
	width:200px;
	background:transparent url(pics/opt_left.gif) no-repeat top left !important;
}

#toc_content, #search_content, #toc .group{
	background-color: #888;
}

#search_option_range {
	text-align: center;
	border-top-width: 1px;
	border-top-style: solid;
	border-top-color: #ddd;
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #ddd;
}
/* --table of contents -- */

#toc ul {
	list-style: none;
}
#toc .group_heading {
	font-weight: 800;
	color: #<?=getColor('dark'); ?>;
}

#searchbox button, #searchbox input{
    color: black !important;
}
/* ----------------end search ------------------------------ */

/*  ---------------------- forms --------------------------- */

.form_submits {
	text-align: right;
}

form.edit input, form textarea, form button { /*form select {*/
	background:#eee;
	border-width:1px;
	border-style: solid;
	border-color: #aaa;
}
/*Focused Form inputs appear lighter in Edit Forms*/
form.edit input:focus, form textarea:focus, form button.focus{ /*form select:focus {*/
	border-color:#<?=getColor('dark'); ?>;
	background-color:#fff;
}

form select {
	vertical-align: middle;
}
/*  -------------------end forms --------------------------- */

/*  ---------------------- comments --------------------------- */
#comments .show_entry, #comments .show_entry_with_options{
	border: 1px solid #<?=getColor('dark'); ?> !important;
    padding-left: 10px !important;
    background: url('pics/comments.gif') left top no-repeat;
}

#comments .value, .comment_prefix {
	color: #<?=getColor('dark');?>;
}

/*  -------------------end comments --------------------------- */

