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
require_once("PolyPagerLib_Sidepane.php");
require_once("PolyPagerLib_Editing.php");


$sys_info = getSysInfo();
header( 'Content-Type: text/html; charset='.$sys_info['encoding'].'' );

// ---------------------------------------
include('auth.php');

$area = "_admin"; 
$path_to_root_dir = "..";
$sys_info["start_page"] = "";
$link = getDBLink();
$params = getShowParameters();
$params["step"] = "all";	//we're showing list views, so show all


if ($params["page"] == "" or isAKnownPage($params["page"])){
	/* --------------- evaluate actions, print message ------------ */
    
    // multiple copmmands
    execBatchCmds();
    
	// installation of database
	if ($_POST["cmd"] == "create" or $_GET["cmd"] == "create") {
		$error = create_sys_Tables($link);
		$sys_msg = __('attempted to create sys tables... ');
		if ($error != "") $sys_msg .= __('The dbms reported the following error: ').$error;
		else $sys_msg .= __('The dbms reported no errors.');
		$sys_msg .= "<br/>\n";
		$sys_msg_text[] = $sys_msg;
	}
	
	// template creation
	if ($_GET["template_name"] != "") {
		$error = executeTemplate($_GET["template_name"], $_GET["page_name"]);
		$sys_msg .= __('attempted to create a page by template... ');
		if ($error != "") $sys_msg .= __('The dbms reported the following error: ').$error;
		else $sys_msg .= __('The dbms reported no errors.');
		$sys_msg .= "<br/>\n";
        $sys_msg_text[] = $sys_msg;
	}

}else{
	$title = __('unknown page').': '.$params["page"];
	$sys_msg_text[] = __('There is no known page specified.');
}
$title = "Admin Area";


$sys_info = getSysInfo();
if ($sys_info['admin_name'] == "" or $sys_info['admin_pass'] == ""){
    $sys_msg_text[] = __('Your administrator-name or the administrator-password is empty. You should consider going to the ').'<a href="edit.php?_sys_sys&from=admin">'.__('system property section').'</a>'.__(' and secure your system!');
}
    

/* the function that writes out the data */
function writeData($ind=5) {
	$indent = translateIndent($ind);
	global $params;
    
	echo($indent.'<h1>Admin Area</h1>'."\n");
        
    if (clearMsgStack()) return;
    
	if ($params["page"] == "" or isAKnownPage($params["page"])){
		showAdminOptions();
		
		$topic = $params["topic"];
		if ($topic != "") {
			if ($topic=='fields') $h = __('On this page, you can make extra statements about fields, for example a label or a list of possible values. To do this, you need to create an entry for that field first.');
			echo($indent.'<h1>-'.__($topic).'-</h1>'."\n");
		}else{
			echo($indent.'<h1>'.__($params["page"]).'</h1>'."\n");
		}
		
		admin_list($ind);
	}
    if (clearMsgStack()) return;
}

useTemplate($path_to_root_dir);

?>
