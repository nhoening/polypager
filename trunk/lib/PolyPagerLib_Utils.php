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

/*this is the place where you can turn logging on or off
  (it is so because almost every script uses this library)
*/
$debug = false ;

/*
 * the PolyPager version
 */
$version = '1.0rc5';

/* when true, the admin name and password are set to
 * 'admin'/'admin' (in getSysInfo()) and openly announced 
 * (in writeIntro())  !!!
 */
$run_as_demo = false;

/* ATTENTION: 	some of the functions here (the getters mostly)
				use lazy instantiation:
				never use those vars directly, although you could
				make them global (calling the methods ensures its
				instantiated at that point)
*/
if ( !defined('FILE_SEPARATOR') ) {
	define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}

set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot());

require_once("PolyPager_Config.php");
require_once("lib"  . FILE_SEPARATOR .  "PolyPagerLib_DB.php");
require_once("lib"  . FILE_SEPARATOR .  "PolyPagerLib_Entities.php");
require_once("plugins"  . FILE_SEPARATOR .  "utf8.php");

$sys = getSysInfo();
$lang = $sys["lang"];

 
/*
 * my own scandir() (PHP 4 doesn't have it) reads directory without "." or ".."
 * NOTE: it also doesn't show hidden files (starting with '.')!
 * @param sort lets you revert-sort the results when it's set to 1 (else: 0)
 * @param only_pics set it to true if you only want pics returned
 * @param only_dirs set it to true if you only want dirnames returned
 */
function scandir_n($dir = './', $sort = 0, $only_pics = false, $only_dirs = false) { //(originally scandir is PHP 5)
	$dir_open = @ opendir($dir);
	if (! $dir_open) return false;
	
	$files = array();
	while (($dir_content = readdir($dir_open)) !== false){
		if($dir_content != "." && $dir_content != ".." && utf8_substr($dir_content,0,1) != '.') 
			if (!$only_pics or in_array(utf8_strtolower(utf8_substr($dir_content,-4)),array('.jpg','.gif','.png','tiff')))
				if (!$only_dirs or filetype($dir.'/'.$dir_content)=='dir')
					$files[] = $dir_content;
	}
	if ($sort == 1)
	   rsort($files, SORT_STRING);
	else
	   sort($files, SORT_STRING);
	return $files;
}

/* returns the SQL-filtered string */
function filterSQL($v) {
	//we do this only for strings and only if magic quotes do not do this
	//already (see http://www.dynamicwebpages.de/php/ref.info.php#ini.magic-quotes-gpc)
	if (gettype($v) == "string" and get_magic_quotes_gpc() != 1) {
		return addslashes($v);
	}
	else return $v;
}


/* formats a date string in, for example, "22 Sep 2006"
*/
function format_date($timestamp) {
    //format depends on wether we have a timestamp or not
    if (utf8_strlen($timestamp)>10) $fstr = 'd M Y - G:i'; else $fstr = 'd M Y';
	if (utf8_substr($timestamp,0,10)=='0000-00-00') return __('no date set yet');
	if ($lang == "de")
		return date($fstr, strtotime($timestamp));
	else 
		return date($fstr, strtotime($timestamp));
}

/*get format string for the calendar*/
function getDateFormat($type){
    switch($type){
        case 'date': return '%Y-%m-%d';
        case 'datetime': return '%Y-%m-%d %I:%M:%S';
        case 'time': case 'timestamp': return '%I:%M:%S';
        case 'year': return '%Y';
        default: return '';
    }
}

/*
 * returns a csv list without whitespace
 */
function stripCSVList($csv_string) {
	$arr1 = utf8_explode(',', $csv_string);
	for($i=0;$i<count($arr1);$i++) {
		$arr1[$i] = trim($arr1[$i]);
	}
	return implode(',',$arr1);
}

/*
 * returns a string like this: '2006-27-4' from an array
 * like those gotten from getdate()
 */
