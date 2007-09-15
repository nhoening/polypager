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
	if ($a['theDate'] == $b['theDate']) return 0;
	return ($a['theDate'] > $b['theDate']) ? -1 : 1;
}

/*	returns an array with the latest entries, count is the number you want
	each entry is a MySQL result row and will have fields 
	named "theID", "theDate", "theText" and "thePage" 
	params: count - the number of entries you want,
			comments - set to true if you want to get comments
*/
function getFeed($amount, $comments = false) {
	//get requested page descriptions
	$p = $_GET['p']; if($p=='') $p = $_POST['p'];
	$p = utf8_explode(',',$p);
	//get requested entry number
    $nr = $_GET['nr']; if($nr=='') $nr = $_POST['nr'];
    
	//make a filter with what was requested
    if (!$comments) $where = ' WHERE public = 1';
	if($p[0] != '') {
        if (!$comments) $where .= " AND (";
        else $where = " WHERE (";
		for($i=0;$i<count($p);$i++) {
			$page = $p[$i];
			$where .= " pagename = '".urldecode(filterSQL($page))."'";
			if($i+1 < count($p)) $where .= ' OR'; 
		}
        $where .= ")";
    }
    
    if ($nr!=""){
        $where .= " AND id = ".$nr;
    }

	$sys = getSysInfo();
    if (!$comments) $query = "SELECT id AS theID, edited_date AS theDate,".
							"title AS theText,".
							"pagename AS thePage FROM _sys_feed";
    else $query = "SELECT pageid AS theID, insert_date AS theDate,".
							"comment AS theContent, id as CommentID,".
							"pagename AS thePage FROM _sys_comments";
	$query .= $where;
    $query .= " ORDER BY theDate DESC LIMIT ".$sys["feed_amount"];
	$res = pp_run_query($query);
	$feeds = array();
	
    //echo('<!-- query: '.$query.'-->');
    
	//enrich with text from the tables themselves
    while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        $the_page = getPageInfo($row['thePage']);
        if ($the_page["name"] != "") {
            $entity = getEntity($row['thePage']);
            if (!$comments) {   // get text from original page for feeds
                $field = guessTextField($entity);
                if ($field=="") $field = $the_page["title_field"];
                $res2 = pp_run_query("SELECT ".$field." AS tfield FROM ".$the_page["tablename"]." WHERE id = ".$row['theID'].";");
    
                if($row2 = mysql_fetch_array($res2, MYSQL_ASSOC)) {
                    //no title? take sthg from content...
                    if($row['theText'] == "" or $row['theText'] == '['.__('update').'] '){
                        $row['theText'] = getFirstWords($row2['tfield'],6);
                    }
                    if ($sys["full_feed"]=='1') $row['theContent'] = $row2['tfield'];
                    else $row['theContent'] = getFirstWords($row2['tfield'],70);
                }
            } else {
                $field = $the_page["title_field"];
                echo('<!--field:'.$row['thePage'].'-->');
                if ($field=="") $field = guessTextField($entity);
                $res2 = pp_run_query("SELECT ".$field." AS tfield FROM ".$the_page["tablename"]." WHERE id = ".$row['theID'].";");
                if($row2 = mysql_fetch_array($res2, MYSQL_ASSOC)) 
                    $row['theText'] = getFirstWords($row2['tfield'],10);
            }
            $feeds[] = $row;
        }
    }

    return $feeds;
}

/* echos out a div with entries to feeds */
function writeFeedDiv($ind=5) {
	$indent = translateIndent($ind);
	$sys_info = getSysInfo();
    global $path_to_root_dir;
	$feed_amount = $sys_info["feed_amount"];
	if ($feed_amount > 0) {
		$res = getFeed($feed_amount);
		if ($sys_info['hide_public_popups']==0) $text = ' onmouseover="popup(\''.__('the RSS feed for this site. &lt;br/&gt; That means you will get to see the newest entries in the XML-Format.&lt;br/&gt;If you want, you can add that URL to your favorite news feed program.').'\')" onmouseout="kill()" title="" onfocus="this.blur()"';
		else $text = '';
		echo($indent.'<div id="feeds"><div class="description"><a'.$text.' href="./'.$path_to_root_dir.'/rss.php">'.__('the latest entries:').'</a></div>'."\n");
		for ($x=0;$x<count($res);$x++) {
			$row = $res[$x];
			echo($indent.'	<div class="entry">'."\n");
			$tipText = '['.__('from the page').' '.$row['thePage'].'] '.utf8_str_replace("'","\'", strip_tags(getFirstWords($row['theContent'],20)));
			if ($sys_info['hide_public_popups']==0) $text = 'onmouseover="popup(\''.$tipText.'\')" onmouseout="kill()" title="" onfocus="this.blur()" ';
			else $text = '';
            global $path_to_root_dir;
			echo($indent.'		<a '.$text.'href="./'.$path_to_root_dir.'/?'.urlencode($row['thePage']).'&amp;nr='.$row['theID'].'">');
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
	
	$tmp_query = "SELECT intro FROM _sys_intros WHERE tablename = '".$page."';";
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
