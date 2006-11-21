<?
/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas H�ning
	polypager.nicolashoening.de
	
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA' .
*/

header( 'Content-Type: text/html; charset=iso-8859-1' );


// --------------------------------------- Inclusions
// PATH_SEPARATOR doesn't exist in versions of php before  4.3.4. here is the trick to declare it anyway :
if ( !defined('PATH_SEPARATOR') ) {
    define('PATH_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? ';' : ':');
}
// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
require_once("PolyPagerLib_HTMLFraming.php");
require_once("PolyPagerLib_AdminIndex.php");
require_once("PolyPagerLib_Utils.php");
require_once("PolyPagerLib_Showing.php");

// ---------------------------------------
include('auth.php');
$area = "_admin"; 
$sys_info = getSysInfo();
$sys_info["start_page"] = "";
$link = getDBLink();
$params = getShowParameters();
$params["step"] = "all";	//we're showing list views, so show all
	
/* --------------- evaluate actions, print message ------------ */
//installation of database
if ($_POST["cmd"] == "create" or $_GET["cmd"] == "create") {
	$error = create_sys_Tables($link);
	$sys_msg = __('attempted to create sys tables... ');
	if ($error != "") $sys_msg = $sys_msg.__('The dbms reported the following error: ').$error;
	else $sys_msg = $sys_msg.__('The dbms reported no errors.');
	$sys_msg = $sys_msg."<br/>\n";
	
	/*
	$error = chmod_dirs($link);
	$sys_msg = $sys_msg.'__('attempted to chmod directories... ');
	if ($error != "") $sys_msg = $sys_msg.__('The dbms reported the following error: ').$error;
	else $sys_msg = $sys_msg.__('The dbms reported no errors.');
	*/

}

if ($sys_info['admin_name'] == "" or $sys_info['admin_pass'] == ""){
	$sys_msg = $sys_msg.__('Your administratorname or the administratorpassword is empty. You should consider going to the system property section and secure your system!'."</br>\n");
}

//template creation
if ($_POST["template_name"] != "") {
	$error = executeTemplate($_POST["template_name"], $_POST["page_name"]);
	$sys_msg = $sys_msg.__('attempted to create a page by template... ');
	if ($error != "") $sys_msg = $sys_msg.__('The dbms reported the following error: ').$error;
	else $sys_msg = $sys_msg.__('The dbms reported no errors.');
	$sys_msg = $sys_msg."<br/>\n";
}

$path_to_root_dir = "..";
$title = "Admin Area";


/* the function that writes out the data */
function writeData($ind=5) {
	$indent = translateIndent($ind);
	global $params;
	global $sys_msg;
	
	//echo('			<div id="data_admin">'."\n");
	echo($indent.'<h1>Admin Area</h1>'."\n");
	
	//sys msg? write it 
	if ($sys_msg != "") {
		echo($indent.'<div class="sys_msg">'.$sys_msg.'</div>'."\n");
	}
	
	showAdminOptions();
	
	$topic = $params["topic"];
	if ($topic != "") {
		echo($indent.'<h1>-'.__($topic).'-</h1>'."\n");
	}else{
		echo($indent.'<h1>'.__($params["page"]).'</h1>'."\n");
	}
	
	admin_list($ind);
	
	//echo('			</div>'."\n");
}

//now ... we are ready to import a PHP/HTML template
if (strpos($sys_info['skin'], 'picswap')>-1) $skin = 'picswap';
else $skin = $sys_info['skin'];
@include("../style/skins/".$skin."/template.php");

?>