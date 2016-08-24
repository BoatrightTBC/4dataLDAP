<?PHP
/*
<!-- This system requires your php.ini has this setting: short_open_tag = On
     If you do not have this set, THE SYSTEM WILL NOT WORK!!!!! -->
*/
     
require_once("settings.php");
require_once("utils.php");
require_once("conexion.php");

$llLogin = checkLogin();
if ($llLogin == FALSE) {
  echo '<META HTTP-EQUIV="Refresh" Content="0; URL=4data.php?alert=yes">';    
  return;
}

$lcDest = $_POST['dest'];
$lcWhich =  $_POST['checkblank'];

if ($lcWhich == "Yes") {
  $lcExe = "DELETE FROM transactions WHERE time_in = time_out AND transactions.uid = '".$_SESSION['user_id']."' ";
//	echo "<br />".$lcExe."<br />";
  $resultID=mysql_query($lcExe, $conexion);
}
  
echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$lcDest.'">';    
return;

?>
