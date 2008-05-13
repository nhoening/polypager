<?
/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas Höning
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


// PATH_SEPARATOR doesn't exist in versions of php before  4.3.4. here is the trick to declare it anyway :
if ( !defined('PATH_SEPARATOR') ) {
    define('PATH_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? ';' : ':');
}
// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}

if (!file_exists("PolyPager_Config.php"))
    die('I need the file "PolyPager_Config.php" in the root directory to go on. <br/><br/> It specifies where to find the database. <br/> You should find "PolyPager_Config.php.template" in your root directory. <br/><br/>Please copy it to make "PolyPager_Config.php" and adjust the values in there.');


set_include_path(get_include_path().PATH_SEPARATOR .'.'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
require_once("PolyPagerLib_HTMLFraming.php");
require_once("PolyPagerLib_Utils.php");
require_once("PolyPagerLib_Sidepane.php");
require_once("PolyPagerLib_Showing.php");

$sys_info = getSysInfo();
header( 'Content-Type: text/html; charset='.$sys_info['encoding'] );

if ($sys_info['whole_site_admin_access']) require_once('admin'.FILE_SEPARATOR.'auth.php');

// ---------------------------------------

$path_to_root_dir = ".";

$link = getDBLink();
$show_params = getShowParameters();

$area = ""; //one of '', '_admin', '_gallery' - makes your template flexible if you want

$known_page = isAKnownPage($show_params["page"]) || $show_params["page"] == "";

if (!$known_page or ($show_params["page"] != '_sys_comments' and isASysPage($show_params["page"]))) {
	$error_msg_text[] = __('There is no known page specified.');
} else if (count($error_msg_text) == 0) {

    $page_info = getPageInfo($show_params["page"]);
    if ($page_info['only_admin_access']) require_once('admin'.FILE_SEPARATOR.'auth.php');
    
	// maybe we need a comment insertion FIRST
	//- afterwards we'll select data to show
	$i_manipulated = true;	//positive assumption
	if ($show_params["cmd"] == "entry") {	// we have a comment entry (for this script there can only be those to be edited)

		require_once("PolyPagerLib_Editing.php");
		$entity = getEntity("_sys_comments");
		$params = getEditParameters();
		
		//let's first check if the comment was ok
		$msg = checkComment($params["values"]["comment"], $params["values"]["time_needed"], $params["values"]["nogarbageplease_"]);
		if ($msg == "") {	//seems ok
	
			$queries = getEditQuery($params["cmd"], "");
			$query = $queries[0];
	
			if ($debug) { echo('<div class="debug">Query is: '.$query.'</div>'); }
	
			//now run db manipulation queries
			$res = mysql_query($query, $link);
			$fehler_nr = mysql_errno($link);
	
			if ($fehler_nr != 0) {
				$i_manipulated = false;
				$error_msg_text[] = __('A database-error ocurred:').' '.mysql_error($link);
			} else {
				$sys_msg_text[] = sprintf(__('The %s-command was successful'), $params["cmd"]);
				if ($debug) { echo('<div class="debug">I used this query: '.$query.'.</div>'."\n");}
				$params["cmd"]="show";
			}
	
		}else{
			$i_manipulated = false;
			$sys_msg_text[] = $msg;
			//refill values for form
			$show_params["values"]["name"] = $params["values"]["name"];
			$show_params["values"]["email"] = $params["values"]["email"];
			$show_params["values"]["www"] = $params["values"]["www"];
			$show_params["values"]["comment"] = $params["values"]["comment"];
		}
		// well, to proceed, our page is not _sys_comments,
		// but the page the comment appears on
		$show_params["page"] = $params["values"]["pagename"];
	}

	//build Show - Query
	$params = $show_params;
	$entity = getEntity($params["page"]);
    $queries = getQuery(true);
    
	// send show quer(y|ies) to DBMS now
	$res = array();
	foreach(array_keys($queries) as $qkey){
		$res[$qkey] = mysql_query($queries[$qkey], $link);
		$error_nr = mysql_errno($link);
		if ($error_nr != 0) {
			$fehler_text = mysql_error($link);
			$error_msg_text[] = __('DB-Error:').' '.$fehler_text;
		}
	}
	
	if (isMultipage($params["page"]) and (eregi('int',$entity['pk-type']) and $params["max"] == "")) { //no other way... db is empty
		$sys_msg_text[] = __('There is no entry in the database yet...');
		$sys_msg_text[] = '<div class="admin_link"><a onmouseover="popup(\''.__('for admins: make a new entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="admin/edit.php?'.$params["page"].'&amp;cmd=new">Enter the first one</a></div>';
	}
	
	// set a title if we show one entry
	if ($res != "" and count($res) > 0 and in_array($params['page'],array_keys($res)) and mysql_num_rows($res[$params['page']]) == 1) {	
		//writing header with a title when we find a good one
		$row = mysql_fetch_array($res[$params['page']], MYSQL_ASSOC);	//get first one
		$title = getTitle($entity,$row);
		//mark our knowledge in "step" param
		$params["step"] = "1";
		mysql_data_seek($res[$params['page']], 0);	//move to initial position again
	} else {
		$title = $params["page"];
        if ($params["page"] == "_search") $title = __('Search');
	}
    
}

/* the function that writes out the data */
function writeData($ind=5) {
	$indent = translateIndent($ind);
	$nind = $ind + 1;
	global $res;
	global $debug;
    global $known_page;
	global $i_manipulated;
	global $params;

    if (clearMsgStack()) return;
    
	if ($known_page) {
		if (isMultipage($params["page"]) and (eregi('int',$entity['pk-type']) and $params["max"] == "")) { //no other way... db is empty
			echo($indent.'<ul id="menu">'."\n");
			echo($indent.'	<div class="sys_msg">'.__('There is no entry in the database yet...').'</div>'."\n");
			echo($indent.'	<div class="admin_link"><a onmouseover="popup(\''.__('for admins: make a new entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="admin/edit.php?'.$params["page"].'&amp;cmd=new">Enter the first one</a></div>'."\n");
			echo($indent.'</ul><ul class="menuend"/>'."\n");
		} else {
			
			//you could type in a too high number - senseless 
			if (!$i_manipulated and $params["nr"] > $params["max"]) { 
                $params["nr"] = $params["max"]; 
                //echo('<div class="sys_msg">'.__('the chosen number was too high - showing newest').'</div>');
            }
			
			$page_info = getPageInfo($params["page"]);
			//------------------------ showing data   --------------
			
			if ($params['page'] != '_search') writeSearchForm(false, $nind);
            
			if (mysql_num_rows($res[$params["page"]])) writeToc($res, false, $nind);
            
            $showid = "";
            
			echo($indent.'<div class="show"');
            if ($params['page'] == "_search") echo(' id="search_results"');
            echo('>'."\n");
			$nind = $ind + 1;
			writeEntries($res, false, $nind, false);
			
            
            $num = mysql_num_rows($res[$params['page']]);
            if ($num > 0 and ($num < getMaxCount($params["page"] ))) {
                echo('<div class="sys_msg">'.__('You are seeing a selection of all entries on this page. '). '<a href="?'.$params["page"].'&amp;step=all">'.__('See all there are.').'</a></div>');
            }
            
			echo($indent.'</div>'."\n");  //end of class "show"
			//--------------------- end showing data  --------------
            
		}
	}
    if (clearMsgStack()) return;
}

useTemplate($path_to_root_dir);

?>
