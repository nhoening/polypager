<?
/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas Hšning
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

// PATH_SEPARATOR doesn't exist in versions of php before  4.3.4. here is the trick to declare it anyway :
if ( !defined('PATH_SEPARATOR') ) {
    define('PATH_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? ';' : ':');
}
// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}

set_include_path(get_include_path() . PATH_SEPARATOR . '.'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
require_once("PolyPagerLib_HTMLFraming.php");
require_once("PolyPagerLib_Utils.php");
require_once("PolyPagerLib_Sidepane.php");
require_once("PolyPagerLib_Showing.php");

// ---------------------------------------
$area = ""; //one of '', '_admin', '_gallery' - makes your template flexible if you want
$link = getDBLink();
$show_params = getShowParameters();
$path_to_root_dir = ".";
$known_page = true;

if ($show_params["page"] == "" or !isAKnownPage($show_params["page"])
		or ($show_params["page"] != '_sys_comments' and isASysPage($show_params["page"]))) {	//nothing to do
	$error_msg_text = '<div class="sys_msg">'.__('There is no known page specified.').'</div>'."\n";
	$known_page = false;
} else {

	// maybe we need a comment insertion FIRST
	//- afterwards we'll select data to show
	$i_manipulated = true;	//positive assumption
	if ($show_params["cmd"] == "entry") {	//for this script there can only be comment entries!

		require_once("PolyPagerLib_Editing.php");
		$entity = getEntity("_sys_comments");
		$params = getEditParameters();
		
		//let's first check if the comment was ok
		$msg = checkComment($params["values"]["comment"], $params["values"]["time_needed"]);
		if ($msg == "") {	//seems ok
			if ($debug) {
				echo('<div class="debug">Page: '.$params["page"].'</div>');
				echo('<div class="debug">cmd: '.$params["cmd"].'</div>');
			}
	
			$queries = getEditQuery($params["cmd"], "");
			$query = $queries[0];
	
			if ($debug) { echo('<div class="debug">Query is: '.$query.'</div>'); }
	
			//now run db manipulation queries
			$res = mysql_query($query, $link);
			$fehler_nr = mysql_errno($link);
	
			if ($fehler_nr != 0) {
				$i_manipulated = false;
				$error_msg_text = '				<div class="sys_msg">'.__('A database-error ocurred:').' '.mysql_error($link).'</div>'."\n";
			} else {
				$sys_msg_text = '<div class="sys_msg">'.sprintf(__('The %s-command was successful'), $params["cmd"]).'.</div>';
				if ($debug) { $sys_msg_text = $sys_msg_text.'<div class="debug">I used this query: '.$query.'.</div>';}
				$params["cmd"]="show";
			}
	
		}else{
			$i_manipulated = false;
			$error_msg_text = '<div class="sys_msg">'.$msg.'</div>';
			//refill values for form
			$show_params["values"]["name"] = $params["values"]["name"];
			$show_params["values"]["email"] = $params["values"]["email"];
			$show_params["values"]["www"] = $params["values"]["www"];
			$show_params["values"]["comment"] = $params["values"]["comment"];
		}
		// well, to proceed, our page is not _sys_comments
		// but the page the comment appears on
		$show_params["page"] = $params["values"]["pagename"];
	}

	//build Show - Query
	$params = $show_params;
	$entity = getEntity($params["page"]);
	$query = getQuery(true);
	$sys_info = getSysInfo();

	if ($debug) { echo('<div class="debug">Query is: '.$query.'</div>'); }


	// send show query to DBMS now
	$res = mysql_query($query, $link);
	$error_nr = mysql_errno($link);
	if ($error_nr != 0) {
		$fehler_text = mysql_error($link);
		$error_msg_text .= '<div class="sys_msg">'.__('DB-Error:').' '.$fehler_text.'</div>'."\n";
	}

	if (isMultipage($params["page"]) and $params["max"] == "") { //no other way... db is empty
		$sys_msg_text .= '<div class="sys_msg">'.__('There is no entry in the database yet...').'</div>'."\n";
		$sys_msg_text .= '<div class="admin_link"><a onmouseover="popup(\''.__('for admins: make a new entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="admin/edit.php?'.$params["page"].'&amp;cmd=new">Enter the first one</a></div>'."\n";
	
	}
	
	// set a title
	// when only one entry is given, we can do some fancy stuff:
	if ($res != "" and mysql_num_rows($res) == 1) {	//we should have a title
		//writing header with a title when we find a good one
		$row = mysql_fetch_array($res, MYSQL_ASSOC);	//get first one
		$title = $row[$entity["title_field"]];
		//mark our knowledge in "step" param
		$params["step"] = "1";
		mysql_data_seek($res, 0);	//move to initial position again
	} else {
		$title = $params["page"];
	}
}

/* the function that writes out the data */
function writeData($ind=5) {
	$indent = translateIndent($ind);
	$nind = $ind + 1;
	global $res;
	global $debug;
	global $i_manipulated;
	global $params;
	global $sys_msg_text;
	global $error_msg_text;	//hopefully empty :-)
	global $known_page;
	
	//error? write it and return
	if ($error_msg_text != "") {
		echo($error_msg_text);
		return;
	}
	
	//sys msg? write it 
	if ($sys_msg_text != "") {
		echo($sys_msg_text);
	}
	
	if ($known_page) {
		if (isMultipage($params["page"]) and $params["max"] == "") { //no other way... db is empty
			echo($indent.'<ul id="menu">'."\n");
			echo($indent.'	<div class="sys_msg">'.__('There is no entry in the database yet...').'</div>'."\n");
			echo($indent.'	<div class="admin_link"><a onmouseover="popup(\''.__('for admins: make a new entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="admin/edit.php?'.$params["page"].'&amp;cmd=new">Enter the first one</a></div>'."\n");
			echo($indent.'</ul><ul class="menuend"/>'."\n");
		} else {
			//we asssembled this while inserting a comment
			echo($sys_msg_text);
			
			//you could type in a too high number - senseless 
			if (!$i_manipulated and $params["nr"] > $params["max"]) { $params["nr"] = $params["max"]; echo('<div class="sys_msg">'.__('the chosen number was too high - showing newest').'</div>');};
			
			$page_info = getPageInfo($params["page"]);
			//------------------------ showing data   --------------
			//removed by popular request
			echo($indent.'<h1 style="display:none;">'.$params["page"].'</h1>'."\n");
			
			writeSearchForm(false, $nind);
			writeToc($res, false, $nind);
	
			echo($indent.'<div class="show">'."\n");
			$nind = $ind + 1;
			writeEntries($res, false, $nind, false);
			
			//if there were entries but we did not show all
			if($debug) echo('<div class="debug">max: '.$params['max'].' step param: '.$params["step"].' show count: '.mysql_num_rows($res).'</div>');
			if (mysql_num_rows($res) > 0 and $params["step"] != "all" and ($params["step"] < $params['max'] and $params['step'] != 1 )) {
				echo('<div class="sys_msg">'.__('you are seeing a selection of all entries on this page.').'<a href="?'.$params["page"].'&step=all"> click</a>'.__(' to see all there are.').'</div>');
			}
			echo($indent.'</div>'."\n");  //end of class "show"
			//--------------------- end showing data  --------------
		}
	}
}

//now ... we are ready to import a PHP/HTML template
if (strpos($sys_info['skin'], 'picswap')>-1) $skin = 'picswap';
else $skin = $sys_info['skin'];
@include("style/skins/".$skin."/template.php");
?>
