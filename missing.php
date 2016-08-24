<?PHP
require_once("settings.php");
require_once("utils.php");
require_once("JSON.php");
require_once("getdata.php");
define('FPDF_FONTPATH','font/');
require_once('fancyrow.php');


if(!checkLogin())
	showLogin();

$lcinput  = $_GET['supes'];
$lcOutput = '';
$lcOutput = checksupes($lcinput);

$splitup = split("\|", $lcOutput);
$lcNoFinds = $splitup[0];
//$lcFinds = split(",", $splitup[1]);
$lcFinds = $splitup[1];
    
echo('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
                    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Missing Cards</title>
<link rel="stylesheet" type="text/css" media="screen" href="themes/basic/grid.css" />
<link rel="stylesheet" type="text/css" media="screen" href="themes/jqModal.css" />
<link rel="stylesheet" type="text/css" media="screen" href="common.css" />
</head><body>');

if ($lcFinds != '') {
  $lcOutput2 = getmissing($lcFinds);
}

echo('<H1>Missing Cards Report</H1><br>');
echo('<b>Supervisors submitted: '.$lcinput.'</b><br><br>');
echo($lcOutput2.'<br>');
echo($lcNoFinds.'<br>');

echo('<br></body></html>');

function getmissing($lcSupes) {
      $lcExe2 = "SELECT clients.emplno, clients.empname, transactions.date". 
               " FROM clients JOIN transactions ON transactions.emplno = clients.emplno ".
               " WHERE (transactions.date, transactions.emplno) ".
               " NOT IN (SELECT date, emplno FROM transactions ".
               " WHERE uid IN ($lcSupes) GROUP BY transactions.date, transactions.emplno) ".
               " GROUP BY transactions.date, transactions.emplno ".
               " ORDER BY transactions.date, transactions.emplno";
      global $mysqli;    
	  $result = $mysqli->query($lcExe2) or die ("QUERY PROBLEM: $lcExe2 ");
/*  
      if ($result) {
        echo $lcExe2;
        exit;
      }
*/      
      if (!$result) {
        echo "Could not successfully run query from DB: " . mysql_error();
        exit;
      }
	
	 if (mysqli_num_rows($result) == 0) {
	    return "No missing cards found";
	    exit;
	 }
	 
    $lcHeader = "<TR><TD>Date</TD<TD>Empl. #</TD><TD>Name</TD></TR>\n";
    $lcLine = '';
    $lcDate = '';
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row["date"] != $lcDate) {
	      $lcLine .= "<TR><TD>".$row["date"]."</TD></TR>\n";
	      $lcDate = $row["date"];
		}
	    $lcLine .= "<TR><TD>&nbsp;</TD><TD>".$row["emplno"]."</TD><TD>".$row["empname"]."</TD></TR>\n";
    }
	
    $lcFinal = "<TABLE>\n".$lcHeader.$lcLine."</TABLE>\n";
    
	return $lcFinal;
}

function checksupes($lcSupes) {
  if ($lcSupes == '') {
    $lcOutput4 = "No supervisors to show!|";
    return $lcOutput4;
    break;
  } else {
    $supes = split(",", $lcSupes);
//    $lcOutput2 = $supes;
  
    global $mysqli;    
    foreach ($supes as $supe) {
      $lcExe = "SELECT uid FROM clients WHERE uid = '".$supe."' GROUP BY uid ";  
	  $result = $mysqli->query($lcExe) or die ("QUERY PROBLEM: ".$lcExe);
  
      if (!$result) {
        echo "Could not successfully run query from DB: " . mysql_error();
        exit;
      }

	  if (mysqli_num_rows($result) == 0) {
        if (!$lcnofind) {
	      $lcnofind = array($supe);
        } else {
		  $lcnofind[sizeof($lcnofind)] = $supe;
  	    }
	  } else {
        if (!$lcfind) {
	      $lcfind = array($supe);
        } else {
		  $lcfind[sizeof($lcfind)] = $supe;
  	    }
	  }
    }
  }

// If no supes good, jump to error message
  if (!$lcfind) {
	$lcOutput2 = "Sorry, but NONE of the Supervisors entered are valid.|";
  } else {
	if ($lcnofind) { 
     $lcOutput1 = '<br>No Find List: <br>'; 
     $lcOutput3 = '';
     foreach ($lcnofind as $supe) {
       $lcOutput3 .= "Supervisor not found: ".$supe."<br>";
     }
     $lcOutput2 = $lcOutput1.$lcOutput3;
    }
    $lcFindlist .= ''; 
	if ($lcfind) { 
     foreach ($lcfind as $supe) {
      $lcFindlist .= "'".trim($supe)."',";
     }
     if ($lcFindlist != '') {
	   $lcFindlist = rtrim($lcFindlist,",");
     }
    }
  }    
   return $lcOutput2."|".$lcFindlist;
}

  
function findJob($jobcode, $jobs) {
	$job = null;
	foreach($jobs as $j) {
		if($j['jobcode']==$jobcode) {
			$job = $j;
			break;
		}
	}
	if(!$job)
		$job = array('jobcode' => '', 'pay_meth' => '', 'task_descr'  => '');
	return $job;
}

