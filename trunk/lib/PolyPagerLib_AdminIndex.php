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

	/*
	Function Index:
	* chmod_dirs()
	* create_sys_Tables()
	* executeTemplate($template_name, $page_name)
	* ensureConsistency()
	* admin_list()
	*/
	if ( !defined('FILE_SEPARATOR') ) {
		define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
	}
		
	/* creates chmodding on selected dirs
		returns a string with dirs that did not work*/
	function chmod_dirs() {
		$failed = "";
		$worked = chmod($_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot()."user/", 0777);
		if (!$worked) $failed += " | user/";
		/*$worked = chmod($_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot()."user/File/", 0777);
		if (!$worked) $failed += " | user/files/"; 
		$worked = chmod(realpath($_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot()."user/Image/"), 0777);
		if (!$worked) $failed += " | user/pics";  */
		$worked = chmod($_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot()."user/Image/_mg/thumbs", 0777);
		if (!$worked) $failed += " | user/Image/_mg/thumbs";
		return $failed;
	}
	
	/* creates the Systable(s) PolyPager needs to work
		returns a string containing SQL errors
		@param the MySQL link*/
	function create_sys_Tables() {
		global $debug;
		$link = getDBLink();
		$query = "CREATE TABLE IF NOT EXISTS `_sys_sys` (
					  `title` varchar(255) NOT NULL default '',
					  `author` varchar(120) NOT NULL default '',
					  `keywords` varchar(255) NOT NULL default '',
					  `admin_name` varchar(120) NOT NULL default '',
					  `admin_pass` varchar(120) NOT NULL default '',
					  `feed_amount` tinyint(4) NOT NULL default '7',
					  `start_page` varchar(120) NOT NULL default '',
					  `lang` varchar(12) NOT NULL default '',
					  `skin` varchar(120) NOT NULL default '',
					  `template` varchar(200) NOT NULL default 'default.php',
					  `submenus_always_on` tinyint(1) NOT NULL default 0,
					  `link_to_gallery_in_menu` tinyint(1) NOT NULL default '0'
					) TYPE=MyISAM ;";
		$res = mysql_query($query, $link);
		$fehler_nr = mysql_errno($link);
		$fehler_text = mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		
		$query = "CREATE TABLE IF NOT EXISTS `_sys_sections` (
					  `id` bigint(20) NOT NULL auto_increment,
					  `input_date` datetime NOT NULL,
					  `edited_date` datetime default NULL,
					  `pagename` varchar(50) NOT NULL default '',
					  `heading` varchar(200) NOT NULL default '',
					  `bla` blob NOT NULL,
					  `publish` tinyint(1) NOT NULL default '1',
					  `in_submenu` tinyint(1) NOT NULL default '0',
					  `order_index` int(11) NOT NULL default '0',
					  `the_group` varchar(120) NOT NULL default '',
					  PRIMARY KEY  (`id`),
					  KEY `page` (`pagename`,`in_submenu`),
					  KEY `publish` (`publish`),
					  KEY `the_group` (`the_group`),
					  KEY `input_date` (`input_date`)
					) TYPE=MyISAM AUTO_INCREMENT=1 ;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		
		$query = "CREATE TABLE IF NOT EXISTS `_sys_intros` (
					  `tablename` varchar(80) NOT NULL default '',
					  `intro` blob NOT NULL,
					  PRIMARY KEY  (`tablename`)
					) TYPE=MyISAM ;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		
		$query = "CREATE TABLE `_sys_feed` (
					  `pk` int(11) NOT NULL auto_increment,
					  `edited_date` datetime NOT NULL,
					  `title` varchar(255) NOT NULL,
					  `pagename` varchar(120) NOT NULL,
					  `id` int(11) NOT NULL,
					  PRIMARY KEY  (`pk`),
					  KEY `edited_date` (`edited_date`)
					) TYPE=MyISAM  AUTO_INCREMENT=1 ;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }

		$query = "CREATE TABLE IF NOT EXISTS `_sys_comments` (
					  `id` int(11) NOT NULL auto_increment,
					  `pagename` varchar(120) NOT NULL default '',
					  `pageid` int(11) NOT NULL default '0',
					  `insert_date` datetime NOT NULL default '0000-00-00 00:00:00',
					  `name` varchar(120) NOT NULL default '',
					  `email` varchar(120) NOT NULL default '',
					  `www` varchar(120) NOT NULL default '',
					  `comment` blob NOT NULL,
					  `is_spam` tinyint(1) NOT NULL default '0',
					  PRIMARY KEY  (`id`),
					  KEY `pagename` (`pagename`,`pageid`),
					  KEY `is_spam` (`is_spam`)
					)  TYPE=MyISAM AUTO_INCREMENT=1 ; ";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		
		
		$query = "CREATE TABLE IF NOT EXISTS `_sys_singlepages` (
					  `id` tinyint(4) NOT NULL auto_increment,
					  `name` varchar(120)  NOT NULL default '',
					  `in_menue` tinyint(1) NOT NULL default '0',
					  `menue_index` mediumint(9) NOT NULL default '1',
					  `commentable` tinyint(1) NOT NULL default '0',
					  `hide_options` tinyint(1) NOT NULL default '1',
					  `hide_search` tinyint(1) NOT NULL default '0',
					  `hide_toc` tinyint(1) NOT NULL default '0',
					  `default_group` varchar(60) NOT NULL default '',
					  `grouplist` varchar(255) NOT NULL default '',
					  PRIMARY KEY  (`id`)
					) TYPE=MyISAM AUTO_INCREMENT=1 ;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }

		$query = "CREATE TABLE IF NOT EXISTS `_sys_multipages` (
					  `id` tinyint(4) NOT NULL auto_increment,
					  `name` varchar(60) NOT NULL default '',
					  `tablename` varchar(60) NOT NULL default '',
					  `in_menue` tinyint(1) NOT NULL default '1',
					  `menue_index` mediumint(9) NOT NULL default '0',
					  `hide_options` tinyint(1) NOT NULL default '0',
					  `hide_search` tinyint(1) NOT NULL default '0', .
					  `hide_toc` tinyint(1) NOT NULL default '0',	
					  `show_labels` tinyint(1) NOT NULL default '0',
					  `hidden_fields` varchar(255) NOT NULL default '',
					  `order_by` varchar(60) NOT NULL default '',
					  `order_order` varchar(12) NOT NULL default '',
					  `publish_field` varchar(60) NOT NULL default '',
					  `group_field` varchar(60) NOT NULL default '',
					  `group_order` varchar(12) NOT NULL default '',
					  `date_field` varchar(60) NOT NULL default '',
					  `edited_field` varchar(60) NOT NULL default '',
					  `title_field` varchar(60) NOT NULL default '',
					  `step` varchar(12) NOT NULL default 'all',
					  `commentable` tinyint(1) NOT NULL default '0',
					  `show_comments` tinyint(1) NOT NULL default '1',
					  `search_month` tinyint(1) NOT NULL default '0',
					  `search_year` tinyint(1) NOT NULL default '0',
					  `search_keyword` tinyint(1) NOT NULL default '1',
					  `search_range` tinyint(1) NOT NULL default '0',
					  PRIMARY KEY  (`id`),
					  KEY `name` (`name`,`tablename`,`group_field`)
					) TYPE=MyISAM AUTO_INCREMENT=1 ;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }

		$query = "CREATE TABLE IF NOT EXISTS `_sys_fields` (
					  `id` tinyint(4) NOT NULL auto_increment,
					  `name` varchar(60) NOT NULL default '',
					  `pagename` varchar(60) NOT NULL default '',
					  `valuelist` varchar(255) NOT NULL default '',
					  `validation` varchar(60) NOT NULL default '',
					  `not_brief` tinyint(1) NOT NULL default '0',
					  PRIMARY KEY  (`id`)
					) TYPE=MyISAM AUTO_INCREMENT=1 ;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }


		if ($fehler_nr != 0) {
			return $fehler_text;
		} else {
			return "";
		}
	}
	
	/**
	* this function will execute SQL-code to insert another page.
	* Each template requires different actions, sometimes there may also
	* entries be made.
	* @template_name the name of the template to be executed
	* @page_name of course the user can choose what name the page should have
	* @return error messages that occured, an empty string otherwise
	*/
	function executeTemplate($template_name, $page_name) {
		global $debug;
		$link = getDBLink();
		if ($page_name == "") return __('you should provide a page name!');
		if ($template_name == "") return __('there is no template provided!');
		else if($template_name == "guestbook") {
			$query = "INSERT INTO `_sys_singlepages` (`name`, `in_menue`, `menue_index`, 
				`commentable`, `hide_options`, `hide_search`, `hide_toc`, `grouplist`) 
				VALUES ('".$page_name."', 1, 1, 1, 1, 1, 1, '');";
			$res = mysql_query($query, $link);
			$fehler_nr = $fehler_nr.mysql_errno($link);
			$fehler_text = $fehler_text.mysql_error($link);
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
			//one entry to allow comments on
			$query = "INSERT INTO `_sys_sections` (`input_date`, `edited_date`, 
			`pagename`, `heading`, `bla`, `publish`, `in_submenu`, 
			`order_index`, `the_group`) 
			VALUES ('".buildDateString(getdate())." ".buildTimeString(localtime(time() , 1))."', '".buildDateString(getdate())." ".buildTimeString(localtime())."', 
			'".$page_name."', '".$page_name."', 'this is the entry that gets commented. Write your own greeting formula here, but do not delete it.', 1, 0, 0, '');";
			$res = mysql_query($query, $link);
			$fehler_nr = $fehler_nr.mysql_errno($link);
			$fehler_text = $fehler_text.mysql_error($link);
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
		}
		else if($template_name == "blog") {
			//first the actual table
			$query = "CREATE TABLE `".$page_name."_table` (
						  `id` bigint(20) NOT NULL auto_increment,
						  `title` varchar(160) NOT NULL default '',
						  `bla` blob NOT NULL,
						  `inputdate` datetime NOT NULL default '0000-00-00 00:00:00',
						  `lastedited` date default NULL,
						  `publish` tinyint(1) NOT NULL default '1',
						  PRIMARY KEY  (`id`),
						  KEY `publish` (`publish`)
						) TYPE=MyISAM;";
			$res = mysql_query($query, $link);
			$fehler_nr = $fehler_nr.mysql_errno($link);
			$fehler_text = $fehler_text.mysql_error($link);
			//now page data (if we created our table as planned)
			if($fehler_text == "") {
				$query = "INSERT INTO `_sys_multipages` (`name`, `tablename`, `in_menue`, `menue_index`, `hide_options`, `hide_search`, `hide_toc`, `show_labels`, `hidden_fields`, `order_by`, `order_order`, `publish_field`, `group_field`, `group_order`, `date_field`, `edited_field`, `title_field`, `step`, `commentable`, `search_month`, `search_year`, `search_keyword`, `search_range`) 
					VALUES ('".$page_name."', '".$page_name."_table', 1, 0, 1, 1, 1, 0, '', 'inputdate', 'DESC', 'publish', '', 'ASC', 'inputdate', 'lastedited', 'title', '7', 1, 1, 0, 1, 0);";
				$res = mysql_query($query, $link);
				$fehler_nr = $fehler_nr.mysql_errno($link);
				$fehler_text = $fehler_text.mysql_error($link);
			}
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
		}
		else if($template_name == "faq") {
			//first the actual table
			$query = "CREATE TABLE `".$page_name."_table` (
						  `id` int(12) NOT NULL auto_increment,
						  `inputdate` datetime NOT NULL default '0000-00-00 00:00:00',
						  `topic` varchar(200) NOT NULL default '',
						  `question` varchar(255) NOT NULL default '',
						  `answer` blob NOT NULL,
						  PRIMARY KEY  (`id`),
						  KEY `topic` (`topic`),
						  KEY `inputdate` (`inputdate`)
						) TYPE=MyISAM ;";
			$res = mysql_query($query, $link);
			$fehler_nr = $fehler_nr.mysql_errno($link);
			$fehler_text = $fehler_text.mysql_error($link);
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
			
			//now page data (if we created our table as planned)
			if($fehler_text == "") {
				$query = "INSERT INTO `_sys_multipages` (`name`, `tablename`, `in_menue`, `menue_index`, `hide_options`, `hide_search`, `hide_toc`, `show_labels`, `hidden_fields`, `order_by`, `order_order`, `publish_field`, `group_field`, `group_order`, `date_field`, `edited_field`, `title_field`, `step`, `commentable`, `search_month`, `search_year`, `search_keyword`, `search_range`) 
				VALUES ('".$page_name."', '".$page_name."_table', 1, 0, 1, 1, 0, 0, '', 'inputdate', 'ASC', '', '', 'ASC', 'inputdate', '', 'question', 'all', 0, 0, 0, 1, 0);";
				$res = mysql_query($query, $link);
				$fehler_nr = $fehler_nr.mysql_errno($link);
				$fehler_text = $fehler_text.mysql_error($link);
			}
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
		}
		
		//return error messages
		if ($fehler_nr != 0) {
			return $fehler_text;
		} else {
			return "";
		}
	}
	
	/* this function ensures that changes on pages affect other places
		on the database that are relevant.
		Therefore, some fields can be marked as important for consistency
		in the entity. Then the HTML Form will store the original value and
		send it along in a hidden field - we find it in $params["values"] like
		all the other values, too.*/
	function ensureConsistency() {
		global $params;
		global $debug;
		if (($params["cmd"] == "edit" or $params["cmd"] == "delete")
		and $params["values"]["old_formfield_name"] != "") {
			if ($params["page"] == '_sys_singlepages'){
					//update sections
					if ($params["cmd"] == "edit") {
						$query = "UPDATE _sys_sections SET pagename = '".$params["values"]["name"]."'".
							" WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					} else {
						$query = "DELETE FROM _sys_sections WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					}
					if ($debug) { echo('<div class="debug">Consistency Query is: '.$query.'</div>'); }
					$res = mysql_query($query, getDBLink());
					$fehler_nr = $fehler_nr.mysql_errno(getDBLink());
					$fehler_text = $fehler_text.mysql_error(getDBLink());
					
					if ($fehler_nr != 0) { echo('<div class="sys_msg">I could not update _sys_sections...</div>'); }
			}
			if ($params["page"] == '_sys_singlepages' or $params["page"] == '_sys_multipages') {
					//update comments
					if ($params["cmd"] == "edit") {
						$query = "UPDATE _sys_comments SET pagename = '".$params["values"]["name"]."'".
							" WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					} else {
						$query = "DELETE FROM _sys_comments WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					}
					$res = mysql_query($query, getDBLink());
					$fehler_nr = $fehler_nr.mysql_errno(getDBLink());
					$fehler_text = $fehler_text.mysql_error(getDBLink());
					if ($debug) { echo('<div class="debug">Consistency Query is: '.$query.'</div>'); }
					if ($fehler_nr != 0) { echo('<div class="sys_msg">I could not update _sys_comments...</div>'); }

			}
			if ($params["page"] == '_sys_singlepages' or $params["page"] == '_sys_multipages') {
					//update feed list
					if ($params["cmd"] == "delete") {
						$query = "DELETE FROM _sys_feed WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					}
					if ($params["cmd"] == "edit") {
						$query = "UPDATE _sys_feed SET pagename = '".$params["values"]["name"]."'".
							" WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					}
					$res = mysql_query($query, getDBLink());
					$fehler_nr = $fehler_nr.mysql_errno(getDBLink());
					$fehler_text = $fehler_text.mysql_error(getDBLink());
					if ($debug) { echo('<div class="debug">Consistency Query is: '.$query.'</div>'); }
					if ($fehler_nr != 0) { echo('<div class="sys_msg">I could not update _sys_feed...</div>'); }
			}
		}
	}
	
	/* writes an admin data list */
	function admin_list($ind=3) {
		$indent = translateIndent($ind);
		$nind = $ind+1;
		global $params;
		global $debug;
		include_once("PolyPagerLib_Showing.php");
		
		$link = getDBLink();
		$topic = $params["topic"];
		
		
		echo($indent.'<form action="." name="choiceForm" id="choiceForm" method="post">'."\n");
		//option list
		if ($topic == 'content' or $topic == 'fields') {
			if ($topic == 'content') {
				$comment_help = __('view the comments list.');
				$feed_help = __('view the feed list. delete here what you do not want in the feed (latest entries) list.');
				echo($indent.'		<div class="sys_msg">'."\n");
				echo($indent.'			<a onmouseover="popup(\''.$comment_help.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="?_sys_comments">'.__('comments').'</a>&nbsp;|&nbsp;'."\n");
				echo($indent.'			<a onmouseover="popup(\''.$feed_help.'\')" onmouseout="kill()" title="" onfocus="this.blur()"  href="?_sys_feed">'.__('feeds').'</a>'."\n");
				echo($indent.'		</div>'."\n");
				echo($indent.'		'.__('page:').'<select name="page" onchange="document.choiceForm.submit();">'."\n");
				$page_selector = $params["page"];
			}else if ($topic == 'fields') {
				echo($indent.'		<select name="group" onchange="document.choiceForm.submit();">'."\n");
				$page_selector = $params["group"];
			}
			echo($indent.'			<option value="">--'.__('select page').'--</option>'."\n");
			$pages = (($topic != 'content') ? getMultipages() : getPages());
			foreach ($pages as $p) {
				if ($page_selector == $p["name"]) $selected = "selected='selected'"; else $selected = "";
				echo($indent.'			<option '.$selected.' value="'.$p["name"].'">'.$p["name"].'</option>'."\n");
			}
			echo($indent.'		</select>'."\n");
		} else if ($topic == 'pages') {
			$link_text = __('a page template creates pages for you that fulfill some well-known function and is used often. So this might be useful for you. After you created the page, you can still edit its properties or delete it.');
			echo($indent.'		<a id="templates_link"  onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="javascript:toggleVisibility(\'template_msg\',\'templates_link\', \''.__('show templates').'\', \''.__('hide templates').'\');">'.__('show templates').'</a>&nbsp;|&nbsp;'."\n");
			echo($indent.'		<span id="template_msg"  style="display:none;" class="sys_msg">'."\n");
			echo($indent.'		'.__('new page named').' <input type="text" name="page_name" maxlength="30"="60" size="20"/> '."\n");
			echo($indent.'		'.__('from template:')."\n");			
			echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()"><img src="../style/pics/help.gif"/></a>'."\n");
			echo($indent.'		<select name="template_name" >'."\n");
			echo($indent.'			<option value="guestbook">'.__('a simple guestbook').'</option>'."\n");
			echo($indent.'			<option value="faq">'.__('an FAQ (Frequently asked questions)').'</option>'."\n");
			echo($indent.'			<option value="blog">'.__('a Weblog (often called Blog)').'</option>'."\n");
			echo($indent.'		</select>'."\n");
			echo($indent.'		<input type="submit" name="dummy" value="'.__('Go!').'"/><input type="hidden" name="page" value="_sys_pages"/>'."\n");
			echo($indent.'		</span>'."\n");
		}
		if ($topic == 'fields') {
			echo($indent.'		<input type="hidden" name="page" value="_sys_fields"/>'."\n");
		}
		echo($indent.'		<input type="hidden" name="cmd" value="show"/>'."\n");
		echo($indent.'		<input type="hidden" name="topic" value="'.$topic.'"/>'."\n");
		
		$entity = getEntity($params["page"], $link);
		//Links, first for contents
		if ($params["page"] != "" and $params["topic"] == "content") {
			$link_text = __('Here you can edit the intro text of this page (this is the text that appears next to all the entries).');
			//if (isMultipage($params["page"])) 
				echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="edit.php?_sys_intros&nr='.$params["page"].'&page='.$params["page"].'&from=list&topic='.$topic.'">'.__('edit intro').'</a>&nbsp;|&nbsp;'."\n");
			$link_text = __('Here you can insert a new entry for this page.');
			echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="edit.php?'.$params["page"].'&cmd=new&from=list&topic='.$topic.'">'.__('new entry').'</a>'."\n");
		//now for fields
		} else if ($params["page"] == "_sys_fields") {
			$link_text = __('Here you can make statements about another field.');
			$the_href = 'edit.php?_sys_fields&cmd=new&from=list&group='.$params["group"].'&topic='.$topic;
			echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$the_href.'">'.__('new entry').'</a>'."\n");
		//now for pages
		} else if (strpos($params["page"], "pages")) {
			$link_text = __('Here you can insert a new simple page (internally also called singlepage). Its entries will simply have a heading and a content, that is all. PolyPager will store it in a special table and you will not need to put much thought in how the page behaves.');
			$the_href = 'edit.php?_sys_singlepages&cmd=new&from=list&topic='.$topic;
			echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$the_href.'">'.__('new simple page').'</a>&nbsp;|&nbsp;'."\n");
			
			$link_text = __('Here you can insert a new complex page (internally also called multipage). The difference to simple pages is that you can use these ones for tables in the database that you have made (and that have any structure). You will have a lot of options to change the behavior of this page.');
			//link is only active when there are tables for multipages
			if (count(getTables()) > 0) {
				$the_href = 'edit.php?_sys_multipages&cmd=new&from=list&topic='.$topic;
			} else {
				$link_text = $link_text.'  '.__('this link is only active when there are tables in the database that multipages would operate on (that means all tables but system tables -they all start with [_sys_] -).');
				$the_href = '';
				$the_style = 'text-decoration:none;color:black;';
			}
			echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$the_href.'" style="'.$the_style.'">'.__('new complex page').'</a>'."\n");
		}
		
		echo($indent.'</form>'."\n");
		
		if ($params["page"] != "") {
			writeSearchForm(false, $nind);
			//build Query
			//this helps getQuery know what we want
			if(isASysPage($params["page"]))	$params["cmd"] = $params["cmd"]." ".$params["page"].'_all';
			$params["nr"] = "";	//we want no special entry, but all
			
			$query = getQuery($params, false);
			
			// send query to DBMS now
			$res = mysql_query($query, $link);
			$error_nr = mysql_errno($link);
			if ($error_nr != 0) {
				$fehler_text = mysql_error($link);
				echo($indent.'	<span class="sys_msg">'.__('DB-Error:').' '.$fehler_text.'</span>'."\n");
				writeFooter();
				
			} else {
				
				if (isMultipage($params["page"]) and $params["max"] == "") { //no other way... db is empty
					echo($indent.'	<ul id="menu">'."\n");
					echo($indent.'		<div class="sys_msg">'.__('There is no entry in the database yet...').'</div>'."\n");
					echo($indent.'		<div class="admin_link"><a onmouseover="popup(\''.__('for admins: make a new entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="admin/edit.php?'.$params["page"].'&amp;cmd=new">Enter the first one</a></div>'."\n");
					echo($indent.'	</ul><div class="menuend"></div>'."\n");
				} else {
				
					//you could type in a too high number - senseless
					if ($params["nr"] > $params["max"]) { $params["nr"] = $params["max"]; echo('<div class="sys_msg">'.__('the chosen number was too high - showing newest').'</div>');};
					//-------------------------- end  ---------------------------------
					
					
					//------------------------ getting and showing data   --------------
					echo($indent.'	<div class="show">'."\n");
					writeEntries($res,true, $nind, false);
					echo($indent.'	</div>'."\n");  //end of class "show"
					//--------------------- end getting and showing data  --------------
			
				}					//if max is not 0
			} 					//if no db error
		}					//if page is valid
	}
?>
