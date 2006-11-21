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

include('auth.php');
header( 'Content-Type: text/html; charset=iso-8859-1' );
// --------------------------------------- Lib Inclusions
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
require_once("PolyPagerLib_Utils.php");
require_once("PolyPagerLib_HTMLForms.php");
require_once("PolyPagerLib_Editing.php");
require_once("PolyPagerLib_AdminIndex.php");
require_once("PolyPagerLib_Sidepane.php");

// ---------------------------------------

$sys_info = getSysInfo();
$area = "_admin"; 

$params = getEditParameters();
$entity = getEntity($params["page"]);

// -------------------- maybe we need a data manipulation FIRST
// -------------------- afterwards we'll select data to show
	$i_manipulated = true;	//positive assumption
	
	if ($params["cmd"] == "entry" or $params["cmd"] == "edit" or $params["cmd"] == "delete") {

		//if system data has been changed, reset $sys_info
		if (($params["cmd"] == "edit" or $params["cmd"] == "entry") and $params["page"] == "_sys_sys") $sys_info = "";

		$query = getEditQuery($params["cmd"], "");
		echo("Query is: ".$query."<br/>");
		//now run db manipulation quer(y|ies) - we might get a few because of  foreign keys
		if ($query != "") {
			$queries = explode(";",$query);
			foreach($queries as $q) {
				echo("running:".$q."<br/>");
				if ($q!=""){
					$res = mysql_query($q, getDBLink());
					$fehler_nr .= mysql_errno(getDBLink());
					$mysqlerror .= mysql_error(getDBLink());
				}
			}
		} else $fehler_nr = 1;
		
		if ($fehler_nr != 0) {
			$i_manipulated = false;
			$sys_msg_text = '				<div class="sys_msg">'.__('A database-error ocurred...').' '.$mysqlerror.'</div>'."\n";
		} else {
			$sys_msg_text = '<div class="sys_msg">'.sprintf(__('The %s-command was successful'), $params["cmd"]).'.</div>';
			if($debug) { $sys_msg_text = $sys_msg_text.'<div class="debug">I used this query: '.$query.'.</div>'; }
			
			//ensure consistency
			ensureConsistency($params);
			
			//reset lazy data - so all we show is fresh
			resetLazyData();
			
			// make a new SELECT Query (we must show something) - later this could get more dynamic
			$query = getEditQuery("show", "");
			if($params["cmd"] == "entry") {
				//here we should show the highest number (that is the one we just inserted)
				$newID = mysql_insert_id();
				$query = getEditQuery("show", $newID);
				$params["nr"] = $newID;
			}
			
			//now that we have the new ID, we can feed it
			if ($params["feed"] == '1') handleFeed($params);
			
			
			// now we switch to another command (see getEditParameters() for documentation)
			if(isMultipage($params["page"])) {
				if($params["cmd"] == "delete") $params["cmd"]="new"; else $params["cmd"]="show";
			} else {
				if($params["cmd"] == "delete") $params["cmd"]="show";
			}
				
			
		}
	} else {
		$query = getEditQuery($params["cmd"], "");
	}
// ---------------------------------------

if ($params["nr"] == "") $params["nr"] = $newID;
$path_to_root_dir = "..";

if ($params["page"] == "" or !isAKnownPage($params["page"])) {	//nothing to do
	$title = __('unknown page').': '.$params["page"];
	$error_msg_text = '<div class="sys_msg">'.__('There is no known page specified.').'</div>'."\n";
}

