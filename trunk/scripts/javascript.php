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

header("Content-type: text/javascript; charset=iso-8859-1");

// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
include_once('..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');
$sys_info = getSysInfo();

?>
/*----------------------------------------------------------------
  This JavaScript was generated by PolyPager 
  see http://polypager.nicolashoening.de
  ----------------------------------------------------------------*/
  
// id browsers
var name = navigator.userAgent.toLowerCase()
var opera = (name.indexOf("opera")> -1) //this is just Opera
var iex=(document.all);	//this can also be Opera!
var nav=(document.layers);
var old=(navigator.appName=="Netscape" && !document.layers && !document.getElementById);
var n_6=(window.sidebar);

function getElementsByClass(classname){
	var elements = new Array();
	var inc = 0;
	var alltags = document.all? document.all : document.getElementsByTagName("*");
	for (i = 0; i < alltags.length; i++){
		if (alltags[i].className == classname)
			elements[inc++] = alltags[i];
	}
	return elements;
}

function toggleVisibility(theElementID, theLinkID, text_invis, text_vis) {
	el = get(theElementID);
	theLink = get(theLinkID);
	theLinkNester = get(theLinkID+'_nester');
	if (el.style.display == 'none' || el.style.display == '') {
		el.style.display = 'block';
		theLink.innerHTML = text_vis;
		if(theLinkNester) theLinkNester.className = 'clicked';
	} else {
		el.style.display = 'none';
		theLink.innerHTML = text_invis;
		if(theLinkNester) theLinkNester.className = '';
	}
}

function toggle_ability(theClass) {
	var inputs = getElementsByClass(theClass);
	var l = inputs.length;
	for (var i = 0; i < l; i++) {
		var e = inputs[i];
		if (e.disabled == true) {
			e.disabled = false;
		} else {
			e.disabled = true;
		}
	}
	var link = document.getElementById(theClass + "_link");
	<? $path_from_docroot = getPathFromDocRoot(); 
		$pic_url = utf8_str_replace("\\", '/', $path_from_docroot).'style/pics/'; ?>
	if (link.style.backgroundImage.match('.*ok.*'))
		link.style.backgroundImage = "url(<? echo($pic_url);?>no.gif)";
	else link.style.backgroundImage = "url(<? echo($pic_url);?>ok.gif)";
}


// get an element - the function is similar to "$()" used in prototype.js
function get(e_name) {
	var the_element;
	if (document.getElementById) {                            // W3C DOM
		the_element = document.getElementById(e_name);
	} else if (document.all) {                                // IE4
		the_element = document.all[e_name];
	} else if (document.layers) {                             // NS4
		the_element = document.layers[e_name];
	}
	return the_element;
}

String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ''); }

var hidden_links = new Array();

function showLink(parent_id, tindex, source_id, sindex) {
    var link_id = parent_id + String(tindex);
    var tlink = get(link_id);
    // if it was hidden, make it visible
    if (link_id in hidden_links){
        tlink.style.display = 'inline';
        hidden_links[link_id] = false;
    // if it doesnt exist yet, create it
    } else if (!tlink){
        target_element = document.getElementById(parent_id);
        source_link = document.getElementById(source_id  + String(sindex));
        var sep = ''; if(target_element.innerHTML.trim() != "") sep = "&nbsp;";
        var cl = ''; if (source_link.getAttribute('class') != null) cl = 'class="' + source_link.getAttribute('class') + '"';
        target_element.innerHTML = target_element.innerHTML + sep + '<a ' + cl + ' id="' + link_id + '" style="display:inline;" onclick="moveContent(\'' + source_id + '\', ' + sindex + ', \'' + parent_id + '\', ' + tindex + ')">' + String(source_link.innerHTML) + '</a>';
    }
    else alert('I know about tlink, it is not hidden and I didnt create it: ' + link_id);
}

function hideLink(parent_id, link_index){
    hlink = get(parent_id + String(link_index));
    if(hlink) {
        hlink.style.display = 'none';
        hidden_links[parent_id + String(link_index)] = true;
    }
}

