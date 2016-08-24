<?PHP
require_once("settings.php");
require_once("utils.php");
require_once("JSON.php");
require_once("getdata.php");

$doSomething = false; 
if(isset($_REQUEST['save'])) {
	if(!checkLogin())
		die($STRINGS[$LANG]['session_expired']);
	if($_REQUEST['save'] == 'users')
	{
		if(isset($_POST['oper'])){
			if(	$_POST['oper']=='add') {
				addUser($_POST);
			} else if($_POST['oper']=='del') {
				deleteUser($_POST['id']);
			}
		} else {
			modifyUser($_POST);
		}
	}
	if($_REQUEST['save'] == 'jobs')
	{
		if(!isset($_REQUEST['user'])) {
			print "Must specify a user";
			exit();
		}
		if(isset($_POST['oper'])){
			if(	$_POST['oper']=='add') {
				addJob($_REQUEST);
			} else if($_POST['oper']=='del') {
				deleteJob($_REQUEST['user'],$_POST['id']);
			}
		} else {
			modifyJob($_REQUEST);
		}
	}
	if($_REQUEST['save'] == 'employees')
	{
		if(!isset($_REQUEST['user'])) {
			print "Must specify a user";
			exit();
		}
		if(isset($_POST['oper'])){
			if(	$_POST['oper']=='add') {
				addEmployee($_REQUEST);
			} else if($_POST['oper']=='del') {
				deleteEmployee($_REQUEST['user'],$_POST['id']);
			}
		} else {
			modifyEmployee($_REQUEST);
		}
	}
	if($_REQUEST['save'] == 'trans') {
		if(!isset($_REQUEST['user'])) {
			print "Must specify a user";
			exit();
		}
		if(isset($_POST['oper'])){
			if(	$_POST['oper']=='add') {
				addTrans($_REQUEST);
			}
            if($_POST['oper']=='del') {
			    deleteTrans($_POST['id']);
            }
			if($_POST['oper']=='edit') {
				modifyTrans($_REQUEST);
		    }
	    }
		if(isset($_REQUEST['oper'])){
			if($_REQUEST['oper'] == 'edit') {
				modifyTrans($_REQUEST);
			}
		}
    }
	if($_REQUEST['save'] == 'template')
	{
		if(!isset($_REQUEST['user'])) {
			print "Must specify a user";
			exit();
		}
		if(isset($_POST['oper'])){
			if(	$_POST['oper']=='add') {
				addTemplate($_REQUEST);
			} else if($_POST['oper']=='del') {
				deleteTemplate($_REQUEST['user'], $_POST['id']);
			} else {
				modifyTemplate($_REQUEST);
			}
		}
	}
	if($_REQUEST['save']=='send') {
		markSent($_REQUEST['user']);
	}
	if($_REQUEST['save'] == 'applyTemplate')
		applyTemplate($_REQUEST);
} else {
	print 'This is savedata, your request lacked a save command';
}

function setErrors($uid)
{
	global $sidx, $sord, $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($uid)) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	$trans = getTransactions($uid, 'tid', 'asc');
	$jobs = getJobs($uid, 'jobcode', 'asc');
	for($i=0; $i<count($trans); $i++) {
		$error = "";
		if(trim($trans[$i]['time_in']) == "") {
			$error.="In,";
		}
		if(trim($trans[$i]['time_out']) == "") {
			$error.="Out,";
		}
		$job = null;
		foreach($jobs as $j) {
			if($j['jobcode']==$trans[$i]['jobcode']) {
				$job = $j;
				break;
			}
		}
		if($job) {
			if($job['pay_meth']=='U' && $trans[$i]['units']=="0") {
				$error.="Units,";
			}
		}
		for($j=0; $j<count($trans); $j++) {
			if($i==$j)
				continue;
			if($trans[$i]['date'] !== $trans[$j]['date'] || $trans[$i]['emplno'] !== $trans[$j]['emplno'])
				continue;
			$i_in = (int)$trans[$i]['time_in'];
			$i_out = (int)$trans[$i]['time_out'];
			$j_in = (int)$trans[$j]['time_in'];
			$j_out = (int)$trans[$j]['time_out'];
			if( strlen($trans[$i]['time_in'])==0 && strlen($trans[$i]['time_out'])==0 )
				break;
			if( strlen($trans[$j]['time_in'])==0 && strlen($trans[$j]['time_out'])==0 )
				continue;
			if(strlen($trans[$i]['time_in'])==0) $i_in=$i_out;
			if(strlen($trans[$j]['time_in'])==0) $j_in=$j_out;
			if(strlen($trans[$i]['time_out'])==0) $i_out=$i_in;
			if(strlen($trans[$j]['time_out'])==0) $j_out=$j_in;

			if( ($i_in < $j_in && $j_in < $i_out) ||
				($i_in < $j_out && $j_out < $i_out) ||
				($j_in < $i_in && $i_in < $j_out) ||
				($j_in < $i_out && $i_out < $j_out) ||
				($i_in == $j_in && $i_out == $j_out)) {
					$error.="Lap,";
					break;
			}
		}
		$query = "
			UPDATE transactions
			SET
				error = '$error'
			WHERE
				tid = '{$trans[$i]['tid']}'
			";
		if (!$mysqli->query($query))
			print $mysqli->error;
	}
	$mysqli->close();
}