function buildDateString($date_array) {
	return $date_array["year"].'-'.$date_array["mon"].'-'.$date_array["mday"];	
}

/*
 * returns a string like this: '8:32:45' from an array
 * like those gotten from localtime()
 */
function buildTimeString($time_array) {
	return $time_array["tm_hour"].':'.$time_array["tm_min"].':'.$time_array["tm_sec"];	
}

/*
 * get a Datetime string
 */
function buildDateTimeString(){
	return buildDateString(getdate()).' '.buildTimeString(localtime(time() , 1));
} 

/* build a valid (in terms of SGML) ID attribute from the given text 
	How? The short answer is that the first character 
	must be a letter, and any other characters 
	may be a letter, a digit, ".", or "-".
*/
function buildValidIDFrom($text){
	$text = preg_replace('/^[^a-zA-Z0-9\.-]/','p',$text);
	$text = preg_replace('/[^a-zA-Z0-9\.-]/','-',$text);
	return $text;
}

/*
	best guess for that at this point: [A-Za-z0-9_]+
	maxlength: 64
	more here: http://dev.mysql.com/doc/refman/5.0/en/legal-names.html
*/
function buildValidMySQLTableNameFrom($text){
	$text = preg_replace('/[^a-zA-Z0-9_]/','_',$text);
	return utf8_substr($text,0,55); //there is a MySQL upper limit for length of a table name 
}

	
/* preserve Markup for text fields
    the consensus here is that &...; entities are going
    to stay, but < and > are preserved (same as Firefox tab titles) 
*/
function preserveMarkup($content){
    $content = utf8_str_replace("&", "&amp;", $content);
    $content = utf8_str_replace(">", "&gt;", $content);
    $content = utf8_str_replace("<", "&lt;", $content);
    return $content;
}
    
/* import a PHP/HTML template.
   If the skin cannot be found, we default back to default
   remember that templates expect writeData() !
*/
function useTemplate($path_to_root_dir){
	global $area, $path_to_root_dir, $error_msg_text, $sys_msg_text;
	$sys_info = getSysInfo();
    
	//this is the first place where we see that sys-tables don't exist!!
	if($sys_info['no_tables']) {
		$link_text = __('PolyPager found the database. Very good. <br/>But: it seems that it does not yet have its database configured. By clicking on the following link you can assure that all the tables that PolyPager needs to operate are being created (if they have not been already).<br/>');
		$link_href = "admin/?&cmd=create";
		global $area;
		if ($area == '_admin') $link_href = './?&cmd=create';
		if ($area == '_gallery') $link_href = '../../admin/?&cmd=create';
		$error_msg_text .= '<div id="no_tables_warning" class="sys_msg">'.$link_text.'<a href="'.$link_href.'">click here to create the tables.</a></div>'."\n";
	}
	if (utf8_strpos($sys_info['skin'], 'picswap')>-1) $skin = 'picswap';
	else $skin = $sys_info['skin'];
    $template_dirpath = $path_to_root_dir."/style/skins/".$skin;
	$template_filepath = $template_dirpath."/template.php";
	if (file_exists($template_filepath)){
		@include($template_filepath);
	}else if (file_exists($template_dirpath)){
		//if($area == '_admin') $sys_msg_text .= '<div class="sys_msg">The template.php file in the '.$skin.'-directory couldn\'t be found.</div>'."\n";
		// we fall silently back to the template file
        @include($template_dirpath."/template.php.template");
	}else {
		$sys_msg_text .= '<div class="sys_msg">'.__('Warning: The selected skin couldn\'t be found.').'</div>'."\n";
		@include($path_to_root_dir."/style/skins/polly/template.php.template");
	}
}

/* Path to Root dir of this webpage relative to the document root
	this path should always end with a "/" if it is not empty,
	and always start with one...*/
