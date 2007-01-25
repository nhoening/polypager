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
    	
header("Content-type: text/xml; charset=iso-8859-1");
//this PHP generates the RSS 0.91 feed 
//it selects the top 10 of every entity that was marked as feed in the XML file

echo('<?xml version="1.0" encoding="ISO-8859-1"?>'."\n");

$path = './lib/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include("./lib/PolyPagerLib_Utils.php");
include("./lib/PolyPagerLib_Sidepane.php");

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

/*
echo('<!DOCTYPE rss ['."\n");
//I would have liked to get those entites from a file (see below), but it just didn't work...
//so this is better than nothing, I guess
echo('<!ENTITY nbsp   "&#160;"> <!-- no-break space = non-breaking space,
                                  U+00A0 ISOnum -->'."\n");
echo('<!ENTITY iexcl  "&#161;"> <!-- inverted exclamation mark, U+00A1 ISOnum -->'."\n");
echo('<!ENTITY cent   "&#162;"> <!-- cent sign, U+00A2 ISOnum -->'."\n");
echo('<!ENTITY pound  "&#163;"> <!-- pound sign, U+00A3 ISOnum -->'."\n");
echo('<!ENTITY curren "&#164;"> <!-- currency sign, U+00A4 ISOnum -->'."\n");
echo('<!ENTITY yen    "&#165;"> <!-- yen sign = yuan sign, U+00A5 ISOnum -->'."\n");
echo('<!ENTITY brvbar "&#166;"> <!-- broken bar = broken vertical bar,
                                  U+00A6 ISOnum -->'."\n");
echo('<!ENTITY sect   "&#167;"> <!-- section sign, U+00A7 ISOnum -->'."\n");
echo('<!ENTITY uml    "&#168;"> <!-- diaeresis = spacing diaeresis,
                                  U+00A8 ISOdia -->'."\n");
echo('<!ENTITY copy   "&#169;"> <!-- copyright sign, U+00A9 ISOnum -->'."\n");
echo('<!ENTITY ordf   "&#170;"> <!-- feminine ordinal indicator, U+00AA ISOnum -->'."\n");
echo('<!ENTITY laquo  "&#171;"> <!-- left-pointing double angle quotation mark
                                  = left pointing guillemet, U+00AB ISOnum -->'."\n");
echo('<!ENTITY not    "&#172;"> <!-- not sign = angled dash,
                                  U+00AC ISOnum -->'."\n");
echo('<!ENTITY shy    "&#173;"> <!-- soft hyphen = discretionary hyphen,
                                  U+00AD ISOnum -->'."\n");
echo('<!ENTITY reg    "&#174;"> <!-- registered sign = registered trade mark sign,
                                  U+00AE ISOnum -->'."\n");
echo('<!ENTITY macr   "&#175;"> <!-- macron = spacing macron = overline
                                  = APL overbar, U+00AF ISOdia -->'."\n");
echo('<!ENTITY deg    "&#176;"> <!-- degree sign, U+00B0 ISOnum -->'."\n");
echo('<!ENTITY plusmn "&#177;"> <!-- plus-minus sign = plus-or-minus sign,
                                  U+00B1 ISOnum -->'."\n");
echo('<!ENTITY sup2   "&#178;"> <!-- superscript two = superscript digit two
                                  = squared, U+00B2 ISOnum -->'."\n");
echo('<!ENTITY sup3   "&#179;"> <!-- superscript three = superscript digit three
                                  = cubed, U+00B3 ISOnum -->'."\n");
echo('<!ENTITY acute  "&#180;"> <!-- acute accent = spacing acute,
                                  U+00B4 ISOdia -->'."\n");
echo('<!ENTITY micro  "&#181;"> <!-- micro sign, U+00B5 ISOnum -->'."\n");
echo('<!ENTITY para   "&#182;"> <!-- pilcrow sign = paragraph sign,
                                  U+00B6 ISOnum -->'."\n");
echo('<!ENTITY middot "&#183;"> <!-- middle dot = Georgian comma
                                  = Greek middle dot, U+00B7 ISOnum -->'."\n");
echo('<!ENTITY cedil  "&#184;"> <!-- cedilla = spacing cedilla, U+00B8 ISOdia -->'."\n");
echo('<!ENTITY sup1   "&#185;"> <!-- superscript one = superscript digit one,
                                  U+00B9 ISOnum -->'."\n");
echo('<!ENTITY ordm   "&#186;"> <!-- masculine ordinal indicator,
                                  U+00BA ISOnum -->'."\n");
echo('<!ENTITY raquo  "&#187;"> <!-- right-pointing double angle quotation mark
                                  = right pointing guillemet, U+00BB ISOnum -->'."\n");
echo('<!ENTITY frac14 "&#188;"> <!-- vulgar fraction one quarter
                                  = fraction one quarter, U+00BC ISOnum -->'."\n");
echo('<!ENTITY frac12 "&#189;"> <!-- vulgar fraction one half
                                  = fraction one half, U+00BD ISOnum -->'."\n");
echo('<!ENTITY frac34 "&#190;"> <!-- vulgar fraction three quarters
                                  = fraction three quarters, U+00BE ISOnum -->'."\n");
echo('<!ENTITY iquest "&#191;"> <!-- inverted question mark
                                  = turned question mark, U+00BF ISOnum -->'."\n");
echo('<!ENTITY Agrave "&#192;"> <!-- latin capital letter A with grave
                                  = latin capital letter A grave,
                                  U+00C0 ISOlat1 -->'."\n");
echo('<!ENTITY Aacute "&#193;"> <!-- latin capital letter A with acute,
                                  U+00C1 ISOlat1 -->'."\n");
echo('<!ENTITY Acirc  "&#194;"> <!-- latin capital letter A with circumflex,
                                  U+00C2 ISOlat1 -->'."\n");
echo('<!ENTITY Atilde "&#195;"> <!-- latin capital letter A with tilde,
                                  U+00C3 ISOlat1 -->'."\n");
echo('<!ENTITY Auml   "&#196;"> <!-- latin capital letter A with diaeresis,
                                  U+00C4 ISOlat1 -->'."\n");
echo('<!ENTITY Aring  "&#197;"> <!-- latin capital letter A with ring above
                                  = latin capital letter A ring,
                                  U+00C5 ISOlat1 -->'."\n");
echo('<!ENTITY AElig  "&#198;"> <!-- latin capital letter AE
                                  = latin capital ligature AE,
                                  U+00C6 ISOlat1 -->'."\n");
echo('<!ENTITY Ccedil "&#199;"> <!-- latin capital letter C with cedilla,
                                  U+00C7 ISOlat1 -->'."\n");
echo('<!ENTITY Egrave "&#200;"> <!-- latin capital letter E with grave,
                                  U+00C8 ISOlat1 -->'."\n");
echo('<!ENTITY Eacute "&#201;"> <!-- latin capital letter E with acute,
                                  U+00C9 ISOlat1 -->'."\n");
echo('<!ENTITY Ecirc  "&#202;"> <!-- latin capital letter E with circumflex,
                                  U+00CA ISOlat1 -->'."\n");
echo('<!ENTITY Euml   "&#203;"> <!-- latin capital letter E with diaeresis,
                                  U+00CB ISOlat1 -->'."\n");
echo('<!ENTITY Igrave "&#204;"> <!-- latin capital letter I with grave,
                                  U+00CC ISOlat1 -->'."\n");
echo('<!ENTITY Iacute "&#205;"> <!-- latin capital letter I with acute,
                                  U+00CD ISOlat1 -->'."\n");
echo('<!ENTITY Icirc  "&#206;"> <!-- latin capital letter I with circumflex,
                                  U+00CE ISOlat1 -->'."\n");
echo('<!ENTITY Iuml   "&#207;"> <!-- latin capital letter I with diaeresis,
                                  U+00CF ISOlat1 -->'."\n");
echo('<!ENTITY ETH    "&#208;"> <!-- latin capital letter ETH, U+00D0 ISOlat1 -->'."\n");
echo('<!ENTITY Ntilde "&#209;"> <!-- latin capital letter N with tilde,
                                  U+00D1 ISOlat1 -->'."\n");
echo('<!ENTITY Ograve "&#210;"> <!-- latin capital letter O with grave,
                                  U+00D2 ISOlat1 -->'."\n");
echo('<!ENTITY Oacute "&#211;"> <!-- latin capital letter O with acute,
                                  U+00D3 ISOlat1 -->'."\n");
echo('<!ENTITY Ocirc  "&#212;"> <!-- latin capital letter O with circumflex,
                                  U+00D4 ISOlat1 -->'."\n");
echo('<!ENTITY Otilde "&#213;"> <!-- latin capital letter O with tilde,
                                  U+00D5 ISOlat1 -->'."\n");
echo('<!ENTITY Ouml   "&#214;"> <!-- latin capital letter O with diaeresis,
                                  U+00D6 ISOlat1 -->'."\n");
echo('<!ENTITY times  "&#215;"> <!-- multiplication sign, U+00D7 ISOnum -->'."\n");
echo('<!ENTITY Oslash "&#216;"> <!-- latin capital letter O with stroke
                                  = latin capital letter O slash,
                                  U+00D8 ISOlat1 -->'."\n");
echo('<!ENTITY Ugrave "&#217;"> <!-- latin capital letter U with grave,
                                  U+00D9 ISOlat1 -->'."\n");
echo('<!ENTITY Uacute "&#218;"> <!-- latin capital letter U with acute,
                                  U+00DA ISOlat1 -->'."\n");
echo('<!ENTITY Ucirc  "&#219;"> <!-- latin capital letter U with circumflex,
                                  U+00DB ISOlat1 -->'."\n");
echo('<!ENTITY Uuml   "&#220;"> <!-- latin capital letter U with diaeresis,
                                  U+00DC ISOlat1 -->'."\n");
echo('<!ENTITY Yacute "&#221;"> <!-- latin capital letter Y with acute,
                                  U+00DD ISOlat1 -->'."\n");
echo('<!ENTITY THORN  "&#222;"> <!-- latin capital letter THORN,
                                  U+00DE ISOlat1 -->'."\n");
echo('<!ENTITY szlig  "&#223;"> <!-- latin small letter sharp s = ess-zed,
                                  U+00DF ISOlat1 -->'."\n");
echo('<!ENTITY agrave "&#224;"> <!-- latin small letter a with grave
                                  = latin small letter a grave,
                                  U+00E0 ISOlat1 -->'."\n");
echo('<!ENTITY aacute "&#225;"> <!-- latin small letter a with acute,
                                  U+00E1 ISOlat1 -->'."\n");
echo('<!ENTITY acirc  "&#226;"> <!-- latin small letter a with circumflex,
                                  U+00E2 ISOlat1 -->'."\n");
echo('<!ENTITY atilde "&#227;"> <!-- latin small letter a with tilde,
                                  U+00E3 ISOlat1 -->'."\n");
echo('<!ENTITY auml   "&#228;"> <!-- latin small letter a with diaeresis,
                                  U+00E4 ISOlat1 -->'."\n");
echo('<!ENTITY aring  "&#229;"> <!-- latin small letter a with ring above
                                  = latin small letter a ring,
                                  U+00E5 ISOlat1 -->'."\n");
echo('<!ENTITY aelig  "&#230;"> <!-- latin small letter ae
                                  = latin small ligature ae, U+00E6 ISOlat1 -->'."\n");
echo('<!ENTITY ccedil "&#231;"> <!-- latin small letter c with cedilla,
                                  U+00E7 ISOlat1 -->'."\n");
echo('<!ENTITY egrave "&#232;"> <!-- latin small letter e with grave,
                                  U+00E8 ISOlat1 -->'."\n");
echo('<!ENTITY eacute "&#233;"> <!-- latin small letter e with acute,
                                  U+00E9 ISOlat1 -->'."\n");
echo('<!ENTITY ecirc  "&#234;"> <!-- latin small letter e with circumflex,
                                  U+00EA ISOlat1 -->'."\n");
echo('<!ENTITY euml   "&#235;"> <!-- latin small letter e with diaeresis,
                                  U+00EB ISOlat1 -->'."\n");
echo('<!ENTITY igrave "&#236;"> <!-- latin small letter i with grave,
                                  U+00EC ISOlat1 -->'."\n");
echo('<!ENTITY iacute "&#237;"> <!-- latin small letter i with acute,
                                  U+00ED ISOlat1 -->'."\n");
echo('<!ENTITY icirc  "&#238;"> <!-- latin small letter i with circumflex,
                                  U+00EE ISOlat1 -->'."\n");
echo('<!ENTITY iuml   "&#239;"> <!-- latin small letter i with diaeresis,
                                  U+00EF ISOlat1 -->'."\n");
echo('<!ENTITY eth    "&#240;"> <!-- latin small letter eth, U+00F0 ISOlat1 -->'."\n");
echo('<!ENTITY ntilde "&#241;"> <!-- latin small letter n with tilde,
                                  U+00F1 ISOlat1 -->'."\n");
echo('<!ENTITY ograve "&#242;"> <!-- latin small letter o with grave,
                                  U+00F2 ISOlat1 -->'."\n");
echo('<!ENTITY oacute "&#243;"> <!-- latin small letter o with acute,
                                  U+00F3 ISOlat1 -->'."\n");
echo('<!ENTITY ocirc  "&#244;"> <!-- latin small letter o with circumflex,
                                  U+00F4 ISOlat1 -->'."\n");
echo('<!ENTITY otilde "&#245;"> <!-- latin small letter o with tilde,
                                  U+00F5 ISOlat1 -->'."\n");
echo('<!ENTITY ouml   "&#246;"> <!-- latin small letter o with diaeresis,
                                  U+00F6 ISOlat1 -->'."\n");
echo('<!ENTITY divide "&#247;"> <!-- division sign, U+00F7 ISOnum -->'."\n");
echo('<!ENTITY oslash "&#248;"> <!-- latin small letter o with stroke,
                                  = latin small letter o slash,
                                  U+00F8 ISOlat1 -->'."\n");
echo('<!ENTITY ugrave "&#249;"> <!-- latin small letter u with grave,
                                  U+00F9 ISOlat1 -->'."\n");
echo('<!ENTITY uacute "&#250;"> <!-- latin small letter u with acute,
                                  U+00FA ISOlat1 -->'."\n");
echo('<!ENTITY ucirc  "&#251;"> <!-- latin small letter u with circumflex,
                                  U+00FB ISOlat1 -->'."\n");
echo('<!ENTITY uuml   "&#252;"> <!-- latin small letter u with diaeresis,
                                  U+00FC ISOlat1 -->'."\n");
echo('<!ENTITY yacute "&#253;"> <!-- latin small letter y with acute,
                                  U+00FD ISOlat1 -->'."\n");
echo('<!ENTITY thorn  "&#254;"> <!-- latin small letter thorn,
                                  U+00FE ISOlat1 -->'."\n");
echo('<!ENTITY yuml   "&#255;"> <!-- latin small letter y with diaeresis,
                                  U+00FF ISOlat1 -->'."\n");
								  
/*

/*echo('<!ENTITY % HTMLlat1 SYSTEM'."\n".'
       "plugins/lat1.dtd">'."\n".'
    %HTMLlat1;'."\n");*///this didn't work, don't know why...
//echo(']'."\n");
//echo('>'."\n");


echo('<rss version="2.0">'."\n");
echo('	<channel>'."\n");
echo('		<title>'.$sys_info["title"].'</title>'."\n");
echo('		<link>http://'.$url.'</link>'."\n");
echo('		<description>a website by '.$sys_info["author"].'</description>'."\n");
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