function checkTime($time, $allowEmpty=true) {
	global $STRINGS, $LANG;
	$valid = false;
	$error = "";
	if(strlen($time) == 0) {
		if(!$allowEmpty) {
			$valid = false;
			$error = $STRINGS[$LANG]['time_null_error'];
		} else {
			$valid = true;
		}
	} else if(ctype_digit($time) && strlen($time)==4) {
		$hours = false;
		if($time[0]<=1) // if first digit is 1 or 0 2nd can be anything
			$hours = true;
		if($time[0]==2 && $time[1]<=3)
			$hours = true;
		$minutes = false;
		if($time[2]<=5)
			$minutes = true;
		if($hours && $minutes) {
			$valid = true;
		} else {
			$valid = false;
			$error = $STRINGS[$LANG]['time_format_error'];
		}
	} else {
		$valid = false;
		$error = $STRINGS[$LANG]['time_format_error'];
	}
	if(!$valid)
		print $error;
	return $valid;
}


function userChecks($data) {
	global $STRINGS, $LANG;
	if(!checkTime($data['time_in']))
		return false;
	if(!checkTime($data['time_out']))
		return false;
	if(strlen($data['time_out'])>0 && intval($data['time_out'])<=intval($data['time_in'])) {
		print $STRINGS[$LANG]['time_out<=in'];
		return false;
	}
	return true;
}

function deleteUser($id) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!$_SESSION['admin'])
		die($STRINGS[$LANG]['admin_error']);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	$query = "DELETE FROM users WHERE uid = '$id'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}

function addUser($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!$_SESSION['admin'])
		die($STRINGS[$LANG]['admin_error']);
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	if(!userChecks($data))
		return;

	$query = "
		INSERT INTO users
		(uid, password, trainer, default_time_in, default_time_out, administrative)
		VALUES(
			'{$data['uid']}',
			'{$data['password']}',
			'{$data['trainer']}',
			'{$data['time_in']}',
			'{$data['time_out']}',
			'{$data['admin']}'
		)";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();

}
function modifyUser($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	$data = removeNbsp($data);
	if(!$_SESSION['admin'])
		die($STRINGS[$LANG]['admin_error']);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	if(!userChecks($data))
		return;

	$query = "
		UPDATE users SET
			password='{$data['password']}',
			trainer='{$data['trainer']}',
			default_time_in='{$data['time_in']}',
			default_time_out='{$data['time_out']}',
			administrative='{$data['admin']}'
		WHERE uid='{$data['id']}'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}



function deleteJob($uid, $jobcode) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($uid)) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	$query = "DELETE FROM jobs WHERE uid = '$uid' AND jobcode='$jobcode'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}

function jobChecks($data) {
	global $STRINGS, $LANG;
	if(!in_array($data['pay_meth'], array('A','S','T','N','U'))) {
		print $STRINGS[$LANG]['pay_method_error'];
		return false;
	}
	return true;
}

function addJob($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	$data = removeNbsp($data);
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if(!jobChecks($data))
		return;
	$query = "
		INSERT INTO jobs
		(uid, jobcode, pay_meth, task_descr)
		VALUES(
			'{$data['user']}',
			'{$data['jobcode']}',
			'{$data['pay_meth']}',
			'{$data['task_descr']}'
		)";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();

}
function modifyJob($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	$data = removeNbsp($data);
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if(!jobChecks($data))
		return;
	$query = "
		UPDATE jobs SET
			pay_meth='{$data['pay_meth']}',
			task_descr='{$data['task_descr']}'
		WHERE uid='{$data['user']}' AND  jobcode='{$data['id']}'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}


function deleteEmployee($uid, $emplno) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($uid)) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	$query = "DELETE FROM clients WHERE uid = '$uid' AND emplno='$emplno'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}

function employeeChecks($data) {
	global $STRINGS,$LANG;
	return true;
}

