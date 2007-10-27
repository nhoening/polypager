<?
/* this code provides the code to setup the MySQL database
	it builds a connection (a link) to the db (once only) and returns it.
*/

/*	TO THE DEAR USER:
	If you find that these variables are not yet configured, this is a good
	time to do so.
	I'm sorry but you'll have to ask your ISP (Internet Service Provider)
	about these data. Most of the times you'll have to click through their
	website to find that info. If you just set up your account there, it is
	likely that you have to create a database there first.
*/
$host = "localhost";
$db = "carl";
$user = "root";
$pass = "";

$the_db_link = "";
function getDBLink() {
	global $the_db_link;
	global $host;
	global $db;
	global $user;
	global $pass;

	if ($the_db_link == "") {
        $text = "Welcome to PolyPager.<br/>Seeing this page proofs that PolyPager is working at the address you typed in. <br/> However, the database is not connectable. <br/>Maybe it is not configured yet or the configuration does not fit. <br/>If you are the administrator of this page, please consult PolyPager_Config.php";

        // build connection to DBMS
        $the_db_link = mysql_connect($host, $user, $pass) or die($text);
        
        // now to the DB
        mysql_select_db($db, $the_db_link) or die ($text);
        mysql_query('SET CHARACTER SET utf8');
        mysql_query("SET SESSION collation_connection ='utf8_general_ci'");
        mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");

	}
	return $the_db_link;
}

function getDBName() {
	global $db;
	return $db;
}
?>
