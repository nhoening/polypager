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

/* this function will provide Code the header of each site
here goes:
- the Doctype
- the head

*/
function writeDocType($ind=0)
{
    $indent = translateIndent($ind);
    $sys_info = getSysInfo();
    echo($indent.'<?xml version="1.0" encoding="'.$sys_info['encoding'].'"?>'."\n");
    echo($indent.'<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 STRICT//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n");
}

function writeHeader($ind=1)
{
    $indent = translateIndent($ind);
    global $title;
    global $path_to_root_dir;
    global $params;
    $sys_info = getSysInfo();
    $page_info = getPageInfo($params['page']);
    $entity = getEntity($params['page']);
    global $version;
    
    echo($indent.'<head>'."\n");
    echo($indent.'	<title>');
    if ($title != '') {
        echo($title.' - ');
    }
    echo($sys_info["title"].'</title>'."\n");
    echo($indent.'	<meta http-equiv="Content-type" content="text/html; charset='.$sys_info['encoding'].'"/>'."\n");
    echo($indent.'	<meta name="description" content="'.str_replace("\"", "'", $sys_info["title"]).'"/>'."\n");
    echo($indent.'	<meta name="DC.creator" content="'.$sys_info["author"].'"/>'."\n");
    echo($indent.'	<meta name="DC.generator" content="PolyPager Version '.$version.'"/>'."\n");
    echo($indent.'	<meta name="keywords" content="'.$sys_info["keywords"].'"/>'."\n");
    if ($path_to_root_dir != ".") {
        echo($indent.'	<meta name="robots" content="noindex, nofollow" />'."\n");
    }
    //echo($indent.'	<meta name="date" content="'.date('Y-m-d').'"></meta>'."\n");
    echo($indent.'	<link rel="Shortcut Icon" href="/favicon.ico" type="image/x-icon"></link>'."\n");
    echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/scripts/javascript.php"></script>'."\n");
    echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/plugins/browser_detect.js"></script>'."\n");
    echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/scripts/popup.js"></script>'."\n");
    
    //backdoor hack to get all picswap colorsets
    if (utf8_strpos($sys_info['skin'],'picswap')>-1) {
        $skin = 'picswap';
        $css = 'picswap/'.$sys_info['skin'].'.css';
    } else {
        $skin = $sys_info["skin"];
        $css = $skin.'/skin.css';
    }
    //test if the skin can be found, go back to default otherwise
    if (!file_exists($path_to_root_dir.'/style/skins/'.$css)) {
        $skin = 'polly';
        $css = $skin.'/skin.css';
    }
    echo($indent.'	<link rel="stylesheet" href="'.$path_to_root_dir.'/style/skins/'.$css.'" type="text/css"></link>'."\n");
    if (file_exists($path_to_root_dir.'/style/user.css')) {
        echo($indent.'	<link rel="stylesheet" href="'.$path_to_root_dir.'/style/user.css" type="text/css"></link>'."\n");
    }
    echo($indent.'	<link rel="stylesheet" href="'.$path_to_root_dir.'/style/basestyles.css" type="text/css"></link>'."\n");
    echo($indent.'	<!--[if lte IE 6]>'."\n");
    echo($indent.'		<link href="'.$path_to_root_dir.'/style/skins/'.$skin.'/iefix.css" rel="stylesheet" type="text/css"/>'."\n");
    echo($indent.'	<![endif]-->'."\n");
    
    //include calendar if necessary
    if (hasDateField($entity) and includedByAdminScript()) {
        echo($indent.'	<style type="text/css">@import url(../plugins/jscalendar-1.0/skins/aqua/theme.css);</style>'."\n");
        echo($indent.'	<script type="text/javascript" src="../plugins/jscalendar-1.0/calendar.js"></script>'."\n");
        echo($indent.'	<script type="text/javascript" src="../plugins/jscalendar-1.0/lang/calendar-'.$sys_info['lang'].'.js"></script>'."\n");
        echo($indent.'	<script type="text/javascript" src="../plugins/jscalendar-1.0/calendar-setup.js"></script>'."\n");
    }
    
    //greybox for previews
    if (includedByAdminScript() or($params["step"]==1) and $page_info["commentable"] == "1") {
        echo($indent.'	<script type="text/javascript">'."\n");
        echo($indent.'	 var GB_ROOT_DIR = "'.$path_to_root_dir.'/plugins/greybox/";'."\n");
        echo($indent.'	</script>'."\n");
        echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/plugins/greybox/AJS.js"></script>'."\n");
        echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/plugins/greybox/AJS_fx.js"></script>'."\n");
        echo($indent.'	<script type="text/javascript" src="'.$path_to_root_dir.'/plugins/greybox/gb_scripts.js"></script>'."\n");
        echo($indent.'	<link href="'.$path_to_root_dir.'/plugins/greybox/gb_styles.css" rel="stylesheet" type="text/css" />'."\n");
    }
    
    if ($sys_info["feed_amount"] > 0) {
        echo($indent.'	<link rel="alternate" type="application/rss+xml" title="'.str_replace("\"", "'", $sys_info["title"]).' as RSS-Feed" href="'.$path_to_root_dir.'/rss.php"></link>'."\n");
        echo($indent.'	<link rel="alternate" type="application/rss+xml" title="Comments on '.str_replace("\"", "'", $sys_info["title"]).' as RSS-Feed" href="'.$path_to_root_dir.'/rss.php"></link>'."\n");
    }
    echo('	</head>'."\n");
}

/*this function will write the title	*/
function writeTitle($ind=4)
{
    $indent = translateIndent($ind);
    global $path_to_root_dir;
    global $params;
    $sys_info = getSysInfo();
    if ($sys_info['hide_public_popups']==0) {
        $text = 'onmouseover="popup(\''.__('Administration Page').'\')" onmouseout="kill()" title="" onfocus="this.blur()"';
    } else {
        $text = '';
    }
    echo($indent.'<div id="title"><span id="text"><a href="'.$path_to_root_dir.'">'.$sys_info["title"].'</a></span><a href="'.$path_to_root_dir.'/admin/" '.$text.'>#</a></div>'."\n");
}

/*this function will provide Code for the footer of each page	*/
function writeFooter($ind=3)
{
    $indent = translateIndent($ind);
    echo($indent.'<div id="footer">'."\n");
    $sys_info = getSysInfo();
    if (!$sys_info['no_tables']) {
        $query = "SELECT * FROM _sys_intros WHERE tablename='_sys_impressum'";
        $res = pp_run_query($query);
        $error_nr = mysqli_errno(getDBLink());
        if ($error_nr == 0) {
            $row = $res[0];
            if ($row["intro"] != "") {
                echo($indent.'	<span id="impressum">'.$row["intro"].'</span>'."\n");
            }
        }
    }
    echo($indent.'</div>'."\n");
}

/*	compares two array entries, using the menue_index entries
*/
function cmpByMenueIndex($a, $b)
{
    if ($a['menue_index'] == $b['menue_index']) {
        return 0;
    }
    return($a['menue_index'] < $b['menue_index']) ? -1 : 1;
}


/*
The menu is the part of the application that makes navigation possible.
We have two menulevels: mainmenu and submenus.
mainmenu entails all pages that are supposed to be accesible.
*/
function writeMenu($ind=4)
{
    $indent = translateIndent($ind);
    global $path_to_root_dir;
    global $params;
    $dblink = getDBLink();
    global $debug;
    
    /* ---------------------- getting sections --------------------------*/
    $query = "SELECT id, pagename, heading, publish, in_submenu, order_index from _sys_sections
WHERE publish = 1
GROUP BY pagename, heading ORDER BY order_index ASC";
    try{
        $res = pp_run_query($query);
    }
    catch(Exception $e){
    }
    
    $sections = array();
    //build a 2-dimensional array with
    //key:page value:heading and order_index
    foreach($res as $row) {
        if ($sections[$row["pagename"]] == "") {
            //add new array
            $tmp = array("heading" => $row["heading"],
            "order_index" => $row["order_index"]);
            $tmp2 = array($tmp);
            $page_info = getPageInfo($row["pagename"]);
            if ($row["in_submenu"] == 1 or $page_info["grouplist"] != "") {
                $sections[$row["pagename"]] = $tmp2;
            }
        } else {
            $tmp = $sections[$row["pagename"]];
            $tmp[count($tmp)] = array("heading" => $row["heading"],
            "order_index" => $row["order_index"]);
            $page_info = getPageInfo($row["pagename"]);
            if ($row["in_submenu"] == 1 or $page_info["grouplist"] != "") {
                $sections[$row["pagename"]] = $tmp;
            }
        }
    }
    
    /* -------------------end getting sections --------------------------*/
    
    echo($indent.'<ul id="main_menu">'."\n");
    $sys_info = getSysInfo();
    
    $pages = getPages();
    
    // are we in the gallery?
    $in_gallery = false;
    if (eregi('user'.FILE_SEPARATOR.'Image',getcwd())) {
        //we're in the gallery
        $in_gallery = true;
    }
    // add the gallery as page in the array, if  alink is wanted
    if ($sys_info["link_to_gallery_in_menu"] == 1) {
        $g = array('in_menue'=>'1','menue_index'=>$sys_info['gallery_index'],'name'=>$sys_info['gallery_name'],'gallery'=>True);
        $pages[] = $g;
    }
    //sort the pages according to the menue_index before we proceed
    uasort($pages, "cmpByMenueIndex");
    
    $counter = 1;
    foreach($pages as $p) {
        if ($p["in_menue"] == "1") {
            //gallery
            if ($p['gallery'] == "1") {
                if ($in_gallery) {
                    $classAtt = 'here';
                } else {
                    $classAtt = 'not_here';
                }
                echo($indent.'	<li class="'.$classAtt.'">'.'<a id="gallery_menulink" href="'.$path_to_root_dir.'/user/Image">'.$p['name']."</a></li>\n");
                
                //normal pages
            } else {
                // if actual page menu entry has a special class
                if (($params["page"] == $p["name"] and !$in_gallery)
                and !includedByAdminScript()) {
                    $classAtt = 'here';
                } else {
                    $classAtt = 'not_here';
                }
                
                $theLink = "?".urlencode($p["name"]);
                
                echo($indent.'	<li class="'.$classAtt.'"><a id="'.buildValidIDFrom($p["name"]).'_menulink" href="'.$path_to_root_dir.'/'.$theLink.'">'.$p["name"].'</a>'."\n");
                writeSubmenus($p, $sections, 5);
                echo($indent.'	</li>'."\n");
                
            }
        }
        $counter++;
    }
    
    echo($indent.'</ul>'."\n");
    
}


/**
*  write a submenu unordered list for this page
*  $p the pagename
*  $sections
*/
function writeSubmenus($p, $sections, $ind=5)
{
    $indent = translateIndent($ind);
    global $path_to_root_dir;
    global $params;
    global $debug;
    $sys_info = getSysInfo();
    $tmp_entity = getEntity($p["name"]);
    
    if ($p["in_menue"] == "1") {
        $page_info = getPageInfo($p["name"]);
        
        //first multi/singlepages with groups
        if ((isMultipage($p["name"]) and $tmp_entity["group"]["field"] != "")
        or(isSinglePage($p["name"]) and $page_info["grouplist"] != "")) {
            //display submenu for $p["name"] with group entries
            if (isMultipage($p["name"])) {
                $efield = getEntityField($tmp_entity["group"]["field"],$tmp_entity);
                $gfield = $efield["valuelist"];
            }
            //for singlepages, all groups without "standard" (is not in
            //the db, so page_info does not have it)
            else {
                $gfield = $page_info["grouplist"];
            }
            
            $a = utf8_explode(',', stripCSVList($gfield));
            
            //test if one of the groups was selected
            $ul_visibility = "none";
            if ($params['page'] == $p["name"] and !includedByAdminScript()) {
                if ($sys_info["submenus_always_on"] == 1) {
                    $ul_visibility = "block";
                }
            }
            
            echo($indent.'	<ul class="sub_menu" id="'.$p["name"].'_menu" style="display:'.$ul_visibility.';">'."\n");
            
            $x=0;
            for (;
            $x < count($a);
            $x++) {
                if ($a[$x] != "") {
                    //test if THIS group was selected
                    if ($a[$x] == $params["group"]) {
                        $classAtt="here";
                    } else {
                        $classAtt="not_here";
                    }
                    echo($indent.'		<li class="'.$classAtt.'"><a href="'.$path_to_root_dir.'/?'.$p["name"].'&amp;group='.urlencode($a[$x]).'">'.preserveMarkup($a[$x]).'</a></li>'."\n");
                }
            }
            if ($x==0) {
                //ul yhould not be empty - that's not valid'
                echo($indent.'		<li></li>'."\n");
            }
            echo($indent.'	</ul>'."\n");
            
        }
        /*else if (isSinglePage($p["name"])) {
            //display submenu for this page with section names
            $headings = $sections[$p["name"]];
            //Array with section names for this page
            
            if ($sys_info["submenus_always_on"] == 1 and $p["name"] == $params["page"]) {
                $visibility = "visible";
            } else {
                $visibility = "hidden";
            }
            echo($indent.'	<ul id="'.$p["name"].'_menu" style="visibility:'.$visibility.'">'."\n");
            if ($headings != "") {
                //sort the sections according to the order_index
                uasort($headings, "cmpByOrderIndexDesc");
                foreach($headings as $h) {
                    //text that doesn't come from a text area still must be escaped
                    $h["heading"] = htmlentities($h["heading"]);
                    $classAtt="not_here";
                    //we're not showing them anyway
                    echo($indent.'		<li class="'.$classAtt.'"><a href="'.$path_to_root_dir.'/?'.$p["name"].'#'.utf8_str_replace(' ', '_', $h["heading"]).'">'.$h["heading"].'</a></li>'."\n");
                }
            } else {
                //ul should not be empty - that's not valid
                echo($indent.'		<li></li>'."\n");
            }
            echo($indent.'	</ul>'."\n");
        }
        */
    }
    
}

?>

