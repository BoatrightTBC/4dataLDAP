<?PHP require_once("settings.php");
require_once("utils.php");
require_once("conexion.php");

/*
<!-- This system requires your php.ini has this setting: short_open_tag = On
     If you do not have this set, THE SYSTEM WILL NOT WORK!!!!! -->
*/
     
$llLogin = checkLogin();
if ($llLogin == FALSE) {
  echo '<META HTTP-EQUIV="Refresh" Content="0; URL=4data.php?alert=yes">';    
  return;
}

$lcDest = $_GET['dest'];

$lcExe = "SELECT transactions.tid, transactions.time_in, transactions.time_out FROM transactions WHERE time_in = time_out AND transactions.uid = '".$_SESSION['user_id']."' ";
//	echo "<br />".$lcExe."<br />";
$resultID=mysql_query($lcExe, $conexion);

if(mysql_num_rows($resultID) > 0 ) {	
  echo("<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>\n");
  echo("<html>\n<head>\n<title>Blank Records Found</title>\n<meta name='viewport' content='width=device-width' />\n");
  echo("<link rel='stylesheet' type='text/css' media='screen' href='common.css' />\n");
  echo("<link rel='stylesheet' type='text/css' media='screen' href='editunits.css' />\n");
  echo("<link rel='stylesheet' type='text/css' media='screen' href='picdisplay.css' />\n");
  echo("<script src='showsize.js' type='text/javascript'></script>\n</head>\n<body>\n");
  
  echo("<h1 align='center'><br /><br />There are ".mysql_num_rows($resultID)." blank records in the transaction table.<br />\n");
  echo("Do you wish to delete them?<br />\n");
  echo("<br />\n");
  echo("<form id='thisform' action='killblanks.php'  method='POST'>\n");
  echo("<table width='50%' border='1' align='center'>\n");
  echo("<tr><td align='center'><input type='submit' name='checkblank' id='checkblank' class='myButtons' value='Yes' style='background-color:#0033FF;' /></td>\n");
  echo("<td align='center'><input type='submit' name='checkblank' id='checkblank' class='myButtons' value='No' style='background-color:#0033FF;' /></td>\n");
  echo("</tr></table>\n");
  echo("<input type='hidden' name='dest' id='dest' value='".$lcDest."'/>\n");
  
  echo("</form>\n</body>\n</html>\n");
  
} else {
  echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$lcDest.'">';    
  return;
}

?>
