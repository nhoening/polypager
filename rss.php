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
$sys_info = getSysInfo();
header("Content-type: text/xml; charset=".$sys_info["encoding"]);
//this PHP generates the RSS 2.0 feed 
//it selects the top 10 of every entity that was marked as feed in the XML file

echo('<?xml version="1.0" encoding="'.$sys_info["encoding"].'"?>'."\n");

$path = './lib/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include("./lib/PolyPagerLib_Utils.php");
include("./lib/PolyPagerLib_Sidepane.php");

$link = getDBLink();


//get the path to this URI
$doc_root_folders = explode("/", $_SERVER['DOCUMENT_ROOT']);
$cwd__folders = explode("/", getcwd());
//the difference between those is the path from doc root to the folder where
//all files for this URI reside
$path_from_doc_root = implode("/", array_diff($cwd__folders, $doc_root_folders));
//now add to base URI
$url = $_SERVER['HTTP_HOST'].'/'.$path_from_doc_root;


echo('<rss version="2.0">'."\n");
echo('	<channel>'."\n");
echo('		<title><![CDATA['.$sys_info["title"].']]></title>'."\n");
echo('		<link>http://'.$url.'</link>'."\n");
echo('		<description><![CDATA[a website by '.$sys_info["author"].']]></description>'."\n");
echo('		<language>'.$sys_info["lang"].'</language>'."\n");
echo('		<generator>PolyPager '.$version.'</generator>'."\n");

$res = getFeed($sys_info["feed_amount"]);

for ($x=0; $x < count($res); $x++) {
	$row = $res[$x];
	echo('		<item>'."\n");
	echo('			<title><![CDATA['.$row["theText"].']]></title>'."\n");
	echo('			<link>http://'.$url.'?'.$row["thePage"].'&amp;nr='.$row["theID"].'</link>'."\n");
	echo('			<description><![CDATA['.$row["theContent"].']]></description>'."\n");
	echo('			<pubDate>'.date('r',strtotime($row["theDate"])).'</pubDate>'."\n");
	echo('			<guid isPermaLink="true">http://'.$url.'?'.$row["thePage"].'&amp;nr='.$row["theID"].'</guid>'."\n");
    echo('		</item>'."\n");
}
echo('	</channel>'."\n");
echo('</rss>'."\n");
?>
