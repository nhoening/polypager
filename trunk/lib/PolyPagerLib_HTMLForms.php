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

/* function index:
 * writeInputElement($tabindex, $type, $size, $name, $class, $value, $full_editor, $dis)
 * writeFiller($spec, $fields, $value, $inp_name, $ind=10)
 * writeOptionList($tabindex, $name, $class, $value, $valuelist)
 * proposeFeeding($tabindex, $value)
 * writeHTMLForm($row, $action_target, $full_editor, $show, $id)
 */

//we could be called from two places - so include both possibilities and no one gets hurt
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot().'plugins'.FILE_SEPARATOR.'FCKeditor'.FILE_SEPARATOR);

require_once("fckeditor.php");

/* 	writes out one HTML Form Input Element
	you can specifiy the following:
	$tabindex: The tabindex in the Form
	$type: MySQL type, one out of blob, longtext, longblob (will all
			lead to a textarea), varchar, int (both text-input),
			bool (checkbox)
	$size: size for datatypes like varchar
	$name: Name of the Input Element
	$class: CSS class of the Input Element
	$value: to be shown in the Input Element
	$full_editor: true when full editor should be used, false otherwise
	$dis: true when field should be disabled
*/
function writeInputElement($tabindex, $type, $size, $name, $class, $value, $full_editor, $dis, $ind=9) {
	global $params;
	$indent = translateIndent($ind);
	
	//write Opening Tag and JS Calls
	$inputType = "input";
	if(isTextAreaType($type)) $inputType = "textarea";
	if (gettype($value) == "string" and $inputType != "textarea") {
		//we cannot write " inside of input-Elements (they're standalone in XHTML)
		$value = str_replace('"', "&quot;", $value);
	}
	echo($indent);
	if ($inputType != "textarea") echo('<'.$inputType.' id="'.$name.'_input" tabindex="'.$tabindex.'"');
	
	//now all inner stuff (attributes, value...)
	//list of types: varchar|date|int|bool|longtext|blob|longblob
	if ($inputType == "textarea")	{
		/*
		$value=filterXMLChars($value);	//make HTML Code visible in editing mode
		echo(' rows="5" cols="70" name="'.$name.'">');
		if ($value == "") echo('...');	//without any data we have strange behavior in IE and Opera
		else echo($value);
		*/
		$oFCKeditor = new FCKeditor($name);
		if (!$full_editor) $oFCKeditor->ToolbarSet = 'Basic';
		$path = getPathFromDocRoot();
		
		$oFCKeditor->BasePath = str_replace("\\", '/', $path).'plugins/FCKeditor/';
		$oFCKeditor->Value = $value;
		$oFCKeditor->Width  = '100%' ;
		$oFCKeditor->Height = '300' ;
		$oFCKeditor->Config['CustomConfigurationsPath'] = str_replace("\\", '/', $path).'plugins/fckconfig.php'  ;
		$oFCKeditor->Create() ;
	} else if ($type == "varchar" or $type=="string" or $type == "date") {
		if ($size > 50) {
			if ($value == "") {echo(' size="50" maxlength="'.$size.'" name="'.$name.'" type="text"');}
			else {echo(' size="50" maxlength="'.$size.'" name="'.$name.'" type="text" value="'.$value.'"');}
		}else {
			if ($value == "") {echo(' size="'.$size.'" maxlength="'.$size.'" name="'.$name.'" type="text"');}
			else {echo(' size="'.$size.'" maxlength="'.$size.'" name="'.$name.'" type="text" value="'.$value.'"');}
		}
		
	} else if ($type=="date") {
		//yet to be implemented in a specific and editable way - up to now (04/06) all dates are handled automatically
		//we just do the same as for strings (see above)
	} else if ($type=="int") {
		if ($value=="") {echo(' size="10" maxlength="'.$size.'" name="'.$name.'" type="text"');}
		else {echo(' size="10" maxlength="'.$size.'" name="'.$name.'" type="text" value="'.$value.'"');}
	} else if ($type=="bool") {
		if ($value=="") {echo(' name="'.$name.'" type="checkbox"');}
		else {
			if ($value=="1") {echo(' size="1" name="'.$name.'" type="checkbox" checked="true"');}
			else {echo(' size="1" name="'.$name.'" type="checkbox"');}
		}
	}
	//now the closing tag
	if ($dis)	{
		echo(' disabled="disabled" ');
	}
	if ($inputType != "textarea") echo('/>'."\n");
	
	//do we fill this field from a list?
	$entity = getentity(getMultipageNameByNr($params['nr']));
	if ($entity != "" and $entity["fillafromb"] != ''){
		//get field names
		$fields = array();
		for($i = 0; $i < count($entity["fields"]); $i++) {
			$fields[$i] = $entity["fields"][$i]["name"];
		}
		foreach($entity["fillafromb"] as $f) {
			if('_formfield_'.$f[0] == $name) writeFiller($f, $fields, $value, $name.'_input', $indent);
		}
	}
		
	//finished
}

