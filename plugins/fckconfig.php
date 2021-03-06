<?
	// FILE SEPARATOR
	if ( !defined('FILE_SEPARATOR') ) {
		define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
	}
	include_once('..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR.'PolyPagerLib_Utils.php');
	$sys_info = getSysInfo();
	header( 'Content-Type: text/javascript; charset='.$sys_info['encoding'].'' );
?>
FCKConfig.AutoDetectLanguage	= false ;
FCKConfig.DefaultLanguage	= '<? echo($sys_info["lang"]) ?>' ;
FCKConfig.UseBROnCarriageReturn	= false ; 	// IE only.
FCKConfig.ToolbarStartExpanded	= false ;
FCKConfig.DefaultLinkTarget = "";

FCKConfig.EditorAreaCSS = '<?echo('http://'.$_SERVER['HTTP_HOST'].utf8_str_replace("\\",'/',getPathFromDocRoot()).'plugins/');?>fckcss.php' ;
FCKConfig.StylesXmlPath = '<?echo('http://'.$_SERVER['HTTP_HOST'].utf8_str_replace("\\",'/',getPathFromDocRoot()).'plugins/');?>fckstyles.xml' ;

//FCKConfig.Plugins.Add( 'autogrow' ) ;
//FCKConfig.AutoGrowMax = 700 ;	//this leads to flickering

//FCKConfig.Plugins.Add('fckEmbedMovies') ;

FCKConfig.ToolbarSets["Basic"] = [
	['Bold','Italic','-','OrderedList','UnorderedList']
] ;

FCKConfig.ToolbarSets["PolyPager"] = [
	['Source','-','Preview', 'Cut','Copy','Undo','Redo','-','Find','Replace','-','RemoveFormat','SpecialChar','Style','FontSize'],
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript','OrderedList','UnorderedList','-','Link','Unlink','Image']
] ;



//FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/php/connector.php' ;
//FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php' ;
//FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&Connector=connectors/php/connector.php' ;
//FCKConfig.LinkUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php' ;
//FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php?Type=Image' ;
//FCKConfig.FlashUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php?Type=Flash' ;
