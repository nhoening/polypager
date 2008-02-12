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

	/*
	Function Index:
	* showAdminOptions()
    * getMySQLCharsetter()
	* create_sys_Tables()
	* executeTemplate($template_name, $page_name)
	* ensureConsistency()
	* admin_list()
    * execBatchCmds()
	*/
	if ( !defined('FILE_SEPARATOR') ) {
		define('FILE_SEPARATOR', ( utf8_substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
	}
	
	/* --------------- show all options  ------------ */
	function showAdminOptions($indent){
        $the_url = "..";
    
		echo($indent.'<div id="admin_options">'.__('Let\'s talk about...').'&nbsp;'."\n");
        $linkText = __('By clicking on this link, you can see (and search for) entries of the page you select. Note that this is the only place you will actually see not published entries/sections.');
		echo($indent.'	<a href=".?page=&amp;topic=content&amp;from=admin" onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" title="" onfocus="this.blur()">'.__('content').'</a>&nbsp;|&nbsp;'."\n");
		echo($indent.'	<a href="edit.php?_sys_sys&amp;from=admin">'.__('the system').'</a>&nbsp;|&nbsp;'."\n");
		$linkText = __('By clicking on this link, you will see a file browser where you can upload files and create folders to store what you need. There are directories for different types of files (File, Image, Media, Flash).');
		echo($indent.'	<a onclick="openWindow(this.href, \'File Manager\', 800, 500, 100, 100, \'yes\'); return false" href="'.$the_url.'/plugins/webadmin.php"  onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" title="" onfocus="this.blur()">'.__('files').'</a>&nbsp;|&nbsp;'."\n");
		$linkText = __('By clicking on this link, you can see what pages you have and maybe enter new ones or delete some.');
		echo($indent.'	<a href=".?page=_sys_pages&amp;topic=pages&amp;from=admin" onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" title="" onfocus="this.blur()">'.__('pages').'</a>'."\n");
		echo($indent.'</div>'."\n");
	}
	
    function getMySQLCharsetter() {
        $client_api = utf8_explode('.', mysql_get_server_info()); 
		if ($client_api[0] >= 5) return " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        else return "";
    }
    
	/* creates the Systable(s) PolyPager needs to work
		returns a string containing SQL errors
		@param the MySQL link*/
	function create_sys_Tables() {
		global $debug;
        
		$link = getDBLink();
        $charsetter = getMySQLCharsetter();
        
        if ($debug) echo("create_sys_Tables on api: ".$client_api[0]);
        
		$query = "CREATE TABLE `_sys_sys` (
                      `title` varchar(255) NOT NULL default '',
                      `author` varchar(120) NOT NULL default '',
                      `keywords` varchar(255) NOT NULL default '',
                      `admin_name` varchar(120) NOT NULL default '',
                      `admin_pass` varchar(120) NOT NULL default '',
                      `feed_amount` tinyint(4) NOT NULL default '7',
                      `full_feed` tinyint(1) NOT NULL default '1',
                      `start_page` varchar(120) NOT NULL default '',
                      `lang` varchar(12) NOT NULL default '',
                      `skin` varchar(120) NOT NULL default '',
                      `submenus_always_on` tinyint(1) NOT NULL default '0',
                      `hide_public_popups` tinyint(1) NOT NULL default '0',
                      `whole_site_admin_access` tinyint(1) NOT NULL default '0',
                      `link_to_gallery_in_menu` tinyint(1) NOT NULL default '0',
                      `gallery_name` varchar(120) NOT NULL default 'gallery',
                      `gallery_index` smallint(6) NOT NULL default '99',
                      `use_captchas` tinyint(1) NOT NULL default '0',
                      `public_captcha_key` varchar(50) NOT NULL default '',
                      `private_captcha_key` varchar(50) NOT NULL default ''
                    ) ENGINE=MyISAM $charsetter;";
		$res = mysql_query($query, $link);
		$fehler_nr = mysql_errno($link);
		$fehler_text = mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		$query = 'SELECT * FROM _sys_sys';
        $res = pp_run_query($query);
        if(mysql_num_rows($res) == 0) { //fill in one row if its not there already for some reason
            $query = "INSERT INTO `_sys_sys` VALUES ('The title of your new page', '', 
                                '', '', '', 12, 0, '', 'en', 'polly', 0, 0, 0, 0, 'gallery', 99, '0','','') ;";
            $res = mysql_query($query, $link);
            $fehler_nr = mysql_errno($link);
            $fehler_text = mysql_error($link);
            if ($debug) { echo('<br/><span class="debug">Insert Sys_Sys is: '.$query.'<br /></span>'); }
        }
        
		$query = "CREATE TABLE `_sys_sections` (
                      `id` bigint(20) NOT NULL auto_increment,
                      `input_date` datetime NOT NULL,
                      `edited_date` datetime default NULL,
                      `pagename` varchar(50) NOT NULL default '',
                      `heading` varchar(200) NOT NULL default '',
                      `bla` text NOT NULL,
                      `publish` tinyint(1) NOT NULL default '1',
                      `in_submenu` tinyint(1) NOT NULL default '0',
                      `order_index` int(11) NOT NULL default '0',
                      `the_group` varchar(120) NOT NULL default 'standard',
                      PRIMARY KEY  (`id`),
                      KEY `page` (`pagename`,`in_submenu`),
                      KEY `publish` (`publish`),
                      KEY `the_group` (`the_group`),
                      KEY `input_date` (`input_date`)
                    ) ENGINE=MyISAM $charsetter;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		
		$query = "CREATE TABLE IF NOT EXISTS `_sys_intros` (
					  `tablename` varchar(80) NOT NULL default '',
					  `intro` text NOT NULL,
					  PRIMARY KEY  (`tablename`)
					) TYPE=MyISAM $charsetter;";
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
                      `public` tinyint(1) NOT NULL default '0',
					  PRIMARY KEY  (`pk`),
					  KEY `edited_date` (`edited_date`)
					) TYPE=MyISAM $charsetter ;";
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
					  `comment` text NOT NULL,
					  `is_spam` tinyint(1) NOT NULL default '0',
					  PRIMARY KEY  (`id`),
					  KEY `pagename` (`pagename`,`pageid`),
					  KEY `is_spam` (`is_spam`)
					)  TYPE=MyISAM $charsetter ; ";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }
		
		
		$query = "CREATE TABLE `_sys_singlepages` (
                      `id` int(11) NOT NULL auto_increment,
                      `name` varchar(120) NOT NULL default '',
                      `in_menue` tinyint(1) NOT NULL default '1',
                      `menue_index` mediumint(9) NOT NULL default '1',
                      `commentable` tinyint(1) NOT NULL default '0',
                      `only_admin_access` tinyint(1) NOT NULL default '0',
                      `hide_options` tinyint(1) NOT NULL default '1',
                      `hide_search` tinyint(1) NOT NULL default '1',
                      `hide_toc` tinyint(1) NOT NULL default '1',
                      `default_group` varchar(60) NOT NULL default '',
                      `grouplist` varchar(255) NOT NULL default '',
                      PRIMARY KEY  (`id`),
                      UNIQUE KEY `name` (`name`)
					) ENGINE=MyISAM $charsetter;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }

		$query = "CREATE TABLE IF NOT EXISTS `_sys_multipages` (
					  `id` int(11) NOT NULL auto_increment,
					  `name` varchar(60) NOT NULL default '',
					  `tablename` varchar(60) NOT NULL default '',
					  `in_menue` tinyint(1) NOT NULL default '1',
					  `menue_index` mediumint(9) NOT NULL default '0',
					  `hide_options` tinyint(1) NOT NULL default '0',
					  `hide_search` tinyint(1) NOT NULL default '1',
					  `hide_toc` tinyint(1) NOT NULL default '1',
					  `hide_labels` tinyint(1) NOT NULL default '1',
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
                      `only_admin_access` tinyint(1) NOT NULL default '0',
					  `hide_comments` tinyint(1) NOT NULL default '1',
					  `search_month` tinyint(1) NOT NULL default '0',
					  `search_year` tinyint(1) NOT NULL default '0',
					  `search_keyword` tinyint(1) NOT NULL default '1',
					  `search_range` tinyint(1) NOT NULL default '0',
					  PRIMARY KEY  (`id`),
					  UNIQUE KEY `name_2` (`name`),
					  KEY `name` (`name`,`tablename`,`group_field`)
					) ENGINE=MyISAM $charsetter;";
		$res = mysql_query($query, $link);
		$fehler_nr = $fehler_nr.mysql_errno($link);
		$fehler_text = $fehler_text.mysql_error($link);
		if ($debug) { echo('<br/><span class="debug">Create Sys Query is: '.$query.'<br /></span>'); }

		$query = "CREATE TABLE `_sys_fields` (
                      `id` tinyint(4) NOT NULL auto_increment,
                      `name` varchar(60) NOT NULL default '',
                      `label` varchar(160) NOT NULL,
                      `order_index` int(11) NOT NULL default '1',
                      `pagename` varchar(60) NOT NULL default '',
                      `valuelist` varchar(255) NOT NULL default '',
                      `validation` varchar(60) NOT NULL default '',
                      `not_brief` tinyint(1) NOT NULL default '0',
                      `embed_in` varchar(140) NOT NULL,
                      `foreign_key_to` varchar(200) NOT NULL,
                      `on_update` varchar(20) NOT NULL,
                      `on_delete` varchar(20) NOT NULL,
                      PRIMARY KEY  (`id`)
                    ) ENGINE=MyISAM $charsetter ;";
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
        $charsetter = getMySQLCharsetter();
		if ($page_name == "") return __('you should provide a page name!');
		$shuffpp = str_shuffle('polypager'); // this helps that we most likely don't create tables twice
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
			$query = "CREATE TABLE `".buildValidMySQLTableNameFrom(utf8_tohtml($page_name)."_".$shuffpp)."` (
						  `id` bigint(20) NOT NULL auto_increment,
						  `title` varchar(160) NOT NULL default '',
						  `bla` text NOT NULL,
						  `inputdate` datetime NOT NULL default '0000-00-00 00:00:00',
						  `lastedited` date default NULL,
						  `publish` tinyint(1) NOT NULL default '1',
						  PRIMARY KEY  (`id`),
						  KEY `publish` (`publish`)
						) ENGINE=MyISAM $charsetter;";
			$res = mysql_query($query, $link);
			$fehler_nr = $fehler_nr.mysql_errno($link);
			$fehler_text = $fehler_text.mysql_error($link);
			//now page data (if we created our table as planned)
			if($fehler_text == "") {
				$query = "INSERT INTO `_sys_multipages` (`name`, `tablename`, `in_menue`, `menue_index`, `hide_options`, `hide_search`, `hide_toc`, `hide_labels`, `hidden_fields`, `order_by`, `order_order`, `publish_field`, `group_field`, `group_order`, `date_field`, `edited_field`, `title_field`, `step`, `commentable`, `search_month`, `search_year`, `search_keyword`, `search_range`) 
					VALUES ('".$page_name."', '".buildValidMySQLTableNameFrom(utf8_tohtml($page_name)."_".$shuffpp)."', 1, 0, 1, 1, 1, 1, '', 'inputdate', 'DESC', 'publish', '', 'ASC', 'inputdate', 'lastedited', 'title', '7', 1, 1, 0, 1, 0);";
				$res = mysql_query($query, $link);
				$fehler_nr = $fehler_nr.mysql_errno($link);
				$fehler_text = $fehler_text.mysql_error($link);
			}
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
		}
		else if($template_name == "faq") {
			//first the actual table
			$query = "CREATE TABLE `".buildValidMySQLTableNameFrom(utf8_tohtml($page_name)."_".$shuffpp)."` (
						  `id` int(12) NOT NULL auto_increment,
						  `inputdate` datetime NOT NULL default '0000-00-00 00:00:00',
						  `topic` varchar(200) NOT NULL default '',
						  `question` varchar(255) NOT NULL default '',
						  `answer` text NOT NULL,
						  PRIMARY KEY  (`id`),
						  KEY `topic` (`topic`),
						  KEY `inputdate` (`inputdate`)
						) ENGINE=MyISAM $charsetter;";
			$res = mysql_query($query, $link);
			$fehler_nr = $fehler_nr.mysql_errno($link);
			$fehler_text = $fehler_text.mysql_error($link);
			if ($debug) { echo('<br/><span class="debug">Create Template Query is: '.$query.'<br /></span>'); }
			
			//now page data (if we created our table as planned)
			if($fehler_text == "") {
				$query = "INSERT INTO `_sys_multipages` (`name`, `tablename`, `in_menue`, `menue_index`, `hide_options`, `hide_search`, `hide_toc`, `hide_labels`, `hidden_fields`, `order_by`, `order_order`, `publish_field`, `group_field`, `group_order`, `date_field`, `edited_field`, `title_field`, `step`, `commentable`, `search_month`, `search_year`, `search_keyword`, `search_range`) 
				VALUES ('".$page_name."', '".buildValidMySQLTableNameFrom(utf8_tohtml($page_name)."_".$shuffpp)."', 1, 0, 1, 1, 0, 0, '', 'inputdate', 'ASC', '', '', 'ASC', 'inputdate', '', 'question', 'all', 0, 0, 0, 1, 0);";
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
        $entity = getEntity($params["page"]);
        
        //entry of a page and no startpage is given
        $sys_info = getSysInfo();
        
        if ($params["cmd"] == "entry" and utf8_strpos($params["page"], "pages") and count(getPageNames()) == 0) {
            $query = "UPDATE _sys_sys SET start_page = '".$params['values']["name"]."'";
            pp_run_query($query);
        }
        
        // change of a page name
		if (($params["cmd"] == "edit" or $params["cmd"] == "delete")
		and $params["values"]["old_formfield_name"] != "") {
			if ($params["page"] == '_sys_singlepages'){
					//update sections
					if ($params["cmd"] == "delete"){
						$query = "DELETE FROM _sys_sections WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					}
                    if ($params["cmd"] == "edit" && $params["values"]["name"] != $params["values"]["old_formfield_name"]) {
						$query = "UPDATE _sys_sections SET pagename = '".$params["values"]["name"]."'".
							" WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
					}
                    pp_run_query($query);
			}
			if ($params["page"] == '_sys_singlepages' or $params["page"] == '_sys_multipages') {
                //update start page
                $sys_info = getSysInfo();
                if ($params["values"]["old_formfield_name"] == $sys_info["start_page"]){
                    $query = "UPDATE _sys_sys SET start_page = '".$params["values"]["name"]."'";
                    pp_run_query($query);
                }
                
                //update comments
                if ($params["cmd"] == "delete") {
                    $query = "DELETE FROM _sys_comments WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
                }
                if ($params["cmd"] == "edit" && $params["values"]["name"] != $params["values"]["old_formfield_name"]){
                    $query = "UPDATE _sys_comments SET pagename = '".$params["values"]["name"]."'".
                        " WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
                }
                pp_run_query($query);
                
                //update feed list
                if ($params["cmd"] == "delete") {
                    $query = "DELETE FROM _sys_feed WHERE pagename = '".$params["values"]["old_formfield_name"]."';";
                }
                if ($params["cmd"] == "edit" && ($params["values"]["name"] != $params["values"]["old_formfield_name"])) {
                    $query = "UPDATE _sys_feed SET pagename = '".$params["values"]["name"]."'".
                        " WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
                }
                pp_run_query($query);
                
                //update field list
                if ($params["cmd"] == "delete") {
                    $query = "DELETE FROM _sys_fields WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
                }
                if ($params["cmd"] == "edit" && $params["values"]["name"] != $params["values"]["old_formfield_name"]) {
                    $query = "UPDATE _sys_fields SET pagename = '".$params["values"]["name"]."'".
                        " WHERE pagename = '".$params["values"]["old_formfield_name"]."'";
                }
                pp_run_query($query);
                
                //update foreign keys
                if ($params["cmd"] == "delete") {
                    $query = "UPDATE _sys_fields SET foreign_key_to = '' WHERE foreign_key_to = '".$params["values"]["old_formfield_name"]."'";
                }
                if ($params["cmd"] == "edit" && $params["values"]["name"] != $params["values"]["old_formfield_name"]) {
                    $query = "UPDATE _sys_fields SET foreign_key_to = '".$params["values"]["name"]."'".
                        " WHERE foreign_key_to = '".$params["values"]["old_formfield_name"]."'";
                }
                pp_run_query($query);
			}
		}
        
        // change of a title field
        $title_field = $entity['title_field'];
        if (($params["cmd"] == "edit" or $params["cmd"] == "delete")
		and $params["values"]["old_formfield_$title_field"] != "") {
			//update feed list
            if ($params["cmd"] == "delete") {
                $query = "DELETE FROM _sys_feed WHERE pagename = '".$params['page']."' and title = '".$params["values"]["old_formfield_$title_field"]."';";
            }
            if ($params["cmd"] == "edit" && $params["values"][$title_field] != $params["values"]["old_formfield_$title_field"]) {
                $query = "UPDATE _sys_feed SET title = '".$params["values"][$title_field]."'".
                    " WHERE pagename = '".$params["page"]."' AND id = ".$params["nr"];
            }
            pp_run_query($query);
        }
	}
	
	/* writes an admin data list */
	function admin_list($ind=3) {
		$indent = translateIndent($ind);
		$nind = $ind+1;
		global $params;
		global $debug;
        global $error_msg_text;
        global $sys_msg_text;
		include_once("PolyPagerLib_Showing.php");
		
		$link = getDBLink();
		$topic = $params["topic"];
		
		echo($indent.'<form action="." name="choiceForm" id="choiceForm" method="get">'."\n");

		//option list
		if ($topic == 'content' or $topic == 'fields') {
			if ($topic == 'content') {
				echo($indent.'		'.__('page:').'<select name="page" onchange="document.choiceForm.submit();">'."\n");
				$page_selector = $params["page"];
			}else if ($topic == 'fields') {
				echo($indent.'		<select name="group" onchange="document.choiceForm.submit();">'."\n");
				$page_selector = $params["group"];
			}
            $pages =  getPageNames();
			if (count($pages) > 1) {
                echo($indent.'			<option value="">--'.__('select page').'--</option>'."\n");
            }else{
                $params['page'] = $pages[0]; //for the only option: pretend it was selected
            }
			foreach ($pages as $p) {
				if ($page_selector == $p) $selected = "selected='selected'"; else $selected = "";
				echo($indent.'			<option '.$selected.' value="'.urlencode($p).'">'.$p.'</option>'."\n");
			}
			echo($indent.'		</select>'."\n");
		} else if ($topic == 'pages') {
			$link_text = __('A page template creates a page for you that fulfills some well-known function which is used often on websites. So this might be useful for you. After you created the page, you can still edit its properties or delete it.');
			$opt_text = $indent.'		<a id="templates_link"  onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="javascript:toggleVisibility(\'template_msg\',\'templates_link\', \''.__('show templates').'\', \''.__('hide templates').'\');">'.__('show templates').'</a>&nbsp;|&nbsp;'."\n";
			$opt_text .= $indent.'		<span id="template_msg" style="display:none;" class="sys_msg_admin">'."\n";
			$opt_text .= $indent.'		'.__('new page named').' <input type="text" name="page_name" maxlength="30"="60" size="20"/> '."\n";
			$opt_text .= $indent.'		'.__('from template:')."\n";
			$opt_text .= $indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()"><img src="../style/pics/help.gif"/></a>'."\n";
			$opt_text .= $indent.'		<select name="template_name">'."\n";
			$opt_text .= $indent.'			<option value="guestbook">'.__('a simple guestbook').'</option>'."\n";
			$opt_text .= $indent.'			<option value="faq">'.__('an FAQ (Frequently asked questions)').'</option>'."\n";
			$opt_text .= $indent.'			<option value="blog">'.__('a Weblog (often called Blog)').'</option>'."\n";
			$opt_text .= $indent.'		</select>'."\n";
			$opt_text .= $indent.'		<input type="submit" name="dummy" value="'.__('Go!').'"/><input type="hidden" name="page" value="_sys_pages"/>'."\n";
			$opt_text .= $indent.'		</span>'."\n";
            echo($opt_text);
		}
		if ($topic == 'fields') {
			echo($indent.'		<input type="hidden" name="page" value="_sys_fields"/>'."\n");
		}
		echo($indent.'		<input type="hidden" name="cmd" value="show"/>'."\n");
		echo($indent.'		<input type="hidden" name="topic" value="'.$topic.'"/>'."\n");
		
		$entity = getEntity($params["page"]);
		//Links, first for contents
		if ($params["page"] != "" and $params["topic"] == "content") {
			$link_text = __('Here you can edit an introduction text for this page. (It will only be seen if the skin template uses writeIntroDiv())');
			//if (isMultipage($params["page"])) 
				echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="edit.php?_sys_intros&nr='.urlencode($params["page"]).'&page='.urlencode($params["page"]).'&from=list&topic='.$topic.'">'.__('edit intro').'</a>&nbsp;|&nbsp;'."\n");
			$link_text = __('Here you can insert a new entry for this page.');
			echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="edit.php?'.urlencode($params["page"]).'&cmd=new&from=list&topic='.$topic.'">'.__('new entry').'</a>'."\n");
		//now for fields
		} else if ($params["page"] == "_sys_fields") {
			$link_text = __('Here you can make statements about another field.');
			$the_href = 'edit.php?_sys_fields&cmd=new&from=list&group='.urlencode($params["group"]).'&topic='.$topic;
			if ($params["group"] != '') echo($indent.'		<a onmouseover="popup(\''.$link_text.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$the_href.'">'.__('new entry').'</a>'."\n");
		//now for pages
		} else if (utf8_strpos($params["page"], "pages")) {
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
			//if(isASysPage($params["page"]))	$params["cmd"] = $params["cmd"]." ".$params["page"].'_all';
			//$params["nr"] = "";	//we want no special entry, but all
            if(isASysPage($params["page"]))	$params["cmd"] = $params["cmd"]." ".$params["page"];
            //echo($params["cmd"]);
			//$params['page'] = $params['group'];
			$queries = getQuery(false);
            
			// send show quer(y|ies) to DBMS now
			$res = array();
			$fehler_text = "";
			foreach(array_keys($queries) as $qkey){
				$res[$qkey] = mysql_query($queries[$qkey], $link);
				$error_nr = mysql_errno($link);
				if ($error_nr != 0) {
					$fehler_text = mysql_error($link);
					$error_msg_text[] = __('DB-Error:').' '.$fehler_text;
				}
			}
	
			if ($fehler_text == "") {
				
				if (isMultipage($params["page"]) and $params["max"] == "" and isset($params["max"])) { //no other way... db is empty
					echo($indent.'	<ul id="menu">'."\n");
					echo($indent.'		<div class="sys_msg_admin">'.__('There is no entry in the database yet...').'</div>'."\n");
					echo($indent.'		<div class="admin_link"><a onmouseover="popup(\''.__('for admins: make a new entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="edit.php?'.$params["page"].'&amp;cmd=new">Enter the first one</a></div>'."\n");
					echo($indent.'	</ul><div class="menuend"></div>'."\n");
				} else {
				
					//you could type in a too high number - senseless
					if ($params["nr"] > $params["max"]) { 
                        $params["nr"] = $params["max"]; 
                    }
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
    
    /* process one command for several entries at once*/
    function execBatchCmds(){
        global $error_msg_text, $sys_msg_text;
        $err_list = array();
        $did_sthg = false;
        foreach (array_keys($_POST) as $k) {
            if (substr($k, 0, 6) == 'batch_') {
                $did_sthg = true;
                if (pp_run_query(urldecode($_POST[$k])) != 1)
                    $error_msg_text[] = __('There was a problem executing this command: ').urldecode($_POST[$k]);
            }
        }
        if (count($err_lust) == 0 and $did_sthg)
            $sys_msg_text[] = __('The commands have been successfully executed.');

    }
?>
