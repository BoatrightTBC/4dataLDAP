<?PHP 

$lcServer = $_SERVER['SERVER_NAME'];

$db_host = 'localhost';                       // mySQL host
$db_name = 'vocshop_4data';                           // mySQL database name
$db_user = 'root';                         // mySQL user
$db_pass = '';                         // mySQL password
$home = "Index.wml#menu";  

// Language settings                          // Change english.php to your language file
$language = 'english.php';                    // placed in lang folder

// Poll graphics                              // Change to 1 for graphical poll results
$pollgraphics = 0;                            // note that not all Wap devices can display
                                              // graphics correctly

$conexion = mysql_connect($db_host, $db_user, $db_pass)
    or die("Could not connect");
    
mysql_select_db($db_name)
    or die("Could not select database");

?>