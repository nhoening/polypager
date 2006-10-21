/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas Höning
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
    
/*----------------------------------------------------------------
  This JavaScript holds general methods for polypager
  (http://polypager.nicolashoening.de)
  ----------------------------------------------------------------*/
  
// id browsers
var name = navigator.userAgent.toLowerCase()
var opera = (name.indexOf("opera")> -1) //this is just Opera

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
	el = document.getElementById(theElementID);
	theLink = document.getElementById(theLinkID);
	if (el.style.display == 'none' || el.style.display == '') {
		el.style.display = 'block';
		theLink.innerHTML = text_vis;
	} else {
		el.style.display = 'none';
		theLink.innerHTML = text_invis;
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
	if (link.style.backgroundImage == "url(http://www.accb.de/test_nic2/style/pics/ok.gif)")
		link.style.backgroundImage = "url(http://www.accb.de/test_nic2/style/pics/no.gif)";
	else link.style.backgroundImage = "url(http://www.accb.de/test_nic2/style/pics/ok.gif)";
}


/* get an element - the function is similar to "$()" used in prototype.js
 but does not only use getElementById() */
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

/* open link in a new window */
function openWindow(adress, title, width, height, top, left, scrollbars) {
	fenster = window.open(adress, title, "width="+width+",height="+height+",left="+left+",top="+top+",scrollbars="+scrollbars);
	fenster.focus();
}

//asks if deleting is really wanted
function checkDelete(){
	agree = confirm("Are you sure you want to delete this entry?");
	if (agree) {
   		document.form1.submit();
		return true;
	}
	else {
   		return false;
	}
} 

//checks input values of forms
function checkValues(entityName) {
	var results = "";
	var tmp = "";

	
}
