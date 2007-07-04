
<?php
/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: config.php
 * 	Configuration file for the PHP File Uploader.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 *		Nicolas Hoening (nicolashoening.de)
 *
 * this file is: editor/filemanager/upload/php/config.php
 */

global $Config ;
// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
require_once('..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');

// SECURITY: You must explicitelly enable this "uploader". 
$Config['Enabled'] = true ;


// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

$path_from_document_root = getPathFromDocRoot();

// Path to uploaded files relative to the document root.
$Config['UserFilesPath'] = $path_from_document_root.'user/File/' ;

$Config['AllowedExtensions']['File']	= array() ;
$Config['DeniedExtensions']['File']		= array('php','php3','php5','phtml','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','dll','reg','cgi') ;

$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;

?>