function getPathFromDocRoot() {
	$doc_root = $_SERVER['DOCUMENT_ROOT'];
	if (FILE_SEPARATOR != '/')
		$doc_root = eregi_replace('/', FILE_SEPARATOR, $doc_root);
	$doc_root_folders = explode(FILE_SEPARATOR, $doc_root);
	$cwd_folders = explode(FILE_SEPARATOR, getcwd());
	$folders_from_doc_root = array_diff($cwd_folders, $doc_root_folders);
	$path = implode(FILE_SEPARATOR, $folders_from_doc_root);
	//maybe we're in admin or scripts or so
	//echo($path."|".strstr($path, 'admin')."|".utf8_substr( $path, 0, utf8_strpos( $path, "admin" ) )."|");
	if(eregi('admin',$path) != false or $path == 'admin')
		{$path = substr( $path, 0, strpos( $path, "admin" ) ) ;}
	if(eregi('scripts',$path) != false or $path == 'scripts')
		{$path =  substr( $path, 0, strpos( $path, "scripts" ) ) ;}
	if(eregi('plugins',$path)!= false or $path == 'plugins')
		{$path =  substr( $path, 0, strpos( $path, "plugins" ) ) ;}
	if(eregi('user',$path) != false or $path == 'user')
		{$path =  substr( $path, 0, strpos( $path, "user" ) ) ;}
	if ($path == "") $path = FILE_SEPARATOR;
	if (substr( $path, 0, 1) != FILE_SEPARATOR) $path = FILE_SEPARATOR.$path;
	if (substr( $path, strlen($path)-1, strlen($path)) != FILE_SEPARATOR) $path = $path.FILE_SEPARATOR;
	return $path;
}

/*writes a PolyPager standard help link (which is an icon, with hover-over text) */
function writeHelpLink($indent, $helptext) {
	echo($indent.'<a class="help" onmouseover="popup(\''.$helptext.'\')" onmouseout="kill()" title="" onfocus="this.blur()" >&nbsp;&nbsp;&nbsp;&nbsp;</a>'."\n");
}

/*	compares two names
*    resulting array will be sorted ascending
*/
function cmpByName($a, $b) {
	$an = utf8_strtolower($a);
	$bn = utf8_strtolower($b);
	if ($an == $bn) return 0;
	return ($an < $bn) ? -1 : 1;
}



/*	compares two array entries, using the order_index entries
*    resulting array will be sorted descending
*/
function cmpByOrderIndexDesc($a, $b) {
	if ($a['order_index'] == $b['order_index']) return 0;
	return ($a['order_index'] > $b['order_index']) ? -1 : 1;
}

/*	compares two array entries, using the order_index entries
*    resulting array will be sorted ascending
*/
function cmpByOrderIndexAsc($a, $b) {
	if ($a['order_index'] == $b['order_index']) return 0;
	return ($a['order_index'] < $b['order_index']) ? -1 : 1;
}


/*returns a (translated) text. this is not the
	GNU gettext module, but it could be used by replacing this method*/
function __($text) {
	//global $_SERVER;
	$sys = getSysInfo();
	$lang = $sys["lang"];
	//set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot().'locales'.FILE_SEPARATOR.$lang);
	if ($lang == "") {	//do nothing
		return $text;
	} else {
		require_once('locales'.FILE_SEPARATOR.$lang.FILE_SEPARATOR.$lang.'.php');
		//$translation = getTranslation($text);
        $translation = call_user_func('getTranslation_'.$lang, $text);
		if ($translation == "") {
			return $text;
			//maybe send an error email to admin?
		}
		else return $translation;
	}
}

/*	returns an array with names, in_menue and
	menue_index info of known singlepages*/
