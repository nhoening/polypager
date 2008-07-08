<?php
if ( !defined('PATH_SEPARATOR') ) {
    define('PATH_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? ';' : ':');
}
if ( !defined('FILE_SEPARATOR') ) {
	define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}

set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR);
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_Utils.php");
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_HTMLFraming.php");
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_Showing.php");
require_once("lib" . FILE_SEPARATOR . "PolyPagerLib_Adminindex.php");

$path_to_root_dir = "..";
$area = "_admin"; 


function writeData(){
    
    showAdminOptions();
    
    clearMsgStack();
    
    echo('<h1>Set Password</h1>');
    
    $res = pp_run_query('SELECT admin_pass, salt from _sys_sys;');
    
    if ($_POST['new_pass1'] != '' or $_POST['new_pass2'] != ''){
        // check old password against hash    
        if (($res[0]['admin_pass'] == '' and $_POST['old_pass'] == '') or ($res[0]['admin_pass'] == sha1($res[0]['salt'].$_POST['old_pass']))){
            // check equality of 1 and 2
            if ($_POST['new_pass1'] == $_POST['new_pass2']){
                // save a hash using salt
                $query = "UPDATE _sys_sys SET admin_pass = ?";
                $param = array('s', sha1($res[0]['salt'].$_POST['new_pass1']));
                pp_run_query(array($query, array($param)));
                global $sys_msg_text;
                $sys_msg_text[] = "The password has been set.";
            } else{
                global $error_msg_text;
                $error_msg_text[] = "Your two passwords do not match!";
            }
        }else{
            global $error_msg_text;
            $error_msg_text[] = "Your old password is not correct!";
        }
    }
        
    echo('<form id="passwd_form" action="'.$PHPSELF.'" method="post"><table>'."\n");
    echo('  <tr><td></td><td><input type="hidden" name="page" value="_sys_sys"/></td></tr>'."\n");
    if ($res[0]['admin_pass']  != '') echo('  <tr><td><label for="old_pass">Old Password:</label></td><td><input type="password" name="old_pass" value=""/></td></tr>'."\n");
    echo('  <tr><td><label for="new_pass1">New Password:</label></td><td><input type="password" name="new_pass1" value=""/></td></tr>'."\n");
    echo('  <tr><td><label for="new_pass2">Again:</label></td><td><input type="password" name="new_pass2" value=""/></td></tr>'."\n");
    echo('  <tr><td></td><td><input type="submit" name="cmd" value="save"/>'."\n");
    echo('</table></form>'."\n");
    
    clearMsgStack();
    
}

useTemplate($path_to_root_dir);
?>
