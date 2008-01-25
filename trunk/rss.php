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

//this PHP generates the RSS 2.0 feed 
//you can use it to get the lates comments by passing channel=comments in the URL


// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}

require_once('.'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');
require_once('.'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Sidepane.php');



//get the path to this URI
$doc_root_folders = utf8_explode("/", $_SERVER['DOCUMENT_ROOT']);
$cwd__folders = utf8_explode("/", getcwd());
//the difference between those is the path from doc root to the folder where
//all files for this URI reside
$path_from_doc_root = implode("/", array_diff($cwd__folders, $doc_root_folders));
$base_url = $_SERVER['HTTP_HOST'].'/'.$path_from_doc_root;

// First thing: Explain how this works if wanted
if($_GET["explain"] == '1') {
    $t1 = __('A feed is the web way of a newspaper subscribtion. It is a special webpage that delivers articles in a special format. ');
    $t1 .= __('If you tell a Feed Reader program where to find that script, it will keep you up to date with all your favourite websites without you having to visit them to check for new entries! ');
    $t1 .= __('This page explains how you can define what that feed should deliver:');
    $t2 = '<p>'.__('The standard address of this feed is ').'<a href="http://'.urldecode($base_url).'/rss.php">http://'.urldecode($base_url).'/rss.php'.'</a></p>';
    $t2 .= '<p>'.__('To see the comments, use ').'<a href="http://'.urldecode($base_url).'/rss.php?channel=comments">http://'.urldecode($base_url).'/rss.php?channel=comments'.'</a></p>';
    $t2 .= '<p>'.__('You can request to receive only updates from specific pages by passing the feed a list of pagenames. For instance, assuming this website had two pages called "page1" and "page2", you could use this address: ').'<a href="http://'.urldecode($base_url).'/rss.php?p=page1,page2">http://'.urldecode($base_url).'/rss.php?p=page1,page2'.'</a></p>';
    $t2 .= '<p>'.__('Some pages can only be seen by people with password-protected access to this site. You can request to include such items in your feed, but then you will have to authenticate yourself. Some feed readers do not allow to do this and if you fail to authenticate, you will not see any items. If you want to authenticate, point your feed reader to ').' <a href="http://'.urldecode($base_url).'/rss.php?restricted=1">http://'.urldecode($base_url).'/rss.php?restricted=1'.'</a></p>';
    $t2 .= '<p>'.__('These extra arguments can also be combined. For instance, to see all comments on page1: ').'<a href="http://'.urldecode($base_url).'/rss.php?p=page1&channel=comments">http://'.urldecode($base_url).'/rss.php?p=page1&channel=comments'.'</a></p>';
    require_once('lib'.FILE_SEPARATOR.'PolyPagerLib_BasicInclude.php'); 
    function writeData(){
        global $t1, $t2;
        echo('<h1 class="entry_title">'.__('How To Feed This Site').'</h1>'."\n");
        echo('<p>'.$t1.'</p>'."\n");
        echo($t2."\n");
    }
    useTemplate($path_to_root_dir);
    die(); // Else: Show some Feeds!
}


// see if access-restricted items are wanted
// If so, we include authentification
// caution: some feedreaders are not capable of this!
$show_restricted = false;
if ($_GET["restricted"] == '1') $show_restricted = true;
if ($show_restricted) include('.'.FILE_SEPARATOR.'admin'.FILE_SEPARATOR.'auth.php');


$sys_info = getSysInfo();
header("Content-type: text/xml; charset=".$sys_info["encoding"]);
echo('<?xml version="1.0" encoding="'.$sys_info["encoding"].'"?>'."\n");

echo('<rss version="2.0">'."\n");
echo('	<channel>'."\n");
if ($_GET["channel"] == "comments") {$title_prefix = __('Comments on ');}
echo('		<title><![CDATA['.$title_prefix.$sys_info["title"].']]></title>'."\n");
echo('		<link>http://'.urldecode($base_url).'</link>'."\n");
echo('		<description><![CDATA[a website by '.$sys_info["author"].']]></description>'."\n");
echo('		<language>'.$sys_info["lang"].'</language>'."\n");

$res = getFeed($sys_info["feed_amount"], $_GET["channel"] == "comments", $show_restricted);

for ($x=0; $x < count($res); $x++) {
	$row = $res[$x];

    //make URL
    $url = 'http://'.$base_url.'?'.urlencode($row["thePage"]).'&amp;nr='.$row["theID"];
    if ($_GET["channel"] == "comments") $url .= '#comment'.$row["CommentID"];

	echo('		<item>'."\n");
	echo('			<title><![CDATA['.$row["theText"].']]></title>'."\n");
	echo('			<link>'.$url.'</link>'."\n");
	echo('			<description><![CDATA['.$row["theContent"].']]></description>'."\n");
	echo('			<pubDate>'.date('r',strtotime($row["theDate"])).'</pubDate>'."\n");
	echo('			<guid isPermaLink="true">'.$url.'</guid>'."\n");
    echo('		</item>'."\n");
}
echo('	</channel>'."\n");
echo('</rss>'."\n");
?>