$singlepages = "";
function getSinglepages() {
	global $singlepages;

	if ($singlepages == "") {
		//echo("getting singlepages");
		$singlepages = array();
		$query = "SELECT * FROM _sys_singlepages";
		$res = mysql_query($query, getDBLink());
		$i = 0;
		while ($res and $row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$page = array();
			$page["id"] = $row["id"];
			$page["name"] = $row["name"];
			$page["in_menue"] = $row["in_menue"];
			$page["menue_index"] = $row["menue_index"];
			$singlepages[$i] = $page;
			$i++;
		}
	}
	return $singlepages;
}

/*	returns an array with just the names of known singlepages*/
$singlepage_names = "";
function getSinglepageNames() {
	global $singlepage_names;
	if ($singlepage_names == "") {
		$singlepage_names = array();
		$sp = getSinglepages();
		for($x = 0; $x < count($sp); $x++){
			$singlepage_names[$x] = $sp[$x]["name"];
		}
	}
	return $singlepage_names;
}

/*	returns an array with names, in_menue and
	menue_index info of known multipages*/
$multipages = "";
function getMultipages() {
	global $multipages;
	if ($multipages == "") {
		$multipages = array();
		$query = "SELECT * FROM _sys_multipages";
		$res = mysql_query($query, getDBLink());
		$i = 0;
		while ($res and $row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$page = array();
			$page["id"] = $row["id"];
			$page["name"] = $row["name"];
			$page["in_menue"] = $row["in_menue"];
			$page["menue_index"] = $row["menue_index"];
			$multipages[$i] = $page;
			$i++;
		}
	}
	return $multipages;
}

/*	returns an array with just the names of known multipages*/
$multipage_names = "";
function getMultipageNames() {
	global $multipage_names;
	if ($multipage_names == "") {
		$multipage_names = array();
		$mp = getMultipages();
		for($x = 0; $x < count($mp); $x++){
			$multipage_names[$x] = $mp[$x]["name"];
		}
	}
	return $multipage_names;
}

/*returns all pages*/
function getPages() {
	return array_merge(getSinglepages(), getMultipages());
}

/*returns all pagenames, sorted alphabetically*/
function getPageNames() {
	$all = array_merge(getSinglepageNames(), getMultipageNames());
	uasort($all,'cmpByName');
	return $all;
}

/* get Pagename according to a number */
function getMultipageNameByNr($nr){
	$pages = getMultipages();
	foreach($pages as $p) {
		if ($p['id'] == $nr) return $p['name'];
	}
	return "";
}

/* returns true if the page is a singlepage */
function isSinglepage($page_name) {
	$sp = getSinglepages();
	foreach ($sp as $p)	{
		if ($p["name"] == $page_name) return true;
	}
	return false;
}

/* returns true if the page is a multipage */
function isMultipage($page_name) {
	if (isASysPage($page_name)){
		return true;
	}
	$mp =  getMultipages();
	foreach ($mp as $p)	if ($p["name"] == $page_name) return true;
	return false;
}

/* returns true if the page is known by the system */
function isAKnownPage($page_name) {
	return (isSinglepage($page_name) or isMultipage($page_name) or $page_name=="_search");
}

/* return true if it is a sys-page, i.e. representing system data.
   Once this checked for a start like "_sys_" but that's not enough security
*/
function isASysPage($page_name) {
	$sysp = array('_sys_comments','_sys_sys','_sys_feed','_sys_fields','_sys_singlepages',
				'_sys_multipages','_sys_sections','_sys_intros','_sys_pages','_sys_tags');
	return (in_array($page_name,$sysp));
}

