<?
/*
PolyPager - a lean, mean web publishing system
Copyright (C) 2006 Nicolas H&#65533;ning
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

/*  compares two result rows, using the date entries
they are expected to be called 'theDate'
*/
function cmpDate($a, $b)
{
    if ($a['theDate'] == $b['theDate']) {
        return 0;
    }
    return($a['theDate'] > $b['theDate']) ? -1 : 1;
}

/*  returns an array with the latest entries, count is the number you want
each entry is a MySQL result row and will have fields
named "theID", "theDate", "theText" and "thePage"
params: count - the number of entries you want,
comments - set to true if you want to get comments
restricted - 1 if all access-restricted entries should be included
(look out: authentication is not handled here!)
2 if page-wise restricted entries should be excluded
3 if no access-restricted entries should be included
*/
function getFeed($amount, $comments = false, $restricted = 3)
{
    // get requested page descriptions
    $p = $_GET['p'];
    if ($p=='') {
        $p = $_POST['p'];
    }
    $p = utf8_explode(',', $p);
    // get requested entry number
    if ($comments) {
        $nr = $_GET['nr'];
        if ($nr=='') {
            $nr = $_POST['nr'];
        }
    }
    
    $sys = getSysInfo();
    $theParams = array();
    // when all access is restricted and we don't want to see restricted, show nothing
    if ($sys['whole_site_admin_access'] and $restricted == 3) {
        $query = "SELECT * FROM _sys_sys WHERE 1=?";
        $theParams[] = array('i', 2);
    } else {
        
        //make a filter with what was requested
        if (!$comments) {
            $where = ' WHERE public = 1';
        }
        if ($p[0] != '') {
            if (!$comments) {
                $where .= " AND (";
            } else {
                $where = " WHERE (";
            }
            for ($i=0; $i<count($p); $i++) {
                $page = $p[$i];
                $where .= " pagename = ?";
                $theParams[] = array('s', urldecode($page));
                if ($i+1 < count($p)) {
                    $where .= ' OR';
                }
            }
            $where .= ")";
        }
        
        if ($comments and $nr != "") {
            $where .= " AND pageid = ?";
            $theParams[] = array('i', $nr);
        }
        
        
        if (!$comments) {
            $query = "SELECT id AS theID, edited_date AS theDate,".
            "title AS theText,".
            "pagename AS thePage FROM _sys_feed";
        } else {
            $query = "SELECT pageid AS theID, insert_date AS theDate,".
            "comment AS theContent, id as CommentID,".
            "pagename AS thePage FROM _sys_comments";
        }
        $query .= $where;
        $query .= " ORDER BY theDate DESC LIMIT ".$sys["feed_amount"];
    }
    
    if (count($theParams) > 0) {
        $res = pp_run_query(array($query, $theParams));
    } else {
        $res = pp_run_query($query);
    }
    $feeds = array();
    
    //enrich with text from the tables themselves
    foreach($res as $row){
        $the_page = getPageInfo($row['thePage']);
        if ($the_page["name"] != "" && !($the_page['only_admin_access'] == '1' and $restricted > 1)) {
            $entity = getEntity($row['thePage']);
            if (!$comments) {
                // get text from original page for feeds
                $field = guessTextField($entity);
                if ($field == "") {
                    $field = $the_page["title_field"];
                }
                $res2 = pp_run_query_old("SELECT ".$field." AS tfield FROM `".$the_page["tablename"]."` WHERE id = ".$row['theID']);
                if (mysql_num_rows($res2) > 0) {
                    
                    $row2 = mysql_fetch_assoc($res2);
                    //no title? take sthg from content...
                    if ($row['theText'] == "" or $row['theText'] == '['.__('update').'] ') {
                        $row['theText'] = getFirstWords($row2['tfield'],6);
                    }
                    if ($sys["full_feed"]=='1') {
                        $row['theContent'] = $row2['tfield'];
                    } else {
                        $row['theContent'] = getFirstWords($row2['tfield'],70);
                    }
                }
            } else {
                $field = $the_page["title_field"];
                if ($field=="") {
                    $field = guessTextField($entity);
                }
                $res2 = pp_run_query_old("SELECT ".$field." AS tfield FROM `".$the_page["tablename"]."` WHERE id = ".$row['theID'].";");
                if (mysql_num_rows($res2) > 0) {
                    $row2 = mysql_fetch_assoc($res2);
                    $row['theText'] = getFirstWords($row2['tfield'],10);
                }
            }
            $feeds[] = $row;
        }
    }
    
    return $feeds;
}

/* echos out a div with entries to feeds */
function writeFeedDiv($ind=5)
{
    $indent = translateIndent($ind);
    $sys_info = getSysInfo();
    global $path_to_root_dir;
    $feed_amount = $sys_info["feed_amount"];
    if ($feed_amount > 0) {
        $res = getFeed($feed_amount, false, 2);
        if ($sys_info['hide_public_popups']==0) {
            $text = ' onmouseover="popup(\''.__('Below you see a list of the latest entries on this website. This link explains how you can subscribe to them via an RSS-Feed.').'\')" onmouseout="kill()" title="" onfocus="this.blur()"';
        } else {
            $text = '';
        }
        echo($indent.'<div id="feeds"><div class="description"><a'.$text.' href="./'.$path_to_root_dir.'/rss.php?explain=1">'.__('the latest entries:').'</a></div>'."\n");
        for ($x=0; $x<count($res); $x++) {
            $row = $res[$x];
            echo($indent.'  <div class="entry">'."\n");
            $tipText = '['.__('from the page').' '.$row['thePage'].'] '.utf8_str_replace("'","\'", strip_tags(getFirstWords($row['theContent'],20)));
            if ($sys_info['hide_public_popups']==0) {
                $text = 'onmouseover="popup(\''.$tipText.'\')" onmouseout="kill()" title="" onfocus="this.blur()" ';
            } else {
                $text = '';
            }
            global $path_to_root_dir;
            echo($indent.'      <a '.$text.'href="./'.$path_to_root_dir.'/?'.urlencode($row['thePage']).'&amp;nr='.$row['theID'].'">');
            echo($row['theText'].'  </a>'."\n");
            echo($indent.'  </div>'."\n");
        }
        echo($indent.'</div>'."\n");
    }
}

/*  echos out a div with the intro for this site
(if there is one in the database)
*/
function writeIntroDiv($ind=4)
{
    
    $indent = translateIndent($ind);
    
    global $run_as_demo;
    global $params;
    $page = $params["page"];
    
    //here goes an extra div if it is a demo
    if ($run_as_demo) {
        echo($indent.'  <div class="sys_msg">'."\n");
        echo($indent.'      '.__('This is a demo version of PolyPager').' '.$version.'. '.__('Admin name and password are set to "admin". Have fun!')."\n");
        echo($indent.'      <br/><a href="admin">&gt;&gt;admin area</a>'."\n");
        echo($indent.'  </div>'."\n");
    }
    
    $tmp_query = "SELECT intro FROM _sys_intros WHERE tablename = '".$page."';";
    try{
        $res = pp_run_query($tmp_query);
    }
    catch(Exception $e){
    }
    $error_nr = mysqli_errno(getDBLink());
    if ($error_nr != 0) {
        //$fehler_text = mysqli_error(getDBLink());
        //echo('<div class="sys_msg">DB-Error: '.$fehler_text.'</div>'."\n");
    } else {
        $row = $res[0];
        if ($row['intro'] != "") {
            echo($indent.'<div id="intro">'."\n");
            echo($indent.'  <div class="entry">'.$row['intro']."</div>\n");
            echo($indent.'</div>'."\n");
        }
    }
}

?>

