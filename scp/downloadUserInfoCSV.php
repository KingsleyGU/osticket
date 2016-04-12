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
ini_set('auto_detect_line_endings',TRUE);
header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT');
header('Accept-Ranges: bytes');  // Allow support for download resume
header('Content-Length: ' . filesize($filePath));  // File size
header('Content-Type: application/csv;charset=utf-8');
header('Content-Disposition: attachment; filename='.basename($filename));
$titleArray =  array('username','firstname','lastname','isadmin','onvacation','created','lastlogin');
$teamsArray = Team::getActiveTeams();
foreach ($teamsArray as $key => $value) {
	array_push($titleArray,$value);
}
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($fp, $titleArray);


function logErrors($errorMessage)
{
    $logFilePath = "/var/log/osticket_download_user_log";
    $timestamp = date("Y-m-d_H:i:s");
    error_log($timestamp.": ".$errorMessage."\n", 3, $logFilePath);
}
function booleanToString($bool)
{
	if($bool)
		return "Yes";
	else
		return "No";
}
if($userInfoArray = Staff::getStaffCSVFile())
{
	// echo json_encode($userInfoArray);
	foreach ($userInfoArray as $fields) {
			// echo json_encode(array($fields['username'],$fields['firstname'],$fields['lastname'],$fields['isadmin'],$fields['onvacation'],$fields['created'],$fields['lastlogin'],Staff::getStaffTeams($fields['staff_id'])));
		fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
		// fputcsv($fp,$fields);
		$teams = Staff::getStaffTeams($fields['staff_id']);
		// logErrors(json_encode(Team::getActiveTeams()));
		// echo $teams;
		html_entity_decode(mb_convert_encoding(stripslashes($teams), "HTML-ENTITIES", 'UTF-8'));
		try {
			$resultArray = array($fields['username'],$fields['firstname'],$fields['lastname'],$fields['isadmin'],$fields['onvacation'],$fields['created'],$fields['lastlogin']);
			foreach ($teamsArray as $key => $value) {
				logErrors("team id: ".$key);
				if($team = Team::lookup(intval($key)))
				{
					// logErrors("staff id: ".$fields['staff_id']);
					array_push($resultArray,booleanToString($team->hasMember(Staff::lookup(intval($fields['staff_id'])))));	

				}
			}
			logErrors(json_encode($resultArray));
			fputcsv($fp,$resultArray);
	    // fputcsv($fp, array_merge(array($fields['username'],$fields['firstname'],$fields['lastname'],$fields['isadmin'],$fields['onvacation'],$fields['created'],$fields['lastlogin']),null));
		
		} catch (Exception $e) {
			logErrors('Caught exception: ',  $e->getMessage(), "\n");
		}
	}

}

fclose($fp);

// ini_set('auto_detect_line_endings',TRUE);
echo "\xEF\xBB\xBF"; 
header('Content-Length: ' . filesize($filePath));
header('Content-Type: application/csv;charset=utf-8');
header('Content-Disposition: attachment; filename='.basename($filename));
echo file_get_contents($filename);
// readfile($filename);
ini_set('auto_detect_line_endings',FALSE);
exit;
?>