/* get system info
*/
$sys_info = "";
function getSysInfo() {
	global $sys_info;
	global $run_as_demo;
	global $params;
	if ($sys_info == "") {
		$query = "SELECT * FROM _sys_sys";
		$res = mysql_query($query, getDBLink());
		if ($res) $sys_info = mysql_fetch_array($res, MYSQL_ASSOC); //we expect only one
	}
	$fehler_nr = mysql_errno(getDBLink());
	if ($fehler_nr!==0) {
		$fehler_text=mysql_error(getDBLink());
		//if we didn't find the table and there is no create-command, then 
		//there doesn't seem to be content in the db...
		if ($_GET['cmd']!='create' and eregi("doesn't exist", $fehler_text) and $params["cmd"] != 'create') {
			$sys_info = array();
			$sys_info['no_tables'] = true; // remember that we didn't see the table
		}else{
			$sys_info['no_tables'] = false;
		}
	}
	
	//default for the case that we haven't any data yet
	if ($sys_info["skin"] == "" or $sys_info['no_tables']) {
		$sys_info["skin"] = 'polly';
	}
	$params['values']['skin'] = $sys_info["skin"];
    
    //encoding is not user-configurable
    $sys_info["encoding"] = "utf-8";
	
	//in demo-mode, adminname and password are set
	if($run_as_demo) {
		$sys_info["admin_name"] = "admin";
		$sys_info["admin_pass"] = "admin";
	}
	return $sys_info;
}

/* get a list of non-system tables
*/
$non_sys_tables = "";
function getTables() {
	global $non_sys_tables;
	if ($non_sys_tables == "") {
		$non_sys_tables = array();
		$tables = mysql_list_tables(getDBName(), getDBLink());
		$amount = mysql_num_rows($tables);
		for($x = 0; $x < $amount; $x++){
		  $table_name = mysql_tablename($tables, $x);
		  if(utf8_substr($table_name,0,5) != "_sys_") $non_sys_tables[$x] = $table_name;
		}
	}
	return $non_sys_tables;
}

/* get page meta-info for the actual page (single/multipage info)
*/
$page_info = "";
$old_page_infos = "";
function getPageInfo($page_name) {
	global $page_info;
    if (isAKnownPage($page_name)) {
        if ($page_name == "") return $page_info;
        if ($page_info == "" or $page_info["name"] != $page_name) {
            if (getAlreadyBuiltPageInfo($page_name) != "") {
                $page_info = getAlreadyBuiltPageInfo($page_name);
            } else {
                $page_info = array();
                if (isSinglePage($page_name)) $query = "SELECT * FROM _sys_singlepages";
                else if (isMultiPage($page_name)) $query = "SELECT * FROM _sys_multipages";
                else { //no page? maybe a table. try the first page for this table
                    $pq = "SELECT name FROM _sys_multipages WHERE tablename = '".$page_name."'";
                    $res = pp_run_query($pq);
                    if($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                        $page_name = $row["name"];
                        $query = "SELECT * FROM _sys_multipages";
                    }
                }
                if ($query != "") {
                    $query = $query." WHERE name = '".$page_name."'";
                    $res = mysql_query($query, getDBLink());
                    $page_info = mysql_fetch_array($res, MYSQL_ASSOC); //we expect only one
                }
                //adding this if page info is used for queries
                if(isSinglePage($page_name)) {
                    $page_info["tablename"] = '_sys_sections';
                    $page_info["title_field"] = 'heading';
                }
                else if (isASysPage($page_name)) $page_info["tablename"] = $page_name;
                //adding this for comment preview
                if($page_name=='_sys_comments') $page_info["hide_toc"] = 1;
            }
        }
	}
	return $page_info;
}

/*gives you an alreay built page_info if it is stored in $old_page_infos (it should)*/
function getAlreadyBuiltPageInfo($page_name) {
	global $old_page_infos;
	if ($old_page_infos!="")
		foreach($old_page_infos as $p){
			if ($p["name"] == $page_name) return $p;
		}
	return "";
}

/* resetting all lazy instantiated stuff - so we get a fresh start
	use this when you updated the db and you might write more stuff */
function resetLazyData() {
	global $singlepages;
	$singlepages = "";
	global $singlepage_names;
	$singlepage_names = "";
	global $multipages;
	$multipages = "";
	global $multipage_names;
	$multipage_names = "";
	global $entity;
	$entity = "";
	global $old_entities;
	$old_entities = "";
    global $old_page_infos;
	$old_page_infos = "";
	global $sys_infos;
	$sys_infos = "";
}


