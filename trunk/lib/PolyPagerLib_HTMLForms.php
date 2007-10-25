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
 * writeFiller($spec, $fields, $value, $ind=10)
 * writeOptionList($tabindex, $name, $class, $value, $valuelist)
 * proposeFeeding($tabindex, $value)
 * writeHTMLForm($row, $action_target, $full_editor, $show, $id)
 */

//we could be called from two places - so include both possibilities and no one gets hurt
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'].getPathFromDocRoot().'plugins'.FILE_SEPARATOR.'FCKeditor'.FILE_SEPARATOR);

require_once("fckeditor.php");
require_once("plugins"  . FILE_SEPARATOR .  "recaptchalib.php");

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

    
    //do we fill this field from a list?
    $need_filler = false;
	$entity = getEntity(getMultipageNameByNr($params['nr']));
	if ($entity != "" and $entity["fillafromb"] != ''){
		foreach($entity["fillafromb"] as $f) {
			if('_formfield_'.$f[0] == $name) { 
                $need_filler = true;
                $fillafromb = $f;
                break;
            }
		}
	}
    
	//write Opening Tag and JS Calls
	$inputType = "input";
	if(isTextAreaType($type)) $inputType = "textarea";

	echo($indent);
	if ($inputType != "textarea") echo('<'.$inputType.' id="'.$name.'_input" tabindex="'.$tabindex.'"');
	
	//now all inner stuff (attributes, value...)
    
	if ($inputType == "textarea")	{
		$oFCKeditor = new FCKeditor($name);
		if (!$full_editor) $oFCKeditor->ToolbarSet = 'Basic';
        else $oFCKeditor->ToolbarSet = 'PolyPager';
		$path = getPathFromDocRoot();
		
		$oFCKeditor->BasePath = utf8_str_replace("\\", '/', $path).'plugins/FCKeditor/';
		$oFCKeditor->Value = $value;
		$oFCKeditor->Width  = '100%' ;
		$oFCKeditor->Height = '300' ;
		$oFCKeditor->Config['CustomConfigurationsPath'] = str_replace("\\", '/', $path).'plugins/fckconfig.php'  ;
		$oFCKeditor->Create() ;
	} else if (isTextType($type) or isDateType($type)) {
        //$value = utf8_str_replace("&", "&amp;", $value);
        //we cannot write " inside of input-Elements (they're standalone in XHTML)
		$value = utf8_str_replace('"', "&quot;", $value);
        if ($need_filler) $t = 'hidden'; else $t = 'text';
		if ($size > 36) {
			//if ($value == "") echo(' size="36" maxlength="'.$size.'" name="'.$name.'" type="'.$t.'"');
			//else 
            echo(' size="36" maxlength="'.$size.'" name="'.$name.'" type="'.$t.'" value="'.$value.'"');
		}else {
			if ($value == "") echo(' size="'.$size.'" maxlength="'.$size.'" name="'.$name.'" type="'.$t.'"');
			else echo(' size="'.$size.'" maxlength="'.$size.'" name="'.$name.'" type="'.$t.'" value="'.$value.'"');
		}
		
	} else if (isDateType($type)) {
		//yet to be implemented in a specific and editable way - up to now (04/06) all dates are handled automatically
		//we just do the same as for strings (see above)
        //we add a calendar, though (see below)
        
	} else if (eregi('int',$type) or $type=="float" or $type=="double" or $type=="decimal") {
		echo(' size="10" maxlength="'.$size.'" name="'.$name.'" type="text"');
		if ($value!="") echo(' value="'.$value.'"');
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
	
    //calendar fields
    if (isDateType($type)){
        echo('<button id="_datefield_setter'.$name.'">...</button>');
    }
    
	if($need_filler) writeFiller($name, array(explode(',', $value), array()), array($fillafromb[1], array()), $indent);

}

