<?php
/*********************************************************************
    class.spent_time.php

    This class is mainly for keeping record of the time spent on a ticket;

    Min Gu <gumin@spitzeco.dk>

**********************************************************************/
class Spent_time{
	var $ticketId;
	var $staffId;
	var $created;
	var $currentTime;
	var $spent_time;

	function create($staffId,$ticketId,$created)
	{
		if(is_numeric($staffId)&&is_numeric($ticketId))
		{
			if ($created instanceof DateTime) {
				$currentTime = new DateTime();
				$interval = $currentTime->diff($created);
				$elapsed = $interval->format('%y years %m months %a days %h hours %i minutes %S seconds');
				$sql = 'INSERT INTO '.SPENT_TIME_TABLE
	            .' SET ticket_id='.db_input($ticketId)
	            .', staff_id='.db_input($staffId)
	            .', created='.db_input($created)
	            .', ended='.db_input($currentTime)
	            .', seconds='.db_input($interval);
				
				return return db_query($sql)&&db_affected_rows() == 1;
			}			
		}
		return false;
	}
}



?>