// appends "," + indexed link from source
// to target and makes source link invisible
var initial_states = new Array();
function moveContent(target, tindex, source, sindex) {
	var target_element = get(target);
    var source_element = get(source);
    // just remembering what had been
	if (!(target in initial_states)) initial_states[target] = target_element.innerHTML;
    if (!(source in initial_states)) initial_states[source] = source_element.innerHTML;
    
    // add source content to target content, hide source link
    showLink(target, tindex, source, sindex);
    hideLink(source, sindex);
}

function reset(inputfieldname){
    for (key in hidden_links) {
        if (hidden_links[key] == true) {
            l = get(key);
            l.style.display = 'inline';
        }
    }
    hidden_links = Array();
    infield = get(inputfieldname);
    infield.innerHTML = initial_states[inputfieldname];
}

function getMetric(num) {
	if(n_6) { return parseInt(num) + 'px';}
	else return parseInt(num);
}

/* open link in a new window */
function openWindow(adress, title, width, height, top, left, scrollbars) {
	fenster = window.open(adress, title, "width="+width+",height="+height+",left="+left+",top="+top+",scrollbars="+scrollbars);
	fenster.focus();
}

//asks if deleting is really wanted
function checkDelete(){
	agree = confirm("<?echo(__('Are you sure you want to delete this entry?'));?>");
	if (agree) {
		return true;
	}
	else {
   		return false;
	}
} 

function submit_form_by_selection(command) {
	agree = confirm('<?=__('a change this field is important for other fields in this form. I therefore would like to reload this page. OK?')?>');
	if (agree) {
		document.forms[0].submit();
	}
}

//checks input values of forms
function checkValues(pageName) {
	var results = "";
	var tmp = "";
	
	/*if (pageName=="_sys_singlepages") {
		if (isNaN(document.forms[0].menue_index_input.value)) {
			results = results + "The field menue_index contains a non-numeric value!\n\n";
		}
	}*/
	if (pageName=="_sys_sections") {
		if (isNaN(document.forms[0].order_index_input.value)) {
			results = results + "The field order_index contains a non-numeric value!\n\n";
		}
	}
	<? $mpages = getPageNames();
	$mpages[count($mpages)] = '_sys_singlepages';
	$mpages[count($mpages)] = '_sys_multipages';
	$mpages[count($mpages)] = '_sys_comments';
	foreach($mpages as $p) { ?>
		if (pageName=="<?=$p?>") {
			//alert('I AM HERE for page ' + pageName);
			<? $act_entity = getEntity($p);
			$fields = $act_entity["fields"];
			if ($fields != "") {
				foreach($fields as $f) {
					//it seems there is a problem here for hidden fields ???
					if($f["data_type"] == 'int' or $f["validation"] == 'number') { ?>
					//	if (isNaN(document.forms[0]._formfield_<?=$f["name"]?>_input.value)) {
					//		results = results + "The field \"" + document.forms[0].<?=$f["name"]?>.name + "\" contains a non-numeric value!\n\n";
					//	}
					<?}
					if($f["data_type"] == 'real') { ?> //this should be implemented as regex! sthg like [[0-9]+\.[0-9]*|[0-9]*\.[0-9]+]
					//	if (isNaN(document.forms[0]._formfield_<?=$f["name"]?>_input.value)) {
					//		results = results + "The field \"" + document.forms[0].<?=$f["name"]?>.name + "\" contains a non-numeric value!\n\n";
					//	}
					<?}
					else if(getValidationRegex($f["validation"]) != '') { ?>
						
						var field = document.forms[0]._formfield_<?=$f["name"]?>_input;
						if (field) {
							tmp = field.value;
							regex = '<?=getValidationRegex($f["validation"])?>';
							var erg = tmp.match(<?=getValidationRegex($f["validation"])?>); 
							if (!erg) {
								results = results + "<?=__($f["name"])?>: <?=getValidationMsg($f["validation"])?>\n\n";
								results = results + "<?echo(__('In detail: the content'));?> \"" + tmp + "\" <?echo(__('of the field'));?> \"<?=__($f["name"])?>\" <?echo(__('does not match the Regular Expression'));?> \"" + regex + "\"!\n";
								results = results + "\n\n";
							}
						}
					<?}?>
					
				<?}
			}?>
		}
		
	<?}?>

	<?
	$text = __('The following of the data you entered do not meet the given specifications:');
	?>
	if (results != "") {
		results = "<?echo($text);?>\n\n" + results;
		alert(results);
		return false;
	}
	return true;
}