/*
  display a list from which the user can click items into a box
  (the actual textbox also gets filled but should be hidden)
  @fieldname fieldname
  @param values: values of the field (so far) : array of two value arrays (2nd can carry presentation vals)
  @param possible_values: array of two value arrays (2nd can carry presentation vals)
*/
function writeFiller($fieldname, $values, $possible_values, $ind=10){
    $inp_name = $fieldname.'_input';
    $fill_name = '_filled_'.$fieldname.'_input';
	$indent = translateIndent($ind);
    
    $show_vals = $values[0];
    if (count($values[1]) > 0) $show_vals = $values[1] ;

    //the visible filled box
    echo($indent.'<div class="filled" id="'.$fill_name.'">'."\n");
    for($i = 0; $i < count($show_vals); $i++) {
        if($show_vals[$i]!='') {
            echo('<a onclick="moveContent()">'.$show_vals[$i].'</a>'."\n");
            if ($i < count($show_vals)-1) echo(','); 
        }
    }
    echo($indent."</div>\n");
	
    // show what can still be filled in
    
    
    $show_poss_vals = $possible_values[0];
    if (count($possible_values[1]) > 0) {
        $fillfrom = $inp_name.'fillfrom';
        $show_poss_vals = $possible_values[1] ;
        //the invisible from box
        echo($indent.'<div class="filled" style="display:none;" id="'.$fieldname.'_from">'."\n");
        for($i = 0; $i < count($possible_values[0]); $i++) {
            if($possible_values[0][$i]!='') {
                echo('<span id="'.$fillfrom.$i.'filler">'.$possible_values[0][$i].'</span>'."\n");
            }
        }
        echo($indent."</div>\n");
        
    } else $fillfrom = $inp_name;

    $b = arrays_exor($show_poss_vals, $show_vals);
    echo($indent.'<div class="filler">'.__('choose:'));
    $helptext = __('This list is here to make sensible suggestions to fill the field above conveniently. Click on an item to paste it into the text box. You can also write something else in there if you know what you are doing :-) Clicking reset will restore the state to the load-time of the page.');
    writeHelpLink($indent, $helptext);
    echo($indent.'(<a onclick="reset(\''.$inp_name.'\');reset(\''.$fill_name.'\');">'.__('reset').'</a>)&nbsp;-&nbsp;'."\n");
    for($i = 0; $i < count($b); $i++) {
        echo($indent.'<a  id="'.$inp_name.$i.'filler"'); 
        echo(' onclick="moveContent(\''.$fill_name.'\',\''.$inp_name.$i.'filler\');');
        echo('moveContent(\''.$inp_name.'\',\''.$fillfrom.$i.'filler\');');
        echo('">'.trim($b[$i]).'</a>'."\n");
        if ($i < count($b)-1) echo('&nbsp;-&nbsp;');
    }
    echo("\n".$indent."</div>\n");
}


