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

//we need this for comment forms
//we cannot include it within a method because the scope wouldn't fit
require_once("PolyPagerLib_HTMLForms.php");

	/*
	Function Index:
	* getShowParameters()
	* getMaxNr($page)
	* writeSearchInfo($ind)
	* getQuery($only_published)
	* writeToc($res, $show, $ind)
	* writeSearchForm($show, $ind)
	* writeEntry($row, $pagename, $list_view, $ind)
	* writeEntries($res, $list_view, $as_toc, $ind)
	* getComments() 
	* writeComments($comments, $ind)
	* writeCommentForm($ind)
	*/
	
	/* returns the highest entry number of the entity's table
	*/
	function getMaxNr($page) {
		global $_POST;
		global $_GET;
		global $debug;
		if ($debug) {echo '				<div class="debug">getting max for:|'.$params["page"].'| and I know it:'.isAKnownPage($page).'</div>'; }
		if ($page != '_sys_pages' and isAKnownPage($page)) {
			$entity = getEntity($page);
			if ($entity['pk'] != "" ) {
				$page_info = getPageInfo($page);
				$max = $_POST["max"];	//get max from request - POST
				if ($max == "") { $max = $_GET["max"]; } //coming in per GET?
				//reading the number of entries, if not give
				if ($max == "" and $entity != "" and $page_info["tablename"] != "" ) {
                    $query = "SELECT max(".$entity["pk"].") AS maxnr FROM ".$entity["tablename"].";";
					$res = pp_run_query($query);
					$row = mysql_fetch_array($res, MYSQL_ASSOC);
					$max = $row["maxnr"];
				}
			}
		}
		if ($max == "") $max = "-1";	//better than nothing, and indeed, there is nothing
		return $max;
	}
	
    /*get number of entries for a page*/
    function getMaxCount($page) {
		if ($page != '_sys_pages' and isAKnownPage($page)) {
			$entity = getEntity($page);
			if ($entity['pk'] != "" ) {
				$page_info = getPageInfo($page);
                $query = "SELECT count(*) AS cnt FROM ".$entity["tablename"];
                if (isSinglePage($page)) $query .= " WHERE pagename = '".$page."'";
                if ($entity["publish_field"] != "") {
                    if (isSinglePage($page)) $query .= " AND "; else $query .= " WHERE ";
                    $query .= $entity["publish_field"]." != 0";
                }
                $res = pp_run_query($query);
                $row = mysql_fetch_array($res, MYSQL_ASSOC);
                $cnt = $row["cnt"];
			}
		}
		if ($cnt == "") $cnt = 0;	//better than nothing, and indeed, there is nothing
		return $cnt;
	}
    
	/*
		returns an Array of Parameters for showing:
		["page"=>""), "cmd"=>""), "nr"=>""), "step"=>""), "group"=>""]

		For SINGLEPAGES, we make only one PHP page, and they will have only one 
		parameter, "page".
			
			For MULTIPAGES, we need some params more. 
			First, we need one for commands (parameter "cmd"). Commands are: 
			-show: default, show entries, the other commands show only specific entries
			-search: do a keyword search (parameter is "kw"), keyword will be highlighted
			-Show year: do a search by year (parameter is "y") 
			-Show month: do a month-and-year search (additional param is "m")
			
			We have a few further parameters:
			-nr:    indicates what entry to show
			-step:  indicates how much entries should be shown, starting from nr
			-max:   initally this is the maximum nr of entries in the db. If
					the page is used more than once, this must not be asked for again 
					and again. Helps giving search links and giving nr a value when not 
					given.
			-group: When just a group of data is to be shown. A class of entries can
					have a grouping field which then is used for this.
					The group param will be passed on to the next call! Only hitting a
					"Show all" - link or selecting another group will put an end that.
	*/
	function getShowParameters() {
		global $_POST;
		global $_GET;
		global $debug;
		//PHP 4 uses HTTP_XXX_VARS
		if (!isset($_SERVER)) {
			global $HTTP_POST_VARS;
			global $HTTP_GET_VARS;
			$_POST =& $HTTP_POST_VARS; $_GET =& $HTTP_GET_VARS; 
		}
		
        global $params;
		if ($params == "") $params = array();
		
		//------------------------ topic (for admin list)
		$params["topic"] = $_POST['topic'];
		if (!isset($params["topic"] )) $params["topic"] = $_GET["topic"];
		
		//------------------------ the page name
        //first, let's see if there are pages at all
        global $path_to_root_dir;
        $sys_info = getSysInfo();
        if (count(getPageNames()) == 0 and !$sys_info['no_tables'] and !includedByAdminScript($path_to_root_dir)){
            global $error_msg_text;
            $error_msg_text.= $indent.'<div class="sys_msg">'.__('There are no pages yet. If you are the admin of this site, you can add your first page <a href="admin/?page=_sys_pages&amp;topic=pages">here</a>.').'</div>'."\n";
        }else{
            $params["page"] = urldecode($_POST["page"]);
            if ($params["page"] == "") {
                //the "page" param will be just the first in GET Requests (so we can write http://www.bla.com/?mypage)
                $query_array = utf8_explode('&', $_SERVER["QUERY_STRING"]);
                $params["page"] = urldecode($query_array[0]);
                if ($params["page"]=="page=") $params["page"] = "";
                //if "page=" is given we should handle this, too
                if ($_GET["page"]!="") $params["page"] = urldecode($_GET["page"]);
            }
            
            //if we have no pagename yet, we just want to take the start page
            if ($params["page"] == "") {
                $sys_info = getSysInfo();
                $params["page"] = $sys_info["start_page"];
                if ($params["page"] == "" and !$sys_info['no_tables'] and !includedByAdminScript($path_to_root_dir)) {
                    global $error_msg_text;
                    $error_msg_text.= $indent.'<div class="sys_msg">'.__('There is no start page set. If you are the admin of this site, you can set it at <a href="admin/edit.php?_sys_sys">the system properties</a>.').'</div>'."\n";
                }
            }
		}
		
        
        
        //one more exception: if there is no page but command is _search, 
        //let's help the user out and conduct pagewise search
        if (($params["page"]=="" or $params["page"]=='cmd=_search') and $_GET["cmd"]=="_search")
            $params["page"] = "_search";

        
		//only go on if we know the page
		if ($params["page"] != "" and isAKnownPage($params["page"])){

			//-------------------------cmd param
			$params["cmd"] = $_POST['cmd'];		//commands: show|_search|Show month|Show year
			if (!isset($params["cmd"])) {$params["cmd"] = $_GET['cmd'];}
			if (!isset($params["cmd"])) {$params["cmd"] = "show";}	//(default)
			
			//"_search" at page-place overwrites cmd!
			if($params['page']=="_search") $params['cmd'] = "_search";
			
			$entity = getEntity($params["page"]);
			
			//-------------------------nr param
			$params["nr"] = $_POST['nr'];	//starting point
			if ($params["nr"] == "") $params["nr"] = $_GET['nr'];  //coming in per GET?
			//pages with countable Primary Key need a max nr
			if (isNumericType($entity['pk_type'])){ 
				$params["max"] = getMaxNr($params["page"]);
				if ($params["nr"] == "" and isMultipage($params["page"])) { $params["nr"] = $params["max"]; }	//no preferation: start with highest entry
			}
	
			//-------------------------step param
			$default_step = $entity["step"];				//show this much on a page, could be a number or "all"
			if ($default_step == "") $default_step = "all";
			//1. normally one should be shown - but now we show all, briefly
			if ($default_step == "1" and ($_POST["nr"] == "" and $_GET["nr"] == "")) $params["step"] = "all";
			//2. a nr is given - show only this entry
			if ($_POST["nr"] != "" or $_GET["nr"] != "") $params["step"] = "1";
			//3. coming in explicitly
			if (isset($_GET["step"])) $params["step"] = $_GET["step"];	//coming in per GET?
			if (isset($_POST["step"])) $params["step"] = $_POST["step"]; //coming in per POST?
			//nothing found yet? use default
			if ($params["step"] == "") $params["step"] = $default_step;
			
			//-------------------------group param
            
			$params["group"] = urldecode($_GET["group"]);	//show only this group
            
			if ($params["group"] == "") { $params["group"] = $_POST["group"]; } //coming in per POST?
			if (!isset($params["group"]) and isSinglepage($params["page"])) {	
				//in singlepages, group is called another name for db reasons
				$params["group"] = $_GET["the_group"];
				if ($params["group"] == "") { $params["group"] = $_POST["the_group"]; }
			}
			//take default group if there hasn't been a special one requested
			if ($params["group"] == "" and $params["nr"] == "") {
				$page_info = getPageInfo($params["page"]);
				//default group when the user had a choice between groups for this page
				if ($glist['valuelist'] != 'standard,') $params["group"] = $page_info["default_group"];
			}
			
			//Search
			if ($params["cmd"] == "_search") {	//search
				$had_value = false;
				$search = array();
				if ($entity["search"]["range"] == "1") {}//range has no parameters we haven't covered already
				if ($entity["search"]["keyword"] == "1") {
					$search["kw"] = $_POST["kw"];
					if ($search["kw"] == "") $search["kw"] = $_GET["kw"];
					if ($search["kw"] != "") $had_value = true; 
				}
				if ($entity["search"]["month"] == "1") {
					$search["m"] = $_POST["m"];
					if ($search["m"] == "") $search["m"] = $_GET["m"];
					if ($search["m"] != "") $had_value = true;
				}
				if ($entity["search"]["year"] == "1" or $entity["search"]["month"] == "1") { 
					$search["y"] = $_POST["y"];
					if ($search["y"] == "") $search["y"] = $_GET["y"];
					if ($search["y"] != "") $had_value = true;
				}
				if ($entity["fields"] != "") foreach ($entity["fields"] as $f) {
					if ($f["valuelist"] != "") {
						$search[$f["name"]] = $_POST[''.$f["name"]];
						if ($search[$f["name"]] == "") $search[$f["name"]] = $_GET[''.$f["name"]];
						if ($search[$f["name"]] != "") $had_value = true;
					}
				}
				
				if ($had_value) $params["search"] = $search;
			}
			if ($debug) { echo('				<div class="debug">page param is: '.$params["page"].', topic param is: '.$params["topic"].'</div>'."\n"); }
		}else{ // fall back
            $params["topic"] = "content";
            $params["from"] = "admin";
        }
		return $params;
	}
	
	/*
	 * puts out what search has been done.
	 */
	function writeSearchInfo($ind=4) {
		$indent = translateIndent($ind);
		global $params;
		if ($params["search"] != "") {
			echo($indent.'<div class="sys_msg" id="searchinfo"><h4>'.__('you searched for:').'</h4><ul>'."\n");
			foreach($params["search"] as $name => $val) {
				if ($name=="kw")$name="keyword";
				if ($val != "") echo($indent.'	<li>'.$name.':'.utf8_str_replace("\\","",$val).'</li>'."\n");
			}
			echo($indent.'</ul></div>'."\n");
		}
	}
	
    /*return text search keywords*/
    function getSearchKeywords(){
        global $params;
        // ensure one standard of parentheses to work with
        $url_kws = utf8_str_replace("\\","",$params["search"]["kw"]);
        $url_kws = utf8_str_replace('"',"'",$url_kws);
        // get keywords encapsulated in parentheses
        $regex = '/\'[^\']*\'/';
        preg_match_all($regex,$url_kws,$kws);
        $kws = $kws[0]; //all hits are in the first element
        // get the others
        $single_kws = preg_split($regex,$url_kws);
        foreach($single_kws as $k) $kws = array_merge($kws, utf8_explode(" ", $k)); 
        //TODO: eject keywords that are part of others?
        $sys_info = getSysInfo();
        $kws_clean = array();
        for($x=0;$x<count($kws);$x++)
            if (trim($kws[$x]) != "") $kws_clean[] = htmlentities(urldecode(utf8_str_replace("'", "", $kws[$x])), ENT_QUOTES, $sys_info['encoding']);
        return $kws_clean;
    }
    
    
	/*
		build (and return) SQL Queries
		(mostly one, but for site-wise search there might be more)
		The queries are indexed by the page name in the returned array.
		(global) $params: an array of parameters for showing (see getParameters())
		         if anything else than page content is to be shown (i.e.comments,
				 pages themselves,...), add
				 what you want to $params["cmd"] !!
		$only_published: (boolean) true if only published entries
	*/
	function getQuery($only_published) {
		global $params;
		$sys_info = getSysInfo();
		global $debug;
		
		$queries = array();
		$pagelist = $params["page"];
        
		// multiple?
		if($params['page']=="_search") {
			if ($params['search']['kw']==""){
                global $sys_msg_text;
				$sys_msg_text .= '<div class="sys_msg">'.__('please provide a keyword for your search.').'</div>';
				return array();
			}	
			// search on every page
			$pagelist = implode(',',getPageNames());
		}
		
		foreach (utf8_explode(',',$pagelist) as $p){
            $pagename = $p;
            $page_info = getPageInfo($p);
			$entity = getEntity($p);
            
            
			if ($entity['pk'] == "") {
                if ($entity != "" and $entity['tablename'] != '') {
                    global $sys_msg_text;
                    $sys_msg_text .= '<div class="sys_msg">'.$entity['tablename'].':'.__('This table has no primary key!').'</div>';
                }
				continue;
			}
			// ---------- first the easy cases: 
			
			// all comments
			if (utf8_strpos($params["cmd"], "_sys_comments_all") > 0) {
				$entity = getEntity("_sys_comments");
				$theQuery = "SELECT * FROM _sys_comments
							WHERE is_spam = 0
							ORDER BY pagename, pageid, insert_date DESC";
			}
			
			// comments for one entry
			else if (utf8_strpos($params["cmd"], "_sys_comments") > 0) {
				$entity = getEntity("_sys_comments");
				$theQuery = "SELECT * FROM _sys_comments
							WHERE pagename = '$pagename'
							AND pageid = ".$params["nr"]."
							AND is_spam = 0
							ORDER BY insert_date ASC";
			}
			
			// feeds
			else if (utf8_strpos($params["cmd"], "_sys_feed") > 0) {
				$entity = getEntity("_sys_feed");
				$theQuery = "SELECT * FROM _sys_feed 
							ORDER BY edited_date DESC";
			}
			
			// pages - always select all of them (user doesn't have to see the distinction)
			else if ((utf8_strpos($params["cmd"], "_sys_multipages") > 0)
				or (utf8_strpos($params["cmd"], "_sys_singlepages") > 0)
				or($entity["tablename"] == "_sys_pages")) {
				$theQuery = "SELECT id, name, in_menue FROM _sys_multipages UNION
								SELECT id, name, in_menue FROM _sys_singlepages ORDER BY name";
			}
			
            // preview - take params from URLS and pretend they came form the database :-)
            else if ($params["cmd"] == "preview") {
                require_once("PolyPagerLib_Editing.php");
                $edit_params = getEditParameters();
                $theQuery = "SELECT ";
                foreach ($entity["fields"] as $f){
                    $theQuery .= "'".$edit_params["values"][$f["name"]]."' AS ".$f["name"].",";
                }
                $theQuery = substr_replace($theQuery,'',-1,1);
            }
    
			else {
				//if we have a multipage without a table specified, there is nothing we can do
				if (isMultipage($pagename) and !isASysPage($pagename) and $page_info["tablename"] == "") {
					echo('<div class="sys_msg">'.__('this complex page has no table specified. Cannot select any data.').'</div>');
					$theQuery = 'SELECT * FROM _sys_sys WHERE 1=2';	//just a valid joke
				}
				//checking for multiple-fields primary keys - since they are not supported,
				//we'll select all there is
				else if ($entity['pk_multiple'] and $params['cmd']!='_search'){
					if ($params['nr']!="-1") echo('<div class="sys_msg">'.__('selected all entries.').'</div>');
					$theQuery = "SELECT * FROM ".$entity["tablename"];
				}else {
					//--------------------- preparing  --------------------------
					
					// are we linking to pages/tables via foreign keys?
					$references = getReferencedTableData($entity);
					$ref_fields = array();
					foreach($references as $r) $ref_fields[$r['fk']['field']] = $r['fk']['ref_table'].'||'.$r['title_field'].'||'.$r['fk']['ref_field'];
                    
					$a = array();
					$a[0] = "SELECT "; 
					$a[0] .= $entity["tablename"].'.'.$entity['pk'].",";
					foreach($entity['fields'] as $f){
						// prefer title from referenced values over referencing ones!
						if (in_array($f['name'],array_keys($ref_fields))) {
							$ref = utf8_explode('||',$ref_fields[$f['name']]);
							// using subselect so that we get NULL when the refencing field IS NULL
							$a[0] .= '(SELECT name FROM '.$ref[0].' WHERE '.$ref[2].' = '.$entity['tablename'].'.'.$f['name'].')';
							$a[0] .= ' AS '.$f['name'].",";
						}else $a[0] .= $entity["tablename"].'.'.$f['name'].",";
					}
					
					$a[0] = preg_replace('@,$@', '', $a[0]); // get rid of comma
					
					$a[0] .= " FROM ".$entity["tablename"].",";
					foreach($references as $r) {
						$a[0] .= $r['fk']['ref_table']." as ".$r['fk']['ref_table']."_".$r['fk']['field'].",";
					}
					$a[0] = preg_replace('@,$@', '', $a[0]); // get rid of comma
					$a[0] .= " ";
					
                    //let's track if we said "WHERE"
                    $said_where = false;
                    
                    if (isSinglepage($pagename)) {
						$a[1] = "WHERE _sys_sections.pagename = '$pagename'";
						if ($params["nr"] != "") $a[1] = $a[1]." AND _sys_sections.id = ".$params["nr"];
						if ($params["group"] != "" and $params["group"] != "_sys_all"){
							//"standard" entries are -per definition- always shown!
							$a[1] = $a[1]." AND (_sys_sections.the_group = '".$params["group"]."' OR _sys_sections.the_group = 'standard')";
						}
                        $said_where = true;
					}
					else {
                        
						//helper vars
						if ($params["step"] != "all") {
							$next = $params["nr"] + ($params["step"]-1);
							$prev = $params["nr"] - ($params["step"]-1);
						} else {
							$next = getMaxNr($pagename);
							$prev = 0;
						}
						if ($prev <= 0) $prev = 0;
						$date_field = $entity["date_field"];
						
						//normal query for "show"
						if (isNumericType($entity["pk_type"])) {
                            $a[1] = " WHERE ".$entity["tablename"].'.'.$entity["pk"]." >= $prev AND ".$entity["tablename"].'.'.$entity["pk"]." <= ".$next." ";
                            $said_where = true;
                        }else if ($params['nr'] != "") {
                            $a[1] = " WHERE ".$entity["tablename"].'.'.$entity["pk"]." = ".$params["nr"];
                            $said_where = true;
                        }
						//show a group rather than id range
						if ($params["group"] != "" and $params["group"] != "_sys_all") {
							$a[1] = " WHERE ".$entity["tablename"].'.'.$entity["group"]["field"]." = '".$params["group"]."'";
                            $said_where = false;
						}
					}
                    
					// -- special case search - new query --
					//Keyword search works page AND sitewide
					if($entity["search"]["keyword"] == '1' or $params['page']=='_search') { 
						if ($params["search"]["kw"] != "") {
							$keyword_lower = utf8_strtolower($params["search"]["kw"]);	 //lower/upper-case should not matter in our keyword search!
                            if ($said_where) $a[] = " AND ";
                            else $a[] = " WHERE ";
                            //$a[1] = " WHERE ";
							if (eregi('delete ',$keyword_lower) or eregi('alter ',$keyword_lower) or eregi('update ',$keyword_lower)) { 	//no critical sql code allowed
								echo('<div class="sys_msg">'.__('please do not use SQL Code here in your keyword search...').'</div>'."\n");
								continue; //show nothing
							} else {
								$a[] = " (";
								// get all keywords
								$kws = getSearchKeywords();
                                $found_a_textfield = false;
								foreach($entity["fields"] as $f) {
                                    if (isTextType($f["data_type"])){
                                        $found_a_textfield = true;
                                        $table_field = $entity["tablename"].'.'.$f['name'];
                                        //remember: BLOB fields are case-sensitive! you should take text for those
                                        $a[] = " (";
                                        foreach($kws as $k)
                                            $a[] = " ".$table_field." LIKE '%".utf8_str_replace('.','\.', $k)."%' AND ";
                                        // replace last AND with OR
                                        $a[count($a)-1] = utf8_str_replace(' AND ','',$a[count($a)-1]);
                                        $a[] = " ) OR";
                                    }
								}
								if ($found_a_textfield) $a[count($a)-1] = substr_replace($a[count($a)-1],'',-2,2);	//the last OR has to go
                                else continue;  //no text fields? no query for this table needed
								$a[] = ")";
							}
						}
					}

					// The other search possibilities work only per page
					else if($params["search"] != "") {
						if($entity["search"]["year"] == '1' or $entity["search"]["month"] == '1') {
							if ($params["search"]["y"] != "" or $params["search"]["m"] != "") {
								$month = $params["search"]["m"];
								$year = $params["search"]["y"];
                                
								//if ($said_where) $a[] = " AND ";
                                //else $a[] = " WHERE ";
                                $a[1] = " WHERE ";
								//if december, increment year for enddate, else only the month
								if ($month == "") {
									$nextYear = $year + 1;
									$a[] = " ".$entity["tablename"].'.'.$entity["date_field"]["name"]." >= '$year-01-01' AND ".$entity["tablename"].'.'.$entity["date_field"]["name"]." < '$nextYear-01-01' ";
								} else if ($month == "12") {
									$nextYear = $year + 1;
									$a[] = " ".$entity["tablename"].'.'.$entity["date_field"]["name"]." >= '$year-$month-01' AND ".$entity["tablename"].'.'.$entity["date_field"]["name"]." < '$nextYear-01-01' ";
								} else {
									$nextMonth = $month + 1;
									$a[] = " ".$entity["tablename"].'.'.$entity["date_field"]["name"]." >= '$year-$month-01' AND ".$entity["tablename"].'.'.$entity["date_field"]["name"]." < '$year-$nextMonth-01' ";
								}
							}
						}
		
						//valuelisted fields
						foreach ($entity["fields"] as $f) {
                            
							//if we have a specified valuelist and the name of the field is a name of a search param...
							if ($params["search"][$f["name"]] != "" and $f["valuelist"] != "") {
								if ($said_where) $a[] = " AND ";
                                else $a[] = " WHERE ";
								$a[] = $f["name"]." = '".$params["search"][$f["name"]]."'";
								
								//if the field is the group field, we knew that is a request - save it for later!
								if ($f["name"] == $entity["group"]["field"]) $params["group"] = $params["search"][$f["name"]];
							}
						}
						
						//if we had nothing, no query for this table is needed
						if (count($a) == 2) continue;
					} 
					
					if($only_published and $entity["publish_field"] != "") {	//publish - Flag
						if ($params['search']!="" or $params['page']!='_search') $a[] = " AND ";
						else $a[] = " WHERE ";
						$a[] = $entity["tablename"].'.'.$entity["publish_field"]." = 1";
					}
	
					//link tables referenced by foreign keys
					//include NULL-values
					if ($params['search']!=""){
						foreach($references as $r) $a[] = " AND (".$entity["tablename"].".".$r['fk']['field'].' IS NULL OR '.$entity["tablename"].".".$r['fk']['field']."=".$r['fk']['ref_table'].'_'.$r['fk']['field'].".".$r['fk']['ref_field'].')';
					}
					//NULL-values may lead to multiple occurences because we select
					//from the referenced table (see above), therefore: GROUP
					if ($references != "" and count($references)>0){
						$a[] = ' GROUP BY '.$entity["tablename"].'.'.$entity["pk"];
					}
					
					$theQuery = implode('',$a);
					
					//ORDER BY: 1. grouping, 2. order_by
					$b = array();
					$b[0] = $theQuery;
					if ($entity["group"] == "") $b[1] = " ORDER BY ";
					else $b[1] = " ORDER BY ".$entity["tablename"].'.'.$entity["group"]["field"]." ".$entity["group"]["order"].", ";
					if ($entity["order_by"] == "") $b[2] = $entity["tablename"].'.'.$entity["pk"]." DESC;";
					else $b[2] = $entity["tablename"].'.'.$entity["order_by"]." ".$entity["order_order"].";";
					
					
					$theQuery = implode('',$b);
				}
			}
			$queries[$p] = $theQuery;
		}
        //print_r($queries);
		return $queries;
	}
	
    
    /*  Write a little search box that performs a sitewide keyword search
        In addition, it displays links to searches for the provides keywords
        @keywords a comma separated list of keywords
    */
	function writeSearchBox($keywords="", $ind=5){
        $indent = translateIndent($ind);
        echo($indent.'<div id="searchbox"><div class="description">'.__('Search this site for:')."</div>\n");
        global $path_to_root_dir;
        $keywords = utf8_explode(',',$keywords);
        $l = array();
        foreach ($keywords as $kw)
            if ($kw!="") $l[] = $indent.'    <a href="'.$path_to_root_dir.'?_search&kw='.$kw.'">'.$kw.'</a>'."\n";
        echo(implode(',',$l));
        $helptext = __('Enter one or more keywords here to search for on this website.');
        echo($indent.'    <form action="'.$path_to_root_dir.'" method="get"><input type="hidden" name="page" value="_search"/><input size="13" type="text" value="'.utf8_str_replace("\'","'", utf8_str_replace('\"',"'", $_GET["kw"])).'" name="kw"/><button type="submit">go</button>'."\n");
        $sys_info = getSysInfo();
        if($sys_info['hide_public_popups'] == '0') writeHelpLink($indent.'     ', $helptext);
        echo($indent.'    </form>'."\n");
            
        echo($indent.'</div>'."\n");
    }
    
    
    
	/* writes out search options for multipages
	*	$show   	- true if display property should not be "none" - that means if
	*				the content of the search form should be visible or just a script link
	*/
	function writeSearchForm($show, $ind=4) {
		$indent = translateIndent($ind);
		global $params;
		global $from_admin; //true if the function is called from the admin area - if
							//'hide_search' is checked, we still don't hide the search from
							//the admin..
		$entity = getEntity($params["page"]);
		$page_info = getPageInfo($params["page"]);
		if (($page_info["hide_search"] == 0 or $from_admin)and $entity["search"] != "" and
			!(isMultipage($params["page"]) and $page_info["tablename"] == "") ) {
			echo($indent.'<div id="search">'."\n");
			if (!$show) echo($indent.'<div id="search_content_link_nester"><a id="search_content_link" href="javascript:toggleVisibility(\'search_content\',\'search_content_link\', \''.__('show search options').'\', \''.__('hide search options').'\');">'.__('show search options').'</a></div>'."\n");
			//previous / next - links are always visible - not within the form
			if ($entity["search"]["range"] == "1") {		// links to further entries
				if ($params["cmd"] == "show") {
					
					//helper vars
					$step = $params["step"];
					if ($params["step"] != "all") {
						$next = $params["nr"] + ($step-1);
						$prev = $params["nr"] - ($step-1);
					} else {
						$next = $params["max"];
						$prev = 0;
					}
					if (!($params["nr"] == 0 and $params["max"] == 0)) {
						echo('<div id="search_option_range">');
						if ($params["step"] == "1") {
							echo('                 	'.sprintf(__('you are seeing Nr %s (in whole there are %s entries)'), $params["nr"],$params["max"] ).'<br/>|');
							$prev = $params["nr"] - ($step-1);
							$step = $entity["step"]; 	//use rather this, not one, to make links
							$next = $params["nr"] + ($step-1);
						} else {
							echo('                 	'.sprintf(__('you are seeing Nr %s through Nr %s (in whole there are %s entries)'),$prev,$params["nr"],$params["max"]).'<br/>|');
						}
						
						if ($prev < 0) $prev = 0;
						if ($next > $params["max"]) $next = $params["max"];
						if ($prev > 0) {    //earlier entries
							$theLink = "?".$params["page"]."&amp;nr=".$prev."&amp;step=".$step."&amp;max=".$params["max"]."&amp;group=".$params["group"];
							$newPrev = $prev - $step + 1;
							$sys_info = getSysInfo();
							if($sys_info['hide_public_popups']==0)$theText = ' onmouseover="popup(\''.sprintf(__('show entries %s through %s'),$newPrev,$prev).'\')" onmouseout="kill()" title="" onfocus="this.blur()"';
							else $theText = "";
							echo($indent.'	<a'.$theText.' href="'.$theLink.'">'.__('previous').'</a>|');
						}else {             //no link to earlier entries possible
							echo('                 <i>'.__('previous').'</i>|'."\n");
						}
						if ($params["nr"] < $params["max"]) {   // link to next entries
							$theLink = "?".$params["page"]."&amp;nr=".$next."&amp;step=".$step."&amp;max=".$params["max"]."&amp;group=".$params["group"];
							$sys_info = getSysInfo();
							if($sys_info['hide_public_popups']==0)$theText = 'onmouseover="popup(\''.sprintf(__('show entries %s through %s'),$params["nr"],$next).'\')" onmouseout="kill()" title="" onfocus="this.blur()"';
							else $theText = "";
							echo($indent.'	<a '.$theText.' href="'.$theLink.'">'.__('next').'</a>');
						}else {         	//no link to later entries possible
							echo($indent.'	<i>'.__('next').'</i>'."\n");
						}
						echo('|</div>');
					}
				}
			}
			
			//------------------------ showing search stuff --------------------
			if ($show) $display = 'block'; else $display = 'none';
			echo($indent.'<div id="search_content" style="display:'.$display.';">'."\n");
	
			if ($entity["search"]["month"] == "1") {
				echo('                 			<br />'."\n");
			}
			$theAction = "?".$params["page"];
			echo($indent.'	<form class="search" action="'.$theAction.'" method="get"><fieldset>'."\n");
            echo($indent.'      <input type="hidden" name="cmd" value="_search"/>'."\n");
			if ($entity["search"]["month"] == "1") { //search for a date
				echo($indent.'		<div class="search_option">'."\n");
				echo($indent.'			<span class="search_toggle"><a id="search_month_link" href="javascript:toggle_ability(\'search_month\');">&nbsp;&nbsp;&nbsp;&nbsp;</a></span>'."\n");
				echo($indent.'			'.__('entered in month').' <select class="search_month" disabled="disabled" name="m" >'."\n");	//month input
				$months = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
				$datum = getdate();
				$actMonth = $datum['mon'];
				for ($i = 1; $i <= 12; $i++) {
					if ( number_format($i) !=  number_format($actMonth)) echo('<option value="'.$i.'">'.$months[$i-1].'</option>'."\n");
					else echo($indent.'			<option selected="selected" value="'.$i.'">'.$months[$i-1].'</option>'."\n");
				}
				echo($indent.'			</select> '.__('of year').' <select class="search_month" disabled="disabled" name="y">'."\n");	//year input
				$actYear = $datum['year'];
				for ($i = 2000; $i <= $datum['year']; $i++) {
					if ($i != $actYear) echo($indent.'			<option>'.$i.'</option>'."\n");
					else echo($indent.'			<option selected="selected">'.$i.'</option>'."\n");
				}
				echo($indent.'			</select>'."\n");
				echo($indent.'		</div>'."\n");
			}
			if ($entity["search"]["year"] == "1" and $entity["search"]["month"] == "0") {    //search for a date
				echo($indent.'		<div class="search_option">'."\n");
				echo($indent.'			<span class="search_toggle"><a id="search_year_link" href="javascript:toggle_ability(\'search_year\');">&nbsp;&nbsp;&nbsp;&nbsp;</a></span>'."\n");
				echo($indent.'		'.__('entered in year').' <select class="search_year" disabled="disabled" name="y">'."\n");	//year input
				$datum = getdate();
				$actYear = $datum['year'];
				for ($i = 2000; $i <= $datum['year']; $i++) {
					if ($i != $actYear) echo($indent.'			<option>'.$i.'</option>'."\n");
					else echo($indent.'			<option selected="selected">'.$i.'</option>'."\n");
				}
				echo($indent.'			</select>'."\n");
				echo($indent.'		</div>'."\n");
			}
			if ($entity["search"]["keyword"] == "1") {
				echo('                        			<!--Keywordsearch-->'."\n");
				echo($indent.'		<div class="search_option">'."\n");
				echo($indent.'			<span class="search_toggle"><a id="search_kw_link" href="javascript:toggle_ability(\'search_kw\');">&nbsp;&nbsp;&nbsp;&nbsp;</a></span>'."\n");
				if ($params["search"]["kw"] == "") echo('            						'.__('for keyword:').' <input class="search_kw" disabled="disabled" type="text" size="12" maxlength="24" name="kw"/>'."\n");
				else echo('            			'.__('with keyword:').' <input class="search_kw" disabled="disabled" type="text" size="12" maxlength="24" name="kw" value="'.$params['search']['kw'].'"/>'."\n");
				echo($indent.'		</div>'."\n");
			}
			//search fields for valuelists
			if ($entity["fields"] != "") foreach ($entity["fields"] as $f) {
				if ($f["valuelist"] != "") {
					echo('                     				<!--'.$f['name'].'search-->'."\n");
					echo($indent.'		<div class="search_option">'."\n");
					echo($indent.'			<span class="search_toggle"><a id="search_'.$f['name'].'_link" href="javascript:toggle_ability(\'search_'.$f['name'].'\');">&nbsp;&nbsp;&nbsp;&nbsp;</a></span>'."\n");
					echo($indent.'			'.$f["name"].': <select class="search_'.$f['name'].'" disabled="disabled" name="'.$f['name'].'">'."\n");	//input
					$vals = utf8_explode(',', $f["valuelist"]);
					foreach ($vals as $v) {
						if ($v != $params["search"][$f["name"]]) echo('<option>'.$v.'</option>'."\n");
						else echo($indent.'			<option selected="selected">'.$v.'</option>'."\n");
					}
					echo($indent.'			</select>'."\n");
					echo($indent.'		</div>'."\n");
				}
			}

			if ($params['topic']!="") echo($indent.'				<input type="hidden" name="topic" value="'.$params['topic'].'" />'."\n");
			echo($indent.'				<input type="hidden" name="page" value="'.$params["page"].'"/>'."\n");
            echo($indent.'				<input type="submit" class="submit" value="'.__('search').'"/>'."\n");
			$helptext = __('Here you can find entries of your interest.&lt;br/&gt; You see several options that help you specifying your search for this kind of entry.&lt;br/&gt; Click on the symbol to the left of the option to in- or exclude it into your search. Several keywords are implicitely connected by AND.');
			writeHelpLink($indent, $helptext);
			echo($indent.'		</fieldset>'."\n");
			echo($indent.'		'."\n");
			echo($indent.'	</form>'."\n");
			echo($indent.'</div>'."\n");
		//---------------------end showing search stuff --------------------
		echo($indent.'</div>'."\n");
		}
	}
	
	
	/* writes an index of contents
	*/
	function writeToc($res, $show, $ind=4) {
		$indent = translateIndent($ind);
		global $params;
		$page_info = getPageInfo($params["page"]);
        
		if ($page_info["hide_toc"] == 0 ) {
			echo($indent.'<!-- table of contents-->'."\n");
			echo($indent.'<div id="toc">'."\n");
			echo($indent.'    <div id="toc_content_link_nester"><a id="toc_content_link" href="javascript:toggleVisibility(\'toc_content\',\'toc_content_link\', \''.__('show index').'\', \''.__('hide index').'\');">'.__('show index').'</a></div>'."\n");
			$entity = getEntity($params["page"]);
			if ($show) $display = 'block'; else $display = 'none';
			echo($indent.'    <div id="toc_content" style="display:'.$display.';">'."\n");
			$nind = $ind + 1;
			writeEntries($res, false, $nind, true);
			echo($indent.'    </div>'."\n");
			echo($indent.'</div>'."\n");
		}
	}
	
	/* does grouping if needed and puts out entries
	   using writeEntry(). resets the result set when it's done.
	   $results - an array of result sets, indexed by page names
	   $listview - true if an admin list should be written
	   $as_toc - true if a table of contents should be written
	*/
	function writeEntries($results, $listview, $ind=5, $as_toc=false) {
		$indent = translateIndent($ind);
		global $params;
		global $debug;
		
		if (!$as_toc) writeSearchInfo();
		

		foreach (array_keys($results) as $respage){
			$res = $results[$respage];
			$entity = getEntity($respage);
			//all that grouping stuff...
			if ($entity["group"] != "" or $as_toc) {
				$group_field_save = "foo";	//initial
				if ($params["page"]!='_search') $before_first_entry = true;
                else $before_first_entry = false; //if we don't find sthg while searching pagewide, no sone cares 
				if ($as_toc) { 
					$html_type = "ul";
					$html_type2 = "li";
					// foreign keys linking here? show them in the tree
					$rt = getReferencingTableData($entity);
					// first we collect the data that might be linking here
					if (count($rt)>0){
						for($x=0;$x<count($rt);$x++) { 
							// get the values we need
							if ($rt[$x]['table_name'] != ""){
								$q = "SELECT ".getPKName($rt[$x]['table_name'])." as pk, ".$rt[$x]['title_field']." as tf, ".$rt[$x]['fk']['field']." as f FROM ".$rt[$x]['table_name'];
								//singlepages can operate on the page level whith all data being in one table...
								if (isSinglepage($rt[$x]['fk']['page'])) $q .= " WHERE pagename = '".$rt[$x]['fk']['page']."'";
								$fk_result = pp_run_query($q);
								$fk_rows = array();
								while($fk_row = mysql_fetch_array($fk_result, MYSQL_ASSOC)) {
									$fk_row['fk_page'] = $rt[$x]['likely_page']; //we'll need this to point there
									$fk_rows[] = $fk_row;
								}
								$rt[$x]['rows'] = $fk_rows;
							}
						}
						
					}
				}else {
					$html_type = "div";
					$html_type2 = "h2";
				}
				//this is only done by default before the first entry
				echo($indent.'<'.$html_type.' class="group">'."\n");
			}else {
				$before_first_entry = true; //we dont want an error message then
			}
			
			
			while($row = mysql_fetch_array($res, MYSQL_ASSOC))  {
                
                // ---- filter out text search results that only are found in tags ----
                $kws = getSearchKeywords();
                if ($params["search"] != "") {
                    $good_hit = false; //negative assumption
                    // date search
                    if ($entity["search"]["year"] == '1' and eregi('-'.$params["search"]["y"], $row[$entity["date_field"]["name"]])) $good_hit = true;
                    if ($entity["search"]["month"] == '1' and eregi('-'.$params["search"]["m"].'-', $row[$entity["date_field"]["name"]])) $good_hit = true;
                    
                    foreach ($entity["fields"] as $f) {
                        // valuelisted fields
                        if ($params["search"][$f["name"]] != "" and $f["valuelist"] != "") {
                            if ($params["search"][$f["name"]] == $row[$f["name"]])  $good_hit = true;
                        }
                        // now include non-markup text hits
                        if ($params["search"]["kw"]!="" and isTextType($f["data_type"])) {
                            foreach($kws as $kw)
                                if (eregi($kw, strip_tags($row[$f["name"]]))) $good_hit = true;
                        }
                    }
                    if (!$good_hit) continue;
                }
                
				//more grouping stuff...
				//if not singlepage, group "standard"
				if ($entity["group"] != "" or $as_toc) {
					// get heading
                    $heading = $row[$entity["group"]["field"]];
                    // for comments, display the title of the entrys that got commented
                    if ($params["page"]=='_sys_comments') {
                        $tmp_entity = getEntity($row["pagename"]);
                        $query = "SELECT ".$tmp_entity["title_field"]." AS title, ".$tmp_entity["pk"]." AS pk FROM ".$tmp_entity["tablename"]." WHERE ".$tmp_entity["pk"]." = ".$row["pageid"];
                        $tmp_result = pp_run_query($query);
                        $tmp_row = mysql_fetch_array($tmp_result, MYSQL_ASSOC);
                        $heading = '<a href="?'.urlencode($row["pagename"]).'&amp;nr='.$tmp_row["pk"].'">'.$tmp_row["title"].'</a>';
                    }
					if ($before_first_entry == true) {
						//heading
						if (!(isSinglepage($params["page"]) and $row[$entity["group"]["field"]] == "standard") and $row[$entity["group"]["field"]] != "")
							echo($indent.'	<'.$html_type2.' class="group_heading">'.$heading.'</'.$html_type2.'>'."\n");
					} else if ($group_field_save != $row[$entity["group"]["field"]]) {
						//write end of group div or ul
						echo($indent.'</'.$html_type.'>'."\n");
						//write a new group header						
						echo($indent.'<'.$html_type.' class="group">'."\n");
						if ($row[$entity["group"]["field"]] != "")
							echo($indent.'	<'.$html_type2.' class="group_heading">'.$heading.'</'.$html_type2.'>'."\n");
					}
					//save actual value
					$group_field_save = $row[$entity["group"]["field"]];
				}
                
                if ($before_first_entry == true) $before_first_entry = false; //indicates we indeed had data
                        
				//this is what we want to do basically...
				if ($as_toc) {	//we need only titles here
					$name = getTitle($entity,$row);
					$name = preserveMarkup($name);
					echo($indent.'	<li class="link"><a href="#'.buildValidIDFrom($name).'">'.$name.'</a></li>'."\n");
					// show referencing table stuff
                    if (count($rt)>0 and count($fk_rows)>0){
                        echo($indent.'		<ul class="fk_link">'."\n");
                        for ($x=0;$x<count($rt);$x++){
                            foreach($fk_rows as $fk_row){
                                if ($row[$rt[$x]['fk']['ref_field']] == $fk_row['f']) 
                                    echo($indent.'			<li><a href="?'.$fk_row['fk_page'].'&amp;nr='.$fk_row['pk'].'">'.$fk_row['tf'].'</a></li>'."\n");
                            }
                        }
                        echo($indent.'		</ul>'."\n");
                    }
				} else {	//here we need some sophisticated stuff: the whole entry
					writeEntry($row, $respage, $listview, $ind);
				}
			}
			
			//even more grouping stuff...
			if ($entity["group"] != "" or $as_toc) {	//write end of last group div
				echo($indent.'</'.$html_type.'>'."\n");
			}
			//if there was no data, give a hint
			if ($params['page'] != '_search' and $before_first_entry == true and !$as_toc) {
				echo($indent.'<div class="sys_msg">'.__('No fitting entry in the database was found...').'</div>'."\n");
			}
			//reset result set
			if (mysql_num_rows($res) > 0)mysql_data_seek($res,0);
		}
		
	}
    
	
	/* writes out an entry in a div with class "show_entry"
	   params:
	   * $row - the db result row
       * $pagename - name of the page this appears on
	   * $indent - the number of indents to put before
	   * $list_view - true when only the title field is shown
	*/
	function writeEntry($row, $pagename, $list_view, $ind=5) {
		$indent = translateIndent($ind);
		global $params, $debug;
		$entity = getEntity($pagename);
		$page_info = getPageInfo($pagename);
        
		//quickhack - normally comments do have a title field but not here
        //no options here for comments - takes too much space
		if (!$list_view and $params["page"] == '_sys_comments') {
            $entity["title_field"] = "";
            $page_info["hide_options"] = 1;
        }
		
		//we'll use this to forward that we had a group request
		if ($params["group"] != "") $group_forward = 'group='.urlencode($row[$entity["group"]["field"]]);
		
        if ($params["page"]=="_sys_comments" and $params["cmd"]=="preview") echo($indent.'<div id="comments">');
        
		if (!$list_view) {
			if ($page_info["hide_options"] == 0 and !($params['page']=='_search' or $params["cmd"] == "_search" )) 
                echo($indent.'<div class="show_entry_with_options">'."\n");
			else echo($indent.'<div class="show_entry">'."\n");
		}
		else echo($indent.'<div class="list_entry">'."\n");
		
		if (!$list_view) {	//write an anchor
			$name = getTitle($entity,$row);
			//text that doesn't come from a text area still must be escaped
			if ($entity["title_field"] != "") echo($indent.'	<a class="target" id="'.buildValidIDFrom($name).'"></a>'."\n");
		}
		
		$briefly = false;	//turns true when some fields are not shown
		$the_url = '?'.urlencode($pagename).'&amp;nr='.$row[$entity['pk']];
        
       
        
		if ($entity["fields"] != "") {
            //we always want the title first when we show search results
            if($entity["title_field"]!="" and ($params['page']=='_search' or $params["cmd"] == "_search" )) {
                $title = strip_tags($row[$entity["title_field"]]);
                
                if ($title == '') $title = getFirstWords($row[guessTextField($entity)], 5);
                foreach(getSearchKeywords() as $k){
                    $title = eregi_replace(escape_regex($k),'<span class="high">'.escape_regex($k).'</span>', $title);
                }
                echo('<a href="'.$the_url.'">'.$title.'</a><br/>'."\n");
            }
                    
			uasort($entity["fields"], "cmpByOrderIndexAsc");
			foreach ($entity["fields"] as $f) {
                
				if (($entity["group"] == "" or $f["name"] != $entity["group"]["field"]))
				$content = $row[$f["name"]];
				if ($f["not_brief"] != "1") {
					$not_brief = false; 
				}else {
					$not_brief = true;
					$something_was_not_brief = true;
				}
                
                //first handle search: show only stuff that was searched for
                if ($params['page']=='_search' or $params["cmd"] == "_search"){
                    if ($f["name"]!=$entity["title_field"]){
                        if (
                            (isDateType($f['data_type']) and ($params["search"]["m"] != "" or $params["search"]["y"] != "")) 
                            or in_array($f['name'], getListOfValueListFields("")) and ($params["search"][$f['name']]!=""))
                        {
                            echo($f['name'].":".$row[$f['name']]."<br/>\n");
                        }
                        //highlight search keywords
                        else if (isTextType($f['data_type']) and $params["search"]["kw"] != "") {
                            
                            $content = strip_tags($row[$f['name']]);
                            foreach(getSearchKeywords() as $k){
                                $hits = spliti(escape_regex($k),$content);
                                $hit_cnt = count($hits);
                                if($hit_cnt>1){
                                    for($x=0;$x<$hit_cnt;$x++) $hits[$x] = preg_split('/\s/',$hits[$x], -1);
                                    for($x=1;$x<$hit_cnt;$x++) {
                                        echo("...");
                                        $wrd_cnt = count($hits[$x-1]);
                                        if($x>0) for($y=6;$y>0;$y--) echo(' '.$hits[$x-1][$wrd_cnt-$y]);
                                        echo('<span class="high">'.$k.'</span>');
                                        if($x<$hit_cnt) for($y=0;$y<6;$y++) echo($hits[$x][$y].' ');
                                        echo('...<br/>'."\n");
                                    }
                                }
                            }
                        }
                    }
                    
                    
                //now to "normal" showing:
				//show field only when it is brief and not the only entry and not grouping criteria
				}else if($f["name"] != $entity["group"]["field"] and ($not_brief == false or $params["step"] == 1)) {
					//another obstacle: in $list_view, we only show titles
					//and: no fields described as "hidden" within the entity or (more important) the (multi)page (for the title field in list view its ok though)
					$hidden_fields = utf8_explode(",",$entity["hidden_fields"]);
					if(isMultipage($params["page"])) {
						$hidden_fields = array_merge($hidden_fields, utf8_explode(',',$page_info["hidden_fields"]));
					}
					if ((!$list_view or $entity["title_field"] == $f["name"]) and
						(!(in_array($f["name"], $hidden_fields)) or $entity["title_field"] == $f["name"] ))	{
						// type - dependent operations on field content
						
						//text that doesn't come from a text area still must be escaped before showing
						$unescaped_content = $content; // save for later
						if(isTextType($f['data_type']) and !isTextAreaType($f['data_type'])) { 
							$content = preserveMarkup($content);
						}
						//format dates
						if (isDateType($f['data_type'])) {
							$content = format_date($content);
						}
						//boolean into HTML
						if ($f['data_type'] == "bool") {  
							if ($content == 1) {$content="yes";} else if ($content == 0) {$content="no";}
						}
						
						
						if($f["name"] == $entity["title_field"] and $list_view) {	//show some symbols for quick glance
							echo($indent.'	<div class="adop">');
							//option _sys_pages can be two things.
							if ($pagename == "_sys_pages") {
								if (isSinglepage($unescaped_content)) $page = "_sys_singlepages"; 
								else if (isMultipage($unescaped_content)) $page = "_sys_multipages";
								else $page = "_sys_singlepages"; //we shouldn't come here, well, take the more probable
							} else {
								$page = $pagename;
							}
							//make it no longer than 14 words
							$content = trim(getFirstWords($content, 14));
							//for entries on pages we can say if they are public
							if (!utf8_strpos($params["page"], 'pages') and $params["page"] !='_sys_fields') {
								$linkText = __('This entry is viewable to the public');
								$pic = "eye.gif";
								if($entity['publish_field'] != "" and $row[$entity['publish_field']] == "0") {
									$linkText = __('This entry is not viewable to the public');
									$pic = "ceye.gif";
								}
								echo($indent.'		<span class="list_pic"><a title="" onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" onfocus="this.blur()"><img src="../style/pics/'.$pic.'"/></a></span>'."\n");
								
							// a link to fields
							}
							if ($pagename == "_sys_pages") {
								$linkText = __('make extra statements about fields of this page (a label, a list of possible values etc.)');
								$the_href = '?_sys_fields&amp;group='.$content.'&amp;from=list&amp;topic=fields';
								echo($indent.'		<span class="list_pic"><a title="" onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" onfocus="this.blur()" href="'.$the_href.'"><img src="../style/pics/fields.gif"/></a></span>'."\n");
							}
							$the_href = 'edit.php?'.urlencode($page).'&amp;cmd=show&amp;nr='.$row[$entity["pk"]].'&amp;'.$group_forward.'&amp;from=list&amp;topic='.$params["topic"].'&name='.$content;
							echo($indent.'		<span class="list_pic"><a title="" onmouseover="popup(\''.__('edit this entry.').'\')" onmouseout="kill()" onfocus="this.blur()" href="'.$the_href.'"><img src="../style/pics/edit.png"/></a></span>'."\n");
							$the_href = 'edit.php?'.urlencode($page).'&amp;cmd=delete&amp;nr='.$row[$entity["pk"]].'&amp;'.$group_forward.'&amp;old_formfield_name='.getTitle($entity,$row).'&amp;from=list&amp;topic='.$params["topic"];
							//check if we should give the old name for consistency reasons
							$consistency_fields = utf8_explode(",",$entity["consistency_fields"]);
							if (in_array($f["name"],$consistency_fields)) $the_href = $the_href.'&amp;old_name='.$row[$f["name"]];
							echo($indent.'		<span class="list_pic"><a onclick="return checkDelete();" title="" onmouseover="popup(\''.__('delete this entry.').'\')" onmouseout="kill()" onfocus="this.blur()" onclick="return checkDelete();" href="'.$the_href.'"><img src="../style/pics/no.gif"/></a></span>'."\n");
							echo($indent.'	</div>');
						}
						
						//comments have neat markup
						if ($params["page"]=='_sys_comments' and $f['name']!='comment'){
							if ($content != ""){
								if ($f['name']=='name') $prefix = "from";
								else if ($f['name']=='insert_date') $prefix = "on";
								else if ($f['name']=='email') $prefix = "email";
								else if ($f['name']=='www') {
                                    $prefix = "www";
                                    if (substr($content,0,7) != "http://") $content = "http://".$content;
                                    $content = '<a rel="nofollow" href="'.$content.'">'.$content.'</a>';
                                }
								else $prefix = "";
								echo($indent.'	<span class="comment_prefix">'.$prefix.'</span><span>'.$content.'</span>'."\n");
							}
						}else{
							
							//no div when title is empty
							if($f["name"] == $entity["title_field"] and $content == "")
								continue;
								
							//this id can be used by users to access individual Elements with their CSS 
							echo($indent.'	<div class="'.$entity["tablename"].'_'.$f["name"].'">'."\n");
		
							if ($entity["hide_labels"] == "0" and !$list_view and $f["name"] != $entity["title_field"]) {
								echo($indent.'		<div class="label">');
								if ($f['label'] != "") echo($f['label']); else echo($f['name']);
								echo('</div>'."\n");
							}
							
                            // data_type = file is a backdoor hack to get a file chooser in the form.
                            // Here, the best guess at this time is that it is an image, but that might evolve
                            if($f['data_type'] == 'file') $content = '<img src="'.$content.'"/>';
                            
							//now, finally, the value
							if($f["name"] == $entity["title_field"]) {
								$theClass = "title";
							} else $theClass = "value"; 
							
							echo($indent.'		<div class="'.$theClass.'">'."\n");
							echo($indent.'			');	//indent
							if($f["name"] == $entity["title_field"] and !$list_view) {	//make a link
								echo('<a class="entry_title_link" href="'.$the_url.'">');
							}
                            
                            if (strstr($f['embed_in'], '[CONTENT]') and $f['embed_in'] != "") $content = str_replace('[CONTENT]', $content, $f['embed_in']);
							echo($content);
                            
							if($f["name"] == $entity["title_field"] and !$list_view) {	//close a link
								echo('</a>'."\n");
							}else echo("\n");
							
							echo($indent.'		</div>'."\n");
							
							echo($indent.'	</div>'."\n");
						}
					}
				}
			}
		}

		if (!$list_view and $something_was_not_brief == true and $params["step"] != 1) { 	//show a link to the whole entry
			$wlink = "?".$pagename.'&amp;nr='.$row[$entity["pk"]];
			echo($indent.'	<div class="whole_link"><a href="'.$wlink.'">&gt;&gt;'.sprintf(__('show whole entry')).'</a></div>'."\n");
		}
		
        if ($params["page"]=="_sys_comments" and $params["cmd"]=="preview") echo($indent.'</div>');
        
        if (!$list_view and $params["page"]!="_search") showRelatedEntries($row[$entity['pk']], ++$ind);
        
		if (!$list_view and $params["cmd"]!="_sys_comments" and !($params['page']=='_search' or $params["cmd"] == "_search")) {
			if ($page_info["hide_options"] == 0 ) {
				echo($indent.'    <div class="options">'."\n");
				echo($indent.'	    <span class="edit">'."\n");
				$sys_info = getSysInfo();
				if ($sys_info['hide_public_popups']==0) $text='onmouseover="popup(\''.__('for admins: edit this entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" ';
				else $text = "";
				echo($indent.'	    <span class="admin_link"><a '.$text.'href="admin/edit.php?'.$pagename.'&amp;cmd=show&amp;nr='.$row[$entity["pk"]].'&amp;'.$group_forward.'">#</a></span>'."\n");
				if($entity["date_field"]["editlabel"] != "") { //show last editing date
					if ($row[$entity["date_field"]["editlabel"]] != NULL) {
						$ed = format_date($row[$entity["date_field"]["editlabel"]]);
						if ($ed != __('no date set yet')) 
							echo($indent.'		    <span class="last_edited_label">'.__('last edited:').' '.$ed.'</span>'."\n");
					}
				}
			}
			
			if ($page_info["commentable"] == "1") {
				
				$params["nr"] = $row[$entity["pk"]];
				$comments = getComments();
				if ($comments == "") $comment_count = 0;
				else $comment_count = mysql_num_rows($comments);
				if ($params["step"] != 1) {
					if($comment_count > 0) {
						$href = '?'.$pagename.'&amp;nr='.$params["nr"].'#comments_anchor';
						echo($indent.'	    <span class="comment_link"><a href="'.$href.'">'.__('comments').'('.$comment_count.')</a></span>'."\n");
					} else {
						$href = '?'.$pagename.'&amp;nr='.$params["nr"].'#commentform_anchor';
						echo($indent.'	    <span class="comment_link"><a href="'.$href.'">'.__('add a comment').'</a></span>'."\n");
					}
                    
				} else {
					
                }
			}
			
			//closing tags
			if ($page_info["hide_options"] == "0" ) {
				echo($indent.'	    </span>'."\n");
				echo($indent.'    </div>'."\n");	//end option div
			}
			
			//show comments
			$nind = $ind + 1;
			if ($page_info["commentable"] == "1" and $params["step"] == 1) {
                
                $href='javascript:document.edit_form._formfield_name_input.focus();';
                echo($indent.'	<span class="comment_link"><a id="comment_link" href="'.$href.'">'.__('add a comment').'</a></span>&nbsp;&nbsp;'."\n");
                echo($indent.'  <span class="comment_rss">'."\n");
                echo($indent.'      <a href="rss.php?p='.$pagename.'&amp;nr='.$params["nr"].'&amp;channel=comments">follow comments per RSS</a>'."\n");
                $helptext = "This Link gives you an RSS feed that tracks all comments on this entry. That way you can be follow the discussion without always coming here to check for new comments.";
                writeHelpLink($indent."     ",$helptext);
                echo($indent.'  </span>'."\n");
                
                echo($indent.'<div id="comments"><a class="target" name="comments_anchor"></a>'."\n");
                
				if($comment_count > 0) {
					writeComments($comments, $nind);
				}
                
				writeCommentForm($nind);
                echo($indent.'</div>'."\n");
			}
		}
        echo($indent."</div>"."\n");
	}
	
    
    /* show a list of values that are referenced from this table via a relational table
        (where this table links to the first field)
    */
    function showRelatedEntries($id, $ind){
        $indent = translateIndent($ind);
        //global $params;
        $entity = getEntity();
        
        $can = getRelationCandidatesFor($entity['tablename']);

        foreach ($can as $c) { 
            if ($c[1] <= 2){
                $query = 'SELECT '.$c[2][1]['fk']['field'].', (SELECT '.$c[2][1]['title_field'].' FROM '.$c[2][1]['fk']['ref_table'];
                $query .= ' WHERE '.$c[2][1]['fk']['ref_field'].' = '.$c[2][1]['fk']['table'].'.'.$c[2][1]['fk']['field'].') AS Title';
                $query .= ' FROM '.$c[2][0]['fk']['table'].','.$c[2][0]['fk']['ref_table'];
                $query .= ' WHERE '.$c[2][0]['fk']['table'].'.'.$c[2][0]['fk']['field'].' = '.$c[2][0]['fk']['ref_table'].'.'.$c[2][0]['fk']['ref_field'];
                $query .= ' AND '.$c[2][0]['fk']['table'].'.'.$c[2][0]['fk']['field'].' = '.$id.';';
                //run Query
                $res = pp_run_query($query);
                if (mysql_errno(getDBLink()) != 0 or mysql_num_rows($res) == 0)  continue;
                
                echo($indent.'<div class="related"><h4>'.__('Related ').$c[2][1]['fk']['ref_table'].':</h4>'."\n");
                echo($indent.'  <ul>'."\n");
                while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                    echo($indent.'      <li><a href="?'.$c[2][1]['likely_page'].'&amp;nr='.$row[$c[2][1]['fk']['field']].'">'.$row['Title']."</a></li>\n");
                }
                echo($indent.'  </ul>'."\n");
                echo($indent.'</div>'."\n");
            }
        }
    }
    
    
	/* gets a resultset of comments according to params
	*/
	function getComments() {
		global $params;
		//get Query
		$params["cmd"] = $params["cmd"]." _sys_comments"; 
		$query = getQuery(true);
		//run Query
		$res = mysql_query($query[$params["page"]], getDBLink());
		if (mysql_errno(getDBLink()) == 0) {
			return $res;
		} else return "";
	}
	
	/* writes all comments that are given as result set
	*/
	function writeComments($comments, $ind=5) {
		$indent = translateIndent($ind);
		global $params;
		
		//save what page we're on
		$page = $params["page"];
		//use comments as page while writing them
		$params["page"] = "_sys_comments";	
		//write the results
		while($row = mysql_fetch_array($comments, MYSQL_ASSOC)) {
            echo($indent.'  <a class="target" name="comment'.$row['id'].'">&nbsp;</a>'."\n");
			writeEntry($row, '_sys_comments', false, ++$ind);
		}
		
		//set param back
		$params["page"] = $page;
	}
	
	/* writes a comment form
	*/
	function writeCommentForm($ind) {
		global $params;

		//fill into $values what we know about this page and entry -
		//writeHTMLForm will look it up...
		$params["values"]["nr"] = $params["nr"];
		$params["values"]["pagename"] = $params["page"];
		$params["values"]["pageid"] = $params["nr"];
		
		//now "instruct" other code what we intend
		$params["cmd"] = "new";
        $swap_page = $params["page"];
		$params["page"] = "_sys_comments";	//we can do this because comments come last
		writeHTMLForm("", ".", false, true, $ind, "commentform");	//no dataset needed
        $params["page"] = $swap_page;
	}
	
	/**
	 * returns an empty string if this comment can be
	 * written to the db, and an error message otherwise
	 * 
	 * @param comment the entered text
	 * @param time the time it took in milliseconds
     * @param nogarbageplease the content of a hidden field that should not be ssen by humans (but by machines)
	 */
	function checkComment($comment, $time, $nogarbageplease) {
        // check 1: a field hidden by CSS that humans can't see. Only machines would see it and fill it out.
        if ($nogarbageplease != "")
            return __('Sorry, your comment has not been entered due to our internal spam filtering. Please try again.').'<a href="javascript:back()">Go back.</a>';
        // check 2: Entering comments too fast lets me think it has been done by a machine
		if ($time < 1000 and $time != '') 
			return __('wow, you sure entered your comment quick. So quick, actually, that I labeled you as a machine and your comment as spam. Your comment has not been saved.');
		$stripped_comment = strip_tags($comment, '<b><i><ul><ol><li><br><p><strong><em>');
		if ($comment != $stripped_comment) 
			return __('Your text contains tags that are not allowed. You can use one of those: &lt;b&gt;&lt;i&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;br&gt;&lt;p&gt;&lt;strong&gt;&lt;em&gt;. Your comment has not been saved.');
		// check 3: was there a reCAPTCHA response?
        $php_version = explode('.', phpversion());
        $sys_info = getSysInfo();
        if ($sys_info['use_captchas'] == 1) {
            if ($_POST["recaptcha_response_field"]){ 
                $resp = recaptcha_check_answer($sys_info['private_captcha_key'],
                                          $_SERVER["REMOTE_ADDR"],
                                          $_POST["recaptcha_challenge_field"],
                                          $_POST["recaptcha_response_field"]);
                if (!$resp->is_valid) return __('You failed the Captcha: ').$resp->error;
            }else if ($_POST["recaptcha_response_field"] == "") return __('You failed the Captcha. Provide a solution, please.');
        }
        return "";
	}
?>