/*function findEmp($emplno, $employees) {
	$emp = null;
	foreach($employees as $e) {
		if($e['emplno'] == $emplono) {
			$emp = $e;
			break;
		}
	}
	if(!$emp)
		$emp = ('empname' => '', */

function hours($in, $out) {
	if($in === '' || $out === '')
		return 0;
	$in = (int)$in;
	$out = (int)$out;
	$in = floor($in/100) + ($in%100)/60.0;
	$out = floor($out/100) + ($out%100)/60.0;
	return $out - $in;
}



function reportByEmps($uid, $sorttype, $emp=null) {
	$cardWidths = array  (14, 17, 15, 18, 59, 19, 13, 13, 28); //total 196
	$cardBorder = array ('T','T','T','T','T','T','T','T','T');
	$cardStyle = array('', '', '', '', '', '', '', '', '');
	$cardMaxlines = array(1,1,1,1,1,1,1,1,1);
	$cardAlign = array('R', 'R', 'R', 'R', 'L', 'R', 'R', 'L', 'L');
	$headerStyle = array('B','B','B','B','B','B','B','B','B');
	$headerBorder = array('TB', 'TB', 'TB', 'TB', 'TB', 'TB', 'TB', 'TB', 'TB');
	$headerAlign = array('R', 'R', 'R', 'R', 'L', 'R', 'R', 'L', 'L');
	$dateWidths = array(31,165);
	$dateAlign = array('L','L');
	$dateBorder = array('B','B');

	$totalWidths = array(46,77,32,41);
	$totalAlign = array('R','R','R','R');
	$totalBorder = array('T','T','T','T');

	$empWidths = array(46, 77);
	$empAlign = array('L', 'L');
	$empBorder = array('', '');

	//$empty = array('','','','','','');
	$header = array('Timein', 'Timeout', 'Hours', 'Jobcode', 'Jobdesc', 'PayMeth', 'Units', 'WO#', 'Errors');

	$jobs = getJobs($uid, 'jobcode', 'asc');
	if($sorttype==1)
		$trans = getTransactions($uid, 'empname, date, time_in, time_out', 'asc');
	else
		$trans = getTransactions($uid, 'emplno, date, time_in, time_out', 'asc');

	
	
	$emplno = null;
	$empname = '';
	$date = null;
	$hours_date = 0;
	$hours_emp = 0;
	$hours_grand = 0;
	$units_date = 0;
	$units_emp = 0;
	$units_grand = 0;
	$pdf = new PDF_FancyRow('P','mm','Letter');
	$pdf->AddPage();
	$pdf->SetFont('Arial', 'B', 14);
	if($sorttype==1)
		$pdf->Write(14,'4Data Remote Data Entry checking report by Employee Name');
	else
			$pdf->Write(14,'4Data Remote Data Entry checking report by Employee number');
	$pdf->Ln(20);
	foreach($trans as $t) {
		if($emp!=null && $emp != $t['emplno'])
			continue;
		if($emplno !== $t['emplno']) {
			if($date != null) {
				$pdf->SetFont('Arial', '', 10);
				$pdf->SetWidths($totalWidths);
				$pdf->Fancyrow(array(number_format($hours_date,3), "Subtotal for $date:", "$units_date", ""), $totalBorder, $totalAlign);
				$pdf->Ln(7);
			}
			if($emplno!==null) {
				//print total
				$pdf->SetFont('Arial', '', 10);
				$pdf->SetWidths($totalWidths);
				$pdf->Fancyrow(array(number_format($hours_emp,3), "Subtotal total for $emplno: $empname", "$units_emp", ""), $totalBorder, $totalAlign);
				$pdf->Ln(12);
			}
			$emplno = $t['emplno'];
			$empname = $t['empname'];
			//print employee header
			$pdf->SetFont('Arial', 'B', 14);
			$pdf->SetWidths($empWidths);
			$pdf->FancyRow(array("Emplno $emplno", $t['empname']), $empBorder, $empAlign);
			//start new section
			$pdf->Ln(1);
			$hours_emp = 0;
			$units_emp = 0;
			$emplno = $t['emplno'];
			$date = null;
		}
		if($date !== $t['date']) {
			if($date!==null) {
				//print subtotal
				$pdf->SetFont('Arial', '', 10);
				$pdf->SetWidths($totalWidths);
				$pdf->Fancyrow(array(number_format($hours_date,3), "Subtotal for $date:", "$units_date", ""), $totalBorder, $totalAlign);
				$pdf->Ln(7);
			}
			$date = $t['date'];
			//print date header
			$pdf->SetFont('Arial', 'B', 14);
			$pdf->SetWidths($dateWidths);
			$pdf->FancyRow(array('', "Workdate: $date"), $dateBorder, $dateAlign);
			//start new section
			$pdf->Ln(1);
			$hours_date = 0;
			$units_date = 0;
			$date = $t['date'];
			$pdf->SetFont('Arial', '', 10);
			$pdf->SetWidths($cardWidths);
			$pdf->FancyRow($header, $headerBorder, $cardAlign, $headerStyle);
			$pdf->Ln(1);
		}
		$job = findJob($t['jobcode'], $jobs);
		$hours = hours($t['time_in'], $t['time_out']);
		$hours_date += $hours;
		$hours_emp += $hours;
		$hours_grand += $hours;
		$units_date += $t['units'];
		$units_emp += $t['units'];
		$units_grand += $t['units'];

		$rows = array($t['time_in'], $t['time_out'], number_format($hours,3), $t['jobcode'], $job['task_descr'], $job['pay_meth'],$t['units'], $t['workorder'], $t['error']);
//SELECT tid, date, time_in, time_out, transactions.trainer, transactions.emplno, empname, jobcode, units, workorder, error, sent
		$pdf->FancyRow($rows, $cardBorder, $cardAlign, $cardStyle, $cardMaxlines);
	}
	//subtotal for final section
	$pdf->SetFont('Arial', '', 10);
	$pdf->SetWidths($totalWidths);
	$pdf->Fancyrow(array(number_format($hours_date,3), "Subtotal for $date:", "$units_date", ""), $totalBorder, $totalAlign);
	$pdf->Ln(7);
	//total for final section
	$pdf->Fancyrow(array(number_format($hours_emp,3), "Subtotal total for $emplno: $empname", "$units_emp", ""), $totalBorder, $totalAlign);
	$pdf->Ln(7);
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Fancyrow(array(number_format($hours_grand,3), "Grand Total:", "$units_grand", ""), array('','','',''), $totalAlign);
	$pdf->Output('report.pdf', 'I');
}