/*
 returns an array containing only what was not in both input arrays
 (Exclusive OR)
*/
function arrays_exor($a1, $a2) {
	$r = array();
	for($i = 0; $i < count($a1); $i++) {
		if($a1[$i] != '' and !in_array($a1[$i], $a2)) $r[] = $a1[$i];
	}
	for($j = 0; $j < count($a2); $j++) {
		if($a2[$j] != '' and !in_array($a2[$j], $a1)) $r[] = $a2[$j];	
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
	$list_arr = utf8_explode(",", $valuelist);
	//in the database, enum and set values must be enclosed by '' - away with that 
	if ($type == "enum" or $type == "set") 
		$value = $value = trim($value,'\'');
		for($x=0;$x < count($list_arr);$x++) $list_arr[$x] = trim($list_arr[$x],'\'');
	for($x=0;$x < count($list_arr);$x++){
		if (ereg(':',$list_arr[$x])) {	//key/value can come in valuelist
			$tmp = utf8_explode(':',$list_arr[$x]);
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
	$helptext = __('If you tick this checkbox before hitting the save button, this entry will be fed to the list of latest entries and the RSS.');
    $entity = getEntity();
    if ($entity["publish_field"]!="") $helptext .= __(' (If you do not publish this entry, it will be invisible there, too)');
	echo($indent.'<span id="feedbox">feed now:'."\n");
	writeInputElement($tabindex, 'bool', 1, '_formfield_feedbox', '', $value, false, false);
	writeHelpLink($indent, $helptext);
	echo($indent.'</span>'."\n");
}

/*	compares two fields, using the formgroup entries
    The resulting array will be sorted according to the order_index in the formgroup entry
*/
function cmpByFormGroup($a, $b) {
    //if the formgroups are the same, maintain the order given by order index
	if ($a['formgroup'] == $b['formgroup']) return ($a['order_index'] > $b['order_index']) ? 1 : -1;
	$entity = getEntity();	//actual entity
	if ($entity['formgroups'][''] == "") $entity['formgroups'][''] = array(100,'show');
	//compare the position of the formgroups on the entitys formgroup array
	//(indicated at position 0)
	return ($entity['formgroups'][$a['formgroup']][0] > $entity['formgroups'][$b['formgroup']][0]) ? 1 : -1;
}


/*  Write HTML Form code to fill data in relational tables
    This makes only sense for 2-column relational tables, where the first references this table
*/
function writeRelationalTableInputs($indent, $entity){
    $can = getRelationCandidatesFor($entity['tablename']);
    foreach ($can as $c) {
        if ($c[1] <= 2){ 
            // get values of rows in relational table with this key as first entry
            $query = 'SELECT '.$c[2][0]['fk']['ref_table'].'.'.$c[2][0]['fk']['ref_field'].', (SELECT '.$c[2][1]['title_field'].' FROM '.$c[2][1]['fk']['ref_table'].' WHERE '.$c[2][1]['fk']['ref_field'].' = '.$c[2][1]['fk']['table'].'.'.$c[2][1]['fk']['field'].') AS '.$c[2][1]['fk']['field'];
            $query .= ' FROM '.$c[2][0]['fk']['table'].','.$c[2][0]['fk']['ref_table'];
            $query .= ' WHERE '.$c[2][0]['fk']['table'].'.'.$c[2][0]['fk']['field'].' = '.$c[2][0]['fk']['ref_table'].'.'.$c[2][0]['fk']['ref_field'];
            $res = pp_run_query($query);
            $already_show_vals = array();
            $already_save_vals = array();
            while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $already_show_vals[] = $row[$c[2][1]['fk']['field']];
                $already_save_vals[] = $row[$c[2][0]['fk']['ref_field']];
            }
            //now get possible values
            $query = 'SELECT '.$c[2][1]['title_field'].', '.$c[2][0]['fk']['ref_field'].' FROM ' .$c[2][1]['fk']['ref_table'];
            $res = pp_run_query($query);
            $poss_show_vals = array();
            $poss_save_vals = array();
            while($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                $poss_show_vals[] = $row[$c[2][1]['title_field']];
                $poss_save_vals[] = $row[$c[2][0]['fk']['ref_field']];
            }
            echo('<input type="hidden" size="36" name="_formfield_'.$c[0].'_input" id="_formfield_'.$c[0].'_input" value="'.$vals.'"/>'."\n");
            writeFiller('_formfield_'.$c[0], array($already_save_vals, $already_show_vals), array($poss_save_vals, $poss_show_vals), $indent);
        }
    }
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
    $sys_info = getSysInfo();
	$hidden_form_fields = utf8_explode(",",$entity["hidden_form_fields"]);
	$disabled_fields = utf8_explode(",",$entity["disabled_fields"]);
	$consistency_fields = utf8_explode(",",$entity["consistency_fields"]);
	
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
	
	// empty form - we might have values in $params["values"] preconfigured (e.g. for hidden fields) - a little trick convention
    // or we can look into the group param value or the field's database defaults
	// if new, let's show that this is NOT a saved entry
	if ($params["cmd"] == "new" and $params["page"] != "_sys_comments"){
		echo($indent.'<div class="sys_msg">'.__('this item has not yet been inserted into the database!').'</div>');
	}
	$target_nr = $params["nr"]; 
    if ($target_nr == "") $target_nr = $params["values"]["nr"];
	if ($target_nr == "") $target_nr = $row[$entity["pk"]];
	
	echo($indent.'<div style="'.$display.'">'."\n");
	if ($params["page"] == "_sys_comments"){
		echo($indent.'	<a class="target" name="commentform_anchor"></a>'."\n");
        $target_page = $params["values"]["pagename"]; // the page the entry will appear on
    }else {
        $target_page = $params["page"];
    }
    
	echo($indent.'	<form accept-charset="'.$sys_info["encoding"].'" name="edit_form" id="'.$id_text.'" class="edit" action="'.$action_target.'?'.urlencode($target_page).'&amp;nr='.$target_nr.'" method="post" onsubmit="return oswald(\'edit_form\');">'."\n");
	echo($indent.'		<input name="_nogarbageplease_" id="_nogarbageplease_" value=""/>'."\n"); //this gets hidden by css to trap machine spam
	echo($indent.'		<input type="hidden" name="_formfield_time_needed" value=""/>'."\n");
	$index = 1;
    
    if ($params["page"] == "_sys_comments" and $sys_info["use_captchas"] == 1){
        echo recaptcha_get_html($sys_info['public_captcha_key'], null);
    }
	// sort according to formgroups
	if ($entity['formgroups']!="") {
        uasort($entity["fields"],"cmpByFormGroup");
    }
    else echo($indent.'		<table>'."\n"); //otherwise a table for each fieldset
	$lastFormGroup = "xxxxxxxx";
    $hasTextarea = false;
    
	foreach($entity["fields"] as $f) {
        if (isTextareaType($f['data_type'])) $hasTextarea = true;
		// open/close fieldsets according to formgroups
		if ($entity['formgroups']!="" and $lastFormGroup != "xxxxxxxx" and $lastFormGroup != $f['formgroup']) {
			echo($indent.'		</table></div></fieldset>'."\n");
		}
		if ($entity['formgroups']!="" and $lastFormGroup != $f['formgroup']) {
			echo($indent.'		<fieldset>');
			if ($f['formgroup']!="") {
				echo('<legend>&nbsp;'.__($f['formgroup']));
				if ($entity['formgroups'][$f['formgroup']][1] == 'hide') {
					$neg_state = "show";
				}else {
					$neg_state = "hide";
				}
				echo('<a id="'.$f['formgroup'].'_link" href="javascript:toggleVisibility(\''.$f['formgroup'].'\',\''.$f['formgroup'].'_link\',\''.__('(show)').'\',\''.__('(hide)').'\');">'.__('('.$neg_state.')',$f['formgroup']).'</a>');
				echo('&nbsp;</legend>');
			}
			echo('<div id="'.$f['formgroup'].'"');
			if ($entity['formgroups'][$f['formgroup']][1] == 'hide') echo(' style="display:none;"');
			else  echo(' style="display:block;"');
			echo('><table>'."\n");
		}
		
		if ($params["cmd"] == "new"){
			// looking for preconfigured values with following prio: 
			// 1. in params
			// 2. in group field 
			// 3. in db defaults
			$val = $f["default"];
			if ($f["name"] == $entity["group"]["field"]) $val = $params["group"];
			if ($params['values'][$f['name']] != "") $val = $params['values'][$f['name']];
		}else{
			$val = $row[$f['name']];
		}
		
		if (in_array($f["name"],$hidden_form_fields) )	{
			echo($indent.'		<tr><td></td><td class="data"><input type="hidden" name="_formfield_'.$f['name'].'" value="'.$val.'"/></td></tr>'."\n");
		} else {
			echo($indent.'		'."<tr>\n");
			// save old value if its relevant for consistency
			if(in_array($f["name"],$consistency_fields)) {
				echo('<input type="hidden" name="old_formfield_'.$f['name'].'" value="'.$val.'"/>'."\n");
			}
			echo($indent.'			<td class="label"><label for="_formfield_'.$f['name'].'_input">');
			if ($f['label'] != "") echo($f['label'].':'); else echo(__($f['name'].':'));
			if ($f["help"] != "") writeHelpLink($indent, $f["help"]);	
			echo($indent.'</label></td>'."\n");
			
			//if we need a text area, let it span over two columns
			if(isTextAreaType($f['data_type'])) echo($indent.'			<td></td></tr><tr>'."\n");
			
			if (in_array($f['name'],$disabled_fields)) {
				$dis = true;
				echo('<input type="hidden" name="_formfield_'.$f["name"].'" value="'.$val.'"/>'."\n");
			} else $dis = false;
			//if the tablename-field changes, other fields need new data from that table!!
			if ($params["page"] == '_sys_multipages' and $f['name'] == 'tablename')	{
				$alert = ' onChange="javascript:submit_form_by_selection(\'edit\');" ';
			} else $alert = "";
			//now write HTML for that input field - finally
			
			if ($f['valuelist'] == "") {
				echo($indent.'			<td class="data"');
				if(isTextAreaType($f['data_type'])) echo(' colspan="2" ');
				echo('>'."\n");writeInputElement($index, $f['data_type'], $f['size'], '_formfield_'.$f['name'], $f['class'], $val, $full_editor, $dis, $nind+3);
			} else {
				echo($indent.'			<td class="data">'."\n");
				writeOptionList($index, '_formfield_'.$f['name'], $f['class'], $val, $f['valuelist'], $dis, $alert, $nind+3);
			}
			echo("\n".$indent.'			</td>'."\n"); 
			echo($indent.'		</tr>'."\n");
			
			
			$lastFormGroup = $f['formgroup'];
			
			$index++;
		}
	}
	
	//close last formgroup?
	if ($entity['formgroups']!="" and $lastFormGroup != "xxxxxxxx") {
		echo($indent.'		</table></div></fieldset>'."\n");
	}
	if ($entity['formgroups']=="") echo($indent.'		</table>'."\n"); //otherwise a table for each fieldset
	
    // this writes input elements to fill data in purely relational
    //tables for which this table is responsible
    writeRelationalTableInputs($indent, $entity);
    
    
	// ------ submit section ------
	$next_command = 'edit';
	if($params["cmd"]=="new") {$next_command="entry";}
	echo($indent.'		<table>'."\n");
    echo($indent.'		    <tr class="submit">'."\n");
	echo($indent.'			    <td style="width:50%;">'."\n");
    //preview
    if ($hasTextarea and $_GET["cmd"]!="preview") {
        echo($indent.'			<script type="text/JavaScript">'."\n");
        echo($indent.'			function getValues(){'."\n");
        echo($indent.'			   var t = \'\';'."\n");
        foreach ($entity["fields"] as $f){
            if (isTextAreaType($f["data_type"])) {
                echo($indent.'		    	   var oEditor = FCKeditorAPI.GetInstance(\'_formfield_'.$f["name"].'\');'."\n");
                echo($indent.'		    	   t += \'_formfield_'.$f["name"].'=\' + escape(oEditor.GetXHTML(false)) + \'&amp;\';'."\n");
            }else
                echo($indent.'		    	   t += \'_formfield_'.$f["name"].'=\' + escape(document.edit_form._formfield_'.$f["name"].'.value) + \'&amp;\';'."\n");
        }
        echo($indent.'			   return t;'."\n");
        echo($indent.'			}</script>'."\n");
        echo($indent.'			<a href="javascript:void(0)" onclick="GB_showFullScreen(\'Preview\', \'../../?'.urlencode($params["page"]).'&amp;cmd=preview&amp;\' + getValues());">Preview</a>'."\n");
    }
    
    //hidden values
	if($params["cmd"] != "new") echo($indent.'			<input value="'.$params['nr'].'" name="nr" type="hidden" />'."\n");
	echo($indent.'			<input value="'.urlencode($params['page']).'" name="page" type="hidden" />'."\n");
	echo($indent.'			<input value="'.$params['topic'].'" name="topic" type="hidden" />'."\n");
	echo($indent.'			<input value="'.urlencode($params['group']).'" name="group" type="hidden" />'."\n");
	echo($indent.'			<input value="'.$params['from'].'" name="from" type="hidden" /></td>'."\n");
    
	//echo($indent.'		<td class="form_options"><input type="checkbox" name="opt"/> '.__('show next entry').'</td>'."\n");
	echo($indent.'			<td class="form_submits">'."\n");
	if($params["cmd"] == "new") $feed = 1; else $feed = 0;
	if (!ereg('_sys_', $params["page"]))  proposeFeeding(++$index, $feed, $nind+3);
	echo($indent.'				<br/><input type="hidden" id="cmd" name="cmd" value="'.$next_command.'"/>'."\n");
	echo($indent.'				<button tabindex="'.++$index.'" type="submit" onclick="return checkValues(\''.$params['page'].'\');">'.__('Save').'</button>'."\n");
	if($params["cmd"] != "new" and $entity["one_entry_only"] != "1") echo($indent.'				<button tabindex="'.++$index.'" type="submit" onclick="get(\'cmd\').value=\'delete\';return checkDelete();">'.__('Delete').'</button>'."\n");
	echo($indent.'			    </td>'."\n");
	echo($indent.'		    </tr>'."\n");
	echo($indent.'    </table>'."\n");
    echo($indent.'    </form>'."\n");
    
    // for date fields: add javascript for calendar (popup)
    foreach($entity['fields'] as $f){
        if (isDateType($f['data_type']) and !eregi($f['name'],$entity["hidden_form_fields"])){
            echo($indent.'	<script type="text/javascript">'."\n");
            echo($indent.'	Calendar.setup('."\n");
            echo($indent.'	{'."\n");
            echo($indent.'	inputField  : "_formfield_'.$f['name'].'_input",         // ID of the input field'."\n");
            echo($indent.'  step        :    1,      //include year'."\n");
            if (isTimeType($f['data_type'])) 
                echo($indent.'  showsTime      :    true,'."\n");
            echo($indent.'  firstDay       :   0,      //0-6'."\n");
            echo($indent.'	ifFormat    : "'.getDateFormat($f['data_type']).'",    // the date format'."\n");
            echo($indent.'	button      : "_datefield_setter_formfield_'.$f['name'].'"       // ID of the button'."\n");
            echo($indent.'	}'."\n");
            echo($indent.'	);'."\n");
            echo($indent.'	</script>'."\n");
        }
    }
    
	echo($indent.'</div>'."\n");
        
	// are tables/pages linking to this entity via foreign keys?
	$references = getReferencingTableData($entity);
	foreach($references as $r){
		echo('<div class="sys_msg">there is sthg linking here: table "'.$r['table_name'].'", page is "'.$r['likely_page'].'"</div>');
	}
}	//end function writeHTMLForm()


?>
