<?
/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas Hšning
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
		$version = '0.9.6';
		
		/* when true, the admin name and password are set to
		 * 'admin'/'admin' (in getSysInfo()) and openly announced 
		 * (in writeIntro())  !!!
		 */
		$run_as_demo = false;
		
		/*
		function index:
		 * scandir_n($dir = './', $sort = 0)
		 * filterXMLChars($v)
		 * getPathFromDocRoot()
		 * __($text)
		 * getDBLink()
		 * getSinglepages()
		 * getSinglepageNames()
		 * getMultipageNames()
		 * getMultipages()
		 * isSinglepage($page_name)
		 * isMultipage($page_name)
		 * isAKnownPage($page_name)
		 * isTextType($var)
		 * getPages()
		 * getSysInfo()
		 * getPageInfo($page_name)
		 * getEntity($page_name)
		 * addFields()
		 * setEntityFieldValue($f_name, $attr_name, $attr_value)
		 * getEntityField($fname)
		 * getListOfDateFields()
		 * getListOfValueListFields()
		 * getColor($code)


		  ATTENTION: 	some of the functions here (the getters mostly)
		  				use lazy instantiation:
						never use those vars directly, although you could
						make them global (calling the methods ensures its
						instantiated at that point)
		*/
		if ( !defined('FILE_SEPARATOR') ) {
			define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
		}

		set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot());
		//echo("get_include_path:".get_include_path()."<br/>");
		//echo("doc-root:".$_SERVER['DOCUMENT_ROOT']."<br/>");
		//echo("getPathFromDocRoot():".getPathFromDocRoot()."<br/>");
		//echo("getcwd()):".getcwd()."<br/>");
		require_once("PolyPager_Config.php");

		$sys = getSysInfo();
		$lang = $sys["lang"];
		
		/*
		 * run the query and append error msg to the buffer (if given) 
		 */
		function pp_run_query($query){
			global $debug;
			if($debug) echo('<div class="debug">running query::|'.$query.'|</div>');
			$res = mysql_query($query, getDBLink());
			$error_nr = mysql_errno(getDBLink());
			if ($error_nr != 0) {
				$error_buffer = $error_buffer.'|'.mysql_error(getDBLink());
			}
			if($debug) echo('<div class="debug">got error(s):|'.$error_buffer.'|</div>');
			
			return $res;
		}
		 
		//my own scandir() (PHP 4 doesn't have it) reads directory without "." or ".."
		function scandir_n($dir = './', $sort = 0) { //(originally scandir is PHP 5)
		   $dir_open = @ opendir($dir);
		   if (! $dir_open) return false;

		   $files = array();

		   while (($dir_content = readdir($dir_open)) !== false)
			 if($dir_content != "." && $dir_content != "..") $files[] = $dir_content;

		   if ($sort == 1)
			   rsort($files, SORT_STRING);
		   else
			   sort($files, SORT_STRING);
		   return $files;
		}
		
		/* returns the SQL filtered string */
		function filterSQL($v) {
			//we do this only for strings and only if magic quots do not do this
			//already (see http://www.dynamicwebpages.de/php/ref.info.php#ini.magic-quotes-gpc)
			if (gettype($v) == "string" and get_magic_quotes_gpc() != 1) {
				return addslashes($v);
				//return $v;
			}
			else return $v;
		}
	
		/* code to escape special chars */
		//returns the filtered string - not needed 29.01.2006
		function filterXMLChars($v) {

			$v = str_replace("&amp;", "&amp;amp;", $v);
			$v = str_replace("&gt;", "&amp;gt;", $v);
			$v = str_replace("&lt;", "&amp;lt;", $v);
			return $v;
		}
		
		/* formats a date string in, for example, "22 Sep 2006"
		   needs a timestamp as input, so if you only have a date
		   convert it with strtotime() beforehand...
		*/
		function format_date($timestamp) {
			if ($lang == "de")
				return date('d.m.Y', strtotime($timestamp));
			else 
				return date('d M Y', strtotime($timestamp));
		}
		
		/*
		 * returns a csv list without whitespace
		 */
		function stripCSVList($csv_string) {
			$arr1 = explode(',', $csv_string);
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
			//echo($path."|".strstr($path, 'admin')."|".substr( $path, 0, strpos( $path, "admin" ) )."|");
			if(strstr($path, 'admin') != false or $path == 'admin')
				{$path = substr( $path, 0, strpos( $path, "admin" ) ) ;}
			if(strstr($path, 'scripts') != false or $path == 'scripts')
				{$path =  substr( $path, 0, strpos( $path, "scripts" ) ) ;}
			if(strstr($path, 'plugins')!= false or $path == 'plugins')
				{$path =  substr( $path, 0, strpos( $path, "plugins" ) ) ;}
			if(strstr($path, 'user') != false or $path == 'user')
				{$path =  substr( $path, 0, strpos( $path, "user" ) ) ;}
			if ($path == "") $path = FILE_SEPARATOR;
			if (substr( $path, 0, 1) != FILE_SEPARATOR) $path = FILE_SEPARATOR.$path;
			if (substr( $path, strlen($path)-1, strlen($path)) != FILE_SEPARATOR) $path = $path.FILE_SEPARATOR;
			//echo($path);
			return $path;
		}

		/*writes a PolyPager standard help link (which is an icon, with hover-over text) */
		function writeHelpLink($indent, $helptext) {
			echo($indent.'<a class="help" onmouseover="popup(\''.$helptext.'\')" onmouseout="kill()" title="" onfocus="this.blur()" >&nbsp;&nbsp;&nbsp;&nbsp;</a>'."\n");
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
			global $_SERVER;
			$sys = getSysInfo();
			$lang = $sys["lang"];
			set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot().'locales'.FILE_SEPARATOR.$lang);
			if ($lang == "") {	//do nothing
				return $text;
			} else {
				require_once($lang.'.php');
				$translation = getTranslation($text);
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
				//echo("getting multipages");
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

		/*returns all pagenames*/
		function getPageNames() {
			return array_merge(getSinglepageNames(), getMultipageNames());
		}

		/* get Pagename accorsing to a number */
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
			if (isSinglepage($page_name) or isMultipage($page_name)) return true;
			else return false;
		}

		/* */
		function isASysPage($page_name) {
			if (strpos($page_name, "_sys_") > -1){
				return true;
			}
			else return false;
		}

		/* returns wether this MySQL type is a date type we support*/
		function isDateType($type) {
			if($type == "date" or $type == "datetime")
				return true;
			else return false;
		}
		
		/* returns wether this MySQL type is a text type*/
		function isTextType($type) {
			if($type == "string" or $type == "varchar" or $type == "blob" or $type == "longtext" or $type=="longblob")
				return true;
			else return false;
		}
		
		/* returns wether this MySQL type is a type that PolyPager handles with a text area*/
		function isTextAreaType($type) {
			if($type=="blob" or $type=="longtext" or $type=="longblob")
				return true;
			else return false;
		}
		
		/* get system info
		*/
		$sys_info = "";
		function getSysInfo() {
			global $sys_info;
			global $run_as_demo;
			if ($sys_info == "") {
				$query = "SELECT * FROM _sys_sys";
				$res = mysql_query($query, getDBLink());
				if ($res) $sys_info = mysql_fetch_array($res, MYSQL_ASSOC); //we expect only one
			}
			//default for the case that we haven't any data yet
			if ($sys_info["skin"] == "") $sys_info["skin"] = 'picswap-aqua';
			global $params;
			$params['values']['skin'] = $sys_info["skin"];
			//as demo, adminname and password are set
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
				  if(substr($table_name,0,5) != "_sys_") $non_sys_tables[$x] = $table_name;
				}
			}
			return $non_sys_tables;
		}

		/* get page meta-info for the actual page (single/multipage info)
		*/
		$page_info = "";
		function getPageInfo($page_name) {
			global $page_info;
			if ($page_info == "" or $page_info["name"] != $page_name) {
				if (isSinglePage($page_name)) $query = "SELECT * FROM _sys_singlepages";
				else if (isMultiPage($page_name)) $query = "SELECT * FROM _sys_multipages";

				if ($query != "") {
					$query = $query." WHERE name = '".$page_name."'";
					$res = mysql_query($query, getDBLink());
					$page_info = mysql_fetch_array($res, MYSQL_ASSOC); //we expect only one
				}
				//adding this if page info is used for queries
				if(isSinglePage($page_name)) $page_info["tablename"] = '_sys_sections';
				else if (isASysPage($page_name)) $page_info["tablename"] = $page_name;
			}
			
			return $page_info;
		}



		/*  an entity is basically some data structure.
			this method gets a multidimensional array for the metadata of pages.
			
			it  gets data from the database, but it is also the place
			to add process information about entites this system handles.
			Because we might ask for the entity multiple times while working
			on a request, we store the variable outside.
			
			Here are some features explained:
			$entity["hidden_form_fields"]": hides form fields from the editing user 
				(mostly the admin). Some fields are hereby hidden
				that are of no interest to the user.
			$entity["hidden_fields"]": hides fields from the visiting user 
				(in the front end)
				For multipages, the admin can hide fields from the user by editing
				the field "hidden_fields" for the page!
			$entity["disabled_fields"]":  like hidden_form_fields, but a disabled field
				is shown in addition to the hidden field (so the value is send, you can see it, 
				but not edit it)
			$entity["dateField"] = array("name"=>".*", "editlabel"=>".*");
			$entity["timeField"] =  array("name"=>".*");
		*/
		$entity = "";		//stores the actual entity
		$old_entities = "";	//stores entites we already built
		function getEntity($page_name) {
			global $entity;
			global $old_entities;
			//echo("getEntity called with param |".$page_name."|\n");
			if ($page_name == "") return $entity;

			if ($entity == "" or $entity["name"] != $page_name) {

				if (getAlreadyBuiltEntity($page_name) != "") {
					$entity = getAlreadyBuiltEntity($page_name);
				} else {
					$entity = array();

					//	metadata for system
					if ($page_name == "_sys_sys") {
						$entity["name"] = "_sys_sys";
						$entity["one_entry_only"] = "1";	//keep it one

						$entity = addFields($entity["name"]);
						$skindirs = scandir_n('../style/skins');
						for($x=0;$x<count($skindirs);$x++){	//picswap gets extra handling in PolyPagerLib_HTMLFraming
							if ($skindirs[$x] == 'picswap') {
								$skindirs = array_splice($skindirs,$x-1,1);
								break;
							}
						}
						$dirs = implode(",",$skindirs);
						$dirs = str_replace(",,", ",",$dirs);
						$dirs = $dirs.",picswap-aqua,picswap-fall,picswap-uptight,picswap-saarpreme";
						setEntityFieldValue("skin", "valuelist", $dirs);
						$sys_info = getSysInfo();

						setEntityFieldValue("lang", "valuelist", "en,de");
						setEntityFieldValue("start_page", "valuelist", implode(',', getPageNames()));
						setEntityFieldValue("feed_amount", "validation", 'number');
						setEntityFieldValue("template", "valuelist", implode(",", scandir_n('../style/templates')));
						
						global $run_as_demo;
						if ($run_as_demo) {
							$entity["hidden_form_fields"] = $entity["hidden_form_fields"].',admin_name,admin_pass';
						}
					}
					//	metadata for multipages that are edited
					else if ($page_name == "_sys_multipages") {
						$entity["name"] = "_sys_multipages";

						$entity = addFields($entity["name"]);

						$entity["title_field"] = "name";
						setEntityFieldValue("order_order", "valuelist", "ASC,DESC");
						setEntityFieldValue("group_order", "valuelist", "ASC,DESC");
						// no tables: no user input
						$tables = getTables();
						if (count($tables) > 0) {
							setEntityFieldValue("tablename", "valuelist", implode(',', $tables));
						} else {
							$entity["disabled_fields"] = $entity["disabled_fields"].',tablename';
							setEntityFieldValue("tablename", "valuelist", __('there is no table in the database yet'));
						}
						$entity["consistency_fields"] = "name";
						
						
						//fill data in for option lists
						//first, find out what table is used for this multipage
						global $params;
						if ($params == "" and function_exists("getEditParameters")) {
							$params = getEditParameters();
						}
						//first, we check if we are sent form data already
						if ($params["values"]["tablename"] != "") {
							$the_table = $params["values"]["tablename"];
						//second, and more difficult, if we come to the form first,
						//we only know the page number... 
						//with that, get the table name
						}else if ($params["nr"] != "") {
							$query = "SELECT tablename FROM _sys_multipages WHERE id = ".$params["nr"];
							$res = mysql_query($query, getDBLink());
							if($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
								$the_table = $row["tablename"];
							}
						//third, if we don't know the table yet (maybe a new page?),
						//take the first one that comes with getTables();
						}else {
							$tables_str = implode("|", $tables);
							if (!strpos( $tables_str, "|" )) {
								$the_table = $tables_str; //there seems to be only one
							} else {
								$the_table = substr( $tables_str, 0, strpos( $tables_str, "|" ) );
							}
						}
						if ($the_table != "") {
							//fill with name fields with suitable type - 
							//the leading "," preserves one empty entry
							setEntityFieldValue("date_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'date,datetime')));
							setEntityFieldValue("time_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'time')));
							setEntityFieldValue("edited_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'date,datetime')));
							setEntityFieldValue("title_field", "valuelist", ','.implode(',', getListOfFields($the_table)));
							setEntityFieldValue("order_by", "valuelist", ','.implode(',', getListOfFields($the_table)));
							setEntityFieldValue("group_field", "valuelist", ','.implode(',', getListOfValueListFields($the_table)));
							//setEntityFieldValue("default_group", "valuelist", ','.$entity["fields"]["group_field"]["valuelist"]);
							setEntityFieldValue("publish_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'bool')));
						} else {
							$entity["disabled_fields"] = $entity["disabled_fields"].',publish_field,date_field,time_field,edited_field,title_field,order_by,group_field';
						}
						
						setEntityFieldValue("menue_index", "validation", 'number');
						setEntityFieldValue("name", "validation", 'any_text');
						setEntityFieldValue("tablename", "validation", 'any_text');
						
						//help texts
						setEntityFieldValue("in_menue", "help", __('when this field is checked, you will find a link to this page in the menu.'));
						setEntityFieldValue("menue_index", "help", __('this field holds a number which determines the order in which pages that are shown in the menu (see above) are arranged.'));
						setEntityFieldValue("commentable", "help", __('when this field is checked, entries on this page will be commentable by users.'));
						setEntityFieldValue("hide_options", "help", __('when this field is checked, administration info under each entry (edit-link,date of last change, ...) will not be shown.'));
						setEntityFieldValue("hide_search", "help", __('when this field is checked, the link to search form will not be shown.'));
						setEntityFieldValue("hide_toc", "help", __('when this field is checked, the table of contents on top of the page will not be shown.'));
						setEntityFieldValue("tablename", "help", __('this field is important: it defines which table to use for this page. So much of the field-fields below depend on what is given here, because PolyPager finds the values for those fields in this table.'));
						setEntityFieldValue("hidden_fields", "help", __('these fields will not be shown to the public. Select fields from the list by clicking on them.'));
						setEntityFieldValue("order_by", "help", __('here you can choose which field should be the order criterium.'));
						setEntityFieldValue("order_order", "help", __('ASC stands for ascending. Take numbers for an example: lowest numbers will come first, highest last. DESC means descending and works the other way round'));
						setEntityFieldValue("publish_field", "help", __('this field will be used to switch if the entry should be public or not'));
						setEntityFieldValue("group_field", "help", __('this field will be used by PolyPager to group entries of this page. It will also be used to create sumenu entries (so the visitor can select what to see quickly) and search criteria. Note that PolyPager lists only fields that have a valuelist assigned (you can do this in the fields section for that page).'));
						setEntityFieldValue("group_order", "help", __('ASC stands for ascending. Take numbers for an example: lowest numbers will come first, highest last. DESC means descending and works the other way round'));
						setEntityFieldValue("date_field", "help", __('this (date)field stores the time its entry was created.'));
						setEntityFieldValue("edited_field", "help", __('this (date)field would display when the last change to its entry took place.'));
						setEntityFieldValue("title_field", "help", __('this field will be used as title field. It will therefore look different to the others.'));
						setEntityFieldValue("feed", "help", __('if this field is checked, new entries of this page will be fed. That means they will be listed under the latest entries (right on the page) and they will be available via RSS. BUT: this will only work if you have selected a title-field AND a date-field !!!'));
						setEntityFieldValue("step", "help", __('here you specify how many entries should be shown on one page. You can use a number or simply all'));
						setEntityFieldValue("show_comments", "help", __('this field has currently no meaning (that means: it is not yet implemented)'));
						setEntityFieldValue("taggable", "help", __('this field has currently no meaning (that means: it is not yet implemented)'));
						setEntityFieldValue("search_month", "help", __('if this field is checked, users can search for entries of this page made in a particular month.'));
						setEntityFieldValue("search_year", "help", __('if this field is checked, users can search for entries of this page made in a particular year.'));
						setEntityFieldValue("search_keyword", "help", __('if this field is checked, users can search for entries of this page by typing in a keyword.'));
						setEntityFieldValue("search_range", "help", __('if this field is checked, users can navigate through entries of this page using previous- and next-links. Only use this when your entries are ordered by (see field order_by, above) the primary key of the table.'));
						setEntityFieldValue("show_labels", "help", __('if this field is checked, the label of each field is shown.'));
					}
					//	metadata for singlepages
					else if ($page_name == "_sys_singlepages") {
						$entity["name"] = "_sys_singlepages";

						$entity = addFields($entity["name"]);
						
						$entity["hidden_fields"] = "default_group";
						$entity["hidden_form_fields"] = "default_group";
						
						$entity["order_order"] = "ASC";
						$entity["title_field"] = "name";
						setEntityFieldValue("menue_index", "validation", 'number');
						setEntityFieldValue("name", "validation", 'any_text');
						
						$entity["consistency_fields"] = ",name";
						
						setEntityFieldValue("in_menue", "help", __('when this field is checked, you will find a link to this page in the menu.'));
						setEntityFieldValue("menue_index", "help", __('this field holds a number which determines the order in which pages that are shown in the menu (see above) are arranged.'));
						setEntityFieldValue("commentable", "help", __('when this field is checked, entries on this page will be commentable by users.'));
						setEntityFieldValue("hide_options", "help", __('when this field is checked, administration info under each entry (edit-link,date of last change, ...) will not be shown.'));
						setEntityFieldValue("hide_search", "help", __('when this field is checked, the link to search form will not be shown.'));
						setEntityFieldValue("hide_toc", "help", __('when this field is checked, the table of contents on top of the page will not be shown.'));
						setEntityFieldValue("grouplist", "help", __('here you can specify groups as a comma separated list. If you do so, the sections of this page can be assigned to one of those groups and the groups will each be an entry in the submenu of the page. If you enter something here, it will override the behavior of letting some sections be anchors to the page that are accessible from the submenu!'));
					}
					//this is just to make the admin/page area work
					else if ($page_name == "_sys_pages") {
						$entity["name"] = "_sys_pages";
							$field1 = array("name"=>"id",
								"data-type"=>"int",
								"size"=>"12");
							$field2 = array("name"=>"name",
								"data-type"=>"varchar",
								"size"=>"60");
							$field3 = array("name"=>"in_menue",
								"data-type"=>"bool",
								"size"=>"1");
						$entity["fields"] = array($field1,$field2,$field3);
						$entity["title_field"] = "name";
						$entity["pk"] = "id";
						$entity["pk_type"] = "int";
					}
					//	table for feeds
					else if ($page_name == "_sys_feed") {
						$entity["name"] = "_sys_feed";
						$entity["title_field"] = "title";
						$entity = addFields($entity["name"]);
						$entity["disabled_fields"] = "pagename";
						$entity["hidden_form_fields"] = "edited_date"; 
					}
					//	table for intros
					else if ($page_name == "_sys_intros") {
						$entity["name"] = "_sys_intros";
						$entity["one_entry_only"] = "1";	//keep it one

						$entity = addFields($entity["name"]);
					}
					//	table for fields
					else if ($page_name == "_sys_fields") {
						$entity["name"] = "_sys_fields";

						$entity = addFields($entity["name"]);

						$group = array("field"=>"pagename",
										"order"=>"DESC");
						$entity["group"] = $group;
						global $params;
						$fields = getListOfFields($params["group"]);
						if (count($fields) > 0) {
							setEntityFieldValue("name", "valuelist", implode(',', $fields));
						} else {
							$entity["disabled_fields"] = $entity["disabled_fields"].',name';
							setEntityFieldValue("name", "valuelist", __('there is no table specified for this page yet'));
						}
						setEntityFieldValue("pagename", "valuelist", implode(',', getMultipageNames()));
						setEntityFieldValue("validation", "valuelist", 'no validation,number,any_text,email');	//not really ready yet
						$entity["title_field"] = "name";
						$entity["disabled_fields"] = $entity["disabled_fields"].",pagename";
						
						//help texts
						setEntityFieldValue("valuelist", "help", __('here you can specify allowed values for this field (via a comma-separated list). By doing so, this field can also become the group field of its page.'));
						setEntityFieldValue("validation", "help", __('you can chose a validation that is checked when you submit a form.'));
						setEntityFieldValue("not_brief", "help", __('check this box when this field contains much data (e.g. long texts). It will then only be shown if the page shows one entry and a link to it otherwise.'));
						setEntityFieldValue("order_index", "help", __('when shown, the fields of an entry are ordered by the order in their table (0 to n). you can change the order index for this field here.'));
					}
					//	table for comments
					else if ($page_name == "_sys_comments") {
						$entity["name"] = "_sys_comments";

						$entity["show_labels"] = "0";											
						$entity["hidden_fields"] = "pagename,pageid,is_spam";
						$entity["hidden_form_fields"] = "pagename,pageid,insert_date,insert_time,is_spam";
						
						$entity["order_by"] = "insert_date";
						$entity["order_order"] = "ASC";
							
						//dateField
						$dateField = array("name"=>"insert_date",
										 "show"=>"1");
						$entity["dateField"] = $dateField;
						$timeField = array("name"=>"insert_time",
										 "show"=>"1",);
						$entity["timeField"] = $timeField;
						$entity["title_field"] = "comment";
						
						$group = array("field"=>"pagename",
										   "order"=>"DESC");
						$entity["group"] = $group;
							
						$entity = addFields($entity["name"]);
						
						setEntityFieldValue("insert_date", "label", __("Date"));
						setEntityFieldValue("insert_time", "label", __("Time"));
						setEntityFieldValue("name", "label", __("Name"));
						setEntityFieldValue("www", "label", __("Homepage"));
						setEntityFieldValue("email", "label", __("eMail"));
						setEntityFieldValue("comment", "label", __("Comment"));
						
						//setEntityFieldValue("email", "validation", "email");
						//to strict - people can write what they want here...
						//setEntityFieldValue("www", "validation", "url");
						setEntityFieldValue("name", "validation", "any_text");
					}
					//single pages
					else if (isSinglepage($page_name)) {
						//echo("$page_name is a singlepage!!");
						$entity["name"] = '_sys_sections';

						$entity["show_labels"] = "no";
						$entity["title_field"] = 'heading';
						$entity["publish_field"] = 'publish';
						$entity["order_by"] = 'order_index';
						$entity["hidden_fields"] = "in_submenu,pagename,order_index,publish,the_group,edited_date,input_date,input_time";
						$entity["hidden_form_fields"] = ",pagename,input_date,input_time,edited_date";
						
						//dateField + timeField
						$entity["dateField"] = array("name"=>"input_date",
										 "editlabel"=>"edited_date");
						$entity["timeField"] = array("name"=>"input_time");
							
						$entity["search"] = array("keyword" => "1");
						$entity = addFields($entity["name"]);
						$page_info = getPageInfo($page_name);
						//now we populate the value list for group with what 
						//might have been typed into the singlepage form - 
						//"standard" is the standard group, not in the submenu and always visible
						setEntityFieldValue("the_group", "valuelist", "standard,".stripCSVList($page_info["grouplist"]));
						//if we have groups, this overwrites the anchor behavior!
						if (trim($page_info["grouplist"]) == "") {
							$entity["hidden_form_fields"] = $entity["hidden_form_fields"].",the_group";
						}else {
							$group = array("field"=>"the_group",
										   "order"=>"DESC");
							$entity["group"] = $group;
							$entity["hidden_form_fields"] = $entity["hidden_form_fields"].",in_submenu";
						}
						//help
						setEntityFieldValue("grouplist", "help", __('the group of this entry (you will find the groups in the specifications for this page). The standard group contains entries that are always shown.'));
					}
					//this is needed when we actually show a multipage
					else if (isMultipage($page_name)) {
						//echo("$page_name is a multipage!!");
						$page_info = getPageInfo($page_name);

						if ($page_info != "") {		//else this makes no sense
							$entity["name"] = $page_info["tablename"];
							$entity["step"] = $page_info["step"];
							$entity["show_labels"] = $page_info["show_labels"];
							$entity["order_by"] = $page_info["order_by"];
							$entity["order_order"] = $page_info["order_order"];
							$entity["feed"] = $page_info["feed"];


							//search array, only if there is something to search
							if(! ($page_info["search_range"] == "0" and $page_info["search_month"] == "0"
									and $page_info["search_year"] == "0" and $page_info["search_keyword"] == "0")) {
								$search = array("range"=>$page_info["search_range"],
												 "month"=>$page_info["search_month"],
												 "year"=>$page_info["search_year"],
												 "keyword"=>$page_info["search_keyword"]);
								$entity["search"] = $search;
							}

							//from now on: field related informations
							

							$entity["title_field"] = $page_info["title_field"];
							$entity["publish_field"] = $page_info["publish_field"];

							//group array, only if there is something to group
							if($page_info["group_field"] != "") {
								$group = array("field"=>$page_info["group_field"],
											 "order"=>$page_info["group_order"]);
								$entity["group"] = $group;
							}

							//dateField - only if there is one specified
							if($page_info["date_field"] != "") {
								$dateField = array("name"=>$page_info["date_field"],
												 "editlabel"=>$page_info["edited_field"]);
								$entity["dateField"] = $dateField;
							}

							$entity["title_field"] = $page_info["title_field"];
							//important: we need something here (for the admin list) - so we take the first field...
							if ($entity["title_field"] == "") $entity["title_field"] = $entity["fields"][0]["name"];
							
							//hide those from input
							$entity["hidden_form_fields"] = $entity["hidden_form_fields"].','.$entity["timeField"]["name"];
							$entity["hidden_form_fields"] = $entity["hidden_form_fields"].','.$entity["dateField"]["name"];
							$entity["hidden_form_fields"] = $entity["hidden_form_fields"].','.$entity["dateField"]["editlabel"];
							//$entity["hidden_fields"] = $entity["hidden_fields"].','.$entity["dateField"]["editlabel"];
							$entity["hidden_fields"] = $entity["hidden_fields"].','.$page_info["publish_field"].','.$page_info["edited_field"];
							$e = array();
							$e[0] = 'hidden_fields';
							$e[1] = $entity["hidden_fields"];
							$entity['fillafromb'][0] = $e;
							
							if($page_info["tablename"] != "") {
								$entity = addFields($page_info["tablename"]);
							}
						}
					}
					else if (in_array($page_name, getTables())) {
						$entity = addFields($page_name);
					}
					$old_entities[count($old_entities)] = $entity;
				}
			}
			return $entity;
		}

		/*gives you an alreay built entity if it is stored in $old_entites (it should)*/
		function getAlreadyBuiltEntity($page_name) {
			global $old_entites;
			for($x=0;$x<count($old_entites);$x++) {
				//doesn't work on some servers - for now its turned off
				//if ($old_entites[$x]["name"] == $page_name) return $old_entites[$x];
			}
			return "";
		}

		/* resetting all lazy instantiated stuff - so we get a fresh start
			use this when you upddated the db and you might write more stuff */
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
			global $sys_infos;
			$sys_infos = "";
		}

		/* gets an array with field metadata from the db, replaces (!) the field array
			in the actual entity and returns it 
			params:
			name: the table name
			not_for_field_list: this space-separated list contains names of fields that should not
								be added - maybe because they are mentioned somewhere else already 
		*/
		function addFields($name, $not_for_field_list = "") {
				$fields = array();
				$entity = getEntity("");	//getting the actual entity
				$page_info = getPageInfo("");
				//echo("addFields for ".$name);

				$link = getDBLink();

				//first, we see what we find in the database's metadata

				$field_list = mysql_list_fields(getDBName(), $name, $link);
				for($i=0; $i<mysql_num_fields($field_list); $i++) {
					//pk
					if (eregi('primary_key',mysql_field_flags($field_list, $i))) {
						$entity["pk"] = mysql_field_name($field_list,$i);
						$entity["pk_type"] = mysql_field_type($field_list,$i);
					}
								
					$db_field = mysql_fetch_field($field_list, $i);
					if (!eregi(mysql_field_name($field_list,$i),$not_for_field_list) and !eregi('primary_key',mysql_field_flags($field_list, $i))) {
						$field = array("name"=>$db_field->name,
								"data-type"=>$db_field->type,
								"size"=>mysql_field_len($field_list,$i),
								"order_index"=>''.$i,
								"default"=>$db_field->def);
						//echo("found field with name ".$field["name"].", default : ".$field["default"].", with size ".$field["size"]." and type ".$field["data-type"]."<br/>\n");
						//IMPORTANT: In MySQL we code int(1) as a boolean !!!
						if (($field["data-type"] == "int" or $field["data-type"] == "tinyint")and $field["size"] == 1) $field["data-type"] = "bool";
						$fields[count($fields)] = $field;
					}

				}

				//now we enrich with data from the _sys_fields table

				$query = "SELECT * FROM _sys_fields WHERE pagename = '".$page_info["name"]."'";
				$res = mysql_query($query, $link);
				
				while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
					for($i=0; $i<count($fields); $i++) {
						
						if ($fields[$i]["name"] == $row["name"]) {
							$fields[$i]["label"] = $row["label"];
							$fields[$i]["validation"] = $row["validation"];
							$fields[$i]["valuelist"] = stripCSVList($row["valuelist"]);
							$fields[$i]["not_brief"] = $row["not_brief"];
							$fields[$i]["order_index"] = $row["order_index"];
						}
						if($fields[$i]["data_type"] == "int" and $fields[$i]["size"] != 1) $fields[$i]["validation"] = 'number';
					}
				}
				$entity["fields"] = $fields;
				return $entity;
		}

		/*	sets the value of a field in the current entity (if there is one)
		*/
		function setEntityFieldValue($f_name, $attr_name, $attr_value) {
			global $entity;
			if ($entity != "") {
				for($i=0; $i < count($entity["fields"]); $i++) {
					$f = $entity["fields"][$i];
					if ($f["name"] == $f_name) {
						$f[$attr_name] = $attr_value;
						$entity["fields"][$i] = $f;
					}
				}
			}
		}

		/*gets an array with field data*/
		function getEntityField($fname) {
			global $entity;
			if($entity["fields"] != "") foreach ($entity["fields"] as $f) if ($f["name"] == $fname) return $f;
			return "";
		}

		/*gets an array with names of fields of the named entity*/
		function getListOfFields($entity_name) {
			if ($entity_name != "") {
				global $entity;
				$actual_entity = $entity;	//save it
				$entity = getEntity($entity_name);
			} else $entity = getEntity("");
			$fields = array();
			for($i=0; $i<count($entity["fields"]); $i++) {
				$fields[$i] = $entity["fields"][$i]["name"];
			}
			if ($entity_name != "") $entity = $actual_entity;	//set $entity back to what it was!
			return $fields;
		}

		/* gets an array with names of fields of the actual 
		 * entity with the named data types (a comma separated list)*/
		function getListOfFieldsByDataType($entity_name, $data_types) {
			$types = explode(',', $data_types);
			if ($entity_name != "") {
				global $entity;
				$actual_entity = $entity;	//save it
				$entity = getEntity($entity_name);
			} else $entity = getEntity("");
			$dfields = array();
			for($k=0; $k<count($entity["fields"]); $k++) {
				for($i=0; $i<count($entity["fields"]); $i++) {
					if ($entity["fields"][$i]["data-type"] == $types[$k]) {
						$dfields[count($dfields)] = $entity["fields"][$i]["name"];
					}
				}
			}
			if ($entity_name != "") $entity = $actual_entity;	//set $entity back to what it was!
			return $dfields;
		}

		/*gets an array with names of date fields of the named entity*/
		function getListOfValueListFields($entity_name) {
			if ($entity_name != "") {
				global $entity;
				$actual_entity = $entity;	//save it
				$entity = getEntity($entity_name);
			} else $entity = getEntity("");
			$dfields = array();
			for($i=0; $i<count($entity["fields"]); $i++) {

				if ($entity["fields"][$i]["valuelist"] != '') {
					$dfields[count($dfields)] = $entity["fields"][$i]["name"];
				}
			}
			if ($entity_name != "") $entity = $actual_entity;	//set $entity back to what it was!
			return $dfields;
		}

		/*
		 * guesses what field might be containing the
		 * interesting text in the named entity
		 * (used for RSS)
		 */
		function guessTextField($entity_name) {
			$the_field = "";
			if ($entity_name != "") {
				global $entity;
				$actual_entity = $entity;	//save it
				$entity = getEntity($entity_name);
			} else $entity = getEntity("");
			//first blob field ?
			for($i=0; $i<count($entity["fields"]); $i++) {
				if (isTextAreaType($entity["fields"][$i]["data-type"])) {
					$the_field = $entity["fields"][$i]["name"];
					break;
				}
			}
			//else: first text field ?
			if ($the_field == "")
				for($i=0; $i<count($entity["fields"]); $i++) {
					if (isTextType($entity["fields"][$i]["data_type"])) {
						$the_field = $entity["fields"]["name"];
						break;
					}
				}
				
			if ($entity_name != "") $entity = $actual_entity;	//set $entity back to what it was!
			
			return $the_field;
		}
		
		/*
		 * gets the first words from an HTML string
		 */
		function getFirstWords($html_str, $number){
			$result = "";
			$text = strip_tags($html_str);
			$text_arr = explode(' ', $text);
			//print_r($text_arr);
			$m_number = min($number, count($text_arr));
			for($i=0; $i<$m_number; $i++) {
				if ($text_arr[$i] != "") $result = $result.' '.$text_arr[$i];
			}
			if ($m_number < count($text_arr)) $result = $result.' (...)';
			return preg_replace('/\s\s+/', ' ', $result);	//strip all whitespace into ' '
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
				return __('please specify any text here.');
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
