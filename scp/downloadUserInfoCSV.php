<?php
require('staff.inc.php');

$filename = "/var/log/users.csv";
// echo $filename;
// echo "existance: ".file_exists($filename);
$fp = fopen($filename, "w") or die('Unable to open file!');;

$filePath = $filename;
$fsize = filesize($filename);

//put the latest data into csv file
// error_reporting(~0); ini_set('display_errors', 1);
// ini_set('auto_detect_line_endings',TRUE);
header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT');
header('Accept-Ranges: bytes');  // Allow support for download resume
header('Content-Length: ' . filesize($filePath));  // File size
header('Content-Type: application/csv;charset=utf-8');
header('Content-Disposition: attachment; filename='.basename($filename));
fputcsv($fp,   array('username','firstname','lastname','isadmin','onvacation','created','lastlogin','teams'));

if($userInfoArray = Staff::getStaffCSVFile())
{
	// echo json_encode($userInfoArray);
	foreach ($userInfoArray as $fields) {
		// echo json_encode(array($fields['username'],$fields['firstname'],$fields['lastname'],$fields['isadmin'],$fields['onvacation'],$fields['created'],$fields['lastlogin'],Staff::getStaffTeams($fields['staff_id'])));
	fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
	// fputcsv($fp,$fields);
	$teams = Staff::getStaffTeams($fields['staff_id']);
	// echo $teams;
	fputcsv($fp,array($fields['username'],$fields['firstname'],$fields['lastname'],$fields['isadmin'],$fields['onvacation'],$fields['created'],$fields['lastlogin'],"æanjå");
    // fputcsv($fp, array_merge(array($fields['username'],$fields['firstname'],$fields['lastname'],$fields['isadmin'],$fields['onvacation'],$fields['created'],$fields['lastlogin']),$teams));
	}

}

fclose($fp);

ini_set('auto_detect_line_endings',TRUE);
echo "\xEF\xBB\xBF"; 
header('Content-Type: application/csv;charset=utf-8');
header('Content-Disposition: attachment; filename='.basename($filename));
readfile($filename);

exit;
?>