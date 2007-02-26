<?php
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
* getEditParameters()
* getEditQuery($command, $theID)
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
    
	if ($params["page"] != "" and isAKnownPage($params["page"])){
		//get metadata for this page
		$entity = getEntity($params["page"]);
	
		//------------------------- command check -------------------------
		//get command: new|entry|show|edit|delete (the last 3 must come with id!)
		$params["cmd"] = $_GET["cmd"];
		if ($params["cmd"] == "") $params["cmd"] = $_POST["cmd"];	//post command
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
				foreach($entity["fields"] as $f) {
					$values[$f["name"]] = filterSQL($_POST['_formfield_'.$f["name"]]);
					//Booleans umwandeln
					if ($f["data_type"] == "bool") {
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
				$params["cmd"] = "new";		//assume new one
			} else {
				$params["cmd"] = "show";	//assume showing what we have
			}
		}
        
        // some links carry the primary key implicitely
        if (!isset($params["values"][$entity["pk"]])) {
                $params["values"][$entity["pk"]] = $params["nr"];
        }
    
		//$opt = $_POST[opt];	//indicates what to show next
		//-----------------end Checking Parameters -----------------------
	}
	return $params;
}

/*
	builds a query, depending on command.
	if id is empty, it uses the param id
	foreign key - relations that point to a field that changes
	might lead to multiple queries returned in an array!
*/
function getEditQuery($command, $theID) {
	global $params;
	$entity = getEntity($params["page"]);
	$page_info = getPageInfo($params["page"]);
	if ($theID == "") $theID = $params["nr"];
    if ($theID == "") $theID = $params["values"][$entity["pk"]];
	$query = "";        # we'll build in this string
	$queries = array(); # and add it to this array we'll return
	
	// resolve foreign keys that the user entered (the constraints in the db are 
	// the dbs thing)
	if ($command == "edit" || $command == "delete"){
		//look in foreign keys:
		$fks = getForeignKeys();
		foreach($fks as $fk){
			//if not automatic (FK is in database) and ref_page = this page:
			if ($fk['in_db'] == '0' && $fk['ref_page'] == $params['page']) {
				$params_backup = $params;
				//get action for current cmd
				$action = ($command=='edit')?'on_update':'on_delete';
				
				//according to http://dev.mysql.com/doc/refman/5.0/en/innodb-foreign-key-constraints.html,
				//RESTRICT and NO ACTION both reject deletion in the parent table!
				if ($fk[$action] == 'RESTRICT' || $fk[$action] == 'NO ACTION'){ 
				  echo('<div class="sys_msg">operation RESTRICTED according to foreign key '.$fk['name'].'</div>');
				  return 'ff';
				}else {	// we really need to work :-(
					$referencing_page_info = getPageInfo($fk['ref_page']);
					$referencing_entity = getEntity($fk['ref_page']);
					$referencing_table = $referencing_page_info['tablename'];
					if ($fk['ref_field'] == $referencing_entity['pk']) $fk['ref_field'] = "nr";
					// find affected entries in referencing table
					$tmp_query = "SELECT * FROM ".$referencing_table." WHERE ".$fk['field']." = ".$params[$fk['ref_field']];
					
					$result = pp_run_query($tmp_query);
					while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
						// if we update and the field hasn't changed, let's not do anything
						if (!($command == 'edit' && 
								$params_backup['values'][$fk['ref_field']] == $params_backup['values']['old_formfield_'.$fk['ref_field']])){
							$params = array(); //make a new one
							$params['values'] = array();
							if ($fk[$action] == 'CASCADE'){
								$params['values'][$fk['field']] = $params_backup['values'][$fk['ref_field']];
							}else if ($fk[$action] == 'SET NULL'){
								$params['values'][$fk['field']] = null;
							}
							//add Query with recursicve call
							$tmp = getEditQuery($command,$row[getPKName($referencing_table)]);;
							if ($tmp == ""); return;	//error
							$queries = array_merge($tmp,$queries);
						}
					}
				}
				$params = $params_backup;
			}
		}
	}
	


	//------------------- insert ----------------------------------
	if ($command == "entry") {			// INSERT Query
		//insert a new recordset
		if ($entity["date_field"] != ""){
			$time = buildTimeString(localtime(time() , 1));
			$params["values"][$entity["date_field"]["name"]] = buildDateString(getdate());
			$f = getEntityField($entity["date_field"]["name"],$entity);
			if ($f["data_type"] == "datetime") {
				$params["values"][$entity["date_field"]["name"]] = $params["values"][$entity["date_field"]["name"]]." ".$time;
			}else if($entity["time_field"] != "") {
				$params["values"][$entity["time_field"]["name"]] = $time;
			}
		}
		$queryA = array();
		$queryA[0] = "INSERT INTO ".$page_info["tablename"]." (";
		$x = 1;
		foreach($entity["fields"] as $f) {
			// add it if it is set or we don't have an ID or the ID comes within the ID param (for non-int IDs)
			if (isset($params["values"][$f["name"]])) {
				$queryA[$x] = " ".$f["name"].","; $x++;
			}
		}
		$x--;
		$queryA[$x] = substr($queryA[$x], 0, strlen($queryA[$x])-1); //remove comma

		//some tables have a string as pk
		//if ($entity["pk"] != "" and !isNumericType($entity["pk_type"])) $queryA[] = ", ".$entity["pk"];
        
		$queryA[] = ") VALUES ( ";
		$x = count($queryA);
		foreach($entity["fields"] as $f) {
			if (isset($params["values"][$f["name"]]) ) {
				if (isTextType($f["data_type"]) or isDateType($f["data_type"]) or $f["data_type"] == 'time') {
					$queryA[$x] = " '".$params["values"][$f["name"]]."',";
				} else {
					$queryA[$x] = " ".$params["values"][$f["name"]].",";
				}
				$x++;
			}
		}
		$x--;
		$queryA[$x] = substr($queryA[$x], 0, strlen($queryA[$x])-1);

		$queryA[count($queryA)] = ")";
		$query .= implode($queryA);
	}
	//---------------end insert -----------------------------------

	//------------------- edit ------------------------------------
	else if ($command == "edit") {			// UPDATE Query
		if ($entity["date_field"]["editlabel"] != ""){
			$time = buildTimeString(localtime(time() , 1));
			$params["values"][$entity["date_field"]["editlabel"]] = buildDateString(getdate());
			$f = getEntityField($entity["date_field"]["editlabel"],$entity);
			if ($f["data_type"] == "datetime") {
				$params["values"][$entity["date_field"]["editlabel"]] = $params["values"][$entity["date_field"]["editlabel"]]." ".$time;
			}else if($entity["time_field"] != "") {
				$params["values"][$entity["time_field"]["editlabel"]] = $time;
			}
		}
		$queryA = array();
		$queryA[0] = "UPDATE ".$page_info["tablename"]." SET";
		$x = 1;
		foreach($entity["fields"] as $f) {
			if (isset($params["values"][$f["name"]])) {
				if (!isTextType($f["data_type"]) and !isDateType($f["data_type"]) and $f["data_type"] != 'time') $queryA[$x] = " ".$f['name']." = ".$params['values'][$f['name']].",";
				else $queryA[$x] = " ".$f["name"]." = '".$params["values"][$f['name']]."',";
				$x++;
			}
		}
		$x--;
		$queryA[$x] = substr($queryA[$x], 0, strlen($queryA[$x])-1);
		if ($entity["pk"] != "") {
			if (isNumericType($entity["pk_type"])) $queryA[count($queryA)] = " WHERE ".$entity["pk"]." = $theID";
			else $queryA[count($queryA)] = " WHERE ".$entity["pk"]." = '".$theID."'";
		}
		$query .= implode($queryA);
	}
	//---------------end edit -------------------------------------

	//------------------- delete ----------------------------------
	else if ($command == "delete") {	// DELETE Query
		$query .= "DELETE FROM ".$page_info["tablename"];
		if ($entity["pk"] != "" and isNumericType($entity["pk_type"])) $query .= " WHERE ".$entity["pk"]." = $theID";
		else if ($entity["pk"] != "") $query .= " WHERE ".$entity["pk"]." = '".$theID."'";
	}
	//---------------end delete -----------------------------------
	//------------------- show ----------------------------------------
	else if ($command == "show") {		// SELECT Query
		$query .= "SELECT * FROM ".$page_info["tablename"];
		if ($entity["pk"] != "" and isNumericType($entity["pk_type"])) $query .= " WHERE ".$entity["pk"]." = $theID";
		else if ($entity["pk"] != "") $query .= " WHERE ".$entity["pk"]." = '".$theID."'";
	}
	//---------------end show -----------------------------------------
	
	$query .= ';';
	$queries[] = $query;
	//print_r($queries);
	return $queries;
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
			$res = pp_run_query("SELECT MIN(edited_date) AS maxed FROM _sys_feed;");
			$row = mysql_fetch_array($res, MYSQL_ASSOC);
			$res = pp_run_query("DELETE FROM _sys_feed WHERE edited_date = '".$row['maxed']."';");
		}
		//insert the new one
		$title = "";
		if ($entity['title_field'] != "") $title = $params['values'][$entity['title_field']];
		$tfield = guessTextField($entity);
		if ($title == "" and $tfield != "") $title = getFirstWords($params['values'][$tfield],50);
		
		//we know if the entry is new or was updated
		if ($params["cmd"] == "edit") $title = '['.__('update').'] '.$title;
		$query = "INSERT INTO _sys_feed (edited_date, title, pagename, id) VALUES ";
		$query = $query."('".buildDateTimeString()."','".$title."','".$params["page"]."',".$params["nr"].");";
		$res = pp_run_query($query);
	}
 }
?>