/*
  display a list from which the user can click items into the actual textbox
  @param spec specification of that input ([field_name, values_already_in_there])
  @param fields possible values that might be input to that field
  @param value value of the field (so far)
  @param inp_name name of the input field
*/
function writeFiller($spec, $fields, $value, $inp_name, $ind=10){
	$indent = translateIndent($ind);
	if ($spec[1] != ''){
		//this is what we want in b
		$diff1 = array_in_one(explode(',',$spec[1]), $fields);
		//this is what we don't want from that
		$b = array_in_one($fields, explode(',',$value));
		echo($indent.'<div class="filler">'.__('choose:'));
		$helptext = __('This list is here to make sensible suggestions to fill the field above conveniently. Click on an item to paste it into the text box. You can also write something else in there if you know what you are doing :-) Clicking reset will restore the state to the load-time of the page.');
		writeHelpLink($indent, $helptext);
		echo($indent.'(<a onclick="reset(\''.$inp_name.'\');">'.__('reset').'</a>) - '."\n");
		for($i = 0; $i < count($b); $i++) {
			echo('<a id="'.$inp_name.$i.'filler" onclick="moveContent(\''.$inp_name.'\',\''.$inp_name.$i.'filler\')">'.trim($b[$i]).'</a>&nbsp;-&nbsp;');
		}
		echo("\n".$indent."</div>\n");
	}
}

/*
 returns an array containing only what was not in both input arrays (and is)
*/
function array_in_one($a1, $a2) {
	$r = array();
	for($i = 0; $i < count($a1); $i++) {
		if($a1[$i] != '' and !in_array($a1[$i], $a2)) $r[count($r)] = $a1[$i];
	}
	for($j = 0; $j < count($a2); $j++) {
		if($a2[$j] != '' and !in_array($a2[$j], $a1)) $r[count($r)] = $a2[$j];	
	}
	return $r;
}


/* 	writes out a HTML Form Select Element
	you can specifiy the following:
	$tabindex: The tabindex in the Form
	$name: Name of the Select Element
	$class: CSS class of the Select Element
	$value: The value that needs to be preselected
	$valuelist: The comma-separated values to select from ("x,y,z")
				it can contain key/value pairs like this: ("x:a,y:b,z:c")
	$dis: true when field should be disabled
	$js: any javascript you might want to add to the select element
*/
function writeOptionList($tabindex, $name, $class, $value, $valuelist, $dis, $js, $ind=9) {
	$indent = translateIndent($ind);
	if ($dis)	{
		$disabled = ' disabled="disabled" ';
	}
	echo($indent.'<select id="'.$name.'_input" tabindex="'.$tabindex.'" name="'.$name.'" '.$disabled.' '.$js.' class="'.$class.'">'."\n");
	$list_arr = explode(",", $valuelist);
	for($x=0;$x < count($list_arr);$x++){
		if (ereg(':',$list_arr[$x])) {
			$tmp = explode(':',$list_arr[$x]);
			$key = chop($tmp[0]);
			$val = chop($tmp[1]);
		} else{
			$key = $list_arr[$x]; $val = $list_arr[$x];
		}
		echo($indent.'	<option value="'.$key.'"'); 
		if($value == $val){echo(' selected="true"');} 
		echo('>'.$val.'</option> '."\n");
	}
	echo($indent.'</select>'."\n");
}

