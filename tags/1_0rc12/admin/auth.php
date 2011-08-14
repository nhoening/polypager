<?php
/*
this PHP authenticates access to the PHP files that include it.
The inclusion should be done before outputting anything.

*/

/* default authentication approach, see below when its (not) used */
function auth_user()
{
    $realm = mt_rand(1, 1000000000 );
    header('WWW-Authenticate: Basic realm="Admin Authentification."');
    header('HTTP/1.0 401 Unauthorized');
    die("Unauthorized access forbidden!");
}

// --------------------------------------- Lib Inclusion (if not already there)
// PATH_SEPARATOR doesn't exist in versions of php before  4.3.4. here is the trick to declare it anyway :
if (!defined('PATH_SEPARATOR') ) {
    define('PATH_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? ';' : ':');
}
// FILE SEPARATOR
if (!defined('FILE_SEPARATOR') ) {
    define('FILE_SEPARATOR', ( substr(PHP_OS, 0, 3) == 'WIN' ) ? "\\" : '/');
}
//make sure this works from whereever we are
set_include_path(get_include_path() . PATH_SEPARATOR . '.'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR. '..'.FILE_SEPARATOR.'lib'.FILE_SEPARATOR);
if (!in_array("PolyPagerLib_Utils.php", get_included_files() ) ) require_once("PolyPagerLib_Utils.php");
// ---------------------------------------

$sys_info = getSysInfo();

//first of all: if  password is still empty then let's just not care:
if($sys_info["admin_pass"] != "" ) {
    
    //when not cgi mode (e.g. apache), then authenticate old style
    //here is a list of what else we could find: http://de3.php.net/php_sapi_name
    if (php_sapi_name() != 'cgi') {
        //use this instead to test sessions
        $sys_info = getSysInfo();
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            auth_user();
        } else if ($_SERVER['PHP_AUTH_USER'] != $sys_info["admin_name"] || sha1($sys_info["salt"].$_SERVER['PHP_AUTH_PW']) != $sys_info["admin_pass"]) {
            auth_user();
        }
        //then do nothing - if everything worked, the page will display
        //if cgi mode, then do it with a session
    } else {
        session_start();
        $sys_info = getSysInfo();
        $hostname = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['PHP_SELF']);
        
        /*  //useful test output...
        echo('http host is:'.$_SERVER['HTTP_HOST']);
        echo('dirname is:'.$path);
        echo("phpself is:".$_SERVER['PHP_SELF']."...".utf8_strpos($path, 'admin'));
        */
        //if not authenticated, then do so
        if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                session_start();
                
                $username = $_POST['username'];
                $passwort = $_POST['password'];
                
                // Benutzername und Passwort werden ueberprueft
                if ($username == $sys_info["admin_name"] && sha1($sys_info['salt'].$passwort) == $sys_info["admin_pass"]) {
                    $_SESSION['authenticated'] = true;
                    
                    // Weiterleitung zur geschuetzten Startseite
                    if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
                        if (php_sapi_name() == 'cgi') {
                            header('Status: 303 See Other');
                        } else {
                            header('HTTP/1.1 303 See Other');
                        }
                    }
                    //success: always forward to admin/index.php
                    header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/index.php');
                    exit;
                    
                }
            }
            if (!$_SESSION['authenticated']) {
                //if the calling script is not within admin/, we have some problems
                //it shouldn't happen anyway, so this quickhack suffices for now
                if (utf8_strpos($path, 'admin') == 0) {
                    echo('    <div class="sys_msg_admin">'.__('you are not logged in!').'</div>');
                } else {
                    //display form
                    echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n");
                    echo('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="en">'."\n");
                    echo(' <head>'."\n");
                    echo('  <title>'.__('Secured Area').'</title>'."\n");
                    echo('	<link rel="stylesheet" href="../style/skins/'.$sys_info["skin"].'/skin.css" type="text/css"></link>'."\n");
                    echo(' </head>'."\n");
                    echo(' <body>'."\n");
                    echo('  <div id="container">'."\n");
                    echo('   <div id="data">'."\n");
                    echo('    <div class="sys_msg_admin">'.__('you need to login to access this area').'</div>'."\n");
                    echo('    <form id="login_form" action="http://'.$hostname.($path == '/' ? '' : $path).'/auth.php" method="post">'."\n");
                    echo('     <label for="username">Username:</label><input type="text" name="username" />'."\n");
                    echo('     <label for="password">Password:</label><input type="password" name="password" />'."\n");
                    echo('     <input type="submit" value="Login" />'."\n");
                    echo('    </form>'."\n");
                    echo('   </div>'."\n");
                    echo('  </div>'."\n");
                    echo(' </body>'."\n");
                    echo('</html>'."\n");
                }
                exit;
            }
        }
    }
}
?>