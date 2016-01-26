<?php
require('staff.inc.php');

$filename = "users.csv";
$fp = fopen($filename, "w");
// fileName = "file\customer-list.csv";
$filePath = $filename;
$fsize = filesize($filename);

//put the latest data into csv file
error_reporting(~0); ini_set('display_errors', 1);
// $fp = fopen('users.csv', 'w');
fputcsv($fp,   array('id','org_id','default_email_id','status','name','created','updated'));

if($userInfoArray = User::getUsersCSVFile())
{
	// echo json_encode($userInfoArray);
	foreach ($userInfoArray as $fields) {
    fputcsv($fp, $fields);
	}

}

// fclose($fp);
// header('Content-Description: File Transfer');
// header('Content-Encoding: UTF-8');
// header('Content-Type: application/csv;charset=utf-8');
// header('Content-Disposition: attachment; filename='.basename($filename));
// header('Expires: 0');
// header('Cache-Control: must-revalidate');
// header('Pragma: public');
// header('Content-Length: ' . filesize($filename));
// echo "\xEF\xBB\xBF"; 
// ob_clean();

// echo "\xEF\xBB\xBF"; // UTF-8 BOM
// echo file_get_contents($filename);
header('Location: $fileName');
exit;
?>