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
 * 	Configuration file for the File Manager Connector for PHP.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 *      Nicolas Hoening (nicolashoening.de)
 *
 * this file is: editor/filemanager/browser/default/connectors/php/config.php
 */

global $Config ;
// FILE SEPARATOR
if ( !defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
require_once('..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');


// SECURITY: You must explicitelly enable this "connector". (Set it to "true").
$Config['Enabled'] = true ;


$path_from_document_root = getPathFromDocRoot();

// Path to user files relative to the document root.
$Config['UserFilesPath'] = $path_from_document_root.'user/' ;

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Useful if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\UserFiles\\' or '/root/mysite/UserFiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
$Config['UserFilesAbsolutePath'] = $_SERVER['DOCUMENT_ROOT'].$path_from_document_root.'user/';

// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

$Config['AllowedExtensions']['File']	= array() ;
$Config['DeniedExtensions']['File']		= array('php','php3','php5','phtml','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','dll','reg','cgi') ;

$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;

$Config['AllowedExtensions']['Media']	= array('swf','fla','jpg','gif','jpeg','png','avi','mpg','mpeg') ;
$Config['DeniedExtensions']['Media']	= array() ;

?>