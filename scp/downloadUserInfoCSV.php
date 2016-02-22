<?php
require('staff.inc.php');

$filename = "users.csv";
$fp = fopen($filename, "w");
// fileName = "file\customer-list.csv";
$filePath = $filename;
$fsize = filesize($filename);

//put the latest data into csv file
// error_reporting(~0); ini_set('display_errors', 1);
// $fp = fopen('users.csv', 'w');
fputcsv($fp,   array('username','firstname','lastname','isadmin','onvacation','created','lastlogin'));

if($userInfoArray = Staff::getStaffCSVFile())
{
	// echo json_encode($userInfoArray);
	foreach ($userInfoArray as $fields) {
	fprintf($df, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($fp, $fields);
	}

}


fclose($fp);
// header('Content-Description: File Transfer');
// header('Content-Encoding: UTF-8');
header('Content-Type: application/csv;charset=utf-8');
header('Content-Disposition: attachment; filename='.basename($filename));
// header('Expires: 0');
// header('Cache-Control: must-revalidate');
// header('Pragma: public');
// header('Content-Length: ' . filesize($filename));
echo "\xEF\xBB\xBF"; 
// ob_clean();
flush();

// //read the file from disk and output the content.
// // echo "\xEF\xBB\xBF"; // UTF-8 BOM
 readfile($filename);

// exit;
?>