function addEmployee($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!$_SESSION['admin'])
		die($STRINGS[$LANG]['admin_error']);
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if(!employeeChecks($data))
		return;
	$query = "
		INSERT INTO clients
		(uid, emplno, empname)
		VALUES(
			'{$data['user']}',
			'{$data['emplno']}',
			'{$data['empname']}'
		)";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();

}
function modifyEmployee($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!$_SESSION['admin'])
		die($STRINGS[$LANG]['admin_error']);
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if(!employeeChecks($data))
		return;
	$query = "
		UPDATE clients SET
			empname='{$data['empname']}'
		WHERE uid='{$data['user']}' AND emplno='{$data['id']}'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}



function deleteTrans($tid) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	$query = "SELECT uid FROM transactions WHERE tid = '$tid'";
	$result = $mysqli->query($query);
	if(!$result) {
		print $mysqli->error;
		return;
	}
	$row = $result->fetch_row();
	$uid = $row[0];
	if(!checkPermissions($uid)) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$result->free();

	$query = "DELETE FROM transactions WHERE tid = '$tid'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
	setErrors($uid);
}

function transChecks($data) {
	global $STRINGS,$LANG;
	return true;
}

function addTrans($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	//if(!transChecks($data))
	//	return;

//VOCSHOP 4DataWeb 1.1 5/10/11 jk Added time rollover
	$returnVal = checkData($data);
	if ($returnVal !== $data['time_out']) {
		$data['time_in'] = $returnVal;
		$data['time_out'] = "";
	}
	if (!isset($data['jobcode'])) {
		$data['jobcode'] = "0";
	}
	$query = "
		INSERT INTO transactions
		(uid, date, time_in, time_out, trainer, emplno,jobcode,units)
		VALUES(
			'{$data['user']}',
			'{$data['date']}',
			'{$data['time_in']}',
			'{$data['time_out']}',
			'{$data['trainer']}',
			'{$data['emplno']}',
			'{$data['jobcode']}',
			'{$data['units']}'
		)";

	if (!$mysqli->query($query))
		print $mysqli->error;

	print $mysqli->insert_id;
	$mysqli->close();
	setErrors($data['user']);
}

//VOCSHOP 4DataWeb 1.1 5/10/11 jk Added time rollover
function checkData($data) {
	$ReturnVal = "";
    if (array_key_exists('time_out', $data)) {
        $ReturnVal = $data['time_out'];
    } else {
		if (array_key_exists('workdate', $_SESSION) && array_key_exists('emplno', $_SESSION)) {
			if(isset($_SESSION['emplno']) && isset($_SESSION['date'])) {
				if (($data['emplno'] == $_SESSION['emplno']) && ($data['workdate'] == $_SESSION['date']) && ($data['user'] == $_SESSION['carduser'])) {
				    if (array_key_exists('timeout', $_SESSION)) {
						$ReturnVal = $_SESSION['timeout'];
					}
				}
			}
		}
	}
    return $ReturnVal;
}

function modifyTrans($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$data = removeNbsp($data);
	setSessVars($data);

	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
//	if(!transChecks($data))
//		return;
	if (isset($data['id'])) {
	$query = "
		UPDATE transactions SET
			date = '{$data['date']}',
			time_in = '{$data['time_in']}',
			time_out = '{$data['time_out']}',
			trainer = '{$data['trainer']}',
			jobcode = '{$data['jobcode']}',
			units = '{$data['units']}',
			workorder = '{$data['workorder']}',
			error = '{$data['error']}'
		WHERE tid = '{$data['id']}'
	";
	} else {
		$query = "";
	}
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
	//setErrors($data['user']);
}

//VOCSHOP 4DataWeb 1.1 5/10/11 jk Added time rollover
function setSessVars($data) {
  $_SESSION['emplno'] = "";
  $_SESSION['workdate'] = "";
  $_SESSION['timeout'] = "";
  $_SESSION['cardid'] = "";
  if(isset($data['emplno'])) { $_SESSION['emplno'] = $data['emplno']; }
  if(isset($data['date'])) { $_SESSION['workdate'] = $data['date']; }
  if(isset($data['timeout'])) { $_SESSION['timeout'] = $data['time_out']; }
  if(isset($data['timein'])) { $_SESSION['timein'] = $data['time_in']; }
  if(isset($data['carduser'])) { $_SESSION['carduser'] = $data['user']; }
}

function deleteTemplate($uid, $id) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($uid)) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	$query = "DELETE FROM templates WHERE uid = '$uid' AND id='$id'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}

function templateChecks($data) {
	global $STRINGS,$LANG;
	return true;
}

