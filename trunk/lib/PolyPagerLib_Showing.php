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
	* getShowParameters()
	* getMaxNr()
	* writeSearchInfo($ind)
	* getQuery($only_published)
	* writeToc($res, $show, $ind)
	* writeSearchForm($show, $ind)
	* highlight($kw, $text)
	* writeEntry($row, $indent, $list_view, $ind)
	* writeEntries($res, $list_view, $as_toc, $ind)
	* getComments() 
	* writeComments($comments, $ind)
	* writeCommentForm($ind)
	*/
	
	/* returns the highest entry number of the entity's table
	*/
	function getMaxNr($params) {
		global $_POST;
		global $_GET;
		global $debug;
		if ($debug) {echo '				<div class="debug">getting max for:|'.$params["page"].'| and I know it:'.isAKnownPage($params["page"]).'</div>'; }
		if ($params["page"] != '_sys_pages' and isAKnownPage($params["page"])) {
			$entity = getEntity($params["page"]);
			$page_info = getPageInfo($params["page"]);
			$max = $_POST["max"];	//get max from request - POST
			if ($max == "") { $max = $_GET["max"]; } //coming in per GET?
			//reading the number of entries, if not give
			if ($max == "" and $entity != "" and !(!isASysPage($params["page"]) and isMultipage($params["page"]) and $page_info["tablename"] == "") ) {
				$query = "SELECT max(".$entity["pk"].") AS maxnr FROM ".$entity["tablename"].";";
				$res = mysql_query($query, getDBLink());
				$error_nr = mysql_errno(getDBLink());
				if ($debug) {echo '				<div class="debug">Query was: '.$query.'</div>'; }
				if ($error_nr != 0) {
					$fehler_text = mysql_error(getDBLink());
					echo "				<br />DB-Error: $fehler_text<br />\n";
					
				}
				$row = mysql_fetch_array($res, MYSQL_ASSOC);
				$max = $row["maxnr"];
			}
		}
		if ($max == "") $max = "0";	//better than nothing, and indeed, there is nothing
		return $max;
	}
	
	/*
		returns an Array of Parameters for showing:
		["page"=>""), "cmd"=>""), "nr"=>""), "step"=>""), "group"=>""]
		I know that PHP stores the params in variables with the name accordingly,
		but I do not have control over all those variable names for some depend
		on configurated db field names. So I go this way to store them.
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
		
		$params = array();
		
		//-------------------------topic (for admin list)
		$params["topic"] = $_POST[topic];
		if ($params["topic"] == "") {$params["topic"] = $_GET[topic];}
		
		//------------------------ the page name
		$params["page"] = urldecode($_POST["page"]);
		
		if ($params["page"] == "") {
			//the "page" param will be just the first in GET Requests (so we can write http://www.bla.com/?mypage)
			$query_array = explode('&', $_SERVER["QUERY_STRING"]);
			$params["page"] = urldecode($query_array[0]);
			//if "page=" is given we can handle this, too
			if (strpos($query_array[0], "page=") !== false) $params["page"] = urldecode($_GET["page"]);
		}
		if ($params["page"] == "") {
			$sys_info = getSysInfo();
			$params["page"] = $sys_info["start_page"];
		}
		//-------------------------nr param
		$params["nr"] = $_POST[nr];	//starting point
		if ($params["nr"] == "") $params["nr"] = $_GET[nr];  //coming in per GET?
		//pages need a max nr
		$params["max"] = getMaxNr($params);
		if ($params["nr"] == "" and isMultipage($params["page"])) { $params["nr"] = $params["max"]; }	//no preferation: start with highest entry

		//-------------------------cmd param
		$params["cmd"] = $_POST[cmd];		//commands: show|search|Show month|Show year
		if ($params["cmd"] == "") {$params["cmd"] = $_GET[cmd];}
		if ($params["cmd"] == "") {$params["cmd"] = "show";}	//(default)
				
		$entity = getEntity($params["page"]);

		//-------------------------step param
		$default_step = $entity["step"];				//show this much on a page, could be a number or "all"
		if ($default_step == "") $default_step = "all";
		//1. normally one should be shown - but now we show all, briefly
		if ($default_step == "1" and ($_POST["nr"] == "" and $_GET["nr"] == "")) $params["step"] = "all";
		//2. a nr is given - show only this entry
		if ($_POST["nr"] != "" or $_GET["nr"] != "") $params["step"] = "1";
		//3. coming in explicitly
		if ($_GET["step"] != "") $params["step"] = $_GET["step"];	//coming in per GET?
		if ($_POST["step"] != "") $params["step"] = $_POST["step"]; //coming in per POST?
		//nothing found yet? use default
		if ($params["step"] == "") $params["step"] = $default_step;
		
		//-------------------------group param
		$params["group"] = $_GET["group"];	//show only this group
		if ($params["group"] == "") { $params["group"] = $_POST["group"]; } //coming in per POST?
		if ($params["group"] == "" and isSinglepage($params["page"])) {	
			//in singlepages, group is called anothe name for db reasons
			$params["group"] = $_GET["the_group"];
			if ($params["group"] == "") { $params["group"] = $_POST["the_group"]; }
		}
		//take default group if there hasn't been a special one requested
		if ($params["group"] == "" and $params["nr"] == "") {
			$page_info = getPageInfo($params["page"]);
			$glist = getEntityField('the_group');
			//default group when the user had a choice between groups for this page
			if ($glist['valuelist'] != 'standard,') $params["group"] = $page_info["default_group"];
		}
		
		
		//Search
		if ($params["cmd"] == "search") {	//search
			$had_value = false;
			$search = array();
			if ($entity["search"]["range"] == "1") {}//range has no parameters we haven't covered already
			if ($entity["search"]["keyword"] == "1") {
				$search["kw"] = $_POST["kw"];
				if ($search["kw"] == "") $search["kw"] = $_GET["kw"];
				if ($search["kw"] != "") $had_value = true; 
			}
			//if (isMultipage($params["page"])) {
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
						$search[$f["name"]] = $_POST['_search_'.$f["name"]];
						if ($search[$f["name"]] == "") $search[$f["name"]] = $_GET['_search_'.$f["name"]];
						if ($search[$f["name"]] != "") $had_value = true;
					}
				}
			//}
			if ($had_value) $params["search"] = $search;
		}
		if ($debug) { echo('				<div class="debug">page param is: '.$params["page"].', topic param is: '.$params["topic"].'</div>'."\n"); }
		return $params;
	}
	
	/*
	 * puts out what search has been done.
	 */
	function writeSearchInfo($ind=4) {
		$indent = translateIndent($ind);
		global $params;
		if ($params["search"] != "") {
			echo($indent.'<div class="sys_msg" id="searchinfo"><h4>'.__('you searched for:').'</h4><ul>');
			//foreach($params["search"] as $sf) {
			foreach($params["search"] as $name => $val) {
				if ($val != "") echo($indent.'	<li>'.$name.':'.$val.'</li>');
			}
			echo($indent.'</ul></div>');
		}
	}
	 
	/*
		build (and return) a MYSQL Query
		$params: an array of parameters for showing (see getParameters())
		         if anything else than page content is to be shown (i.e.comments,
				 pages themselves,...), add
				 what you want to $params["cmd"] !!
		$only_published: (boolean) true if only published entries
	*/
	function getQuery($only_published) {
		global $params;
		$entity = getEntity($params["page"]);
		$pagename = $params["page"];
		$page_info = getPageInfo($params["page"]);
		$sys_info = getSysInfo();
		global $debug;
		
		// ---------- first the easy cases: 
		
		// all comments
		if (strpos($params["cmd"], "_sys_comments_all") > 0) {
			$entity = getEntity("_sys_comments");
			$theQuery = "SELECT * FROM _sys_comments
						WHERE is_spam = 0
						ORDER BY insert_date DESC";
		}
		
		// comments for one entry
		else if (strpos($params["cmd"], "_sys_comments") > 0) {
			$entity = getEntity("_sys_comments");
			$theQuery = "SELECT * FROM _sys_comments
						WHERE pagename = '$pagename'
						AND pageid = ".$params["nr"]."
						AND is_spam = 0
						ORDER BY insert_date ASC";
		}
		
		
		
		// feeds
		else if (strpos($params["cmd"], "_sys_feed") > 0) {
			$entity = getEntity("_sys_feed");
			$theQuery = "SELECT * FROM _sys_feed 
						ORDER BY edited_date DESC";
		}
		
		// pages - always select all of them (user doesn't have to see the distinction)

		else if ((strpos($params["cmd"], "_sys_multipages") > 0)
			or (strpos($params["cmd"], "_sys_singlepages") > 0)
			or($entity["tablename"] == "_sys_pages")) {
			$theQuery = "SELECT id, name, in_menue FROM _sys_multipages UNION
							SELECT id, name, in_menue FROM _sys_singlepages ORDER BY name";
		}
		
		else {
			//if we have a multipage without a table specified, there is nothing we can do
			if (isMultipage($params["page"]) and !isASysPage($params["page"]) and $page_info["tablename"] == "") {
				$theQuery = 'SELECT * FROM _sys_sys WHERE 1=2';	//just a valid joke
			} else {
				//--------------------- preparing  --------------------------
				$a = array();
				$a[0] = "SELECT * FROM ".$entity["tablename"]." ";
					
				if (isMultipage($params["page"])) {
					//helper vars
					if ($params["step"] != "all") {
						$next = $params["nr"] + ($params["step"]-1);
						$prev = $params["nr"] - ($params["step"]-1);
					} else {
						$next = $params["max"];
						$prev = 0;
					}
					if ($prev <= 0) $prev = 0;
					$date_field = $entity["dateField"];
					
					//normal query for "show"			
					
					if ($entity["pk_type"] == "int") $a[1] = " WHERE ".$entity["pk"]." >= $prev AND ".$entity["pk"]." <= ".$next." ";
					else $a[1] = " WHERE ".$entity["pk"]." = ".$params["nr"];
					//show a group rather than id range
					if ($params["group"] != "" and $params["group"] != "_sys_all") {
						$a[1] = " WHERE ".$entity["group"]["field"]." = '".$params["group"]."'";
					}
				} else if (isSinglepage($pagename)) {
					$a[1] = "WHERE pagename = '$pagename' $pub";
					if ($params["nr"] != "") $a[1] = $a[1]." AND id = ".$params["nr"];
					if ($params["group"] != "" and $params["group"] != "_sys_all" and $sys_info["submenus_always_on"] != 0) {
						//"standard" entries are -per definition- always shown!
						$a[1] = $a[1]." AND (the_group = '".$params["group"]."' OR the_group = 'standard')";
					}
				}
				if($params["search"] != "") {
					//special case search - new query
					$a[1] = " WHERE ";
					if($entity["search"]["year"] == '1' or $entity["search"]["month"] == '1') {
						if ($params["search"]["y"] != "" or $params["search"]["m"] != "") {
							$month = $params["search"]["m"];
							$year = $params["search"]["y"];
							if (count($a) != 2) $a[count($a)] = " AND ";
							//if december, increment year for enddate, else only the month
							if ($month == "") {
								$nextYear = $year + 1;
								$a[count($a)] = " ".$entity["dateField"]["name"]." >= '$year-01-01' AND ".$entity["dateField"]["name"]." < '$nextYear-01-01' ";
							} else if ($month == "12") {
								$nextYear = $year + 1;
								$a[count($a)] = " ".$entity["dateField"]["name"]." >= '$year-$month-01' AND ".$entity["dateField"]["name"]." < '$nextYear-01-01' ";
							} else {
								$nextMonth = $month + 1;
								$a[count($a)] = " ".$entity["dateField"]["name"]." >= '$year-$month-01' AND ".$entity["dateField"]["name"]." < '$year-$nextMonth-01' ";
							}
						}
					}
					if($entity["search"]["keyword"] == '1') { //Keyword
						if ($params["search"]["kw"] != "") {
							$keyword = $params["search"]["kw"];
							$keyword_lower = strtolower($keyword);	 //lower/upper-case should not matter in our keyword search!
							if (count($a) != 2) $a[count($a)] = " AND ";
							if (strstr($keyword_lower, "delete ") or strstr($keyword_lower, "update ")) { 	//no critical sql code allowed
								echo('<div class="sys_msg">'.__('please do not use SQL Code here in your keyword search...').'</div>'."\n");
								$a[count($a)] = " 2=1"; //show nothing
							} else {
								$a[count($a)] = " (";
								foreach($entity["fields"] as $f) {
									if(isTextType($f["data-type"])) {
										//BLOB fields are case-sensitive, therefore lcase - see http://forums.devshed.com/t1909/s.html
										$a[count($a)] = " lcase(".$f["name"].") LIKE '%$keyword_lower%' OR ";
									}
								}
								$a[count($a)-1] = substr_replace($a[count($a)-1],'',-3,-1);	//the last OR has to go
								$a[count($a)] = ")";
							}
						}
					}
	
					//valuelisted fields
					
					foreach ($entity["fields"] as $f) {
					
						//if we have a specified valuelist and the name of the field is a name of a search param...
						if ($params["search"][$f["name"]] != "" and $f["valuelist"] != "")
							//((isMultipage($params["page"]) and $f["valuelist"] != "") or 
							//(isSinglepage($params["page"]) and $page_info["grouplist"] != ""))
						   {
						  
							if (count($a) != 2) $a[count($a)] = " AND ";
							$a[count($a)] = $f["name"]." = '".$params["search"][$f["name"]]."'";
							
							//if the field is the group field, we knew that is a request - save it!
							if ($f["name"] == $entity["group"]["field"]) $params["group"] = $params["search"][$f["name"]];
						}
					}
					
					//if we had nothing, make search query valid at least
					if (count($a) == 2) $a[2] = "1=2";
				}
				
				if($only_published and $entity["publish_field"] != "") {	//publish - Flag
					$a[count($a)] = " AND ".$entity["publish_field"]." = 1";
				}
				$theQuery = implode('',$a);
				
				//ORDER BY: 1. grouping, 2. order_by
				$b = array();
				$b[0] = $theQuery;
				if ($entity["group"] == "") $b[1] = " ORDER BY ";
				else $b[1] = " ORDER BY ".$entity["group"]["field"]." ".$entity["group"]["order"].", ";
				if ($entity["order_by"] == "") $b[2] = $entity["pk"]." DESC;";
				else $b[2] = $entity["order_by"]." ".$entity["order_order"].";";
				
				
				$theQuery = implode('',$b);
			}
		}
		if ($debug) { echo('				<div class="debug">the Query is: '.$theQuery.'</div>'."\n"); }
		return $theQuery;
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
							$theLink = "?".$params["page"]."&amp;nr=".$prev."&amp;step=".$step."&amp;max=".$params["max"]."&amp;group=".$params[group];
							$newPrev = $prev - $step + 1;
							$theText = sprintf(__('show entries %s through %s'),$newPrev,$prev);
							echo($indent.'	<a  onmouseover="popup(\''.$theText.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$theLink.'">'.__('previous').'</a>|');
						}else {             //no link to earlier entries possible
							echo('                 <i>'.__('previous').'</i>|'."\n");
						}
						if ($params["nr"] < $params["max"]) {   // link to next entries
							$theLink = "?".$params["page"]."&amp;nr=".$next."&amp;step=".$step."&amp;max=".$params["max"]."&amp;group=".$params["group"];
							$theText = sprintf(__('show entries %s through %s'),$params["nr"],$next);
							echo('                 <a  onmouseover="popup(\''.$theText.'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$theLink.'">'.__('next').'</a>');
						}else {         	//no link to later entries possible
							echo('                 <i>'.__('next').'</i>'."\n");
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
			echo($indent.'	<form class="search" action="'.$theAction.'" method="post"><fieldset>'."\n");
	
			if ($entity["search"]["month"] == "1") { //search for a date
				echo($indent.'		<div class="search_option">'."\n");
				echo($indent.'			<span class="search_toggle"><a id="search_month_link" href="javascript:toggle_ability(\'search_month\');">&nbsp;&nbsp;&nbsp;&nbsp;</a></span>'."\n");
				echo($indent.'			'.__('entered in month').' <select class="search_month" disabled="disabled" name="m" >'."\n");	//month input
				$months = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December'));
				$datum = getdate();
				$actMonth = $datum[mon];
				for ($i = 1; $i <= 12; $i++) {
					if ( number_format($i) !=  number_format($actMonth)) echo('<option value="'.$i.'">'.$months[$i-1].'</option>'."\n");
					else echo($indent.'			<option selected="selected" value="'.$i.'">'.$months[$i-1].'</option>'."\n");
				}
				echo($indent.'			</select> '.__('of year').' <select class="search_month" disabled="disabled" name="y">'."\n");	//year input
				$actYear = $datum[year];
				for ($i = 2000; $i <= $datum[year]; $i++) {
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
				$actYear = $datum[year];
				for ($i = 2000; $i <= $datum[year]; $i++) {
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
				else echo('            			'.__('with keyword:').' <input class="search_kw" disabled="disabled" type="text" size="12" maxlength="24" name="kw" value="'.$params[search][kw].'"/>'."\n");
				echo($indent.'		</div>'."\n");
			}
			//search fields for valuelists
			if ($entity["fields"] != "") foreach ($entity["fields"] as $f) {
				if ($f["valuelist"] != "") {
					echo('                     				<!--'.$f['name'].'search-->'."\n");
					echo($indent.'		<div class="search_option">'."\n");
					echo($indent.'			<span class="search_toggle"><a id="search_'.$f['name'].'_link" href="javascript:toggle_ability(\'search_'.$f['name'].'\');">&nbsp;&nbsp;&nbsp;&nbsp;</a></span>'."\n");
					echo($indent.'			'.$f["name"].': <select class="search_'.$f['name'].'" disabled="disabled" name="_search_'.$f['name'].'">'."\n");	//input
					$vals = explode(',', $f["valuelist"]);
					foreach ($vals as $v) {
						if ($v != $params["search"][$f["name"]]) echo('<option>'.$v.'</option>'."\n");
						else echo($indent.'			<option selected="selected">'.$v.'</option>'."\n");
					}
					echo($indent.'			</select>'."\n");
					echo($indent.'		</div>'."\n");
				}
			}
			echo($indent.'				<input type="hidden" name="max" value="'.$params['max'].'" />'."\n");
			echo($indent.'				<input type="hidden" name="group" value="'.$params['group'].'" />'."\n");
			echo($indent.'				<input type="hidden" name="topic" value="'.$params['topic'].'" />'."\n");
			echo($indent.'				<input type="hidden" name="page" value="'.$params["page"].'"/>'."\n");
			echo($indent.'				<input type="hidden" name="cmd" value="search"/>'."\n");
			echo($indent.'				<button type="submit" class="submit" name="dummy">'.__('search').'</button>'."\n");
			$helptext = __('Here you can find entries of your interest.&lt;br/&gt; You see several options that help you specifying your search for this kind of entry.&lt;br/&gt; Click on the symbol to the left of the option to in- or exclude it into your search.');
			writeHelpLink($indent, $helptext);
			echo($indent.'		</fieldset>'."\n");
			echo($indent.'		'."\n");
			echo($indent.'	</form>'."\n");
			echo($indent.'</div>'."\n");
		//---------------------end showing search stuff --------------------
		echo($indent.'</div>'."\n");
		}
	}
	
	
	/* highlight the search phrase with <span class="high">phrase</span>
	   with some tricky string operations and return it.
	   todo: parse for HTML interruptions, make several keywords possible (pass an array?)
	*/
	function highlight($kw, $text) {
		$kwlen = strlen($kw);
		$kw_lower = strtolower($kw);
		$text_lower = strtolower($text);	//we'll search occurences in here
		//$hits holds number of occurences+1 (first element is the search string!)
		if (eregi($kw, $text, $hits)) {
			$result = array(); //holds the parts of the result String
			$i_h = 1;	//counter for hits
			$i_a = 0;	//counter for the result-array
			$index = 0;
			$hit_c = count($hits)+1;
			while ($i_h < $hit_c) {	
				$hit_index = strpos($text_lower, $kw_lower, $index);	//next hit
				$result[$i_a] = substr($text, $index, $hit_index);
				$result[$i_a +1] = join('',array('<span class="high">',substr($text, $hit_index, $kwlen),'</span>'));
				$i_h++; $i_a += 2;$index = $hit_index;
			}
			$result[$i_a +1] = substr($text, $index + $kwlen, strlen($text) - $index);
			return implode('', $result);
		} else {return $text;}	//no hit - simply return
	}
	
	/* writes an index of contents
	*/
	function writeToc($res, $show, $ind=4) {
		$indent = translateIndent($ind);
		global $params;
		$page_info = getPageInfo($params["page"]);
		echo($indent.'<!-- table of contents-->'."\n");
		if ($page_info["hide_toc"] == 0 ) {
			
			echo($indent.'<div id="toc">'."\n");
			echo($indent.'<div id="toc_content_link_nester"><a id="toc_content_link" href="javascript:toggleVisibility(\'toc_content\',\'toc_content_link\', \''.__('show index').'\', \''.__('hide index').'\');">'.__('show index').'</a></div>'."\n");
			$entity = getEntity($params["page"]);
			if ($show) $display = 'block'; else $display = 'none';
			echo($indent.'<div id="toc_content" style="display:'.$display.';">'."\n");
			$nind = $ind + 1;
			writeEntries($res, false, $nind, true);
			echo($indent.'</div>'."\n");
			echo($indent.'</div>'."\n");
		}
	}
	
	/* does grouping if needed and puts out entries
	   using writeEntry(). resets the result set when it's done.
	   $res - the result set
	   $listview - true if an admin list should be written
	   $as_toc - true if a table of contents should be written
	*/
	function writeEntries($res, $listview, $ind=5, $as_toc=false) {
		$indent = translateIndent($ind);
		global $params;
		global $debug;
		
		$entity = getEntity($params["page"]);
		//all that grouping stuff...
		if ($entity["group"] != "" or $as_toc) {
			$group_field_save = "foo";	//initial
			$before_first_entry = true;
			if ($as_toc) { 
				$html_type = "ul";
				$html_type2 = "li";
			}else {
				$html_type = "div";
				$html_type2 = "h2";
			}
			//this is only done by default before the first entry
			echo($indent.'<'.$html_type.' class="group">'."\n");
		}else {
			$before_first_entry = false; //we don't want an error message then
		}
		
		if (!$as_toc) writeSearchInfo();
		
		while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			
			//more grouping stuff...
			//if not singlepage, group "standard"
			if ($entity["group"] != "" or $as_toc) {// and
				//!(isSinglepage($params["page"]) and $row[$entity["group"]["field"]] == "standard")){
				if ($debug) {echo('<div class="debug">group_field_save is '.$group_field_save.' ...row[$entity["group"]["field"]] is '.$row[$entity["group"]["field"]].'</div>'); }
				if ($before_first_entry == true) {
					$before_first_entry = false; //indicates we indeed had data
					//heading
					if (!(isSinglepage($params["page"]) and $row[$entity["group"]["field"]] == "standard") and $row[$entity["group"]["field"]] != "")
						echo($indent.'	<'.$html_type2.' class="group_heading">'.$row[$entity["group"]["field"]].'</'.$html_type2.'>'."\n");
				} else if ($group_field_save != $row[$entity["group"]["field"]]) {
					//write end of group div or ul
					echo($indent.'</'.$html_type.'>'."\n");
					//write a new group header						
					echo($indent.'<'.$html_type.' class="group">'."\n");
					if ($row[$entity["group"]["field"]] != "")
						echo($indent.'	<'.$html_type2.' class="group_heading">'.$row[$entity["group"]["field"]].'</'.$html_type2.'>'."\n");
				}
				//save actual value
				$group_field_save = $row[$entity["group"]["field"]];
			}
			//this is what we want to do basically...
			if ($as_toc) {	//we need only titles here
				$name = $row[$entity["title_field"]];
				$name = htmlentities($name);
				echo($indent.'	<li class="link">-<a href="#'.str_replace(' ', '_', $name).'">'.$name.'</a></li>'."\n");
			} else {	//here we need some sophisticated stuff
				writeEntry($row, 5, $listview);
			}
		}
		
		//even more grouping stuff...
		if ($entity["group"] != "" or $as_toc) {	//write end of last group div
			echo($indent.'</'.$html_type.'>'."\n");
		}
		//if there was no data, give a hint
		if ($before_first_entry == true) {
			echo($indent.'<div class="sys_msg">'.__('There is no entry in the database meeting the search criteria...').'</div>'."\n");
		}
			//reset result set
			if (mysql_num_rows($res) > 0)mysql_data_seek($res,0);
		
	}
	
	/* writes out an entry in a div with class "show_entry"
	   params:
	   * $row - the db result row
	   * $indent - the number of indents to put before
	   * $list_view - true when only the title field is shown
	*/
	function writeEntry($row, $indent, $list_view, $ind=5) {
		$indent = translateIndent($ind);
		global $params, $debug;
		$entity = getEntity($params["page"]);
		$page_info = getPageInfo($params["page"]);
		
		//quickhack - normally comments do have a title field but not here
		if (!$list_view and $params["page"] == '_sys_comments') $entity["title_field"] = "";
		
		//we'll use this to forward that we had a group request
		if ($params["group"] != "") $group_forward = '&group='.$row[$entity["group"]["field"]];
		
		if (!$list_view) {
			if ($page_info["hide_options"] == 0) echo($indent.'<div class="show_entry_with_options">'."\n");
			else echo($indent.'<div class="show_entry">'."\n");
		}
		else echo($indent.'<div class="list_entry">'."\n");
		
		if (!$list_view) {	//write an anchor
			$name = $row[$entity["title_field"]];
			//text that doesn't come from a text area still must be escaped
			$name = htmlentities($name);
			if ($entity["title_field"] != "") echo($indent.'	<a class="target" id="'.str_replace(' ', '_', $name).'"></a>'."\n");
		}
		
		$briefly = false;	//turns true when some fields are not shown
			
		if ($entity["fields"] != "") {
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
				//show field only when it is brief and not the only entry and not grouping criteria
				if($f["name"] != $entity["group"]["field"] and ($not_brief == false or $params["step"] == 1)) {
					//another obstacle: in $list_view, we only show titles
					//and: no fields described as "hidden" within the entity or the (multi)page
					$hidden_fields = explode(",",$entity["hidden_fields"]);
					if(isMultipage($params["page"])) {
						$hidden_fields = array_merge($hidden_fields, explode(',',$page_info["hidden_fields"]));
					}
					if ((!$list_view or $entity["title_field"] == $f["name"]) and
						!(in_array($f["name"],$hidden_fields)))	{
							
						//text that doesn't come from a text area still must be escaped before showing
						$unescaped_content = $content;
						if(isTextType($f['data-type']) and !isTextAreaType($f['data-type'])) { 
							$content = htmlentities($content);
						}
						//format dates
						if (isDateType($f['data-type'])) {
							$content = format_date($content);
						}
						
						if($f["name"] == $entity["title_field"] and $list_view) {	//show some symbols for quick glance
							echo($indent.'	<div class="adop">');
							//option _sys_pages can be two things.
							if ($params["page"] == "_sys_pages") {
								if (isSinglepage($unescaped_content)) $page = "_sys_singlepages"; 
								else if (isMultipage($unescaped_content)) $page = "_sys_multipages";
								else $page = "_sys_singlepages"; //we shouldn't come here, well, take the more probable
							} else {
								$page = $params["page"];
							}
							//make it no longer than 14 words
							$content = trim(getFirstWords($content, 14));
							if ($params["group"] != "") $group_forward = '&group='.$row[$entity["group"]["field"]];
							//for entries on pages we can say if they are public
							if (!strpos($params["page"], 'pages') and $params["page"] !='_sys_fields') {
								$linkText = __('This entry is viewable to the public');
								$pic = "eye.gif";
								if($entity['publish_field'] != "" and $row[$entity['publish_field']] == "0") {
									$linkText = __('This entry is not viewable to the public');
									$pic = "ceye.gif";
								}
								echo($indent.'		<span class="list_pic"><a title="" onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" onfocus="this.blur()"><img src="../style/pics/'.$pic.'"/></a></span>'."\n");
							// a link to fields
							}
							if ($params["page"] == "_sys_pages") {
								$linkText = __('make extra statements about fields of this page (a label, a list of possible values, foreign key relations etc.)');
								$the_href = '?_sys_fields&amp;group='.$content.'&amp;from=list&amp;topic=fields';
								echo($indent.'		<span class="list_pic"><a title="" onmouseover="popup(\''.$linkText.'\')" onmouseout="kill()" onfocus="this.blur()" href="'.$the_href.'"><img src="../style/pics/fields.gif"/></a></span>'."\n");
							}
							$the_href = 'edit.php?'.$page.'&amp;cmd=show&amp;nr='.$row[$entity["pk"]].$group_forward.'&amp;from=list&amp;topic='.$params["topic"].'&name='.$content;
							echo($indent.'		<span class="list_pic"><a title="" onmouseover="popup(\''.__('edit this entry.').'\')" onmouseout="kill()" onfocus="this.blur()" href="'.$the_href.'"><img src="../style/pics/edit.png"/></a></span>'."\n");
							$the_href = 'edit.php?'.$page.'&amp;cmd=delete&amp;nr='.$row[$entity["pk"]].$group_forward.'&amp;old_formfield_name='.$row[$entity["title_field"]].'&amp;from=list&amp;topic='.$params["topic"];
							//check if we should give the old name for consistency reasons
							$consistency_fields = explode(",",$entity["consistency_fields"]);
							if (in_array($f["name"],$consistency_fields)) $the_href = $the_href.'&amp;old_name='.$row[$f["name"]];;
							echo($indent.'		<span class="list_pic"><a onclick="return checkDelete();" title="" onmouseover="popup(\''.__('delete this entry.').'\')" onmouseout="kill()" onfocus="this.blur()" onclick="return checkDelete();" href="'.$the_href.'"><img src="../style/pics/no.gif"/></a></span>'."\n");
							echo($indent.'	</div>');
						}
						
						if (isTextType($f["data-type"])) {
							//$content = filterXMLChars($content);
							if ($entity["search"]["keyword"] == "1" and $params["cmd"] == "search" and $params["search"]["kw"] != "") {
								$content = highlight($params["search"]["kw"], $content);
							}
						}
						if ($f['data-type'] == "bool") {  //boolean into HTML
							if ($content == 1) {$content="yes";} else if ($content == 0) {$content="no";}
						}
						
						//comments have neat markup
						if ($params["page"]=='_sys_comments' and $f['name']!='comment'){
							if ($content != ""){
								if ($f['name']=='name') $prefix = "from";
								else if ($f['name']=='insert_date') $prefix = "on";
								else if ($f['name']=='email') $prefix = "email";
								else if ($f['name']=='www') $prefix = "www";
								else $prefix = "";
								echo($indent.'	<span class="comment_prefix">'.$prefix.'</span><span>'.$content.'</span>');
							}
						}else{
							//this id can be used by users to access individual Elements with their CSS 
							echo($indent.'	<div class="'.$entity["tablename"].'_'.$f["name"].'">'."\n");
		
							if ($entity["show_labels"] == "1" and !$list_view) {
								echo($indent.'	<div class="label">');
								if ($f['label'] != "") echo($f['label']); else echo($f['name']);
								echo('</div>'."\n");
							}
							
							//now, finally, the value
							if($f["name"] == $entity["title_field"]) {
								$theClass = "title";
							} else $theClass = "value"; 
							
							echo($indent.'		<div class="'.$theClass.'">'."\n");
							echo($indent.'			');	//indent
							if($f["name"] == $entity["title_field"] and !$list_view) {	//make a link
								$the_url = '?'.$params["page"].'&amp;nr='.$row["id"];
								echo('<a class="entry_title_link" href="'.$the_url.'">');
							}
							
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
		if (!$list_view and $entity["tablename"] == "_sys_comments") {
			echo($indent.'		<div><a href="admin/edit.php?_sys_comments&amp;cmd=show&amp;nr='.$row[$entity["pk"]].'">#</a></div>'."\n");
		}
		if (!$list_view and $something_was_not_brief == true and $params["step"] != 1) { 	//show a link to the whole entry
			$wlink = "?".$params["page"].'&amp;nr='.$row[$entity["pk"]];
			echo($indent.'	<div class="whole_link"><a onmouseover="popup(\''.__('show this entry in full length').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="'.$wlink.'">&gt;&gt;'.sprintf(__('show whole entry')).'</a></div>'."\n");
		}
		echo("						</div>"."\n");

		if (!$list_view) {
			if ($page_info["hide_options"] == "0" ) {
				echo($indent.'<div class="options">'."\n");
				echo($indent.'	<span class="edit">'."\n");
				echo($indent.'	<span class="admin_link"><a onmouseover="popup(\''.__('for admins: edit this entry').'\')" onmouseout="kill()" title="" onfocus="this.blur()" href="admin/edit.php?'.$params["page"].'&amp;cmd=show&amp;nr='.$row[$entity["pk"]].$group_forward.'">#</a></span>'."\n");
				if($entity["dateField"]["editlabel"] != "") { //show last editing date
					if ($row[$entity["dateField"]["editlabel"]] != NULL) {
						if ($row[$entity["dateField"]["editlabel"]] != '0000-00-00')
							echo($indent.'		<span class="last_edited_label">'.__('last edited:').' '.format_date($row[$entity["dateField"]["editlabel"]]).'</span>'."\n");
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
						$href = '?'.$params["page"].'&amp;nr='.$params["nr"].'#comments_anchor';
						echo($indent.'	<span class="comment_link"><a href="'.$href.'">comments('.$comment_count.')</a></span>'."\n");
					} else {
						$href = '?'.$params["page"].'&amp;nr='.$params["nr"].'#commentform_anchor';
						echo($indent.'	<span class="comment_link"><a href="'.$href.'">'.__('add a comment').'</a></span>'."\n");
					}
				} else {
					$href='javascript:document.new_form._formfield_name_input.focus();';
					echo($indent.'	<span class="comment_link"><a id="comment_link" href="'.$href.'">'.__('add a comment').'</a></span>'."\n");
				}
			}
			
			//closing tags
			if ($page_info["hide_options"] == "0" ) {
				echo($indent.'	</span>'."\n");
				echo($indent.'</div>'."\n");	//end option div
			}
			
			//show comments
			$nind = $ind + 1;
			if ($page_info["commentable"] == "1" and $params["step"] == 1) {
				if($comment_count > 0) {
					writeComments($comments, $nind);
				}
				writeCommentForm($nind);
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
		$res = mysql_query($query, getDBLink());
		if (mysql_errno(getDBLink()) == 0) {
			return $res;
		} else return "";
	}
	
	/* writes all comments that are given as result set
	*/
	function writeComments($comments, $ind=5) {
		$indent = translateIndent($ind);
		global $params;
		echo($indent.'<div id="comments"><a class="target" name="comments_anchor"></a>'."\n");
		
		//save what page we're on
		$page = $params["page"];
		//use comments as page while writing them
		$params["page"] = "_sys_comments";	
		//write the results
		while($row = mysql_fetch_array($comments, MYSQL_ASSOC)) {
			writeEntry($row, "", false, false);
		}
		
		//set param back
		$params["page"] = $page;
		echo($indent.'</div>'."\n");
	}
	
	/* writes a comment form
	*/
	function writeCommentForm($ind) {
		global $params;
		require_once("PolyPagerLib_HTMLForms.php");

		//fill into $values what we know about this page and entry -
		//writeHTMLForm will look it up...
		$params["values"]["nr"] = $params["nr"];
		$params["values"]["pagename"] = $params["page"];
		$params["values"]["pageid"] = $params["nr"];
		
		//now "instruct" other code what we intend
		$params["cmd"] = "new";
		$params["page"] = "_sys_comments";	//we can do this because comments come last
		writeHTMLForm("", ".", false, true, $ind, "commentform");	//no dataset needed
	}
	
	/**
	 * returns an empty string if this comment can be
	 * written to the db, and an error message otherwise
	 * 
	 * @param comment the entered text
	 * @param time the time it took in milliseconds
	 */
	function checkComment($comment, $time) {
		if ($time < 1000 and $time != '') 
			return __('wow, you sure entered your comment quick. So quick, actually, that I labeled you as a machine and your comment as spam. Your comment has not been saved.');
		$stripped_comment = strip_tags($comment, '<b><i><ul><ol><li><br><p>');
		if ($comment != $stripped_comment) 
			return __('Your text contains tags that are not allowed. You can use one of those: &lt;b&gt;&lt;i&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;br&gt;&lt;p&gt;. Your comment has not been saved.');
		return "";
	}
?>