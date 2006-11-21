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
    	
	/* this function will provide Code the header of each site
		here goes:
		- the Doctype
		- the head

	*/
	function writeDocType($ind=0) {
		$indent = translateIndent($ind);
		echo($indent.'<?xml version="1.0" encoding="ISO-8859-1"?>'."\n"); 
		echo($indent.'<!DOCTYPE html
			PUBLIC "-//W3C//DTD XHTML 1.0 STRICT//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n");
	}
	
	function writeHeader($ind=1) {
		$indent = translateIndent($ind);
		global $title;
		global $path_to_root_dir;
		$sys_info = getSysInfo();
		global $version;

		echo($indent.'<head>'."\n");
		echo($indent.'	<title>'.$title.' - '.$sys_info["title"].'</title>'."\n");
		echo($indent.'	<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1"/>'."\n");
		echo($indent.'	<meta name="description" content="'.$sys_info["title"].'"/>'."\n");
		echo($indent.'	<meta name="DC.creator" content="'.$sys_info["author"].'"/>'."\n");
		echo($indent.'	<meta name="DC.generator" content="PolyPager Version '.$version.'"/>'."\n");
		echo($indent.'	<meta name="keywords" content="'.$sys_info["keywords"].'"/>'."\n");
		if ($path_to_root_dir != ".") echo($indent.'	<meta name="robots" content="noindex, nofollow" />');
		//echo($indent.'	<meta name="date" content="'.date('Y-m-d').'"></meta>'."\n");
		echo($indent.'	<link rel="Shortcut Icon" href="/favicon.ico" type="image/x-icon"></link>'."\n");
		echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/scripts/javascript.php"></script>'."\n");
		echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/scripts/popup.js"></script>'."\n");
		//backdoor hack to get all picswap colorsets
		if (strpos($sys_info['skin'],'picswap')>-1) {
			$skin = 'picswap';
			$css = 'picswap/'.$sys_info['skin'].'.css';
		}else {
			$skin = $sys_info["skin"];
			$css = $sys_info["skin"].'/skin.css';
		}
		echo($indent.'	<link rel="stylesheet" href="'.$path_to_root_dir.'/style/skins/'.$css.'" type="text/css"></link>'."\n");
		echo($indent.'	<link rel="stylesheet" href="'.$path_to_root_dir.'/style/user.css" type="text/css"></link>'."\n");
		echo($indent.'	<link rel="stylesheet" href="'.$path_to_root_dir.'/style/basestyles.css" type="text/css"></link>'."\n");
		echo($indent.'	<!--[if gte IE 5]>'."\n");
		echo($indent.'		<link href="'.$path_to_root_dir.'/style/skins/'.$skin.'/iefix.css" rel="stylesheet" type="text/css"/>'."\n");
		echo($indent.'	<![endif]-->'."\n");
		if ($sys_info["feed_amount"] > 0) {
			echo($indent.'	<link rel="alternate" type="application/rss+xml" title="'.$sys_info["title"].' as RSS-Feed" href="'.$path_to_root_dir.'/rss.php"></link>'."\n");
		}
		echo('	</head>'."\n");
	}
	
	/*this function will write the title	*/
	function writeTitle($ind=4) {
		$indent = translateIndent($ind);
		global $path_to_root_dir;
		global $params;
		$sys_info = getSysInfo();
		echo($indent.'<div id="title"><span id="text"><a href="'.$path_to_root_dir.'">'.$sys_info["title"].'</a></span><a href="'.$path_to_root_dir.'/admin/" onmouseover="popup(\''.__('Administration Page').'\')" onmouseout="kill()" title="" onfocus="this.blur()">#</a></div>'."\n");
	}
	
	/*this function will provide Code the footer of each page	*/
	function writeFooter($ind=3) {
		$indent = translateIndent($ind);
		echo($indent.'<div id="bottom">'."\n");
		$query = "SELECT * FROM _sys_intros WHERE tablename='_sys_impressum'";
		$res = mysql_query($query, getDBLink());
		$error_nr = mysql_errno(getDBLink());
		if ($error_nr == 0) {
			$row = mysql_fetch_array($res, MYSQL_ASSOC);
			if ($row["intro"] != "") {
				echo($indent.'	<span id="impressum">'.$row["intro"].'</span>'."\n");
			}
		}
		echo($indent.'</div>'."\n");
	}
	
	/*	compares two array entries, using the menue_index entries
	*/
	function cmpByMenueIndex($a, $b) {
		if ($a['menue_index'] == $b['menue_index']) return 0;
		return ($a['menue_index'] < $b['menue_index']) ? -1 : 1;
	}
		
	/*
	The menu is the part of the application that makes navigation possible.
	We have two menulevels: mainmenu and submenus.
	mainmenu entails all pages that are supposed to be accesible.
	*/
	function writeMenu($ind=4) {
		$indent = translateIndent($ind);
		global $path_to_root_dir;
		global $params;
		$dblink = getDBLink();
		global $debug;
		$no_sys_tables = false;
		
		/* ---------------------- getting sections --------------------------*/
		$query = "SELECT id, pagename, heading, publish, in_submenu, order_index from _sys_sections
					WHERE publish = 1
					GROUP BY pagename, heading ORDER BY order_index ASC";
		$res = mysql_query($query, $dblink);
		$fehler_nr = mysql_errno($dblink);
		if ($debug) { echo('<div class="debug">Query is: '.$query.'</div>'); }
		if ($fehler_nr!==0) {
			$fehler_text=mysql_error($dblink);
			if ($debug) echo('<div class="sys_msg">DB-Error: '.$fehler_text.'</div>'."\n");
			//this is the first place where we see that sys-tables don't exist!!
			if (eregi("doesn't exist", $fehler_text) and $params["cmd"] != 'create') {
				$no_sys_tables = true;
			}
		}
		
		$sections = array(); //build a 2-dimensional array with 
							 //key:page value:heading and order_index
		while($res and $row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			if ($sections[$row["pagename"]] == "") {	//add new array
				$tmp = array("heading" => $row["heading"], 
								"order_index" => $row["order_index"]);
				$tmp2 = array($tmp);
				$page_info = getPageInfo($row["pagename"]);
				if ($row["in_submenu"] == 1 or $page_info["grouplist"] != "") $sections[$row["pagename"]] = $tmp2;
			} else {
				$tmp = $sections[$row["pagename"]];
				$tmp[count($tmp)] = array("heading" => $row["heading"], 
										"order_index" => $row["order_index"]);
				$page_info = getPageInfo($row["pagename"]);
				if ($row["in_submenu"] == 1 or $page_info["grouplist"] != "") $sections[$row["pagename"]] = $tmp;
			}
		}
		
		/* -------------------end getting sections --------------------------*/
		
		/* ---------------------- writing menues ----------------------------*/
		echo($indent.'<ul id="main_menu">'."\n");
		$sys_info = getSysInfo();
		
		$pages = getPages();
		//sort the pages according to the menue_index before we proceed
		$in_gallery = false;
		if (eregi('user'.FILE_SEPARATOR.'Image',getcwd())) { //we're in the gallery
			$in_gallery = true;
		}
		uasort($pages, "cmpByMenueIndex");
		foreach ($pages as $p) {
			if($p["in_menue"] == "1") {
				//actual page menu entry has a special class
				if (($params["page"] == $p["name"] and !$in_gallery) 
					and !includedByAdminScript($path_to_root_dir)) {
					$classAtt = 'here';	
				}
				else $classAtt = 'not_here';

				if (isMultipage($p["name"])) {
					$theLink = "?".$p["name"];
					$tmp_entity = getEntity($p["name"]);
					if ($tmp_entity["group"] != "") $has_sub = true;
					else $has_sub = false;
				} else {
					$theLink = "?".$p["name"];
					if ($sections[$p["name"]] == "") $has_sub = false;
					else $has_sub = true;
				}
	
				if (!$has_sub or $sys_info["submenus_always_on"] == 1) {
					echo($indent.'	<li class="'.$classAtt.'"><a href="'.$path_to_root_dir.'/'.$theLink.'&amp;group=_sys_all">'.$p["name"]."</a></li>\n");
				} else {
					echo($indent.'	<li class="'.$classAtt.'" id="'.$p["name"].'_li">'.'<a id="'.$p["name"].'_a"  onmouseover="popup(\''.__('click to see options (re-click to close)').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="javascript:toggleMenuVisibility(\''.$p["name"].'\')">'.$p["name"]."</a></li>\n");
				}
			}
		}
		if ($sys_info["link_to_gallery_in_menu"] == 1) {
			if ($in_gallery)$classAtt = 'here';
			else $classAtt = 'not_here';
			echo($indent.'	<li class="'.$classAtt.'" id="'.$p["name"].'_li">'.'<a onmouseover="popup(\''.__('click to see pictures').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$path_to_root_dir.'/user/Image">'.__('gallery')."</a></li>\n");
		}
		
		echo($indent.'</ul>'."\n");
		/* -------------------end writing menues ----------------------------*/
		
		/* ---------------------- writing submenues -------------------------*/
		echo($indent.'<div id="sub_menus">'."\n");
		$pages = getPages();
		$not_used_singlepages = array();
		//first multi/singlepages with groups
		foreach ($pages as $p) {
			$tmp_entity = getEntity($p["name"]);
			if($p["in_menue"] == "1") { 
				$page_info = getPageInfo($p["name"]);
				
				//if it is a singlepage, grouplist should have values
				if((isMultipage($p["name"]) and $tmp_entity["group"]["field"] != "")
				or (isSinglePage($p["name"]) and $page_info["grouplist"] != "")) {
					//display submenu for $p["name"] with group entries
					if(isMultipage($p["name"])) {
						$efield = getEntityField($tmp_entity["group"]["field"]);
						$gfield = $efield["valuelist"];
					}
					//for singlepages, all groups without "standard" (is not in 
					//the db, so page_info does not have it)
					else $gfield = $page_info["grouplist"];
					
					$a = explode(',', stripCSVList($gfield));
					
					//test if one of the groups was selected
					$ul_visibility = "hidden";
					if ($params['page'] == $p["name"] and !includedByAdminScript($path_to_root_dir)) {
						if ($sys_info["submenus_always_on"] == 1) {
							$ul_visibility = "visible";
						} else {
							for($x=0;$x < count($a);$x++){
								if ($a[$x] == $params['group']) {$ul_visibility = "visible"; break;}
							}
						}
					}
					echo($indent.'	<ul id="'.$p["name"].'_menu" style="visibility:'.$ul_visibility.'">'."\n");
					//for on-click-submenus, we provide an extra submenu to see them
					//all (because the main menu entry's function now is showing the
					//submenus)
					if($sys_info["submenus_always_on"] == 0){
						echo($indent.'		<li><a href="'.$path_to_root_dir.'/?'.$p["name"].'&amp;group=_sys_all">'.__('all').' '.$p["name"]."</a></li>\n");
					}
					$x=0;
					for(;$x < count($a);$x++){
						if ($a[$x] != "") {
							//test if THIS group was selected
							if ($a[$x] == $params["group"]) $classAtt="here"; else $classAtt="not_here";
							echo($indent.'		<li class="'.$classAtt.'"><a href="'.$path_to_root_dir.'/?'.$p["name"].'&amp;group='.$a[$x].'">'.$a[$x].'</a></li>'."\n");
						}
					}
					if ($x==0) {	//ul yhould not be empty - that's not valid'
						echo($indent.'		<li></li>'."\n");
					}
					echo($indent.'	</ul>'."\n");
				}else if (isSinglePage($p["name"])){
					//save it for later
					$not_used_singlepages[count($not_used_singlepages)] = $p;
				}
			}
		}
		
		//now we try all the rest
		foreach ($not_used_singlepages as $p) {
			if($p["in_menue"] == "1") {
				//display submenu for this page with section names
				$headings = $sections[$p["name"]]; //Array with section names for this page
				
				if($sys_info["submenus_always_on"] == 1 and $p["name"] == $params["page"]) {
					$visibility = "visible";
				} else {
					$visibility = "hidden";
				}
				echo($indent.'	<ul id="'.$p["name"].'_menu" style="visibility:'.$visibility.'">'."\n");
				if ($headings != "") {
					//sort the sections according to the order_index
					uasort($headings, "cmpByOrderIndexDesc");
					foreach ($headings as $h) {
						//text that doesn't come from a text area still must be escaped
						$h["heading"] = htmlentities($h["heading"]);
						$classAtt="not_here";	//we're not showing them anyway
						echo($indent.'		<li class="'.$classAtt.'"><a href="'.$path_to_root_dir.'/?'.$p["name"].'#'.str_replace(' ', '_', $h["heading"]).'">'.$h["heading"].'</a></li>'."\n");
					}
				} else {	//ul should not be empty - that's not valid
					echo($indent.'		<li></li>'."\n");
				}
				echo($indent.'	</ul>'."\n");
			}
		}
		echo($indent.'</div>'."\n");
		/* -------------------end writing submenues -------------------------*/
		
		if ($no_sys_tables) {
			$link_text = __('PolyPager found the database. Very good. <br/>But: it seems that it does not yet have its database configured. By clicking on the following link you can assure that all the tables that PolyPager needs to operate are being created (if they have not been already).<br/>');
			$link_href = "admin/?&cmd=create";
			global $area;
			if ($area == '_admin')$link_href = './?&cmd=create';
			if ($area == '_gallery') $link_href = '../../admin/?&cmd=create';
			echo('<div id="no_tables_warning" class="sys_msg">'.$link_text.'<a href="'.$link_href.'">click here to create the tables.</a></div>');
		}
		
	}
	
	/**
	 * returns true when the including script is in the admin area
	 */
	 function includedByAdminScript($path_to_root_dir) {
	 	if ((eregi('index\.php', $_SERVER['SCRIPT_NAME']) and $path_to_root_dir == "..")
			or (eregi('edit\.php', $_SERVER['SCRIPT_NAME']))) return true;
	 	else return false;
	 }
?>