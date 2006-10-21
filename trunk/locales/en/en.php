<?
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

/*This function gives german translations for english texts
	two things need to be followed:
	1. do HTML-Umlaute like &uuml;, also no "..." in either text
	2. every string should be in one line (also in the original file, of course)
	@param text: the text to be translated
*/
function getTranslation($text) {
	$translation_table = array(
/* Labels */
//_sys_multipages / _sys_singlepages

'in_menue:' => 'visible in menue:',
'menue_index:' => 'menue-index:',
'hide_options:' => 'hide options:',
'hide_search:' => 'hide search:',
'hide_toc:' => 'hide table of contents:',
'hidden_fields:' => 'hidden fields:',
'order_by:' => 'order by:',
'order_order:' => 'asc/descending order of order:',
'publish_field:' => 'publish-field:',
'group_field:' => 'group-field:',
'group_order:' => 'asc/descending order of groups:',
'date_field:' => 'date-fields:',
'edited_field:' => 'last-edited-field:',	
'title_field:' => 'title-field:',
'show_comments:' => 'show comments:', 
'search_month:' => 'search for month:', 	
'search_year:' => 'search for year:',
'search_keyword:' => 'search for keyword:',	
'show_labels:' => 'show labels:',
'search_range:' => 'search range:',

//_sys_fields
'not_brief:' => 'content is not really short:',
'order_index:' => 'order index:',
 
//_sys_sys
'admin_name:' => 'administrator-name:',
'admin_pass:' => 'administrator-password:',
'feed_amount:' => 'number of feeds:',
'start_page:' => 'start-page:',
'lang:' => 'language:',
'submenus_always_on:' => 'submenus are always on:',
'link_to_gallery_in_menu:' => 'show link to gallery in menu:',
	
//system pagenames
'_sys_comments' => 'Comments',
'_sys_fields' => 'Fields',
'_sys_feed' => 'Feeds'
);
	return $translation_table[$text];
}
?>
