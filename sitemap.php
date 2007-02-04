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

//this PHP generates thesitemap


$path = './lib/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include("./lib/PolyPagerLib_Utils.php");

$sys_info = getSysInfo();
header("Content-type: text/xml; charset=".$sys_info["encoding"]);
echo('<?xml version="1.0" encoding="'.$sys_info["encoding"].'"?>'."\n");

$link = getDBLink();


//get the path to this URI
$doc_root_folders = explode("/", $_SERVER['DOCUMENT_ROOT']);
$cwd__folders = explode("/", getcwd());
//the difference between those is the path from doc root to the folder where
//all files for this URI reside
$path_from_doc_root = implode("/", array_diff($cwd__folders, $doc_root_folders));
//now add to base URI
$url = $_SERVER['HTTP_HOST'].'/'.$path_from_doc_root;


echo('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
$pages = getPageNames();

function prioH($pagename){
    //simple heuristic for priority
    global $sys_info;
    $prio = 0.5;
    if ($pagename == $sys_info["start_page"]) $prio = 1.0; // we want search engines to index our start page
    return $prio;
}

foreach ($pages as $p) {
    $entity = getEntity($p);
    if (isMultipage($p)){
        $query = "SELECT ".$entity['pk']." AS theID, ".$entity['title_field']." AS theTitle";
        if ($entity['publish_field']!="") $query .= ", ".$entity['publish_field']." AS pub ";
        if ($entity['date_field']!="") $query .= ", ".$entity['date_field']["name"]." AS theDate1 ";
        if ($entity['date_field']["editlabel"]!="") $query .= ", ".$entity['date_field']["editlabel"]." AS theDate2 ";
        $res = pp_run_query($query."  FROM " . $entity['tablename']);
    }else if (isSinglepage($p)){
        $res = pp_run_query("SELECT id AS theID, heading AS theTitle, input_date AS theDate1, edited_date AS theDate2, publish AS pub FROM ".$entity['tablename']." WHERE pagename='".$p."'");
    }

    $prio = prioH($p);
    
    //now put'em out
	while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        if ($entity["publish_field"] == "" or ($entity["publish_field"] != "" and $row["pub"] == '1')){
            echo('	<url>'."\n");
            echo('		<loc>http://'.$url.'?'.$p.'&amp;nr='.$row["theID"].'</loc>'."\n");
            //prefer last edited date over input date
            if ($row["theDate2"]!="" and $row["theDate2"]!="NULL" and substr($row['theDate2'],0,10) != '0000-00-00') echo('		<lastmod>'.date('Y-m-d',strtotime($row["theDate2"])).'</lastmod>'."\n");
            else if ($row["theDate1"]!="" and$row["theDate1"]!="NULL" and substr($row['theDate1'],0,10) != '0000-00-00') echo('		<lastmod>'.date('Y-m-d',strtotime($row["theDate1"])).'</lastmod>'."\n");
   
            echo('		<changefreq>weekly</changefreq>'."\n");
            if ($prio < 1 and $entity["publish_field"] != "" and $row["pub"] == '1') $prio = 0.7;
            echo('		<priority>'.$prio.'</priority>'."\n");
            echo('	</url>'."\n");
            $prio = prioH($p);
        }
    }
}
echo('</urlset>'."\n");
?>