/**
 * display a check box 
 */
function proposeFeeding($tabindex, $value, $ind=11) {
	$indent = translateIndent($ind);
	$helptext = __('if you tick this checkbox before hitting the save button, this entry will be seen on top of the feed list (the list of latest entries).');
	echo($indent.'<span id="feedbox">feed now:'."\n");
	writeInputElement($tabindex, 'bool', 1, '_formfield_feedbox', '', $value, false, false);
	writeHelpLink($indent, $helptext);
	echo($indent.'</span>'."\n");
}


/* writes out an HTML Form for singlepages, multipages and other stuff
that is described in $entity. It can fill the form with data from $row
*/
function writeHTMLForm($row, $action_target, $full_editor, $show, $ind=4, $id) {
	$indent = translateIndent($ind);
	$nind = $ind + 1;
	global $params;
	$entity = getEntity($params["page"]);
	$page_info = getPageInfo($params["page"]);
	$hidden_form_fields = explode(",",$entity["hidden_form_fields"]);
	$disabled_fields = explode(",",$entity["disabled_fields"]);
	$consistency_fields = explode(",",$entity["consistency_fields"]);
	
	if ($show) $display = 'display:block;'; else $display = 'display:none;';
	if ($id) $id_text = $id; else $id_text = '';
	
	//some javascript to get the time it took the user to do the input 
	//(good for detecting commentspam/sblog) - thanks to oswaldism.de
	echo($indent.'<script language="JavaScript" type="text/javascript">'."\n");
	echo($indent.' var loaded=Math.round(new Date().getTime());'."\n");

	echo($indent.' function oswald(formname)'."\n");
	echo($indent.' {'."\n");
	echo($indent.'   var now=Math.round(new Date().getTime());'."\n");
	echo($indent.'   document.forms[formname].elements["_formfield_time_needed"].value=now-loaded;'."\n");
	echo($indent.'   return true;'."\n");
	echo($indent.' }'."\n");
	echo($indent.'</script>'."\n");
	
	//empty form - we might have values in $params["values"] preconfigured (e.g. for hidden fields) - a little trick convention
	//             or we can look into the group param value or the field's database defaults
	if ($params["cmd"] == "new") {
		//let's show that this is NOT a saved entry
		if ($params["page"] != "_sys_comments"){
			echo($indent.'<div class="sys_msg">'.__('this item has not yet been inserted into the database!').'</div>');
		}
		$target_nr = $params["nr"]; if ($target_nr == "") $target_nr = $params["values"]["nr"];
		echo($indent.'<div style="'.$display.'">'."\n");
		echo($indent.'	<a class="target" name="commentform_anchor"></a>'."\n");
		echo($indent.'	<form name="new_form" id="'.$id_text.'" class="edit" action="'.$action_target.'?'.$params["page"].'&amp;nr='.$target_nr.'" method="post" onsubmit="return oswald(\'new_form\');"><fieldset><table>'."\n");
		
		echo($indent.'		<input type="hidden" name="_formfield_time_needed" value="">'."\n");
		$index = 1;
		foreach($entity["fields"] as $f) {
			// looking for preconfigured values with following prio: 
			// 1. in params
			// 2. in group field 
			// 3. in db defaults
			$preval = $f["default"];
			if ($f["name"] == $entity["group"]["field"]) $preval = $params["group"];
			if ($params['values'][$f['name']] != "") $preval = $params['values'][$f['name']];

			if ( in_array($f["name"],$hidden_form_fields))	{
				echo($indent.'			');
				echo('<tr><td></td><td class="data"><input type="hidden" name="_formfield_'.$f['name'].'" value="'.$preval.'"/></td></tr>'."\n");
			} else {
				echo($indent.'				<tr>'."\n");
				echo($indent.'			<td class="label"><label for="'.$f['name'].'_input">');
				if ($f['label'] != "") echo($f['label'].':'); else echo(__($f['name'].':'));
				if ($f["help"] != "") writeHelpLink($indent, $f["help"]);
				echo($indent.'</label></td>'."\n");
				
				//if we need a text area, let it span over two columns
				if(isTextAreaType($f['data-type'])) echo($indent.'			<td></td></tr><tr>'."\n");
					
				if (in_array($f['name'],$disabled_fields)) {
					$dis = true;
					echo($indent.'			<input type="hidden" name="_formfield_'.$f["name"].'" value="'.$preval.'"/>'."\n");
				} else $dis = false;
				
				//if the tablename-field changes, other fields need new data from that table!!
				if ($params["page"] == '_sys_multipages' and $f['name'] == 'tablename')	{
					$alert = ' onChange="javascript:submit_form_by_selection(\'entry\');" ';
				} else $alert = "";
				//now write HTML for that input field - finally
				
				if ($f['valuelist'] == "") {
					echo($indent.'			<td class="data"');
					if(isTextAreaType($f['data-type'])) echo(' colspan="2" ');
					echo('>'."\n");
					writeInputElement($index, $f['data-type'], $f['size'], '_formfield_'.$f['name'], $f['class'], $preval, $full_editor,$dis,$nind+3);
				} else {
					echo($indent.'			<td class="data">'."\n");
					writeOptionList($index, '_formfield_'.$f['name'], $f['class'], $preval, $f['valuelist'],$dis, $alert,$nind+3);
				}
				echo("\n".$indent.'			</td>'."\n"); 
				echo($indent.'			</tr>'."\n");
			}
		}

		echo($indent.'			<tr class="submit"><td>'."\n");
		echo($indent.'				<input value="'.$params['page'].'" name="page" type="hidden" />'."\n");
		echo($indent.'				<input value="'.$params['topic'].'" name="topic" type="hidden" />'."\n");
		echo($indent.'				<input value="'.$params['group'].'" name="group" type="hidden" />'."\n");
		echo($indent.'				<input value="'.$params['from'].'" name="from" type="hidden" /></td>'."\n");
		echo($indent.'				<input type="hidden" id="the_real_cmd" name="cmd" value="nothing_yet"/>'."\n");
		echo($indent.'				<td class="form_submits">'."\n");
		if (!ereg('_sys_', $params["page"])) proposeFeeding(++$index, 1, $nind+3);
		echo($indent.'					<button tabindex="'.++$index.'" type="submit" onclick="get(\'the_real_cmd\').value=\'entry\';return checkValues(\''.$params['page'].'\');">'.__('Send').'</button>'."\n");
		echo($indent.'				</td>');
		echo($indent.'			</tr></table>'."\n");
		echo($indent.'	</fieldset></form>'."\n");
		echo($indent.'</div>'."\n");
	} 
	else {										//form with data
		$params["nr"] = $row[$entity["pk"]];	//??
		echo($indent.'<div  display="'.$display.'">'."\n");
		echo($indent.'	<form name="edit_form" id="'.$id_text.'" class="edit" action="'.$action_target.'?'.$params["page"].'&amp;nr='.$params["nr"].'" method="post" onsubmit="return oswald(\'edit_form\');"><fieldset><table>'."\n");
		
		echo($indent.'		<input type="hidden" name="_formfield_time_needed" value="">'."\n");
		$index = 1;
		foreach($entity["fields"] as $f) {
				
			if (in_array($f["name"],$hidden_form_fields) )	{
				echo($indent.'		<tr><td></td><td class="data"><input type="hidden" name="_formfield_'.$f['name'].'" value="'.$row[$f['name']].'"/></td></tr>'."\n");
			} else {
				echo("							<tr>\n");
				// save old value if its relevant for consistency
				if(in_array($f["name"],$consistency_fields)) {
					echo('<input type="hidden" name="old_formfield_'.$f['name'].'" value="'.$row[$f['name']].'"/>'."\n");
				}
				echo($indent.'			<td class="label"><label for="'.$f['name'].'_input">');
				if ($f['label'] != "") echo($f['label'].':'); else echo(__($f['name'].':'));
				if ($f["help"] != "") writeHelpLink($indent, $f["help"]);	
				echo($indent.'</label></td>'."\n");
				
				//if we need a text area, let it span over two columns
				if(isTextAreaType($f['data-type'])) echo($indent.'			<td></td></tr><tr>'."\n");
				
				//hack for FCKeditor: singlepage-textareas must have different names
				//if there are more than one db-entry - form (is no more the case since veriosn 0.9.0)
				//if (isSinglepage($params["page"]) and $f['data-type'] == 'blob')  $areaid = $f['name'].''.$row[$entity["pk"]]; else $areaid = $f['name'];
				$areaid = $f['name'];
				
				if (in_array($f['name'],$disabled_fields)) {
					$dis = true;
					echo('<input type="hidden" name="_formfield_'.$f["name"].'" value="'.$row[$f['name']].'"/>'."\n");
				} else $dis = false;
				//if the tablename-field changes, other fields need new data from that table!!
				if ($params["page"] == '_sys_multipages' and $f['name'] == 'tablename')	{
					$alert = ' onChange="javascript:submit_form_by_selection(\'edit\');" ';
				} else $alert = "";
				//now write HTML for that input field - finally
				
				if ($f['valuelist'] == "") {
					echo($indent.'			<td class="data"');
					if(isTextAreaType($f['data-type'])) echo(' colspan="2" ');
					echo('>'."\n");writeInputElement($index, $f['data-type'], $f['size'], '_formfield_'.$areaid, $f['class'], $row[$f['name']], $full_editor, $dis, $nind+3);
				} else {
					echo($indent.'			<td class="data">'."\n");
					writeOptionList($index, '_formfield_'.$f['name'], $f['class'], $row[$f['name']], $f['valuelist'], $dis, $alert, $nind+3);
				}
				echo("\n".$indent.'			</td>'."\n"); 
				echo($indent.'		</tr>'."\n");
				$index++;
			}
		}
		
		//submits
		//hack for FCKeditor: hidden elements must have different names
		$real_cmd_name = "the_real_cmd";
		if (isSinglepage($params["page"]))  $real_cmd_name = $real_cmd_name.''.$row[$entity["pk"]];
		echo($indent.'		<tr class="submit">'."\n");
		echo($indent.'			<td><input value="'.$params['nr'].'" name="nr" type="hidden" />'."\n");
		echo($indent.'			<input value="'.$params['page'].'" name="page" type="hidden" />'."\n");
		echo($indent.'			<input value="'.$params['topic'].'" name="topic" type="hidden" />'."\n");
		echo($indent.'			<input value="'.$params['group'].'" name="group" type="hidden" />'."\n");
		echo($indent.'			<input value="'.$params['from'].'" name="from" type="hidden" /></td>'."\n");
		//echo($indent.'		<td class="form_options"><input type="checkbox" name="opt"/> '.__('show next entry').'</td>'."\n");
		echo($indent.'			<td class="form_submits">'."\n");
		if (!ereg('_sys_', $params["page"]))  proposeFeeding(++$index, 0, $nind+3);
		echo($indent.'				<input type="hidden" id="'.$real_cmd_name.'" name="cmd" value="nothing_yet"/>'."\n");
		echo($indent.'				<button tabindex="'.++$index.'" type="submit" onclick="get(\''.$real_cmd_name.'\').value=\'edit\';return checkValues(\''.$params['page'].'\');">'.__('Save').'</button>'."\n");
		if($entity["one_entry_only"] != "1") echo($indent.'				<button tabindex="'.++$index.'" type="submit" onclick="get(\''.$real_cmd_name.'\').value=\'delete\';return checkDelete();">'.__('Delete').'</button>'."\n");
		echo($indent.'			</td>'."\n");
		echo($indent.'		</tr>'."\n");
		echo($indent.'	</table></fieldset></form>'."\n");
		echo($indent.'</div>'."\n");

	}
	// are tables/pages linking to this entity via foreign keys?
	$references = getReferencingTableData($entity);
	foreach($references as $r){
		echo('<div class="sys_msg">there is sthg linking here: '.$r['table_name'].', page is '.$r['fk']['page'].'</div>');
	}
}	//end function writeHTMLForm()
?>