/* Get the title of an entry.
   Takes a database result row, finds the title field and returns the content.
   Makes sure its not too long... (maybe the title_field had to be guessed and
   it's a long text or blob)
*/
function getTitle($entity,$row){
    return trim(getFirstWords($row[$entity['title_field']], 30));
}

/*
 * guesses what field might be containing the
 * interesting text in the named entity
 * (used for example for RSS)
 */
function guessTextField($entity, $prefer_long_text=true) {
	$the_field = "";                              
	//try long texts
	for($i=0; $i<count($entity["fields"]); $i++) {
        $dt = $entity["fields"][$i]["data_type"];
		if (($prefer_long_text and isTextAreaType($dt)) or
            (!$prefer_long_text and isTextType($dt))) {
			$the_field = $entity["fields"][$i]["name"];
			break;
		}
	}
	//now others
	if ($the_field == "")
		for($i=0; $i<count($entity["fields"]); $i++) {
            $dt = $entity["fields"][$i]["data_type"];
			if (($prefer_long_text and isTextType($dt)) or
                (!$prefer_long_text and isTextAreaType($dt))) {
				$the_field = $entity["fields"][$i]["name"];
				break;
			}
		}
	
	return $the_field;
}

/*
 * gets the first words from an HTML string
 */
function getFirstWords($html_str, $number){
	$result = "";
	$text = strip_tags($html_str);
	$text_arr = utf8_explode(' ', $text);
	//print_r($text_arr);
	$m_number = min($number, count($text_arr));
	for($i=0; $i<$m_number; $i++) {
		if ($text_arr[$i] != "") $result = $result.' '.$text_arr[$i];
	}
	if ($m_number < count($text_arr)) $result = $result.' (...)';
	return preg_replace('/\s\s+/', ' ', $result);	//strip all whitespace into ' '
}

/* escape regexes */

function escape_regex($t) {
    $t = utf8_str_replace(".", "\.", $t);
    $t = utf8_str_replace("+", "\+", $t);
    $t = utf8_str_replace("*", "\*", $t);
    $t = utf8_str_replace("[", "\[", $t);
    $t = utf8_str_replace("]", "\]", $t);
    $t = utf8_str_replace("?", "\?", $t);
    $t = utf8_str_replace('$', '\$', $t);
    $t = utf8_str_replace("^", "\^", $t);
    $t = utf8_str_replace("(", "\(", $t);
    $t = utf8_str_replace(")", "\)", $t);
    $t = utf8_str_replace("|", "\|", $t);
    return $t;
}

if (!function_exists("str_ireplace")) {
    function str_ireplace($search, $replace, $subject) {
        return eregi_replace(escape_regex($search), escape_regex($replace), $subject);
    }
} 

/* Validation functions - get validation regexes*/
function getValidationRegex($validation) {
	if ($validation == 'any_text') {
		return '/.+/';
	}
	if ($validation == 'email') {
		return '/([A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]+)/';
		//return '/.+@.+\.[A-Za-z]+/';
	}
	if ($validation == 'url') {
		return '/(((ht|f)tp(s?))\:\/\/)?.+\..+\.[A-Za-z]+/';
	}
	
	return "";
}

/* Validation functions - get validation messages*/
function getValidationMsg($validation) {
	if ($validation == 'any_text') {
		return __('please specify some text here.');
	}
	if ($validation == 'email') {
		return __('please specify a valid email address here.');
	}
	if ($validation == 'url') {
		return __('please specify a valid URL here.');
	}
	return "";
}

/*return a whitespace string containing of tabs,
	the length according to the given numner*/
function translateIndent($number) {
	$indent = "";
	for ($x = $number; $x>0; $x--)	{
		$indent = $indent."\t";
	}
	return $indent;
}

?>