function addTemplate($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if(!templateChecks($data))
		return;
	$query = "
		INSERT INTO templates
		(uid, time_in, time_out, trainer, jobcode, units, workorder )
		VALUES(
			'{$data['user']}',
			'{$data['time_in']}',
			'{$data['time_out']}',
			'{$data['trainer']}',
			'{$data['jobcode']}',
			'{$data['units']}',
			'{$data['workorder']}'
		)";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();

}
function modifyTemplate($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if(!templateChecks($data))
		return;
	$query = "
		UPDATE templates SET
			time_in = '{$data['time_in']}',
			time_out = '{$data['time_out']}',
			trainer = '{$data['trainer']}',
			jobcode = '{$data['jobcode']}',
			units = '{$data['units']}',
			workorder = '{$data['workorder']}'
		WHERE uid='{$data['user']}' AND  id='{$data['id']}'";
	if (!$mysqli->query($query))
		print $mysqli->error;
	$mysqli->close();
}

function markSent($uid) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!checkPermissions($uid)) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
   // Delete leftover _*.pdf files from reports 
   $mask = "_*.pdf";
   array_map("unlink", glob( $mask) );
   // start checks and send data
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

//jk Added Total Units check
	$zeroquery =  "SELECT SUM(transactions.units) AS total, transactions.emplno,
	                 transactions.date, transactions.Jobcode, jobs.Pay_meth
					 FROM transactions JOIN jobs
					   ON transactions.Jobcode = jobs.Jobcode and transactions.uid = jobs.uid
					 WHERE transactions.Sent = 0 AND jobs.Pay_meth = 'U' and transactions.uid = '$uid'
					 GROUP BY transactions.emplno, transactions.date, transactions.Jobcode
					 HAVING total = 0
					 ORDER BY transactions.emplno, transactions.date, transactions.Jobcode";

	if($result = $mysqli->query($zeroquery)) {
	    $lcReturn = "";
	    while($row = $result->fetch_assoc()) {
	      $lcReturn .= "Emp: ".$row["emplno"]." - Date: ".$row["date"]." - JobCode: ".$row["Jobcode"]." - PayMeth: ".$row["Pay_meth"]."<br />";
	    }

	    $result->free();

		if ($lcReturn > "Emp:") {
		  print "<b>Did not send because of errors listed for these rows:</b><br /><br />".$lcReturn;
		  return;
	    }
	}
//jk end

	$query = "
		UPDATE transactions
		SET sent = true
		WHERE uid = '$uid'
	";
	if (!$mysqli->query($query)) {
		print $mysqli->error;
		return;
	}


}

function applyTemplate($data) {
	global $db_server, $db_user, $db_pass, $db_database, $STRINGS, $LANG;
	if(!isset($data['user']) || !isset($data['template']) || !isset($data['employees'])) {
		//0 to indicate number of duplicate records to calling javascript
		print "0";
		return;
	}
	if(!checkPermissions($data['user'])) {
		print $STRINGS[$LANG]['no_auth'];
		return;
	}
	$data = removeNbsp($data);
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_database);
	if (mysqli_connect_errno()) {
    	printf("Connect failed: %s\n", mysqli_connect_error());
	    return;
	}
	$dupe_count = 0;
	$newlist = "(";
	for($i=0; $i<count($data['employees']); $i++) {
		if($i!=0)
			$newlist .= ', ';
		$emplno = $data['employees'][$i];
		$newlist .= "'$emplno'";
	}
	$newlist .= ")";
	$query = "
		SELECT count(emplno) FROM transactions
		WHERE uid = '{$data['user']}'
			AND template = '{$data['template']}'
			AND sent = false
			AND emplno IN $newlist
	";
	$result = $mysqli->query($query);
	if(!$result) {
		print $mysqli->error;
		return;
	}
	$row = $result->fetch_row();
	print "{$row[0]}";
	$result->free();
	$query = "
		INSERT into transactions
		(uid, template, emplno, date, time_in, time_out, trainer, jobcode, units, workorder)
		(SELECT '{$data['user']}', '{$data['template']}', emplno, '{$data['date']}', time_in, time_out, trainer, jobcode, units, workorder
			FROM templates, clients WHERE
				clients.emplno IN $newlist AND
				clients.emplno NOT IN
					(SELECT emplno FROM transactions
					 WHERE
						uid = '{$data['user']}'
						AND template = '{$data['template']}'
						AND sent = false)
				AND templates.id = '{$data['template']}'
				AND templates.uid = '{$data['user']}'
				AND clients.uid = '{$data['user']}'
		)";
	if (!$mysqli->query($query)) {
		print $mysqli->error;
		return;
	}
}



?>
