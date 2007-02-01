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
    	
header("Content-type: text/xml; charset=utf-8");
//this PHP generates thesitemap

echo('<?xml version="1.0" encoding="utf-8"?>'."\n");

$path = './lib/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include("./lib/PolyPagerLib_Utils.php");

$link = getDBLink();
$sys_info = getSysInfo();

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
        $res = pp_run_query($query."  FROM " . $entity['tablename']);
    }else if (isSinglepage($p)){
        $res = pp_run_query("SELECT id AS theID, heading AS theTitle, input_date AS theDate, publish AS pub FROM ".$entity['tablename']." WHERE pagename='".$p."'");
    }

    $prio = prioH($p);
    
    //now put'em out
	while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        if ($entity["publish_field"] == "" or ($entity["publish_field"] != "" and $row["pub"] == '1')){
            echo('	<url>'."\n");
            echo('		<loc>http://'.$url.'?'.$p.'&amp;nr='.$row["theID"].'</loc>'."\n");
            if ($row["theDate"]!="") echo('		<lastmod>'.date('Y-m-d',strtotime($row["theDate"])).'</lastmod>'."\n");
            echo('		<changefreq>weekly</changefreq>'."\n");
            if ($prio < 1 and $entity["publish_field"] != "" and $row["pub"] == '1') $prio = 0.8;
            echo('		<priority>'.$prio.'</priority>'."\n");
            echo('	</url>'."\n");
            $prio = prioH($p);
        }
    }
}
echo('</urlset>'."\n");
?>
