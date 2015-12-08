<?php 
require('client.inc.php');
// require_once(dirname(__file__).'/bootstrap.php');
// include_once(INCLUDE_DIR.'mysqli.php');
	// changeTicketTable("orderNumber","string");
	// changeTicketTable("cvr_number","string");
	// changeTicketTable("cvr_number","string");
	// changeTicketTable("company_name","string");
	// changeTicketTable("user_agent","string");
	// changeTicketTable("crm_subject1_id","int");
	// changeTicketTable("crm_subject2_id","int");
	// changeTicketTable("activity_id","int");
	// changeTicketTable("crm_activity_code","string");
	// changeTicketTable("business_form_id","int");
	// addCRMSubject1();
	// addCRMSubject2();
	// addCRMActivity();
	// addCRMFile();
	addCRMActivity();
	function addCRMSubject1()
	{
		$sql = 'CREATE TABLE ost_ticket_crm_subject1 (
		id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		crm_subject1_reference_id int(11) NOT NULL,
		url VARCHAR(255),
		crm_subject1_text text
		)';
		if(!($res=db_query($sql)) || !db_num_rows($res))
	    	echo "add the table ost_ticket_crm_subject1 to the database";
		else
			echo "can not add the table ost_ticket_crm_subject1 to the database";
	}
	function addCRMSubject2()
	{
		$sql = 'CREATE TABLE ost_ticket_crm_subject2 (
		id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		crm_subject2_reference_id int(11) NOT NULL,
		cvr_rule VARCHAR(10),
		order_rule VARCHAR(10),
		title_rule VARCHAR(10),
		crm_subject2_text text
		)';
		if(!($res=db_query($sql)) || !db_num_rows($res))
	    	echo "add the table ost_ticket_crm_subject2 to the database";
		else
			echo "can not add the table ost_ticket_crm_subject2 to the database";
	}
	function addCRMActivity()
	{
		$sql = 'CREATE TABLE ost_ticket_crm_activity (
		activity_code VARCHAR(255),
		activity_description VARCHAR(255) 
		)';
		if(!($res=db_query($sql)) || !db_num_rows($res))
	    	echo "add the table ost_ticket_crm_activity to the database";
		else
			echo "can not add the table ost_ticket_crm_activity to the database";
	}
	function addCRMFile()
	{
		$sql = 'CREATE TABLE ost_ticket_crm_files (
		crm_file_reference_id int(11) NOT NULL,
		ost_ticket_id int(11) NOT NULL,
		name VARCHAR(255),
		mime VARCHAR(255),
		url VARCHAR(255),
		created datetime
		)';
		if(!($res=db_query($sql)) || !db_num_rows($res))
	    	echo "add the table ost_ticket_crm_files to the database";
		else
			echo "can not add the table ost_ticket_crm_files to the database";
	}	
	function changeTicketTable($fieldName,$type)
	{
		$fieldType = 'int(11)';
		if($type =='string')
			$fieldType = 'VARCHAR(255)';
		$sql = 'ALTER TABLE `osticket`.`ost_ticket` ADD COLUMN `'.$fieldName.'` '.$fieldType;
		if(!($res=db_query($sql)) || !db_num_rows($res))
	    	echo "add the field ".$fieldName." to table ost_ticket";
		else
			echo "can not add the field ".$fieldName." to table ost_ticket";
	}
?>