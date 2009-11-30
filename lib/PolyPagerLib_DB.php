<?php

require_once("plugins"  . FILE_SEPARATOR .  "codesense_mysqli.php");

/* this code provides the code to setup the MySQL database
it builds a connection (a link) to the db (once only) and returns it.
*/
if (!function_exists("getDBLink")) {
    // for upgrades from <= 1.0rc4 to >= 1.0rc5
    $db_obj = "";
    function getDBLink()
    {
        global $db_obj;
        global $host;
        global $db;
        global $user;
        global $pass;
        
        if ($db_obj == "") {
            $text = "Welcome to PolyPager.<br/>Seeing this page proofs that PolyPager is working at the address you typed in. <br/> However, the database is not connectable. <br/>Maybe it is not configured yet or the configuration does not fit. <br/>If you are the administrator of this page, please consult PolyPager_Config.php";
            
            // build connection to DBMS
            $db_obj = CodeSense_mysqli::CreateNew($user, $pass, $db, $host);
            
            /* check connection */
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
            
            // now to the DB
            $db_obj->ExecuteSQL('SET CHARACTER SET utf8');
            $db_obj->ExecuteSQL("SET SESSION collation_connection ='utf8_general_ci'");
            $db_obj->ExecuteSQL("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
        }
        return $db_obj;
    }
    
    function getDBName()
    {
        global $db;
        return $db;
    }
}


/* call with a query OR an array of the query together with an array of params*/
function pp_run_query($query_set)
{
    global $debug;
    $sql_exeq_words = array("CREATE", "UPDATE", "INSERT", "DELETE");
    $d = getDBLink();
    if (!is_array($query_set)) {
        if (in_array(substr($query_set,0,6), $sql_exeq_words)) {
            $res = $d->ExecuteSQL($query_set);
        } else {
            $res = $d->FetchAll($query_set, null);
        }
    } else {
        $format_str = '';
        $params = array($query_set[0]);
        foreach($query_set[1] as $p) $format_str .= $p[0];
        if ($format_str != "") {
            $params[] = $format_str;
        }
        foreach($query_set[1] as $p) $params[] = $p[1];
        if(in_array(substr($query_set[0],0,6), $sql_exeq_words)) $func='ExecuteSQL';
        else {
            $func='FetchAll';
        }
        $res = call_user_func_array(array($d, $func), $params);
    }
    return $res;
}

/* Run queries that can't be / don't need to be prepared*/
function pp_run_query_unprepared($query)
{
    global $debug;
    $res = mysqli_query(getDBLink(), $query);
    
    $error_nr = mysqli_errno(getDBLink());
    if ($error_nr != 0) {
        $error_buffer .= '|'.mysqli_error(getDBLink()).'|';
    }
    if ($error_buffer != "") {
        echo('<div class="debug">got error(s):|'.$error_buffer.'| when running the query "'.$query.'"</div>');
    }
    
    return $res;
}

/* old way of getting data - returns not just an array, but a mysql result set */
function pp_run_query_old($query)
{
    global $debug, $host, $db, $user, $pass;
    $the_db_link = mysql_connect($host, $user, $pass, $db) or die('pp_run_query_old: no link to db possible');
    mysql_select_db($db, $the_db_link) or die('pp_run_query_old: no db select possible');
    $res = mysql_query($query, $the_db_link);
    $error_nr = mysql_errno($the_db_link);
    if ($error_nr != 0) {
        $error_buffer .= '|'.mysql_error($the_db_link).'|';
    }
    if ($debug and $error_buffer != "") {
        echo('<div class="debug">got error(s):|'.$error_buffer.'| when running the query "'.$query.'"</div>');
    }
    mysql_close($the_db_link);
    return $res;
}

/* returns wether this MySQL type is numeric one */
function isNumericType($type)
{
    return(eregi('int',$type) or $type=="real" or $type=="float" or $type=="double" or $type=="decimal");
}

/* returns wether this MySQL type is a date type we support (includes
types that also or only store time !)*/
function isDateType($type)
{
    return($type == "date" or $type == "year" or isTimeType($type));
}

/* returns wether this MySQL type is a date type we support */
function isTimeType($type)
{
    return($type == "datetime" or $type == "time"  or $type == "timestamp");
}


/* returns wether this MySQL type is a text type */
function isTextType($type)
{
    return($type == "string" or $type == "varchar" or $type == "tinytext" or $type == "password" or
    $type == "set" or $type == "enum" or $type == "char" or $type == "file" or isTextAreaType($type));
}

/* returns wether this MySQL type is a type that PolyPager handles with a text area*/
function isTextAreaType($type)
{
    return($type=="tinyblob" or $type=="blob" or $type=="mediumblob" or $type=="longblob"
        or $type=="tinytext" or $type=="text" or $type=="mediumtext" or $type=="longtext");
}

function getMySQLiType($t)
{
    $type = 's';
    if (isNumericType($t)) {
        $type = 'd';
    }
    if ($t == 'bool') {
        $type = 'i';
    }
    //if ($t == 'blob') $type = 'b'; // with this, we have problems saving blob fields (they won't be saved)
    return $type;
}


/*
Returns an array with "table_name", "title_field", "likely_page" and "fk"
for every table that is referenced by this entity via a foreign key "fk"
likely_page is the best guess on which page we might find the table
represented(this is only hard when we encounter "real" FKs from the database and
one table is represented on several multipages).
The order of the fields is preserved(PP treats the first field of a purely
relational table as responible for the others).
*/
function getReferencedTableData($entity)
{
    $tables = array();
    foreach($entity['fields'] as $f){
        $fk = getFK($entity['tablename'], $f['name']);
        
        $referenced_table = "";
        $title_field = "";
        $likely_page = "";
        // Get the referenced table and the title field to show
        // Now, what can we show? Is there a more useful field for
        // the valuelist than an id or the like? Maybe the title_field
        // of a page? Let's see if we can get one
        if ($fk['page'] == $entity["pagename"]) {
            $page_info = getPageInfo($fk['ref_page']);
            $referenced_table = $page_info['tablename'];
            if (isMultipage($fk['ref_page'])) {
                $title_field = $page_info['title_field'];
            } else {
                $title_field = 'heading';
            }
            $likely_page = $fk['ref_page'];
        } else if ($fk['table'] == $entity['tablename']) {
            $referenced_table = $fk['ref_table'];
            //in principle, _sys_sections could be referenced - that's easy
            if ($referenced_table == '_sys_sections') {
                $title_field = 'heading';
                $likely_page = $fk['ref_page'];
            } else {
                // more likely are multipages
                $pk_field = getPKName($referenced_table);
                $pq = "SELECT name,title_field FROM _sys_multipages WHERE tablename = '".$referenced_table."'";
                $result = pp_run_query($pq);
                $row = $result[0];
                if (count($result)>1) {
                    $title_field = $pk_field;
                }
                //no chance of a good choice :-(
                else {
                    // we have the one page for this table!
                    $title_field = $row['title_field'];
                    if ($title_field=="") {
                        $title_field = $pk_field;
                    }
                }
                $likely_page = $row['name'];
            }
        }
        if ($referenced_table != "") {
            $tables[] = array('fk'=>$fk,'table_name'=>$referenced_table, 'likely_page' => $likely_page , 'title_field' => $title_field);
        }
    }
    return $tables;
}

/*
Returns an array with "table_name", "title_field", "likely_page" and "fk"
for every table that is referencing to this entity via a foreign key "fk"
*/
function getReferencingTableData($entity)
{
    $fks = getForeignKeys();
    $tables = array();
    
    foreach($fks as $fk){
        $referencing_table = "";
        $title_field = "";
        $likely_page = "";
        if ($fk['ref_page'] == $entity["pagename"]) {
            $page_info = getPageInfo($fk['page']);
            $referencing_table = $page_info['tablename'];
            if (isMultipage($fk['page'])) {
                $title_field = $page_info['title_field'];
            } else {
                $title_field = 'heading';
            }
            $likely_page = $fk['page'];
        } else if ($fk['ref_table'] == $entity['tablename']) {
            $referencing_table = $fk['table'];
            //in principle, _sys_sections could be referenced - that's easy
            if ($referencing_table == '_sys_sections') {
                $title_field = 'heading';
                $likely_page = $fk['page'];
            } else {
                // more likely are multipages
                $pk_field = getPKName($referencing_table);
                $pq = "SELECT name, title_field FROM _sys_multipages WHERE tablename = '".$referencing_table."'";
                $result = pp_run_query($pq);
                $row = $result[0];
                if (count($result)>1) {
                    $title_field = $pk_field;
                }
                //no chance of a good choice :-(
                else {
                    // we have the one page for this table!
                    $title_field = $row['title_field'];
                    if ($title_field=="") {
                        $title_field = $pk_field;
                    }
                }
                $likely_page = $row['name'];
            }
        }
        if ($referencing_table != "") {
            $tables[] = array('fk'=>$fk,'table_name'=>$referencing_table, 'likely_page' => $likely_page, 'title_field' => $title_field);
        }
    }
    return $tables;
}


function getTableFields($table)
{
    return pp_run_query('SHOW FIELDS FROM `'.$table.'`');
}

/* looks up the name of the pk field*/
function getPKName($table)
{
    $field_list = getTableFields($table);
    foreach($field_list as $f) {
        if ($f['Key' == 'PRI']) {
            return $f['Field'];
        }
    }
    return "";
}

/* looks up the type of the pk field*/
function getPKType($table)
{
    $field_list = getTableFields($table);
    foreach($field_list as $f) {
        if ($f['Key' == 'PRI']) {
            return $f['Type'];
        }
    }
    return "";
}

/* gets an array with field metadata from the db, replaces (!) the field array
in the actual entity with it. Sets the primary key info in the entity
and returns the entity.
params:
entity: the entity to add to
name: the table name
not_for_field_list: this space-separated list contains names of fields that should not
be added - maybe because they are mentioned somewhere else already

You can expect these fields to be filled:
"data_type" - the MySQL data type
"size" - the size, depending on the type
"order_index" - index in which order the field will be displayed
"help" - a help terxt for this field
"default" - the default value from the DB
"valuelist" - a comma separated list of posiible values
"name" - the name of this field
"label" - a label to show in forms
"validation" - a validation method (see getValidationMsg() below)
"not_brief" - if 1, and several entries are shown, then this field will not be shown
because it's too long (there will be a link to the whole entry)
*/
function addFields($entity, $name, $not_for_field_list = "")
{
    $fields = array();
    $page_info = getPageInfo($entity['pagename']);
    global $db;
    
    $link = getDBLink();
    
    //do we know where to look at all?
    if ($name == "") {
        $entity['fields'] = array();
        return $entity;
    }
    
    // -- first, we see what we find in the database's metadata
    
    //test for Information_schema.columns (SQL-92 standard)
    $client_api = explode('.', mysqli_get_server_info(getDBLink()));
    if ($client_api[0] >= 5) {
        //test for existence of/access to INFORMATION_SCHEMA database
        $info_schema_accessible = false;
        $db_list = pp_run_query('Show databases;');
        foreach($db_list as $row){
            if ($row["Database"] == "information_schema") {
                $info_schema_accessible = true;
            }
        }
        if ($info_schema_accessible) {
            // information_schema exists
            //we align the columns that we'd also find in the "SHOW COLUMNS"-
            //Query (see below) to the standard query with " AS "
            $query = " SELECT
COLUMN_NAME AS `Field`,
COLUMN_KEY AS `Key`,
COLUMN_TYPE AS `Type`,
CHARACTER_MAXIMUM_LENGTH,
NUMERIC_PRECISION,
COLUMN_DEFAULT AS `Default`,
EXTRA AS `Extra`,
COLUMN_COMMENT
FROM information_schema.COLUMNS WHERE TABLE_NAME = '".$entity["tablename"]."' AND TABLE_SCHEMA = '".$db."'";
        }
    }
    //if we can't use it, do it the old way, with less information, sadly
    if ($query == "") {
        $query = "SHOW COLUMNS FROM `".$entity["tablename"]."`";
    }
    
    $res = pp_run_query($query);
    $i = 0;
    foreach($res as $row){
        
        //primary key
        if ($row['Key']=='PRI') {
            if ($entity['pk']!="") {
                // seems to be a 2-field PK - not supported!
                $entity['pk_multiple'] = true;
            }
            $entity["pk"] = $row['Field'];
            //overwriting the first!
            $entity["pk_type"] = preg_replace('@\([0-9]+\,?[0-9]*\)$@', '', $row['Type']);
        }
        
        
        if (!eregi($row['Field'], $not_for_field_list)) {
            //determine length - use only "Type" due to http://polypager.nicolashoening.de/?bugs&nr=318
            //$len = $row['CHARACTER_MAXIMUM_LENGTH'];
            //if ($len == "" or $len == "NULL") {
                $len = $row['NUMERIC_PRECISION'];
            }
            //those fields are not there when we said SHOW COLUMNS, so...
            //if ($len == "" or $len == "NULL") {
                $hits = array();
                eregi('[0-9]+',$row['Type'],$hits);
                $len = $hits[0];
                //}
                //support sets or enums,
                //but we save the valuelist - PolyPager can handle those
                if (eregi('^set\(', $row['Type']) or eregi('^enum\(', $row['Type'])) {
                    $type = preg_replace('@\((\'.+\'\,?)+(\'.+\')\)$@', '', $row['Type']);
                    eregi('\((\'.*\')\)', $row['Type'], $hits);
                    $hlist = explode(',', $hits[1]);
                    $valuelist = array();
                    //remove '' on outsets
                    foreach($hlist as $l) $valuelist[] = trim($l,"'");
                    $valuelist = implode(',', $valuelist);
                    $valuelist = str_replace(",,", ",", $valuelist);
                } else {
                    $type = preg_replace('@\([0-9]+\,?[0-9]*\)$@', '', $row['Type']);
                    $valuelist = "";
                }
                $field = array("name"=>$row['Field'],
                "data_type"=>$type,
                "size"=>$len,
                "order_index"=>''.$i,
                "help"=>$row['COLUMN_COMMENT'],
                "default"=>$row['Default'],
                "valuelist"=>$valuelist);
                
                //if default is CURRENT_TIMESTAMP, then retrieve it
                if ($type="timestamp" and $row['Default']=="CURRENT_TIMESTAMP") {
                    $field['default'] = date("Y-m-d H:i:s");
                }
                
                if ($row['Extra'] == 'auto_increment') {
                    $entity['hidden_form_fields'].=','.$row['Field'];
                    $field['auto'] = 1;
                } else {
                    $field['auto'] = 0;
                }
                
                //IMPORTANT: In MySQL we code a boolean as int(1) !!!
                if (($row['Type'] == "int(1)" or $row['Type'] == "tinyint(1)")) {
                    $field["data_type"] = "bool";
                }
                
                //set some defaults
                $field['formgroup'] = "";
                
                $fields[count($fields)] = $field;
                
                $i++;
            }
            
        }
        
        // -- now we enrich with data from the _sys_fields table
        if ($page_info != "") {
            $query = "SELECT * FROM _sys_fields WHERE pagename = '".$page_info["name"]."'";
            $res = pp_run_query($query);
            foreach($res as $row){
                for ($i=0; $i<count($fields); $i++) {
                    
                    if ($fields[$i]["name"] == $row["name"]) {
                        $fields[$i]["label"] = $row["label"];
                        $fields[$i]["validation"] = $row["validation"];
                        if ($fields[$i]["valuelist"] == "") {
                            //if from db (set/enum-type), it shouldn't be overwritten
                            $fields[$i]["valuelist"] = stripCSVList($row["valuelist"]);
                        }
                        $fields[$i]["not_brief"] = $row["not_brief"];
                        $fields[$i]["order_index"] = $row["order_index"];
                        $fields[$i]["embed_in"] = $row["embed_in"];
                    }
                    if (eregi('int',$fields[$i]["data_type"]) and $fields[$i]["size"] != 1) {
                        $fields[$i]["validation"] = 'number';
                    }
                }
            }
        }
        
        // group field : valuelist stuff
        for ($i=0; $i<count($fields); $i++) {
            // remember from where the values come
            if ($fields[$i]["valuelist"] != "") {
                $fields[$i]['valuelist_from_db'] = true;
            } else {
                $fields[$i]['valuelist_from_db'] = false;
            }
        }
        
        uasort($fields,"cmpByOrderIndexAsc");
        $entity["fields"] = $fields;
        
        return $entity;
    }
    
    /*  get names of purely relational tables (and the number of their fields)
that need to be filled from this table
At this time, PP only treats relational tables with two columns differently.
The thing is: A table (and its page) is responsible to fill values
in a relational table when it is referenced from the first column!
This is an assumption, but it it makes sense if you think about it.
*/
    function getRelationCandidatesFor ($tablename) {
        $entity = getEntity($tablename); // find tables that link to this table from their 1st field
        $linking_tables = array(); $rf = getReferencingTableData($entity);
        // make sure we know what the 1st field is
        foreach($rf as $t) {
            $res = pp_run_query('SHOW COLUMNS FROM `'.$t['table_name'].'`');
            if ($res[0]['Field'] == $t['fk']['field']) {
                $linking_tables[] = $t['table_name'];
            }
        }
        
        $rel_candidates = array();
        foreach($linking_tables as $lt) {
            // find out if they are purely relational (contain only keys)
            $entity = getEntity($lt);
            $purely_relational = true;
            $rf = getReferencedTableData($entity);
            if (count($rf)==0) {
                $purely_relational = false;
            } else foreach($entity['fields'] as $f) {
                foreach($rf as $r) {
                    $found = false;
                    if ($r['fk']['field'] == $f['name']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $purely_relational = false;
                    break;
                }
            }
            if ($purely_relational) {
                $rel_candidates[] = array($lt, count($entity['fields']), $rf);
            }
        }
        return $rel_candidates;
    }
    
    
    /*
Searches the database for foreign keys (possible in InnoDB tables, for example)
and returns an array of them. This is the structure you get:

fks
|_
|_ "table"
|_ "field"
|_ "ref_table"
|_ "ref_field"
|_ "on_update"
|_ "on_delete"
|_ 'in_db"

The user can also enter references from fields to pages in the interface.
Those will be collected, too (from the table _sys_fields).
NOTE: Pages are views on tables (there can be several multipages for a table and
a singlepage is one part-view on _sys_sections).
The user handles pages! So the references the user enters refer to pages
So they will have a "ref_pages" - attribute instead of "ref_table" and a
"page" one instead of "table"
You can also differentiate them with "in_db"

According to this, the key-name will be:
[{table}|{page}]_[{ref_table}|{ref_page}]_{ref_field}
*/
    $fks = "";
    function getForeignKeys()
    {
        global $db,$fks;
        
        if ($fks == "" or count($fks)==0) {
            $tables = pp_run_query("SHOW TABLES");
            
            $fk = array();
            foreach($tables as $t) {
                $table = $t['Tables_in_'.getDBName()];
                $res = pp_run_query_unprepared("SHOW CREATE TABLE `".$table."`;");
                $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
                
                $create_query = $row['Create Table'];
                
                $crlf = "||";
                // Convert end of line chars to one that we want (note that MySQL doesn't return query it will accept in all cases)
                if (utf8_strpos($create_query, "(\r\n ")) {
                    $create_query = utf8_str_replace("\r\n", $crlf, $create_query);
                } else if (utf8_strpos($create_query, "(\n ")) {
                    $create_query = utf8_str_replace("\n", $crlf, $create_query);
                } else if (utf8_strpos($create_query, "(\r ")) {
                    $create_query = utf8_str_replace("\r", $crlf, $create_query);
                }
                
                // are there any foreign keys to cut out?
                if (preg_match('@CONSTRAINT|FOREIGN[\s]+KEY@', $create_query)) {
                    // Split the query into lines, so we can easily handle it. We know lines are separated by $crlf (done few lines above).
                    $sql_lines = utf8_explode($crlf, $create_query);
                    $sql_count = count($sql_lines);
                    
                    // lets find first line with foreign keys
                    for ($i = 0; $i < $sql_count; $i++) {
                        if (preg_match('@^[\s]*(CONSTRAINT|FOREIGN[\s]+KEY)@', $sql_lines[$i])) {
                            break;
                        }
                    }
                    
                    // If we really found a constraint, fill the contsraint array for this field:
                    if ($i != $sql_count) {
                        for ($j = $i; $j < $sql_count; $j++) {
                            if (preg_match('@CONSTRAINT|FOREIGN[\s]+KEY@', $sql_lines[$j])) {
                                //remove "," at the end
                                $sql_lines[$j] = preg_replace('@,$@', '', $sql_lines[$j]);
                                $tokens = utf8_explode(' ',$sql_lines[$j]);
                                
                                $fk['table'] = $table;
                                
                                $token_count = count($tokens);
                                // Here is an example string to understand the code better:
                                // "CONSTRAINT `verb_phrases_ibfk_1` FOREIGN KEY (`verbid`)
                                //  REFERENCES `verbs` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE"
                                // We will find out when the next token is interesting
                                // sometimes we'll have to cut stuff like (,) or the like off
                                // with utf8_substr()
                                for ($k=0; $k<$token_count; $k++) {
                                    // THE FIELD
                                    if ($tokens[$k] == 'KEY') {
                                        $fk['field'] = utf8_substr($tokens[$k + 1],2,-2);
                                    }
                                    
                                    // THE CONSTRAINT NAME
                                    if ($tokens[$k] == 'CONSTRAINT') {
                                        $fkname = utf8_substr($tokens[$k + 1],1,-1);
                                    }
                                    
                                    // WHERE DOES IT POINT?
                                    if ($tokens[$k] == 'REFERENCES') {
                                        $fk['ref_table'] = utf8_substr($tokens[$k + 1],1,-1);
                                        $fk['ref_field'] = utf8_substr($tokens[$k + 2],2,-2);
                                    }
                                    
                                    // ON UPDATE, ON DELETE
                                    //SET and NO have another token!
                                    if ($tokens[$k] == 'DELETE') {
                                        $fk['on_delete'] = $tokens[$k + 1];
                                        if ($tokens[$k + 1] == "SET" || $tokens[$k + 1] == "NO") {
                                            $fk['on_delete'] .= ' '.$tokens[$k + 2];
                                        }
                                    }
                                    if ($tokens[$k] == 'UPDATE') {
                                        $fk['on_update'] = $tokens[$k + 1];
                                        if ($tokens[$k + 1] == "SET" || $tokens[$k + 1] == "NO") {
                                            $fk['on_update'] .= ' '.$tokens[$k + 2];
                                        }
                                    }
                                    //defaults
                                    if ($fk['on_update'] == "") {
                                        $fk['on_update'] = "CASCADE";
                                    }
                                    if ($fk['on_delete'] == "") {
                                        $fk['on_delete'] = "RESTRICT";
                                    }
                                    
                                    // A MARKER THAT THIS IS REALLY A CONSTRAINT FROM THE DB
                                    $fk["in_db"] = 1;
                                }
                                $fks[] = $fk;
                            } else {
                                // that's all, folks
                                break;
                            }
                        }
                    }
                    // end if we found a constraint
                }
                // end if any fks at all
            }
            // end for all tables
            
        
    }
    //return an array so that foreach loops on this will work
    if ($fks=="") {
        return array();
    }
    return $fks;
}

function getFK($tablename, $fieldname)
{
    $fks = getForeignKeys();
    foreach($fks as $fk)
    if ($fk['table'] == $tablename and $fk['field'] == $fieldname) {
        return $fk;
    }
}



/* return "name = value" escape value with quotes if needed*/
function nameEqValueEscaped($data_type, $field_name, $value)
{
    return " ".$field_name." = ".escapeValue($data_type, $value);
}

function escapeValue($data_type, $value)
{
    if (!isTextType($data_type) and !isDateType($data_type) and $data_type != 'time') {
        return $value;
    } else {
        return "'".$value."'";
    }
}

/* Returns a really simple HTML table from a SQL statement */
function SQL2HTML($query, $title="Table Summary")
{
    
    if (!$result = pp_run_query($query)) {
        $sRetVal = mysql_error();
    } else {
        $sRetVal = "<table border='1'>\n";
        $sRetVal .= "<tr><th colspan='" . count($result[0]) . "'>";
        $sRetVal .= $title . "</th></tr>";
        $sRetVal .= "<tr>";
        $i=0;
        //while ($i < count($result[0])) {
        foreach(array_keys($result[0]) as $key){
            $sRetVal .= "<th>" . $key . "</th>";
            $i++;
        }
        $sRetVal .= "</tr>";
        foreach($result as $line){
            $sRetVal .= "\t<tr>\n";
            foreach($line as $col_value) {
                $sRetVal .= "\t\t<td>$col_value</td>\n";
            }
            $sRetVal .= "\t</tr>\n";
        }
        $sRetVal .= "</table>\n";
    }
    
    return($sRetVal);
}


?>

