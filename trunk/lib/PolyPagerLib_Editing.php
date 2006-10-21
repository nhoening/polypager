<?php
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
	* getEditParameters()
	* getEditQuery($commmand, $theID)
	* handleFeed()
	*/

	/*
	this documentation is old - there is more happening than is described here..
	time is precious...

	returns an Array of Parameters for editing:
	["page"=>"", "cmd"=>"", "id"=>"", array: values]

	for Variables you read from the HTTP Stream, always ask yourself if
	  they'll come with HTTP GET (in the URL, mostly from links), in HTTP POST
	  (from a form) or maybe one of either way.
	  Then you always have to check both ways to read it in!

	each page can be in one of two modes:
	-'new', show an empty form, provide a "save" button for entered data
	-'show', that means: show a dataset, provide a "save" and a "delete" button

	Now before we enter that mode, we might execute an action because we
	already edited something here:
	-we might want to enter a new record
	-we might want to edit an existing record
	-we might want to delete an existing record

	That sums up to five distinct use cases: new, show, edit, delete, entry.
	In the code they'll be called Commands (cmd).

	Note that the three in the middle have to come with an id!
	And, trivially, "entry" and "edit" should come with some data.

	The first two states are by definition the ones that are used to start
	administration.

	Note also that the last three will have to do data manipulations and
	then go to some other state, automatically.
	Usability could now handle the topic of what should happen if the user
	enters, edits or deletes. Some message should always indicated what has
	been done. A checkbox could give the possibility to show something else
	than the same ol' boring record (in addition to the status report,
	of course). That could be an empty form (when entering) or the next record
	(when editing or deleting). But that is not yet clear...

	So this table shows the intended usage:

	state  |succ.-state     | auto status |with   |with   | options
		   |				| change?     |ID     |data   |
	- - - -+- - - - - - - - + - - - - - - + - - - + - - - + - - - -
      ...  | show           | ...         | ...   | ...   |  ...
	  ...  | (new)          | ...         | ...   | ...   |  ...
	 new   | entry          | no          | no    | no    |  ...
	 entry | show           | yes         | no    | yes   |  state: new
	 show  | edit, delete   | no          | yes   | no    |  ...
	 edit  | show           | yes         | yes   | yes   |  id: actual/next
	 delete| show           | yes         | yes   | no    |  state: new
	*/
	function getEditParameters() {
		global $_POST;
		global $_GET;

		//PHP 4 uses HTTP_XXX_VARS
		if (!isset($_SERVER)) {
			global $HTTP_POST_VARS;
			global $HTTP_GET_VARS;
			$_POST =& $HTTP_POST_VARS; $_GET =& $HTTP_GET_VARS;
		}

		//------------------------- page check ----------------------------
		//the "page" param
		$query_array = explode("&", $_SERVER["QUERY_STRING"]);
		$params["page"] = urldecode($_POST["page"]);
		if ($params["page"] == "") {
			//the "page" param will be just the first in GET Requests (so we can write http://www.bla.com/?mypage)
			$query_array = explode("&", $_SERVER["QUERY_STRING"]);
			$params["page"] = urldecode($query_array[0]);
			//if "page=" is given we can handle this, too
			if (strpos($query_array[0], "page=") !== false) $params["page"] = urldecode($_GET["page"]);
		}

		//get metadata for this page
		$entity = getEntity($params["page"]);

		//------------------------- command check -------------------------
		//get command: new|entry|show|edit|delete (the last 3 must come with id!)
		$params["cmd"] = $_GET["cmd"];
		if ($params["cmd"] == "") $params["cmd"] = $_POST["cmd"];	//post command
		//if ($params["cmd"] == "new" and isSinglepage($params["page"])) $params["cmd"] = "entry";
		//one_entry_only: there is only show(default) and edit
		if($entity["one_entry_only"] == "1" and $params["cmd"] != "edit" and $params["cmd"] != "new"  and $params["cmd"] != "entry") {
			$params["cmd"] = "show";
		}

		//-------------------------from param
		$params["from"] = $_GET["from"];
		if ($params["from"] == "") { $params["from"] = $_POST["from"]; } //coming in per POST?

		//-------------------------group param
		$params["group"] = urldecode($_GET["group"]);
		if ($params["group"] == "") { $params["group"] = urldecode($_POST["group"]); } //coming in per POST?

		//-------------------------topic (for admin list)
		$params["topic"] = $_POST["topic"];
		if ($params["topic"] == "") {$params["topic"] = $_GET["topic"];}
		
		//-------------------------feed (from checkbox)
		$params["feed"] = $_POST['_formfield_feedbox'];
		if ($params["feed"] == "") {$params["feed"] = $_GET["_formfield_feedbox"];}
		if($params["feed"] == "on"){$params["feed"] = "1";}
							else{$params["feed"] = "0";}
		
		//-----------checking Parameters ---------------------------------
		if ($params["cmd"] != "") {

			if ($params["cmd"] == "show" or $params["cmd"] == "entry" or $params["cmd"] == "edit" or $params["cmd"] == "delete") {	//get data
				$consistency_fields = explode(",",$entity["consistency_fields"]);
				$values = array();
				//if (isMultipage($params["page"])) {
					foreach($entity["fields"] as $f) {
						$values[$f["name"]] = filterSQL($_POST['_formfield_'.$f["name"]]);
						//Booleans umwandeln
						if ($f["data-type"] == "bool") {
							if($values[$f["name"]] == "on"){$values[$f["name"]] = "1";}
							else{$values[$f["name"]] = "0";}
						}
						if(in_array($f["name"],$consistency_fields)) {
							$values['old_formfield_'.$f["name"]] = filterSQL($_POST['old_formfield_'.$f["name"]]);
							if ($values['old_formfield_'.$f["name"]] == "") $values['old_formfield_'.$f["name"]] = filterSQL($_GET['old_formfield_'.$f["name"]]);
							//echo("f[old_formfield_name] is:".$values["old_formfield_name"]);
						}
						//echo("f[name] is: ".$f["name"]." and values[f[name]] is: ".$values[$f["name"]]."<br/>");
					}
					if(isSinglepage($params["page"])) {
						$values["pagename"] = $params["page"];
					}
					$values["time_needed"] = $_POST['_formfield_time_needed'];
				$params["values"] = $values;
			}

			//those commands need an entry number
			if (($params["cmd"] == "show" or $params["cmd"] == "edit" or $params["cmd"] == "delete"
					or ($params["page"] == "_sys_intros" and $params["cmd"] == "entry"))
					and $entity["pk"] != "") {
				$params["nr"] = $_GET['nr'];if ($params["nr"] == "") $params["nr"] = $_POST['nr'];	//can come in both ways
				if ($params["nr"] == "" and $params["cmd"] == "show") $params["cmd"] = "entry";	//assume new one
			}

		} else {
			if (isMultipage($params["page"])) {
				$params["cmd"] = "new";	//assume new one
			} else {
				$params["cmd"] = "show";	//assume showing what we have
			}
		}

		//$opt = $_POST[opt];	//indicates what to show next
		//-----------------end Checking Parameters -----------------------

		return $params;
	}

	/*
		builds a query, depending on command.
		if id is empty, it uses the param id
	*/
	function getEditQuery($commmand, $theID) {
		global $params;
		$entity = getEntity($params["page"]);
		$page_info = getPageInfo($params["page"]);
		if ($theID == "") $theID = $params["nr"];
		//------------------- insert ----------------------------------
		if ($commmand == "entry") {			// INSERT Query
			//insert a new recordset
				if ($entity["dateField"] != ""){
					$time = buildTimeString(localtime(time() , 1));
					$params["values"][$entity["dateField"]["name"]] = buildDateString(getdate());
					$f = getEntityField($entity["dateField"]["name"]);
					//echo("|".$entity["dateField"]["name"]."|".$f["data-type"]."|");
					if ($f["data-type"] == "datetime") {
						$params["values"][$entity["dateField"]["name"]] = $params["values"][$entity["dateField"]["name"]]." ".$time;
					}else if($entity["timeField"] != "") {
						$params["values"][$entity["timeField"]["name"]] = $time;
					}
				}
				$queryA = array();
				$queryA[0] = "INSERT INTO ".$entity["name"]." (";
				$x = 1;
				foreach($entity["fields"] as $f) {
					//if (!in_array($f["name"],explode(',', $entity["hidden_fields"])) ) {
						$queryA[$x] = " ".$f["name"].","; $x++;
					//}
				}
				$x--;
				$queryA[$x] = substr($queryA[$x], 0, strlen($queryA[$x])-1);

				//some tables have a string as pk
				if ($entity["pk"] != "" and $entity["pk_type"] != "int") $queryA[count($queryA)] = ", ".$entity["pk"];
				$queryA[count($queryA)] = ") VALUES ( ";
				$x = count($queryA);
				foreach($entity["fields"] as $f) {

					//if (!in_array($f["name"],explode(',', $entity["hidden_fields"])) ) {
						if (isTextType($f["data-type"]) or isDateType($f["data-type"]) or $f["data-type"] == 'time') {
							$queryA[$x] = " '".$params["values"][$f["name"]]."',";
						} else {
							$queryA[$x] = " ".$params["values"][$f["name"]].",";
						}
						$x++;
					//}
				}
				$x--;
				$queryA[$x] = substr($queryA[$x], 0, strlen($queryA[$x])-1);

				//some tables have a string as pk - then we take the id param for that
				if ($entity["pk"] != "" and $entity["pk_type"] != "int") $queryA[count($queryA)] = ", '".$theID."'";

				$queryA[count($queryA)] = ")";
				$query = implode($queryA);

		}
		//---------------end insert -----------------------------------

		//------------------- edit ------------------------------------
		else if ($commmand == "edit") {			// UPDATE Query
				
				if ($entity["dateField"]["editlabel"] != ""){
					$time = buildTimeString(localtime(time() , 1));
					$params["values"][$entity["dateField"]["editlabel"]] = buildDateString(getdate());
					$f = getEntityField($entity["dateField"]["editlabel"]);
					if ($f["data-type"] == "datetime") {
						$params["values"][$entity["dateField"]["editlabel"]] = $params["values"][$entity["dateField"]["editlabel"]]." ".$time;
					}else if($entity["timeField"] != "") {
						$params["values"][$entity["timeField"]["editlabel"]] = $time;
					}
				}
				$queryA = array();
				$queryA[0] = "UPDATE ".$entity["name"]." SET";
				$x = 1;
				foreach($entity["fields"] as $f) {
					//if (!in_array($f["name"],explode(',', $entity["hidden_fields"])) ) {
						if (!isTextType($f["data-type"]) and !isDateType($f["data-type"]) and $f["data-type"] != 'time') $queryA[$x] = " ".$f['name']." = ".$params['values'][$f['name']].",";
						else $queryA[$x] = " ".$f["name"]." = '".$params["values"][$f['name']]."',";
						$x++;
					//}
				}
				$x--;
				$queryA[$x] = substr($queryA[$x], 0, strlen($queryA[$x])-1);
				/*if($entity["dateField"]["editlabel"] != "") $queryA[count($queryA)] = ",".$entity["dateField"]["editlabel"]." = '".$params["values"][$entity["dateField"]["editlabel"]][year]."-".$params["values"][$entity["dateField"]["editlabel"]][mon]."-".$params["values"][$entity["dateField"]["editlabel"]][mday]."'";
				if($entity["publish_field"] != "") $queryA[count($queryA)] = ",".$entity["publish_field"]." = ".$params["values"][$entity["publish_field"]];*/
				if ($entity["pk"] != "") {
					if ($entity["pk_type"] == "int") $queryA[count($queryA)] = " WHERE ".$entity["pk"]." = $theID";
					else $queryA[count($queryA)] = " WHERE ".$entity["pk"]." = '".$theID."'";
				}
				$query = implode($queryA);

		}
		//---------------end edit -------------------------------------

		//------------------- delete ----------------------------------
		else if ($commmand == "delete") {	// DELETE Query
				$query = "DELETE FROM ".$page_info["tablename"];
				if ($entity["pk"] != "" and $entity["pk_type"] == "int") $query = $query." WHERE ".$entity["pk"]." = $theID";
				else if ($entity["pk"] != "") $query = $query." WHERE ".$entity["pk"]." = '".$theID."'";
		}
		//---------------end delete -----------------------------------
		//------------------- show ----------------------------------------
		else if ($commmand == "show") {		// SELECT Query
				$query = "SELECT * FROM ".$page_info["tablename"];
				//if($entity["one_entry_only"] != "1") {
					if ($entity["pk"] != "" and $entity["pk_type"] == "int") $query = $query." WHERE ".$entity["pk"]." = $theID";
					else if ($entity["pk"] != "") $query = $query." WHERE ".$entity["pk"]." = '".$theID."'";
				//}
		}
		//---------------end show -----------------------------------------
		return $query;
	}
	
	/*
	 * handles the feed table
	 */
	 function handleFeed() {
		global $params;
	 	global $entity;
	 	$max_entries = 50;
	 	//delete all possible entries with the same pagename/id from the feed list
	 	if($params["cmd"] != "entry") $res = pp_run_query("DELETE FROM _sys_feed WHERE pagename = '".$params["page"]."' AND id = ".$params["nr"].";");
		
		if($params["cmd"] != "delete") {	//new one comes in
		 	//find out how much is still in there
		 	$res = pp_run_query("SELECT COUNT(*) AS nr FROM _sys_feed;");
		 	if($row = mysql_fetch_array($res, MYSQL_ASSOC))	$count = $row['nr'];
			
		 	//if bigger than max_entries, delete oldest
		 	if ($count > ($max_entries-1)){
		 		$res = pp_run_query("SELECT MAX(edited_date) AS maxed FROM _sys_feed;");
		 		$row = mysql_fetch_array($res, MYSQL_ASSOC);
		 		$res = pp_run_query("DELETE FROM _sys_feed WHERE edited_date = '".$row['maxed']."';");
		 	}
		 	//insert the new one
		 	$title = "";
		 	if ($entity['title_field'] != "") $title = $params['values'][$entity['title_field']];
		 	//we know if the entry is new or was updated
		 	if ($params["cmd"] == "edit") $title = '['.__('update').'] '.$title;
		 	$query = "INSERT INTO _sys_feed (edited_date, title, pagename, id) VALUES ";
		 	$query = $query."('".buildDateTimeString()."','".$title."','".$params["page"]."',".$params["nr"].");";
		 	$res = pp_run_query($query);
		}
	 }
?>
