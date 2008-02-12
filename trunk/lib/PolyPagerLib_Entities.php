<?php
/*  This function gets a multidimensional array for the metadata of pages.
    An entity is basically some data structure. Here, it is all you might want 
    to know when you deal with a page (but you can also call this function with
    a tablename to get its structure).
	
	It gets data from the database (from the table that holds the page's data and
    the table that describes the page), but this function is also the place
	to add process information about pages taht this system handles.
	Because we might ask for the entity multiple times while working
	on a request, we store the variable outside.
	
	Here are some features explained:
    $entity["tablename"]: the table containing the data this page shows
    $entity["pagename"]: the pagename
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
	$entity["date_field"] = array("name"=>".*", "editlabel"=>".*");
	$entity["time_field"] =  array("name"=>".*");
    $entity["title_field"]: The field to be used for the title of each entry
    $entity["fields"]: a multidimensional array with metadata for each field in 
                        the table containing the data to show. See addFields()
                        for more details.
    $entity["one_entry_only"]: if 1, PolyPager will make it impossible to create a second entry
    $entity['formgroups']: an associated array of groups in which form fields should be put.
                       For example:
                       $entity['formgroups']['admin'] = array(0,'hide');
                       This formgroup is called "admin", its order index within 
                       the other formgroups is 0 and it's initially shown.
                       In the $entity["fields"] - array, each field can get a formgroup assigned
                       (see addFields())
    $entiy["pk"]: the name of the primary key - field
    $entiy["pk-type"]: the type of the primary key - field
    $entity["hide_labels"]: if 1, labels will not be shown
    $entity["publish_field"]: the name of the (boolean, i.e. tinyint(1)) - field
                        that stores if this entry should be published
    $entity["order_by"]: name of the field which will be order criteria
    $entity["search"]: an array describing what search is possible on this page.
                        For example, here we search for keywords only:
                        array("range"=>0,"month"=>0,"year"=>0,"keyword"=>1)
    $entity["fillafromb"]: a is a field name and b is alist of values. In the form,
                            PolyPager will provide javascript to fill the field a with values from list b 
*/
$entity = "";		//stores the actual entity
$old_entities = "";	//stores entites we already built within this request
function getEntity($page_name) {
	global $entity;
	global $old_entities;
	//echo("getEntity called with param |".$page_name."|\n");
	if ($page_name == "") return $entity;

	if ($entity == "" or $entity["pagename"] != $page_name) {
		
		// look for already built entities - we need to build again for 
		// _sys_multipages - this could be any table
		if ($page_name != '_sys_multipages' and
				getAlreadyBuiltEntity($page_name) != "") {
			$entity = getAlreadyBuiltEntity($page_name);
		} else {
			$entity = array();
			$entity["pagename"] = $page_name;
			
			//	metadata for system
			if ($page_name == "_sys_sys") {
				$entity["tablename"] = "_sys_sys";		//there is no _sys_sys - table
				$entity["one_entry_only"] = "1";	//keep it one

				$entity = addFields($entity,$entity["tablename"]);
				$skindirs = scandir_n('../style/skins', 0, false, true);
				$skindirs_wo_picswap = array();
				for($x=0;$x<count($skindirs);$x++){	//picswap gets extra handling in PolyPagerLib_HTMLFraming
					if ($skindirs[$x] != 'picswap') $skindirs_wo_picswap[] = $skindirs[$x];
				}
				
				//if we had picswap, now put in the four artificial colorset-dummies
				if (count($skindirs) != count($skindirs_wo_picswap)){
					$skindirs = $skindirs_wo_picswap;
					$dirs = implode(",",$skindirs);
					$dirs = utf8_str_replace(",,", ",",$dirs);
					$dirs = $dirs.",picswap-aqua,picswap-fall,picswap-uptight,picswap-saarpreme";
				}else{
					$dirs = implode(",",$skindirs);
				}
				
				setEntityFieldValue("skin", "valuelist", $dirs);
				setEntityFieldValue("lang", "valuelist", "en,de");
				setEntityFieldValue("start_page", "valuelist", implode(',', getPageNames()));
				setEntityFieldValue("feed_amount", "validation", 'number');
				
				//formgroups
				$entity['formgroups'] = array();
				$entity['formgroups']['metadata'] = array(1,'hide');
				setEntityFieldValue("title", "formgroup", 'metadata');
				setEntityFieldValue("author", "formgroup", 'metadata');
				setEntityFieldValue("keywords", "formgroup", 'metadata');
				$entity['formgroups']['admin'] = array(0,'hide');
				setEntityFieldValue("admin_name", "formgroup", 'admin');
				setEntityFieldValue("admin_pass", "formgroup", 'admin');
				$entity['formgroups']['gallery'] = array(2,'hide');
				setEntityFieldValue("link_to_gallery_in_menu", "formgroup", 'gallery');
				setEntityFieldValue("gallery_name", "formgroup", 'gallery');
				setEntityFieldValue("gallery_index", "formgroup", 'gallery');
				$entity['formgroups']['misc'] = array(3,'show');
				setEntityFieldValue("hide_public_popups", "formgroup", 'misc');
				setEntityFieldValue("start_page", "formgroup", 'misc');
				setEntityFieldValue("feed_amount", "formgroup", 'misc');
                setEntityFieldValue("full_feed", "formgroup", 'misc');
                setEntityFieldValue("encoding", "formgroup", 'misc');
				setEntityFieldValue("lang", "formgroup", 'misc');
				setEntityFieldValue("skin", "formgroup", 'misc');
				setEntityFieldValue("submenus_always_on", "formgroup", 'misc');
                setEntityFieldValue("whole_site_admin_access", "formgroup", 'misc');
                
				$entity['formgroups']['captcha'] = array(4,'hide');
				setEntityFieldValue("use_captchas", "formgroup", 'captcha');
				setEntityFieldValue("public_captcha_key", "formgroup", 'captcha');
				setEntityFieldValue("private_captcha_key", "formgroup", 'captcha');
                
				global $run_as_demo;
				if ($run_as_demo) {
					$entity["hidden_form_fields"] .= ',admin_name,admin_pass';
				}
                setEntityFieldValue("use_captchas", "help", __('Activate this if you want your commenters to proof they are human before entering a comment. They will have to do so by entering one or two words. This will only work if you have PHP version >= 5 and if you get your personal access keys for this service (recaptcha.net) and fill them in below (It is worth it).'));
                setEntityFieldValue("label", "public_captcha_key", __('public key (<a href="recaptcha_get_signup_url function">get your own</a>')); 
			}
			//	metadata for multipages that are edited
			else if ($page_name == "_sys_multipages") {
				$entity["tablename"] = "_sys_multipages";

				$entity = addFields($entity,$entity["tablename"]);
                
				$entity["title_field"] = "name";
				setEntityFieldValue("order_order", "valuelist", "ASC,DESC");
				setEntityFieldValue("group_order", "valuelist", "ASC,DESC");
				// no tables: no user input
				$tables = getTables();
				if (count($tables) > 0) {
					setEntityFieldValue("tablename", "valuelist", ','.implode(',', $tables));
				} else {
					$entity["disabled_fields"] .= $entity["disabled_fields"].',tablename';
					setEntityFieldValue("tablename", "valuelist", __('there is no table in the database yet'));
				}
				
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
                    //edit: this causes more trouble than it does good (where does it actually do any good?)
                    //      on a new page, we don't want table data if we don"t have a table
					/*$tables_str = implode("|", $tables);
					if (!utf8_strpos( $tables_str, "|" )) {
						$the_table = $tables_str; //there seems to be only one
					} else {
						$the_table = utf8_substr( $tables_str, 0, utf8_strpos( $tables_str, "|" ) );
					}*/
				}
				
				if ($the_table != "") {
					//fill with name fields with suitable type - 
					//the leading "," preserves one empty entry
					setEntityFieldValue("date_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'date,datetime')));
					setEntityFieldValue("time_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'time')));
					setEntityFieldValue("edited_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'date,datetime')));
					setEntityFieldValue("title_field", "valuelist", ','.implode(',', getListOfFields($the_table)));
					setEntityFieldValue("order_by", "valuelist", ','.implode(',', getListOfFields($the_table)));
					setEntityFieldValue("group_field", "valuelist", ','.implode(',', getListOfFields($the_table)));
					setEntityFieldValue("publish_field", "valuelist", ','.implode(',', getListOfFieldsByDataType($the_table, 'bool')));
				} else {
					$entity["disabled_fields"] .= ',publish_field,date_field,time_field,edited_field,title_field,order_by,group_field';
				}
				
				$entity["hidden_form_fields"] .= ',hide_comments';
				
				setEntityFieldValue("menue_index", "validation", 'number');
				setEntityFieldValue("name", "validation", 'any_text');
				setEntityFieldValue("tablename", "validation", 'any_text');
				
				//formgroups
				$entity['formgroups'] = array();
				$entity['formgroups']['name/table'] = array(0,'show');
				setEntityFieldValue("name", "formgroup", 'name/table');
				setEntityFieldValue("tablename", "formgroup", 'name/table');
				$entity['formgroups']['menu'] = array(1,'show');
				setEntityFieldValue("in_menue", "formgroup", 'menu');
				setEntityFieldValue("menue_index", "formgroup", 'menu');
				$entity['formgroups']['what to hide or show'] = array(2,'hide');
				setEntityFieldValue("hidden_fields", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_options", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_toc", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_search", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_labels", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_comments", "formgroup", 'what to hide or show');
                setEntityFieldValue("only_admin_access", "formgroup", 'what to hide or show');
				$entity['formgroups']['fields with special meaning'] = array(3,'hide');
				setEntityFieldValue("publish_field", "formgroup", 'fields with special meaning');
				setEntityFieldValue("group_field", "formgroup", 'fields with special meaning');
				setEntityFieldValue("group_order", "formgroup", 'fields with special meaning');
				setEntityFieldValue("date_field", "formgroup", 'fields with special meaning');
				setEntityFieldValue("edited_field", "formgroup", 'fields with special meaning');
				setEntityFieldValue("title_field", "formgroup", 'fields with special meaning');
                setEntityFieldValue("order_order", "formgroup", 'fields with special meaning');
				setEntityFieldValue("order_by", "formgroup", 'fields with special meaning');
				$entity['formgroups']['search'] = array(4,'hide');
				setEntityFieldValue("search_month", "formgroup", 'search');
				setEntityFieldValue("search_year", "formgroup", 'search');
				setEntityFieldValue("search_keyword", "formgroup", 'search');
				setEntityFieldValue("search_range", "formgroup", 'search');
				$entity['formgroups']['misc'] = array(5,'hide');
				setEntityFieldValue("commentable", "formgroup", 'misc');
				setEntityFieldValue("step", "formgroup", 'misc');
				
				
				//help texts
				setEntityFieldValue("name", "help", __('the name of the page.'));
				setEntityFieldValue("in_menue", "help", __('when this field is checked, you will find a link to this page in the menu.'));
				setEntityFieldValue("menue_index", "help", __('this field holds a number which determines the order in which pages that are shown in the menu (see above) are arranged.'));
				setEntityFieldValue("commentable", "help", __('when this field is checked, entries on this page will be commentable by users.'));
				setEntityFieldValue("hide_options", "help", __('when this field is checked, administration info under each entry (edit-link,date of last change, ...) will not be shown.'));
				setEntityFieldValue("hide_search", "help", __('when this field is checked, the link to search form will not be shown.'));
				setEntityFieldValue("hide_toc", "help", __('when this field is checked, the table of contents on top of the page will not be shown.'));
				setEntityFieldValue("tablename", "help", __('this field is important: it defines which table to use for this page. Some of the fields below depend on what is given here, because PolyPager finds the values for those fields in this table.'));
				setEntityFieldValue("hidden_fields", "help", __('these fields will not be shown to the public. Select fields from the list by clicking on them.'));
				setEntityFieldValue("order_by", "help", __('here you can choose which field should be the order criterium.'));
				setEntityFieldValue("order_order", "help", __('ASC stands for ascending. Take numbers for an example: lowest numbers will come first, highest last. DESC means descending and works the other way round'));
				setEntityFieldValue("publish_field", "help", __('this field will be used to switch if the entry should be public or not'));
				setEntityFieldValue("group_field", "help", __('this field will be used by PolyPager to group entries of this page. It will also be used to create sumenu entries (so the visitor can select what to see quickly) and search criteria.'));
				setEntityFieldValue("group_order", "help", __('ASC stands for ascending. Take numbers for an example: lowest numbers will come first, highest last. DESC means descending and works the other way round'));
				setEntityFieldValue("date_field", "help", __('this (date)field stores the time its entry was created.'));
				setEntityFieldValue("edited_field", "help", __('this (date)field would display when the last change to its entry took place.'));
				setEntityFieldValue("title_field", "help", __('this field will be used as title field. It will therefore look different to the others.'));
				setEntityFieldValue("feed", "help", __('if this field is checked, new entries of this page will be fed. That means they will be listed under the latest entries (right on the page) and they will be available via RSS.'));
				setEntityFieldValue("step", "help", __('here you specify how many entries should be shown on one page. You can use a number or simply all'));
				setEntityFieldValue("hide_comments", "help", __('this field has currently no meaning (that means: it is not yet implemented)'));
				setEntityFieldValue("only_admin_access", "help", __('this field should be checked if you want only admins to see it (you can also protect all pages at once in the system settings)'));
                setEntityFieldValue("taggable", "help", __('this field has currently no meaning (that means: it is not yet implemented)'));
				setEntityFieldValue("search_month", "help", __('if this field is checked, users can search for entries of this page made in a particular month.'));
				setEntityFieldValue("search_year", "help", __('if this field is checked, users can search for entries of this page made in a particular year.'));
				setEntityFieldValue("search_keyword", "help", __('if this field is checked, users can search for entries of this page by typing in a keyword.'));
				setEntityFieldValue("search_range", "help", __('if this field is checked, users can navigate through entries of this page using previous- and next-links. Only use this when your entries are ordered by (see field order_by, above) the primary key of the table.'));
				setEntityFieldValue("hide_labels", "help", __('if this field is checked, the label of each field is shown.'));
			}
			//	metadata for singlepages
			else if ($page_name == "_sys_singlepages") {
				$entity["tablename"] = "_sys_singlepages";

				$entity = addFields($entity,$entity["tablename"]);
				
				$entity["hidden_fields"] = "default_group";
				$entity["hidden_form_fields"] = "default_group";
				
				$entity["order_order"] = "ASC";
				$entity["title_field"] = "name";
				setEntityFieldValue("menue_index", "validation", 'number');
				setEntityFieldValue("name", "validation", 'any_text');

				
				//formgroups
				$entity['formgroups'] = array();
				$entity['formgroups'][''] = array(0,'show');
				setEntityFieldValue("name", "formgroup", '');
				$entity['formgroups']['menu-settings'] = array(1,'show');
				setEntityFieldValue("in_menue", "formgroup", 'menu-settings');
				setEntityFieldValue("menue_index", "formgroup", 'menu-settings');
				$entity['formgroups']['what to hide or show'] = array(2,'hide');
				setEntityFieldValue("hide_options", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_toc", "formgroup", 'what to hide or show');
				setEntityFieldValue("hide_search", "formgroup", 'what to hide or show');
                setEntityFieldValue("only_admin_access", "formgroup", 'what to hide or show');
				$entity['formgroups']['misc'] = array(3,'hide');
				setEntityFieldValue("commentable", "formgroup", 'misc');
				setEntityFieldValue("grouplist", "formgroup", 'misc');
				
				setEntityFieldValue("name", "help", __('the name of the page.'));
				setEntityFieldValue("only_admin_access", "help", __('this field should be checked if you want only admins to see it (you can also protect all pages at once in the system settings)'));
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
				$entity["tablename"] = "_sys_pages";
					$field1 = array("name"=>"id",
						"data_type"=>"int",
						"size"=>"12");
					$field2 = array("name"=>"name",
						"data_type"=>"varchar",
						"size"=>"60");
					$field3 = array("name"=>"in_menue",
						"data_type"=>"bool",
						"size"=>"1");
				$entity["fields"] = array($field1,$field2,$field3);
				$entity["title_field"] = "name";
				$entity["pk"] = "id";
				$entity["pk_type"] = "int";
			}
			//this is to make sitewide search work
			else if ($page_name == "_search") {
				$s = array('range'=>0, 'keyword'=>1, 'month'=>1, 'year'=>1);
				$entity["search"] = $s;
			}
			//	table for feeds
			else if ($page_name == "_sys_feed") {
				$entity["tablename"] = "_sys_feed";
				$entity["title_field"] = "title";
				$entity = addFields($entity,$entity["tablename"]);
				$entity["disabled_fields"] = "pagename";
                $group = array("field"=>"pagename",
								   "order"=>"DESC");
				$entity["group"] = $group;
				$entity["hidden_form_fields"] = "id"; 
			}
			//	table for intros
			else if ($page_name == "_sys_intros") {
				$entity["tablename"] = "_sys_intros";
				$entity["one_entry_only"] = "1";	//keep it one

				$entity = addFields($entity,$entity["tablename"]);
			}
			//	table for fields
			else if ($page_name == "_sys_fields") {
				$entity["tablename"] = "_sys_fields";

				$entity = addFields($entity,$entity["tablename"]);
				
                // for showing sys_fields, we pass a group parameter with the page
                // and then this helps to build the query
				$group = array("field"=>"pagename", "order"=>"DESC");
				$entity["group"] = $group;
                
                
                $param_group = urldecode($_GET["group"]);
                if ($param_group == '')  $param_group = urldecode($_POST["group"]);
				$fields = getListOfNonExistingFields($param_group);
				if (count($fields) > 0) {
					// when field options of simple pages are edited by 
					// the users, I prefer to not show'em all
					if ($params['page']=='_sys_fields' && isSinglePage($param_group)){
						$flist = implode(',', $fields);
						$flist = utf8_str_replace('input_date','',$flist); 
						$flist = utf8_str_replace('edited_date','',$flist); 
						$flist = utf8_str_replace('the_group','',$flist);
						$flist = utf8_str_replace('publish','',$flist);
						$flist = utf8_str_replace('in_submenu','',$flist);
						$flist = utf8_str_replace('pagename','',$flist); 
						while (ereg(',,',$flist)) $flist = utf8_str_replace(',,',',',$flist);
						//now commas at start or end have to go
						$flist = preg_replace('@^,@', '', $flist);
						$flist = preg_replace('@,$@', '', $flist);
						setEntityFieldValue("name", "valuelist", $flist);
					}else setEntityFieldValue("name", "valuelist", implode(',', $fields));
				} else {
					$entity["disabled_fields"] .= ',name';
					setEntityFieldValue("name", "valuelist", __('there is no table specified for this page yet'));
				}
				setEntityFieldValue("pagename", "valuelist", implode(',', getPageNames()));
                setEntityFieldValue("pagename", "valuelist_from_db", true); //user cannot add any
				setEntityFieldValue("validation", "valuelist", 'no validation,number,any_text,email');	//not really ready yet
				setEntityFieldValue("foreign_key_to", "valuelist", ','.implode(',', getPageNames()));
				setEntityFieldValue("on_update", "valuelist", "SET NULL,NO ACTION,CASCADE,RESTRICT");
				setEntityFieldValue("on_delete", "valuelist", "RESTRICT,CASCADE,NO ACTION,SET NULL");
				$entity["title_field"] = "name";
				
				$entity["disabled_fields"] .= ",pagename";
				//enum or set fields have a valuelist in the db: make it impossible to change
				$f = getEntityField($_GET["name"], $entity);
				$t = __('here you can specify allowed values for this field (via a comma-separated list). By doing so, you can choose from this list conveniently when editing the form.');
				if($f['data_type']=="enum" or $f['data_type']=="set"){
					$entity["disabled_fields"] .= ',valuelist';
					$t = __('[This field is disabled because the database specifies these values]').$t;
					setEntityFieldValue("valuelist", "help", $t);
				}
                
                //get valuelist for group field if none is set
                $page_info = getPageInfo($param_group);
                if ($page_info["group_field"] != "" and $page_info["group_field"] == $f['name']){
                    $q = "SELECT ".$page_info["group_field"]." FROM ".$page_info["tablename"]." GROUP BY ".$page_info["group_field"];
                    echo($q);
                    $res = pp_run_query($q);
                    $group_vals = array();
                    while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
                        $group_vals[] = $row[$page_info["group_field"]];
                    global $params;
                    if ($params['cmd'] == "new" and $params["values"] = "")  $params['values'] = array();
                    $params['values']['valuelist'] = implode(',', $group_vals);
                }
                    
				
				$entity["hidden_form_fields"] = "foreign_key_to,on_update,on_delete";//this feature is not clearly defined as from 0.9.7
				
				//help texts
				setEntityFieldValue("validation", "help", __('you can chose a validation method that is checked on the content of this field when you submit a form.'));
				setEntityFieldValue("not_brief", "help", __('check this box when this field contains much data (e.g. long texts). It will then only be shown if the page shows one entry and a link to it otherwise.'));
				setEntityFieldValue("order_index", "help", __('when shown, the fields of an entry are ordered by the order in their table (0 to n). you can change the order index for this field here.'));
				setEntityFieldValue("embed_in", "help", __('you can embed the contents of this field within a string when it is displayed. Use &quot;[CONTENT]&quot; to represent its content. For instance, &lt; img src=&quot;path/to/[CONTENT]&quot;/> lets you display image names as actual image on the page.'));
				
                //setEntityFieldValue("foreign_key_to", "help", __('Here you can specify a Foreign Key - relation. If this field corresponds entries of this page to another, say so here (for example, the field [bookid] on the page [chapters] could reference the page [books]). One advantege would be that you can chose from a convenient list when you edit entries of this page (rather than, for example, always type the bookid by hand). For more advantages, see the fields on_update and on_delete below.'));
				//$update_delete_help = 'If you have chosen to reference another page with this field (see above), then you can specify here how PolyPager should behave when something happens to the referenced entry. To use the example from the help on the reference-field: If you delete/update the bookid, then what happens to its chapters? You might want PolyPager to do nothing (NO ACTION), restrict it (RESTRICT), forward the change/deletion to referencing entries of this page (CASCADE) or just delete the reference to that entry (SET NULL).';
				//setEntityFieldValue("on_update", "help", __($update_delete_help));
				//setEntityFieldValue("on_delete", "help", __($update_delete_help));
			}
			//	table for comments
			else if ($page_name == "_sys_comments") {
				$entity["tablename"] = "_sys_comments";

				$entity["hide_labels"] = "1";
                
				$entity["hidden_fields"] = "pagename,pageid,is_spam,email";
				$entity["hidden_form_fields"] = "pagename,pageid,insert_date,insert_time,is_spam";
				
				$entity["order_by"] = "insert_date";
				$entity["order_order"] = "ASC";
					
				//date_field
				$date_field = array("name"=>"insert_date",
								 "show"=>"1");
				$entity["date_field"] = $date_field;
				$time_field = array("name"=>"insert_time",
								 "show"=>"1",);
				$entity["time_field"] = $time_field;
				$entity["title_field"] = "comment";
				
				$group = array("field"=>"pageid",
								   "order"=>"DESC");
				$entity["group"] = $group;
					
				$entity = addFields($entity,$entity["tablename"]);
				
				setEntityFieldValue("insert_date", "label", __("Date"));
				setEntityFieldValue("insert_time", "label", __("Time"));
				setEntityFieldValue("name", "label", __("Name"));
				setEntityFieldValue("www", "label", __("Homepage"));
				setEntityFieldValue("email", "label", __("eMail"));
				setEntityFieldValue("comment", "label", __("Comment"));
				
				//setEntityFieldValue("email", "validation", "email");
				//too strict - people can write what they want here...
				//setEntityFieldValue("www", "validation", "url");
				setEntityFieldValue("name", "validation", "any_text");
			}
			//single pages
			else if (isSinglepage($page_name)) {
				//echo("$page_name is a singlepage!!");
				$entity["tablename"] = '_sys_sections';

				$entity["hide_labels"] = "no";
				$entity["title_field"] = 'heading';
				$entity["publish_field"] = 'publish';
				$entity["order_by"] = 'order_index';
				$entity["hidden_fields"] = "in_submenu,pagename,order_index,publish,the_group,edited_date,input_date,input_time";
				$entity["hidden_form_fields"] = ",pagename,input_date,input_time,edited_date";
                
				//date_field + time_field
				$entity["date_field"] = array("name"=>"input_date",
								 "editlabel"=>"edited_date");
				$entity["time_field"] = array("name"=>"input_time");
					
				$entity["search"] = array("keyword" => "1");
				$entity = addFields($entity,$entity["tablename"]);
				$page_info = getPageInfo($page_name);
				//now we populate the value list for group with what 
				//might have been typed into the singlepage form - 
				//"standard" is the standard group, not in the submenu and always visible
				setEntityFieldValue("the_group", "valuelist", "standard,".stripCSVList($page_info["grouplist"]));
                setEntityFieldValue("the_group", "valuelist_from_db", true); //user cannot add any via entry form
				//if we have groups, this overwrites the anchor behavior!
				if (trim($page_info["grouplist"]) == "") {
					$entity["hidden_form_fields"] .=",the_group";
				}else {
					$group = array("field"=>"the_group",
								   "order"=>"DESC");
					$entity["group"] = $group;
					$entity["hidden_form_fields"] .=",in_submenu";
				}
				//help
				setEntityFieldValue("the_group", "help", __('the group of this entry (you will find the groups in the specifications for this page). The standard group contains entries that are always shown.'));
			}
			//this is needed when we actually show a multipage
			else if (isMultipage($page_name)) {
				//echo("$page_name is a multipage!!");
				$page_info = getPageInfo($page_name);

				if ($page_info != "") {		//else this makes no sense
					$entity["tablename"] = $page_info["tablename"];
					$entity["step"] = $page_info["step"];
					$entity["hide_labels"] = $page_info["hide_labels"];
					$entity["order_by"] = $page_info["order_by"];
					$entity["order_order"] = $page_info["order_order"];

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
                    
					//date_field - only if there is one specified
					if($page_info["date_field"] != "") {
						$date_field = array("name"=>$page_info["date_field"],
										 "editlabel"=>$page_info["edited_field"]);
						$entity["date_field"] = $date_field;
					}

					$entity["title_field"] = $page_info["title_field"];
					
                    if($page_info["tablename"] != "") {
						$entity = addFields($entity,$page_info["tablename"]);
					}
                    
					//hide those from input
					$entity["hidden_form_fields"] .= ','.$entity["time_field"]["name"];
					$entity["hidden_form_fields"] .= ','.$entity["date_field"]["name"];
					$entity["hidden_form_fields"] .= ','.$entity["date_field"]["editlabel"];
					$entity["hidden_fields"] .= ','.$page_info["publish_field"].','.$page_info["edited_field"];
					//let the hidden fields be filled from the field list
					$e = array();
					$e[0] = 'hidden_fields';
                    $e[1] = getListOfFields($params['page']);
					$entity['fillafromb'][] = $e;
					
                    //get valuelist for group field if none is set
                    $f = getEntityField($entity['group']['field'],$entity);
                    if ($entity["group"] != "" and $f['valuelist'] == ""){
                        $q = "SELECT ".$entity['group']['field']." FROM ".$entity['tablename']." GROUP BY ".$entity["group"]['field'];
                        $res = pp_run_query($q);
                        $group_vals = array();
                        while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
                            $group_vals[] = $row[$entity['group']['field']];
                        setEntityFieldValue($entity['group']['field'], 'valuelist', implode(',',$group_vals));
                    }
                    
				}
			}
            // if it was no page, maybe it's a table - return the info from addFields($entity,)
			else if (in_array($page_name, getTables())) {
                $entity['tablename'] = $page_name;
                $entity['pagename'] = $page_name;
				$entity = addFields($entity,$page_name);
			}
			
			//fk stuff - preselect valuelists
			if (isMultipage($page_name) || isSinglepage($page_name)){
				$ref_tables = getReferencedTableData($entity);
				foreach ($ref_tables as $rt) {
					// make field consistent (send also old values) when a change in them might
					// trigger cascading changes that PolyPager manages (only pages)
					if ($rt['fk']['ref_page'] == $page_name)
						$entity['consistency_fields'] .= ','.$rt['fk']['ref_field'];
					// get the values we need
					if ($rt['table_name'] != ""){
						$q = "SELECT ".$rt['fk']['ref_field']." as pk, ".$rt['title_field']." as tf FROM ".$rt['table_name'];
						//singlepages can operate on the page level whith all data being in one table...
						if ($rt['fk']['ref_page'] != '' and isSinglepage($rt['fk']['ref_page'])) $q .= " WHERE pagename = '".$rt['fk']['ref_page']."'";
						$result = pp_run_query($q);
						
						$tmp = array();
						$used_ids = array();
						while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
							if (!in_array($row['pk'],$used_ids)){
								$tmp[] = $row['pk'].':'.$row['tf'];
								$used_ids[] = $row['pk'];
							}
						}
						setEntityFieldValue($rt['fk']['field'], "valuelist", implode(',',$tmp));
					}
				}
			}
			
            
            // guess title field if not set
            if ($entity["title_field"] == "") $entity["title_field"] = guessTextField($entity,false);
            // because: if titles change, I always want to know (if they are no blob)
            $f = getEntityField($entity["title_field"], $entity);
            if (!isTextareaType($f['data_type']))
                $entity["consistency_fields"] .= ','.$entity["title_field"];
            
            $old_entities[] = $entity;
		}
	}
    return $entity;
}

/*gives you an already built entity if it is stored in $old_entities (it should)*/
function getAlreadyBuiltEntity($page_name) {
	global $old_entities;
	if ($old_entities != "")
		foreach($old_entities as $e){
			if ($e["pagename"] == $page_name) {
                return $e;
            }
		}
	return "";
}


/*
    returns true if this entity has a date field
*/
function hasDateField($entity){
    foreach($entity['fields'] as $f){
        if (isDateType($f['data_type'])) return true;
    }
    return false;
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

/*gets an array with field data from the entity*/
function getEntityField($fname, $entity) {
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
	foreach($entity["fields"] as $f){
		$fields[] = $f["name"];
	}
	$fields[] = $entity["pk"];
	if ($entity_name != "") $entity = $actual_entity;	//set $entity back to what it was!
	return $fields;
}

/* gets an array with names of fields of the actual 
 * entity with the named data types (a comma separated list)*/
function getListOfFieldsByDataType($entity_name, $data_types) {
	$types = utf8_explode(',', $data_types);
	if ($entity_name != "") {
		global $entity;
		$actual_entity = $entity;	//save it
		$entity = getEntity($entity_name);
	} else $entity = getEntity("");
	$dfields = array();
	foreach($types as $t) {
		foreach($entity["fields"] as $f) {
			if ($f["data_type"] == $t) {
				$dfields[] = $f["name"];
			}
		}
		if ($entity["pk_type"] == $t) {
			$dfields[] = $entity["pk"];
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

function getListOfNonExistingFields($entity_name){
    if ($entity_name != "") {
		global $entity;
		$actual_entity = $entity;	//save it
		$entity = getEntity($entity_name);
	} else $entity = getEntity("");
    $query = "SELECT name from _sys_fields WHERE pagename = '".$entity['tablename']."'";
    $result = pp_run_query($query);
    $all = getListOfFields($entity_name);
    $existing = array();
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
        $existing[] = $row['name'];
    }
    if ($entity_name != "") $entity = $actual_entity;	//set $entity back to what it was!
    return arrays_exor($all, $existing);
}
?>
