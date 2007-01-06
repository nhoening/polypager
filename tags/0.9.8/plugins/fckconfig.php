<?
	header( 'Content-Type: text/javascript; charset=utf-8' );

	// FILE SEPARATOR
	if ( !defined('FILE_SEPARATOR') ) {
		define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
	}
	include_once('..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');
	$sys_info = getSysInfo();
?>
FCKConfig.AutoDetectLanguage	= false ;
FCKConfig.DefaultLanguage		= '<? echo($sys_info["lang"]) ?>' ;
FCKConfig.UseBROnCarriageReturn	= false ;
FCKConfig.ToolbarStartExpanded	= false ;

FCKConfig.EditorAreaCSS = '<?echo(str_replace("\\",'/',getPathFromDocRoot()).'plugins/');?>fckcss.php' ;
//$sys_info = getSysInfo();
//FCKConfig.EditorAreaCSS = '<?echo(str_replace("\\",'/',getPathFromDocRoot()).'style/skin/'.$sys_info['skin'].'/');?>skin.css' ;

FCKConfig.Plugins.Add( 'autogrow' ) ;
FCKConfig.AutoGrowMax = 700 ;

//FCKConfig.Plugins.Add('fckEmbedMovies') ;

FCKConfig.ToolbarSets["Default"] = [
	['Source','DocProps','-','Save','NewPage','Preview','-','Templates',
	'Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck',	'Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','Smiley','SpecialChar','PageBreak'],
	//['EmbedMovies'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-'],
	['Link','Unlink','Anchor','Image','Flash','Table','Rule'],
	['Style','FontFormat','FontName','FontSize'],['TextColor','BGColor'],['About']
] ;

FCKConfig.ToolbarSets["Basic"] = [
	['Bold','Italic','-','OrderedList','UnorderedList']
] ;




FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/php/connector.php' ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php' ;
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&Connector=connectors/php/connector.php' ;
FCKConfig.LinkUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php' ;
FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php?Type=Image' ;
FCKConfig.FlashUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php?Type=Flash' ;

var _FileBrowserLanguage        = 'php' ;
var _QuickUploadLanguage        = 'php' ;
