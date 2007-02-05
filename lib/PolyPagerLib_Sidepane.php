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
	
/* functions for showing things ithat are not main data:
 * intro, feeds ...
 *
 * function index:
 * cmpDate($a, $b)
 * getFeed($amount)
 * writeFeedDiv($ind)
 * writeIntroDiv($ind)
 */
 
require_once("PolyPagerLib_Utils.php");

/*	compares two result rows, using the date entries
	they are expected to be called 'theDate'
*/
function cmpDate($a, $b) {
	//echo "$a .. $b ";
	if ($a['theDate'] == $b['theDate']) return 0;
	return ($a['theDate'] > $b['theDate']) ? -1 : 1;
}

/*	returns an array with the latest entries, count is the number you want
	each entry is a MySQL result row and will have fields 
	named "theID", "theDate", "theText" and "thePage" 
	params: count is the number of entries you want,
			link the db link
*/
function getFeed($amount) {
	//get channel descriptions
	$p = $_GET['p']; if($p=='') $p = $_POST['p'];
	$p = explode(',',$p);
	$t = $_GET['t']; if($t=='') $t = $_POST['t'];
	$t = explode(',',$t);
	
	//make a filter with what was requested
	$where = ' WHERE';
	if($p[0] != '') 
		for($i=0;$i<count($p);$i++) {
			$page = $p[$i];
			$where .= " pagename = '".filterSQL($page)."'";
			if($i+1 < count($p)) $where .= ' OR'; 
		}
	//do the same for tags, once the feature is given...
	
	//get fed entries
	$sys = getSysInfo();
	$query = "SELECT id AS theID, edited_date AS theDate,".
							"title AS theText,".
							"pagename AS thePage FROM _sys_feed";
	if ($where != ' WHERE') $query .= $where;
	$res = pp_run_query($query);
	$feeds = array();
	
	//enrich with text from the tables themselves
	$i = 0;
	while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$the_page = getPageInfo($row['thePage']);
		if ($the_page["name"] != "") {
			//title should be there, gather some content...
			if (isSinglePage($row['thePage'])) {
				$res2 = pp_run_query("SELECT bla AS tfield FROM _sys_sections WHERE pagename = '".$row['thePage']."' AND id = ".$row['theID'].";");
			} else {
                $entity = getEntity($row['thePage']);
				$field = guessTextField($entity);
				if ($field=="") $field = $the_page["title_field"];
				$res2 = pp_run_query("SELECT ".$field." AS tfield FROM ".$the_page["tablename"]." WHERE id = ".$row['theID'].";");
			}
			if($row2 = mysql_fetch_array($res2, MYSQL_ASSOC)) {
				//no title? take sthg from content...
				if($row['theText'] == "" or $row['theText'] == '['.__('update').'] '){
					$row['theText'] = getFirstWords($row2['tfield'],6);
				}
				$row['theContent'] = str_replace('\'', '\\\'', getFirstWords($row2['tfield'],70));
			}
		    $feeds[$i++] = $row;
		}
	}
	
	
	
	uasort($feeds, "cmpDate");
	return array_slice($feeds, 0, $sys["feed_amount"]);
}

/* echos out a div with entries to feeds */
function writeFeedDiv($ind=5) {
	$indent = translateIndent($ind);
	$sys_info = getSysInfo();
	$feed_amount = $sys_info["feed_amount"];
	if ($feed_amount > 0) {
		$res = getFeed($feed_amount);
		if ($sys_info['hide_public_popups']==0) $text = ' onmouseover="popup(\''.__('the RSS feed for this site. &lt;br/&gt; That means you will get to see the newest entries in the XML-Format.&lt;br/&gt;If you want, you can add that URL to your favorite news feed program.').'\')" onmouseout="kill()" title="" onfocus="this.blur()"';
		else $text = '';
		echo($indent.'<div id="feeds"><div class="description"><a'.$text.' href="rss.php">'.__('the latest entries:').'</a></div>'."\n");
		for ($x=0;$x<count($res);$x++) {
			$row = $res[$x];
			echo($indent.'	<div class="entry">'."\n");
			$tipText = '['.__('from the page').' '.$row['thePage'].'] '.$row['theContent'];
			if ($sys_info['hide_public_popups']==0) $text = 'onmouseover="popup(\''.$tipText.'\')" onmouseout="kill()" title="" onfocus="this.blur()" ';
			else $text = '';
            global $path_to_root_dir;
			echo($indent.'		<a '.$text.'href="./'.$path_to_root_dir.'/?'.$row['thePage'].'&amp;nr='.$row['theID'].'">');
			echo($row['theText'].'	</a>'."\n");
			echo($indent.'	</div>'."\n");
		}
		echo($indent.'</div>'."\n");
	}
}

/*	echos out a div with the intro for this site
	(if there is one in the database)
*/
function writeIntroDiv($ind=4) {
    
	$indent = translateIndent($ind);
	
	global $run_as_demo;
	global $params;
	$page = $params["page"];
	
	//here goes an extra div if it is a demo
	if($run_as_demo) {
		echo($indent.'	<div class="sys_msg">'."\n");
		echo($indent.'		'.__('This is a demo version of PolyPager').' '.$version.'. '.__('Admin name and password are set to "admin". Have fun!')."\n");
		echo($indent.'		<br/><a href="admin">&gt;&gt;admin area</a>'."\n");
		echo($indent.'	</div>'."\n");
	}
	
	$results = array();
	$tmp_query = "SELECT intro FROM _sys_intros WHERE tablename = '".$page."';";
	//echo('Query: '.$tmp_query);
	$res = mysql_query($tmp_query, getDBLink());
	$error_nr = mysql_errno(getDBLink());
	if ($error_nr != 0) {
		//$fehler_text = mysql_error(getDBLink());
		//echo('<div class="sys_msg">DB-Error: '.$fehler_text.'</div>'."\n");
	} else {
		$row = mysql_fetch_array($res, MYSQL_ASSOC);
		if ($row['intro'] != "") {
			echo($indent.'<div id="intro">'."\n");
			echo($indent.'	<div class="description">'.__('about this page').'</div>'."\n");
			echo($indent.'	<div class="entry">'.$row['intro']."</div>\n");
			echo($indent.'</div>'."\n");
		}
	}
}

?>