/* the function that writes out the data */
function writeData($ind=4) {
	$indent = translateIndent($ind);
	$nind = $ind + 1;
	global $params;
	global $debug;
	global $query;
	global $i_manipulated;
	global $sys_msg_text;
	global $error_msg_text;	//hopefully empty :-)
	
	showAdminOptions();
	
	if($debug) {
		echo('<div class="debug">Query is: '.$query.'</div>');
		echo('<div class="debug">cmd is '.$params["cmd"].'</div>');
	}
	
	//error? write it and return
	if ($error_msg_text != "") {
		echo($error_msg_text);
		return;
	}
	
	//sys msg? write it 
	if ($sys_msg_text != "") {
		echo($indent.$sys_msg_text);
	}
	
	$title = __('Editing').' '.$params["page"];
	$entity = getEntity($params["page"]);

	//echo('				<div id="data_admin">'."\n");
	
	$page = $params["page"];
	if ($page == "_sys_singlepages" or $page == "_sys_multipages" or $page == "_sys_intros") $page = "_sys_pages";
	
	//showing some navigation links
	echo('				<ul>'."\n");
	if ($page == "_sys_pages") {	//entry on that  page
		$pname = $params["values"]["name"];
		if ($pname == "") $pname = $_GET["name"];
		if ($pname == "") $pname = $_GET["page"];
		echo('					<li><a
			onmouseover="popup(\''.sprintf(__('click to make a new entry on the %s - page'),$pname).'\')" onmouseout="kill()" title="" onfocus="this.blur()"
			href="edit.php?'.$pname.'&amp;cmd=new&amp;from='.$params["from"].'&topic=content&group='.$params["group"].'">'.__('insert a new entry').'</a></li>'."\n");
	} else if ($entity["one_entry_only"] != "1"){
		echo('					<li>'.__('insert a').' <a
			onmouseover="popup(\''.sprintf(__('click to insert a new record in [%s]'),$params["page"]).'\')" onmouseout="kill()" title="" onfocus="this.blur()"
			href="edit.php?'.$params["page"].'&amp;cmd=new&amp;from='.$params["from"].'&topic='.$params["topic"].'&group='.$params["group"].'">'.__('new record').'</a></li>'."\n");
	}
	
	if ($params["from"] == "list") echo('					<li><a onmouseover="popup(\''.__('go back to the list overview where you chose the edited entry.').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href=".?'.$page.'&topic='.$params["topic"].'&group='.$params["group"].'">'.__('back to list view').'</a></li>'."\n");
	if ($params["from"] == "admin") echo('					<li><a onmouseover="popup(\''.__('go back to the administration page.').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href=".">'.__('back to admin index').'</a></li>'."\n");
	$page_info = getPageInfo($params["page"]);
	if($page_info["in_menue"] == "0" and !strpos($params["page"], "_sys_")) {
		echo('					<li><a onmouseover="popup(\''.__('click to see the public page').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="../?'.$params['page'].'&amp;group='.$params['group'].'">'.__('see the page').'</a></li>'."\n");
	}
	echo('				</ul>'."\n");

	//heading with explanation what to do here
	if (!isASysPage($params["page"])) {
		echo('				<h1>'.__('Editing page:').' '.$params["page"]);
		if (isMultipage($params["page"])) {
			if($entity["publish_field"] != "") $publish_info = __('Uncheck the publish-checkbox if you do not want users to see the entry yet.');
			$helptext = sprintf(__( 'Here you can edit an entry. You find an HTML Form where you can type in values.&lt;br/&gt;Hit the Save-Button to save your changes to the database.&lt;br/&gt;You can also delete existing entries with the Delete-Button.&lt;br/&gt;&lt;br/&gt; %s'
				),$publish_info);
			writeHelpLink($indent, $helptext);
		}else {
			$helptext = sprintf(__('Here you can edit the %s-page.&lt;br/&gt;&lt;br/&gt; A page consists of sections. One section might be all you need. If so, go ahead and type into the header- and the textfield what you want to publish (uncheck the publish-checkbox if you do not want users to see it yet).&lt;br/&gt;&lt;br/&gt;You can always add other sections if the page grows more complex. Then it might also be useful to check the show-in-submenu checkbosso users can access your structure quickly.'
				),$params['page']);
			writeHelpLink($indent, $helptext);
		}
		echo('</h1>'."\n");
	} else if ($params["page"] == "_sys_intros") {
		if ($params["nr"] != '_sys_impressum') echo('				<h1>'.__('Editing intro for page').' '.$params["nr"].'</h1>');
		else echo('				<h1>'.__('Editing impressum').'</h1>');
	} else if ($params["page"] == "_sys_sys") {
		echo('				<h1>'.__('Editing system properties').'</h1>');
		//link to write impressum
		$link_text = __('Here you can edit the impressum (it appears at the bottom of each page).');
		echo('					&nbsp;|&nbsp;<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="edit.php?_sys_intros&nr=_sys_impressum&page='.$params['page'].'from=list&topic='.$params["topic"].'">'.__('edit impressum').'</a>&nbsp;|&nbsp;'."\n");
	} else if ($params["page"] == "_sys_multipages" and $params["cmd"] != "new") {
		echo('				<h1>'.__('Editing a multipage').'</h1>');
	} else if ($params["page"] == "_sys_multipages" and $params["cmd"] == "new") {
		echo('				<h1>'.__('Creating a multipage').'</h1>');
	}  else if ($params["page"] == "_sys_singlepages" and $params["cmd"] != "new") {
		echo('				<h1>'.__('Editing a singlepage').'</h1>');
	} else if ($params["page"] == "_sys_singlepages" and $params["cmd"] == "new") {
		echo('				<h1>'.__('Creating a singlepage').'</h1>');
	}

	$iwrote = false; //negative assumption about writeHMLForm
	if ($i_manipulated) {	//else we don't need to procede
		//now, finally, get data from db for filling forms if we need any
		if ($params["cmd"] != "new") {
			$res = mysql_query($query, getDBLink());
			
			$fehler_nr = mysql_errno(getDBLink());
				if($debug) { echo('<div class="debug">Query is: '.$query.'</div>'); }
			if ($fehler_nr != 0) {
				$fehler_text = mysql_error(getDBLink());
				echo('				<div class="sys_msg">'.__('DB-Error:').' '.$fehler_text.'</div>'."\n");
			}

			// now write all entries we have (for singlepages these are all sections,
			// multipages and others should be only one)
			while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
				writeHTMLForm($row, "edit.php", true, true, $nind,"edit_form");
				$iwrote = true;
			}
		} else {
			// if we had none, maybe we want an empty form
			writeHTMLForm("", "edit.php", true, true, $nind, "edit_form");
			$iwrote = true;
		}


		if ($iwrote == false) {
			echo('					<br/><br/>'."\n");
			if ($entity["one_entry_only"] != "1") {
				//makes no sense anymore, search is off or invisible on the pages
				//echo('				<a href="../?'.$params["page"].'#search">'.__('search for what you are looking for').'</a>'."\n");
			} else {
				//when we have a number, we should enter a new entry using that!
				if ($params["nr"] == "") $the_cmd = "new";
				else $the_cmd = "entry";
				echo('				<a href="edit.php?'.$params["page"].'&nr='.$params["nr"].'&cmd='.$the_cmd.'">'.__('there is nothing here yet - create that entry now').'</a>'."\n");
			}
		}
	}
}

//now ... we are ready to import a PHP/HTML template
if (strpos($sys_info['skin'], 'picswap')>-1) $skin = 'picswap';
else $skin = $sys_info['skin'];
@include("../style/skins/".$skin."/template.php");
	
?>