function reportByJobs($uid, $emp=null) {
	$cardWidths = array (10, 83, 13, 13, 23, 13, 13, 28); //adds to 196
	$cardBorder = array ('','','','','','','','');
	$cardStyle = array('', '', '', '', '', '', '', '');
	$cardMaxlines = array(1,1,1,1,1,1,1,1);
	$cardAlign = array('L', 'L', 'L', 'L', 'R', 'R', 'L', 'L');

	$headerWidths = array(93,13,13,23,13,13,28);
	$headerStyle = array('B','B','B','B','B','B','B');
	$headerBorder = array('TB', 'TB', 'TB', 'TB', 'TB', 'TB', 'TB');
	$headerAlign = array('L','L','L','R','R','L','L');

	$dateWidths = array(31,165);
	$dateAlign = array('L','L');
	$dateBorder = array('','');

	$totalWidths = array(35,84,23,13,39);
	$totalAlign = array('L','L','R','R','L');
	$totalBorder = array('','','','','');
	$totalBorderJob = array('T','T','T','T','T');


	$jobWidths = array(50, 10, 136);
	$jobAlign = array('L', 'L', 'L');
	$jobBorder = array('', '', '');

	//$empty = array('','','','','','');
	$header = array('Empl Name', 'In', 'Out', 'Hours', 'Units', 'WO#', 'Errors');

	$jobs = getJobs($uid, 'jobcode', 'asc');
	$trans = getTransactions($uid, 'jobcode, date, time_in, time_out, empname', 'asc');
	
	$jobcode = null;
	$job = findJob("", $jobs);
	$date = null;
	$hours_date = 0;
	$hours_job = 0;
	$hours_grand = 0;
	$units_date = 0;
	$units_job = 0;
	$units_grand = 0;
	$pdf = new PDF_FancyRow('P','mm','Letter');
	$pdf->AddPage();
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Write(14,'4Data Remote Data Entry checking report by Job Code');
	$pdf->Ln(20);
	foreach($trans as $t) {
		if($emp!=null && $emp != $t['emplno'])
			continue;
		if($jobcode !== $t['jobcode']) {
			if($date != null) {
				$pdf->SetFont('Arial', '', 10);
				$pdf->SetWidths($totalWidths);
				$pdf->Fancyrow(array('',"Subtotal for $date:", number_format($hours_date,3), "$units_date", ""), $totalBorder, $totalAlign);
				$pdf->Ln(7);
			}
			if($jobcode!==null) {
				//print total
				$pdf->SetFont('Arial', '', 10);
				$pdf->SetWidths($totalWidths);
				$pdf->Fancyrow(array('',"Subtotal for $jobcode: {$job['task_descr']}", number_format($hours_job,3), "$units_job", ""), $totalBorderJob, $totalAlign);
				$pdf->Ln(12);
			}
			$jobcode = $t['jobcode'];
			$job = findJob($jobcode, $jobs);
			//print job header
			$pdf->SetFont('Arial', 'B', 14);
			$pdf->SetWidths($jobWidths);
			$pdf->FancyRow(array("Jobcode $jobcode", $job['pay_meth'], $job['task_descr']), $jobBorder, $jobAlign);
			//start new section
			$pdf->Ln(1);
			$pdf->SetFont('Arial', '', 10);
			$pdf->SetWidths($headerWidths);
			$pdf->FancyRow($header, $headerBorder, $headerAlign, $headerStyle);

			$hours_job = 0;
			$units_job = 0;
			$date = null;
		}
		if($date !== $t['date']) {
			if($date!==null) {
				//print subtotal
				$pdf->SetFont('Arial', '', 10);
				$pdf->SetWidths($totalWidths);
				$pdf->Fancyrow(array('',"Subtotal for $date:", number_format($hours_date,3), "$units_date", ""), $totalBorder, $totalAlign);
				$pdf->Ln(7);
			}
			$date = $t['date'];
			//print date header
			$pdf->SetFont('Arial', 'B', 14);
			$pdf->SetWidths($dateWidths);
			$pdf->FancyRow(array('', "Workdate: $date"), $dateBorder, $dateAlign);
			//start new section
			$pdf->Ln(1);
			$hours_date = 0;
			$units_date = 0;
			$date = $t['date'];
			//prepare for rows
			$pdf->SetFont('Arial', '', 10);
			$pdf->SetWidths($cardWidths);
			$pdf->Ln(1);
		}
		$hours = hours($t['time_in'], $t['time_out']);
		$hours_date += $hours;
		$hours_job += $hours;
		$hours_grand += $hours;
		$units_date += $t['units'];
		$units_job += $t['units'];
		$units_grand += $t['units'];

		$rows = array($t['emplno'], $t['empname'], $t['time_in'], $t['time_out'], number_format($hours,3), $t['units'], $t['workorder'], $t['error']);
//SELECT tid, date, time_in, time_out, transactions.trainer, transactions.emplno, empname, jobcode, units, workorder, error, sent
		$pdf->FancyRow($rows, $cardBorder, $cardAlign, $cardStyle, $cardMaxlines);
	}
	//subtotal for final section
	$pdf->SetFont('Arial', '', 10);
	$pdf->SetWidths($totalWidths);
	$pdf->Fancyrow(array('',"Subtotal for $date:", number_format($hours_date,3), "$units_date", ""), $totalBorder, $totalAlign);
	$pdf->Ln(7);
	//total for final section
	$pdf->Fancyrow(array('',"Subtotal for $jobcode: {$job['task_descr']}", number_format($hours_job,3), "$units_job", ""), $totalBorderJob, $totalAlign);
	$pdf->Ln(7);
	$pdf->SetFont('Arial', 'B', 10);
	$pdf->Fancyrow(array('',"Grand Total:", number_format($hours_grand,3), "$units_grand", ""), array('','','','',''), $totalAlign);



	$pdf->Output('report.pdf','I');
}
