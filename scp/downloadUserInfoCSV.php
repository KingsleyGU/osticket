<?php
require('staff.inc.php');

$filename = "users.csv";
$fp = fopen($filename, "w");

$filePath = $filename;
$fsize = filesize($filename);

//put the latest data into csv file
// error_reporting(~0); ini_set('display_errors', 1);
header('Content-Type: application/csv;charset=utf-8');
header('Content-Disposition: attachment; filename='.basename($filename));
fputcsv($fp,   array('username','firstname','lastname','isadmin','onvacation','created','lastlogin'));

if($userInfoArray = Staff::getStaffCSVFile())
{
	// echo json_encode($userInfoArray);
	foreach ($userInfoArray as $fields) {
	fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($fp, $fields);
	}

}

fclose($fp);


echo "\xEF\xBB\xBF"; 
 readfile($filename);

exit;
?>